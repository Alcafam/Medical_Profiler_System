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

        Visit::clearExpiredConsultationLocks();

        $tab = $request->input('tab', 'active') === 'completed' ? 'completed' : 'active';

        $query = Visit::query()
            ->with(['client', 'fieldValues.formField', 'consultationLocker:id,name'])
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

        $initialLocks = $tab === 'active'
            ? $visits->getCollection()
                ->filter(fn (Visit $visit) => $visit->hasFreshConsultationLock())
                ->map(fn (Visit $visit) => [
                    'visit_id' => $visit->id,
                    'locked_by' => $visit->consultation_locked_by,
                    'locked_by_name' => $visit->consultationLocker?->name,
                    'locked_at' => $visit->consultation_locked_at?->toIso8601String(),
                    'is_mine' => (int) $visit->consultation_locked_by === (int) $user->id,
                ])
                ->values()
                ->all()
            : [];

        return view('clients.consultation-queue', [
            'visits' => $visits,
            'tab' => $tab,
            'search' => $search,
            'activeCount' => $activeCount,
            'completedCount' => $completedCount,
            'dispositions' => VisitDisposition::cases(),
            'currentUserId' => $user->id,
            'locksPollUrl' => route('consultation-queue.locks'),
            'initialLocks' => $initialLocks,
        ]);
    }

    public function locks(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadMissing('station');
        abort_unless($user->isConsultationEncoder(), 403);

        Visit::clearExpiredConsultationLocks();

        $locks = Visit::query()
            ->consultationActive()
            ->whereNotNull('consultation_locked_by')
            ->where('consultation_locked_at', '>=', now()->subSeconds(Visit::CONSULTATION_LOCK_SECONDS))
            ->with('consultationLocker:id,name')
            ->get()
            ->map(fn (Visit $visit) => [
                'visit_id' => $visit->id,
                'locked_by' => $visit->consultation_locked_by,
                'locked_by_name' => $visit->consultationLocker?->name,
                'locked_at' => $visit->consultation_locked_at?->toIso8601String(),
                'is_mine' => (int) $visit->consultation_locked_by === (int) $user->id,
            ])
            ->values();

        return response()->json(['locks' => $locks]);
    }

    public function heartbeat(Request $request, Client $client, Visit $visit): JsonResponse
    {
        abort_unless($visit->client_id === $client->id, 404);

        $user = $request->user();
        $user->loadMissing('station');
        abort_unless($user->isConsultationEncoder(), 403);

        if (! $visit->touchConsultationLock($user)) {
            $visit->loadMissing('consultationLocker:id,name');

            return response()->json([
                'status' => 'locked',
                'message' => 'This patient is being treated by '.$visit->consultationLocker?->name.'.',
                'locked_by_name' => $visit->consultationLocker?->name,
            ], 423);
        }

        return response()->json([
            'status' => 'ok',
            'locked_until' => now()->addSeconds(Visit::CONSULTATION_LOCK_SECONDS)->toIso8601String(),
        ]);
    }

    public function release(Request $request, Client $client, Visit $visit): JsonResponse
    {
        abort_unless($visit->client_id === $client->id, 404);

        $user = $request->user();
        $visit->releaseConsultationLock($user);

        return response()->json(['status' => 'released']);
    }

    public function updateDisposition(Request $request, Client $client, Visit $visit): JsonResponse
    {
        abort_unless($visit->client_id === $client->id, 404);

        $user = $request->user();
        abort_unless($user->isEncoder() || $user->isAdmin() || $user->isSuperAdmin(), 403);

        $data = $request->validate([
            'disposition' => ['required', Rule::enum(VisitDisposition::class)],
        ]);

        Visit::clearExpiredConsultationLocks();
        $visit->refresh();

        if ($user->isConsultationEncoder() && $visit->isConsultationLockedByOther($user)) {
            $visit->loadMissing('consultationLocker:id,name');

            return response()->json([
                'status' => 'locked',
                'message' => 'This patient is being treated by '
                    .($visit->consultationLocker?->name ?? 'another consultant')
                    .'.',
            ], 423);
        }

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
