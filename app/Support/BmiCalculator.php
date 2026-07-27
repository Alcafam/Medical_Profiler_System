<?php

namespace App\Support;

class BmiCalculator
{
    /**
     * BMI = weight(kg) / (height(m) ^ 2)
     * Height is stored in centimeters.
     */
    public static function calculate(?float $heightCm, ?float $weightKg): ?float
    {
        if ($heightCm === null || $weightKg === null) {
            return null;
        }

        if ($heightCm <= 0 || $weightKg <= 0) {
            return null;
        }

        $heightM = $heightCm / 100;

        return round($weightKg / ($heightM * $heightM), 1);
    }

    /**
     * ASIAN criteria BMI cut-off (from clinic reference chart).
     */
    public static function asianCategory(?float $bmi): ?string
    {
        if ($bmi === null) {
            return null;
        }

        return match (true) {
            $bmi < 18.5 => 'Underweight',
            $bmi < 23.0 => 'Normal',
            $bmi < 25.0 => 'Overweight',
            $bmi < 30.0 => 'Obese I',
            default => 'Obese II',
        };
    }

    /**
     * Background classes for BMI category badges/cells.
     */
    public static function categoryBackgroundClass(?string $category): string
    {
        return match ($category) {
            'Underweight' => 'bmi-cat-underweight',
            'Normal' => 'bmi-cat-normal',
            'Overweight' => 'bmi-cat-overweight',
            'Obese I' => 'bmi-cat-obese-i',
            'Obese II' => 'bmi-cat-obese-ii',
            default => 'bmi-cat-empty',
        };
    }
}
