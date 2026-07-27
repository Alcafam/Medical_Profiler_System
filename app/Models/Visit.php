<?php

namespace App\Models;

use App\Enums\VisitDisposition;
use App\Support\BmiCalculator;
use App\Support\BpCategoryCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Visit extends Model
{
    public const COPY_SLUGS = [
        'last_name',
        'first_name',
        'date_of_birth',
        'sex',
        'client_type',
    ];

    protected $fillable = [
        'client_id',
        'visited_at',
        'created_by',
        'notes',
        'queued_for_consultation_at',
        'disposition',
        'disposition_at',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
            'queued_for_consultation_at' => 'datetime',
            'disposition_at' => 'datetime',
            'disposition' => VisitDisposition::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fieldValues(): HasMany
    {
        return $this->hasMany(ClientFieldValue::class);
    }

    public function medicineRecommendations(): HasMany
    {
        return $this->hasMany(VisitMedicineRecommendation::class);
    }

    public function medicineDispenses(): HasMany
    {
        return $this->hasMany(VisitMedicineDispense::class);
    }

    public function scopeQueuedForConsultation(Builder $query): Builder
    {
        return $query->whereNotNull('queued_for_consultation_at');
    }

    public function scopeConsultationActive(Builder $query): Builder
    {
        return $query->queuedForConsultation()
            ->where('disposition', VisitDisposition::Active);
    }

    public function scopeConsultationCompleted(Builder $query): Builder
    {
        return $query->queuedForConsultation()
            ->whereIn(
                'disposition',
                array_map(fn (VisitDisposition $case) => $case->value, VisitDisposition::completedCases())
            );
    }

    public function isQueuedForConsultation(): bool
    {
        return $this->queued_for_consultation_at !== null;
    }

    public function queueForConsultation(): void
    {
        $this->forceFill([
            'queued_for_consultation_at' => $this->queued_for_consultation_at ?? now(),
            'disposition' => VisitDisposition::Active,
            'disposition_at' => now(),
        ])->save();
    }

    public function removeFromConsultationQueue(): bool
    {
        if ($this->disposition !== null && $this->disposition !== VisitDisposition::Active) {
            return false;
        }

        $this->forceFill([
            'queued_for_consultation_at' => null,
            'disposition' => null,
            'disposition_at' => null,
        ])->save();

        return true;
    }

    public function setDisposition(VisitDisposition $disposition): void
    {
        $this->forceFill([
            'disposition' => $disposition,
            'disposition_at' => now(),
        ])->save();
    }

    public function waitingMinutes(): int
    {
        if ($this->queued_for_consultation_at === null) {
            return 0;
        }

        $end = $this->disposition === VisitDisposition::Active || $this->disposition === null
            ? now()
            : ($this->disposition_at ?? now());

        return max(0, (int) $this->queued_for_consultation_at->diffInMinutes($end));
    }

    public function waitingLabel(): string
    {
        $minutes = $this->waitingMinutes();

        if ($minutes < 60) {
            return $minutes.' min';
        }

        $hours = intdiv($minutes, 60);
        $remain = $minutes % 60;

        return $remain === 0
            ? "{$hours}h"
            : "{$hours}h {$remain}m";
    }

    public function valueFor(FormField|int $field): ?ClientFieldValue
    {
        $fieldId = $field instanceof FormField ? $field->id : $field;

        return $this->fieldValues->firstWhere('form_field_id', $fieldId);
    }

    public function displayName(): string
    {
        $this->loadMissing('fieldValues.formField', 'client');

        $values = $this->fieldValues->keyBy(fn (ClientFieldValue $value) => $value->formField?->slug);

        $first = $values->get('first_name')?->value;
        $last = $values->get('last_name')?->value;

        $name = trim(collect([$first, $last])->filter()->implode(' '));

        return $name !== '' ? $name : ($this->client?->system_id ?? 'Visit #'.$this->id);
    }

    public function fieldValue(string $slug): ?string
    {
        $this->loadMissing('fieldValues.formField');

        $value = $this->fieldValues
            ->first(fn (ClientFieldValue $fieldValue) => $fieldValue->formField?->slug === $slug)
            ?->value;

        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return (string) $value;
    }

    public function age(): ?int
    {
        $dob = $this->fieldValue('date_of_birth');

        if ($dob === null) {
            return null;
        }

        try {
            return Carbon::parse($dob)->age;
        } catch (\Throwable) {
            return null;
        }
    }

    public function bmi(): ?float
    {
        $height = $this->fieldValue('height_cm');
        $weight = $this->fieldValue('weight_kg');

        if ($height === null || $weight === null) {
            return null;
        }

        return BmiCalculator::calculate((float) $height, (float) $weight);
    }

    public function bmiCategory(): ?string
    {
        return BmiCalculator::asianCategory($this->bmi());
    }

    public function bloodPressure(): ?string
    {
        $systolic = $this->fieldValue('systolic');
        $diastolic = $this->fieldValue('diastolic');

        if ($systolic === null || $diastolic === null) {
            return null;
        }

        return "{$systolic}/{$diastolic}";
    }

    public function bpCategory(): ?string
    {
        $systolic = $this->fieldValue('systolic');
        $diastolic = $this->fieldValue('diastolic');

        if ($systolic === null || $diastolic === null) {
            return null;
        }

        return BpCategoryCalculator::category((float) $systolic, (float) $diastolic);
    }
}
