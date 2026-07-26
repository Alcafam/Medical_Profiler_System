<?php

namespace App\Models;

use App\Support\BmiCalculator;
use App\Support\BpCategoryCalculator;
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
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
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
