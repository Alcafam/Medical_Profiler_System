<?php

namespace App\Support;

class BpCategoryCalculator
{
    /**
     * Clinic BP categories (worse of systolic / diastolic tier wins).
     *
     * Normal:                 Systolic ≤ 120 AND Diastolic ≤ 80
     * Elevated:               Systolic 121–139 / Diastolic 81–89
     * Hypertension Stage 1:   Systolic 140–159 / Diastolic 90–99
     * Hypertension Stage 2:   Systolic 160–179 / Diastolic 100–109
     * Hypertensive Crisis:    Systolic ≥ 180 / Diastolic ≥ 110
     */
    public static function category(?float $systolic, ?float $diastolic): ?string
    {
        if ($systolic === null || $diastolic === null) {
            return null;
        }

        $tier = max(
            self::systolicTier($systolic),
            self::diastolicTier($diastolic),
        );

        return match ($tier) {
            0 => 'Normal',
            1 => 'Elevated',
            2 => 'Hypertension Stage 1',
            3 => 'Hypertension Stage 2',
            4 => 'Hypertensive Crisis',
            default => null,
        };
    }

    /**
     * Background classes for BP category badges/cells.
     */
    public static function categoryBackgroundClass(?string $category): string
    {
        return match ($category) {
            'Normal' => 'bp-cat-normal',
            'Elevated' => 'bp-cat-elevated',
            'Hypertension Stage 1' => 'bp-cat-stage-1',
            'Hypertension Stage 2' => 'bp-cat-stage-2',
            'Hypertensive Crisis' => 'bp-cat-crisis',
            default => 'bp-cat-empty',
        };
    }

    private static function systolicTier(float $systolic): int
    {
        return match (true) {
            $systolic >= 180 => 4,
            $systolic >= 160 => 3,
            $systolic >= 140 => 2,
            $systolic >= 121 => 1,
            default => 0, // <= 120
        };
    }

    private static function diastolicTier(float $diastolic): int
    {
        return match (true) {
            $diastolic >= 110 => 4,
            $diastolic >= 100 => 3,
            $diastolic >= 90 => 2,
            $diastolic >= 81 => 1,
            default => 0, // <= 80
        };
    }
}
