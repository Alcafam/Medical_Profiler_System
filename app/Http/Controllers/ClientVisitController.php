<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\FormField;
use App\Models\Medicine;
use App\Models\Station;
use App\Models\Visit;
use App\Services\ClientFieldValueService;
use App\Services\VisitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientVisitController extends Controller
{
    public function show(Client $client): View
    {
        $client->load([
            'visits' => fn ($q) => $q->with([
                'creator:id,name',
                'fieldValues.formField',
                'medicineRecommendations.medicine',
                'medicineDispenses.medicine',
            ])->orderByDesc('visited_at')->orderByDesc('id'),
            'latestVisit.fieldValues.formField',
        ]);

        return view('clients.show', compact('client'));
    }

    public function store(Request $request, Client $client, VisitService $visits): RedirectResponse
    {
        $visit = $visits->createForClient($client, $request->user(), copyIdentityFromLatest: true);

        return redirect()
            ->route('clients.visits.encode', [
                'client' => $client,
                'visit' => $visit,
                ...$request->user()->encodeStationQuery(),
            ])
            ->with('status', 'New visit created. Identity fields copied from the previous visit.');
    }

    public function encode(Request $request, Client $client, Visit $visit): View
    {
        abort_unless($visit->client_id === $client->id, 404);

        $user = $request->user();

        $stations = Station::query()
            ->where('is_active', true)
            ->with(['formFields' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        $unassigned = FormField::query()
            ->whereNull('station_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $visit->load([
            'fieldValues.editor',
            'fieldValues.formField',
            'client',
            'medicineRecommendations.medicine',
            'medicineRecommendations.recommender:id,name',
            'medicineDispenses.medicine',
            'medicineDispenses.dispenser:id,name',
        ]);

        $historyVisits = $client->visits()
            ->with([
                'fieldValues.formField',
                'medicineRecommendations.medicine',
                'medicineDispenses.medicine',
            ])
            ->orderByDesc('visited_at')
            ->orderByDesc('id')
            ->get();

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
            'patient_condition',
        ];

        $previewLabels = [
            'client_type' => 'Patient Type',
            'department' => 'Department',
            'last_name' => 'Last Name',
            'first_name' => 'First Name',
            'date_of_birth' => 'Date of Birth',
            'sex' => 'Sex',
            'height_cm' => 'Height (cm)',
            'weight_kg' => 'Weight (kg)',
            'temperature' => 'Temperature (Celsius)',
            'heart_rate_bpm' => 'Heart Rate (BPM)',
            'spo2' => 'SPO2',
            'respiratory_rate' => 'Respiratory Rate',
            'systolic' => 'Systolic',
            'diastolic' => 'Diastolic',
            'cbg' => 'CBG',
            'fasting_state' => 'Fasting State',
            'history' => 'History',
            'current_conditions' => 'Current Conditions',
            'current_medications' => 'Current Medications',
            'allergies' => 'Allergies',
            'notes' => 'Notes',
            'patient_condition' => 'Patient Condition',
        ];

        $defaultStationId = $user->isEncoder() && $user->station_id
            ? (int) $user->station_id
            : $stations->first()?->id;

        $activeStationId = $request->integer('station') ?: $defaultStationId;

        $medicineOptions = Medicine::query()
            ->active()
            ->orderBy('generic_name')
            ->orderBy('brand_name')
            ->get()
            ->map(fn (Medicine $medicine) => $medicine->toPickerArray())
            ->values()
            ->all();

        $recommendedMedicines = $visit->medicineRecommendations->map(fn ($row) => [
            'id' => $row->id,
            'medicine_id' => $row->medicine_id,
            'label' => $row->medicine?->displayLabel(),
            'quantity' => $row->quantity,
            'instructions' => $row->instructions,
            'by' => $row->recommender?->name,
            'expiration_label' => $row->medicine?->expirationLabel(),
            'expiry_status' => $row->medicine?->expiryStatus(),
        ])->values()->all();

        $dispensedMedicines = $visit->medicineDispenses->map(fn ($row) => [
            'id' => $row->id,
            'medicine_id' => $row->medicine_id,
            'label' => $row->medicine?->displayLabel(),
            'quantity' => $row->quantity,
            'remarks' => $row->remarks,
            'by' => $row->dispenser?->name,
            'expiration_label' => $row->medicine?->expirationLabel(),
            'expiry_status' => $row->medicine?->expiryStatus(),
            'quantity_remaining' => $row->medicine?->quantityRemaining(),
        ])->values()->all();

        return view('clients.encode', [
            'client' => $client,
            'visit' => $visit,
            'historyVisits' => $historyVisits,
            'previewSlugs' => $previewSlugs,
            'previewLabels' => $previewLabels,
            'stations' => $stations,
            'unassigned' => $unassigned,
            'activeStationId' => $activeStationId,
            'values' => $visit->fieldValues->keyBy('form_field_id'),
            'medicineOptions' => $medicineOptions,
            'recommendedMedicines' => $recommendedMedicines,
            'dispensedMedicines' => $dispensedMedicines,
        ]);
    }

    public function updateConsultationQueue(Request $request, Client $client, Visit $visit): JsonResponse
    {
        abort_unless($visit->client_id === $client->id, 404);

        $data = $request->validate([
            'queued' => ['required', 'boolean'],
        ]);

        if ($request->boolean('queued')) {
            $visit->queueForConsultation();
        } else {
            if (! $visit->removeFromConsultationQueue()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot remove from queue after a completed disposition.',
                ], 422);
            }
        }

        $visit->refresh();

        return response()->json([
            'status' => 'saved',
            'queued' => $visit->isQueuedForConsultation(),
            'disposition' => $visit->disposition?->value,
            'queued_at' => optional($visit->queued_for_consultation_at)?->toDateTimeString(),
        ]);
    }

    public function saveField(
        Request $request,
        Client $client,
        Visit $visit,
        FormField $field,
        ClientFieldValueService $service,
    ): JsonResponse {
        abort_unless($visit->client_id === $client->id, 404);

        $data = $request->validate([
            'value' => ['nullable', 'string'],
            'version' => ['nullable', 'integer', 'min:0'],
            'force' => ['nullable', 'boolean'],
            'station_id' => ['nullable', 'integer'],
        ]);

        $user = $request->user();
        $service->assertCanEdit($user, $field);

        $result = $service->save(
            visit: $visit,
            field: $field,
            user: $user,
            value: $data['value'] ?? null,
            expectedVersion: $data['version'] ?? null,
            force: (bool) ($data['force'] ?? false),
        );

        if ($result['status'] === 'conflict') {
            return response()->json([
                'status' => 'conflict',
                'conflict' => $result['conflict'],
            ], 409);
        }

        $value = $result['value'];

        return response()->json([
            'status' => 'saved',
            'value' => $value->value,
            'version' => $value->version,
            'updated_by' => $value->editor?->name,
            'updated_at' => optional($value->updated_at)?->diffForHumans(),
        ]);
    }
}
