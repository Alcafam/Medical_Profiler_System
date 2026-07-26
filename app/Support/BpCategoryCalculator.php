<?php

namespace App\Support;

class BpCategoryCalculator
{
    /**
     * AHA / ASA Blood Pressure Categories.
     *
     * Normal:                Systolic < 120 AND Diastolic < 80
     * Elevated:              Systolic 120–129 AND Diastolic < 80
     * Hypertension Stage 1:  Systolic 130–139 OR Diastolic 80–89
     * Hypertension Stage 2:  Systolic ≥ 140 OR Diastolic ≥ 90
     * Hypertensive Crisis:   Systolic > 180 AND/OR Diastolic > 120
     */
    public static function category(?float $systolic, ?float $diastolic): ?string
    {
        if ($systolic === null || $diastolic === null) {
            return null;
        }

        if ($systolic > 180 || $diastolic > 120) {
            return 'Hypertensive Crisis';
        }

        if ($systolic >= 140 || $diastolic >= 90) {
            return 'Hypertension Stage 2';
        }

        if (($systolic >= 130 && $systolic <= 139) || ($diastolic >= 80 && $diastolic <= 89)) {
            return 'Hypertension Stage 1';
        }

        if ($systolic >= 120 && $systolic <= 129 && $diastolic < 80) {
            return 'Elevated';
        }

        if ($systolic < 120 && $diastolic < 80) {
            return 'Normal';
        }

        return null;
    }
}
