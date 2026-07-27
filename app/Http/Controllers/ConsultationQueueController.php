<?php

namespace App\Http\Controllers;

use App\Enums\VisitDisposition;
use App\Models\Client;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConsultationQueueController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing('station');
        abort_unless($user->isConsultationEncoder(), 403);

        $tab = $request->input('tab', 'active') === 'completed' ? 'completed' : 'active';

        $query = Visit::query()
            ->with(['client', 'fieldValues.formField'])
            ->when(
                $tab === 'active',
                fn ($q) => $q->consultationActive()->orderBy('queued_for_consultation_at'),
                fn ($q) => $q->consultationCompleted()->orderByDesc('disposition_at'),
            );

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder->whereHas('client', fn ($clientQuery) => $clientQuery->where('system_id', 'like', "%{$search}%"))
                    ->orWhereHas('fieldValues', function ($valueQuery) use ($search) {
                        $valueQuery->where('value', 'like', "%{$search}%")
                            ->whereHas('formField', fn ($fieldQuery) => $fieldQuery->whereIn('slug', [
                                'last_name',
                                'first_name',
                            ]));
                    });
            });
        }

        $visits = $query->paginate(30)->withQueryString();

        $activeCount = Visit::query()->consultationActive()->count();
        $completedCount = Visit::query()->consultationCompleted()->count();

        return view('clients.consultation-queue', [
            'visits' => $visits,
            'tab' => $tab,
            'search' => $search,
            'activeCount' => $activeCount,
            'completedCount' => $completedCount,
            'dispositions' => VisitDisposition::cases(),
        ]);
    }

    public function updateDisposition(Request $request, Client $client, Visit $visit): JsonResponse
    {
        abort_unless($visit->client_id === $client->id, 404);

        $user = $request->user();
        abort_unless($user->isEncoder() || $user->isAdmin() || $user->isSuperAdmin(), 403);

        $data = $request->validate([
            'disposition' => ['required', Rule::enum(VisitDisposition::class)],
        ]);

        $disposition = VisitDisposition::from($data['disposition']);

        if (! $visit->isQueuedForConsultation()) {
            $visit->queueForConsultation();
        }

        $visit->setDisposition($disposition);

        return response()->json([
            'status' => 'saved',
            'disposition' => $disposition->value,
            'disposition_label' => $disposition->label(),
            'waiting_label' => $visit->fresh()->waitingLabel(),
        ]);
    }
}
