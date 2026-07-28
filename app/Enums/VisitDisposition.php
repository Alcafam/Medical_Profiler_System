<?php

namespace App\Enums;

enum VisitDisposition: string
{
    case Active = 'active';
    case TreatedSentHome = 'treated_sent_home';
    case Referred = 'referred';
    case Died = 'died';
    case Absconded = 'absconded';
    case OutWhenCalled = 'out_when_called';
    case RefusedTreatment = 'refused_treatment';
    case DischargeAgainstMedicalAdvice = 'discharge_against_medical_advice';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::TreatedSentHome => 'Treated and Sent Home',
            self::Referred => 'Referred',
            self::Died => 'Died',
            self::Absconded => 'Absconded',
            self::OutWhenCalled => 'Out When Called',
            self::RefusedTreatment => 'Refused Treatment',
            self::DischargeAgainstMedicalAdvice => 'Discharged Against Medical Advice',
        };
    }

    /**
     * @return list<self>
     */
    public static function completedCases(): array
    {
        return [
            self::TreatedSentHome,
            self::Referred,
            self::Died,
            self::Absconded,
            self::OutWhenCalled,
            self::RefusedTreatment,
            self::DischargeAgainstMedicalAdvice,
        ];
    }
}
