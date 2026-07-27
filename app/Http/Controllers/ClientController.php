<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\FormField;
use App\Services\VisitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing('station');

        if ($user->isConsultationEncoder()) {
            return app(ConsultationQueueController::class)->index($request);
        }

        $query = Client::query()->with([
            'latestVisit.fieldValues.formField',
            'latestVisit.fieldValues.editor',
        ]);

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('system_id', 'like', "%{$search}%")
                    ->orWhereHas('visits.fieldValues', function ($valueQuery) use ($search) {
                        $valueQuery->where('value', 'like', "%{$search}%")
                            ->whereHas('formField', fn ($fieldQuery) => $fieldQuery->where('is_searchable', true));
                    });
            });
        }

        $lastNameFieldId = FormField::query()->where('slug', 'last_name')->value('id');
        $firstNameFieldId = FormField::query()->where('slug', 'first_name')->value('id');

        $latestVisitSql = '(select id from visits where client_id = clients.id order by visited_at desc, id desc limit 1)';

        if ($lastNameFieldId) {
            $query->orderBy(
                DB::table('client_field_values')
                    ->select('value')
                    ->whereRaw("visit_id = {$latestVisitSql}")
                    ->where('form_field_id', $lastNameFieldId)
                    ->limit(1)
            );
        }

        if ($firstNameFieldId) {
            $query->orderBy(
                DB::table('client_field_values')
                    ->select('value')
                    ->whereRaw("visit_id = {$latestVisitSql}")
                    ->where('form_field_id', $firstNameFieldId)
                    ->limit(1)
            );
        }

        $clients = $query->orderBy('clients.id')->paginate(20)->withQueryString();

        $isEncoder = $request->user()->isEncoder();

        $identitySlugs = $isEncoder
            ? ['last_name', 'first_name', 'date_of_birth', 'sex']
            : [
                'client_type',
                'department',
                'last_name',
                'first_name',
                'date_of_birth',
                'sex',
                'height_cm',
                'weight_kg',
                'temperature',
                'heart_rate_bpm',
                'spo2',
                'respiratory_rate',
                'systolic',
                'diastolic',
                'cbg',
                'fasting_state',
                'history',
                'current_conditions',
                'current_medications',
                'allergies',
                'notes',
            ];

        $previewSlugs = [
            'client_type',
            'department',
            'last_name',
            'first_name',
            'date_of_birth',
            'sex',
            'height_cm',
            'weight_kg',
            'temperature',
            'heart_rate_bpm',
            'spo2',
            'respiratory_rate',
            'systolic',
            'diastolic',
            'cbg',
            'fasting_state',
            'history',
            'current_conditions',
            'current_medications',
            'allergies',
            'notes',
        ];

        $clientTotalCount = $request->user()->canBulkCreateVisits()
            ? Client::query()->count()
            : null;

        return view('clients.index', compact(
            'clients',
            'identitySlugs',
            'previewSlugs',
            'search',
            'isEncoder',
            'clientTotalCount',
        ));
    }

    public function create(): View
    {
        return view('clients.create');
    }

    public function store(Request $request, VisitService $visits): RedirectResponse
    {
        $client = Client::query()->create([
            'system_id' => Client::generateSystemId(),
            'created_by' => $request->user()->id,
        ]);

        $visit = $visits->createForClient($client, $request->user(), copyIdentityFromLatest: false);

        return redirect()
            ->route('clients.visits.encode', [
                'client' => $client,
                'visit' => $visit,
                ...$request->user()->encodeStationQuery(),
            ])
            ->with('status', 'Client created. Begin encoding this visit.');
    }

    public function encodeRedirect(Client $client, VisitService $visits): RedirectResponse
    {
        $user = auth()->user();
        $visit = $visits->ensureLatestVisit($client, $user);

        return redirect()->route('clients.visits.encode', [
            'client' => $client,
            'visit' => $visit,
            ...$user->encodeStationQuery(),
        ]);
    }

    public function destroy(Client $client): RedirectResponse
    {
        abort_unless(auth()->user()->canSoftDeleteClients(), 403);

        $client->delete();

        return redirect()->route('clients.index')->with('status', 'Client deleted.');
    }
}
