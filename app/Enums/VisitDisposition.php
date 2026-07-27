<?php

namespace App\Enums;

enum VisitDisposition: string
{
    case Active = 'active';
    case TreatedSentHome = 'treated_sent_home';
    case RefusedTreatment = 'refused_treatment';
    case DischargeAgainstMedicalAdvice = 'discharge_against_medical_advice';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::TreatedSentHome => 'Treated and Sent Home',
            self::RefusedTreatment => 'Refused Treatment and Management',
            self::DischargeAgainstMedicalAdvice => 'Discharge Against Medical Advice',
        };
    }

    /**
     * @return list<self>
     */
    public static function completedCases(): array
    {
        return [
            self::TreatedSentHome,
            self::RefusedTreatment,
            self::DischargeAgainstMedicalAdvice,
        ];
    }
}
