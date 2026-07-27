<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitMedicineDispense;
use App\Models\VisitMedicineRecommendation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VisitMedicineService
{
    public function recommend(
        Visit $visit,
        Medicine $medicine,
        User $user,
        ?int $quantity = null,
        ?string $instructions = null,
    ): VisitMedicineRecommendation {
        abort_if($medicine->isArchived(), 422, 'Cannot recommend an archived medicine.');

        return VisitMedicineRecommendation::query()->create([
            'visit_id' => $visit->id,
            'medicine_id' => $medicine->id,
            'quantity' => $quantity,
            'instructions' => $instructions,
            'recommended_by' => $user->id,
        ]);
    }

    public function removeRecommendation(VisitMedicineRecommendation $recommendation): void
    {
        $recommendation->delete();
    }

    public function dispense(
        Visit $visit,
        Medicine $medicine,
        User $user,
        int $quantity,
        ?string $remarks = null,
    ): VisitMedicineDispense {
        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be at least 1.',
            ]);
        }

        return DB::transaction(function () use ($visit, $medicine, $user, $quantity, $remarks) {
            /** @var Medicine $locked */
            $locked = Medicine::query()->whereKey($medicine->id)->lockForUpdate()->firstOrFail();

            if ($locked->isArchived()) {
                throw ValidationException::withMessages([
                    'medicine_id' => 'Cannot dispense an archived medicine.',
                ]);
            }

            if ($locked->quantityRemaining() < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Not enough stock remaining ('.$locked->quantityRemaining().' left).',
                ]);
            }

            $locked->increment('quantity_dispensed', $quantity);

            return VisitMedicineDispense::query()->create([
                'visit_id' => $visit->id,
                'medicine_id' => $locked->id,
                'quantity' => $quantity,
                'remarks' => $remarks,
                'dispensed_by' => $user->id,
            ]);
        });
    }

    public function removeDispense(VisitMedicineDispense $dispense): void
    {
        DB::transaction(function () use ($dispense) {
            /** @var Medicine $locked */
            $locked = Medicine::query()->whereKey($dispense->medicine_id)->lockForUpdate()->firstOrFail();

            $locked->quantity_dispensed = max(0, (int) $locked->quantity_dispensed - (int) $dispense->quantity);
            $locked->save();

            $dispense->delete();
        });
    }
}
