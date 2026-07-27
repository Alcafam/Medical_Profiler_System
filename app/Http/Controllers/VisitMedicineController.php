<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Medicine;
use App\Models\Visit;
use App\Models\VisitMedicineDispense;
use App\Models\VisitMedicineRecommendation;
use App\Services\VisitMedicineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitMedicineController extends Controller
{
    public function storeRecommendation(
        Request $request,
        Client $client,
        Visit $visit,
        VisitMedicineService $service,
    ): JsonResponse {
        abort_unless($visit->client_id === $client->id, 404);

        $data = $request->validate([
            'medicine_id' => ['required', 'integer', 'exists:medicines,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'instructions' => ['nullable', 'string', 'max:500'],
        ]);

        $medicine = Medicine::query()->findOrFail($data['medicine_id']);
        $recommendation = $service->recommend(
            visit: $visit,
            medicine: $medicine,
            user: $request->user(),
            quantity: $data['quantity'] ?? null,
            instructions: $data['instructions'] ?? null,
        );

        $recommendation->load(['medicine', 'recommender:id,name']);

        return response()->json([
            'status' => 'saved',
            'item' => $this->recommendationPayload($recommendation),
        ]);
    }

    public function destroyRecommendation(
        Request $request,
        Client $client,
        Visit $visit,
        VisitMedicineRecommendation $recommendation,
        VisitMedicineService $service,
    ): JsonResponse {
        abort_unless($visit->client_id === $client->id, 404);
        abort_unless($recommendation->visit_id === $visit->id, 404);

        $service->removeRecommendation($recommendation);

        return response()->json(['status' => 'deleted']);
    }

    public function storeDispense(
        Request $request,
        Client $client,
        Visit $visit,
        VisitMedicineService $service,
    ): JsonResponse {
        abort_unless($visit->client_id === $client->id, 404);

        $data = $request->validate([
            'medicine_id' => ['required', 'integer', 'exists:medicines,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $medicine = Medicine::query()->findOrFail($data['medicine_id']);

        try {
            $dispense = $service->dispense(
                visit: $visit,
                medicine: $medicine,
                user: $request->user(),
                quantity: (int) $data['quantity'],
                remarks: $data['remarks'] ?? null,
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => collect($e->errors())->flatten()->first() ?? 'Unable to dispense.',
                'errors' => $e->errors(),
            ], 422);
        }

        $dispense->load(['medicine', 'dispenser:id,name']);
        $medicine->refresh();

        return response()->json([
            'status' => 'saved',
            'item' => $this->dispensePayload($dispense),
            'medicine' => $medicine->toPickerArray(),
        ]);
    }

    public function destroyDispense(
        Request $request,
        Client $client,
        Visit $visit,
        VisitMedicineDispense $dispense,
        VisitMedicineService $service,
    ): JsonResponse {
        abort_unless($visit->client_id === $client->id, 404);
        abort_unless($dispense->visit_id === $visit->id, 404);

        $medicineId = $dispense->medicine_id;
        $service->removeDispense($dispense);

        $medicine = Medicine::query()->find($medicineId);

        return response()->json([
            'status' => 'deleted',
            'medicine' => $medicine?->toPickerArray(),
        ]);
    }

    private function recommendationPayload(VisitMedicineRecommendation $recommendation): array
    {
        return [
            'id' => $recommendation->id,
            'medicine_id' => $recommendation->medicine_id,
            'label' => $recommendation->medicine?->displayLabel(),
            'quantity' => $recommendation->quantity,
            'instructions' => $recommendation->instructions,
            'by' => $recommendation->recommender?->name,
            'expiration_label' => $recommendation->medicine?->expirationLabel(),
            'expiry_status' => $recommendation->medicine?->expiryStatus(),
        ];
    }

    private function dispensePayload(VisitMedicineDispense $dispense): array
    {
        return [
            'id' => $dispense->id,
            'medicine_id' => $dispense->medicine_id,
            'label' => $dispense->medicine?->displayLabel(),
            'quantity' => $dispense->quantity,
            'remarks' => $dispense->remarks,
            'by' => $dispense->dispenser?->name,
            'expiration_label' => $dispense->medicine?->expirationLabel(),
            'expiry_status' => $dispense->medicine?->expiryStatus(),
            'quantity_remaining' => $dispense->medicine?->quantityRemaining(),
        ];
    }
}
