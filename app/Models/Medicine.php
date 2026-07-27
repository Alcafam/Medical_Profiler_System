<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Medicine extends Model
{
    protected $fillable = [
        'generic_name',
        'brand_name',
        'dosage_strength',
        'expiration_date',
        'quantity',
        'quantity_dispensed',
        'remarks',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'expiration_date' => 'date',
            'archived_at' => 'datetime',
            'quantity' => 'integer',
            'quantity_dispensed' => 'integer',
        ];
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(VisitMedicineRecommendation::class);
    }

    public function dispenses(): HasMany
    {
        return $this->hasMany(VisitMedicineDispense::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function quantityRemaining(): int
    {
        return max(0, (int) $this->quantity - (int) $this->quantity_dispensed);
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function expirationLabel(): string
    {
        if ($this->expiration_date === null) {
            return '—';
        }

        return $this->expiration_date->format('F Y');
    }

    /**
     * @return 'expired'|'current'|'soon'|'ok'|null
     */
    public function expiryStatus(): ?string
    {
        if ($this->expiration_date === null) {
            return null;
        }

        $expiryMonth = $this->expiration_date->copy()->startOfMonth();
        $currentMonth = now()->startOfMonth();

        if ($expiryMonth->lt($currentMonth)) {
            return 'expired';
        }

        if ($expiryMonth->equalTo($currentMonth)) {
            return 'current';
        }

        if ($expiryMonth->equalTo($currentMonth->copy()->addMonth())) {
            return 'soon';
        }

        return 'ok';
    }

    public function expiryRowClass(): string
    {
        return match ($this->expiryStatus()) {
            'expired' => 'bg-red-800 text-white',
            'current' => 'bg-red-100 text-red-950',
            'soon' => 'bg-amber-100 text-amber-950',
            default => '',
        };
    }

    public function displayLabel(): string
    {
        $parts = array_filter([
            trim((string) $this->generic_name),
            trim((string) $this->brand_name) !== '' ? '('.trim((string) $this->brand_name).')' : null,
            trim((string) ($this->dosage_strength ?? '')) !== '' ? trim((string) $this->dosage_strength) : null,
        ]);

        return implode(' ', $parts);
    }

    public function toPickerArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->displayLabel(),
            'generic_name' => $this->generic_name,
            'brand_name' => $this->brand_name,
            'dosage_strength' => $this->dosage_strength,
            'expiration_label' => $this->expirationLabel(),
            'expiry_status' => $this->expiryStatus(),
            'quantity_remaining' => $this->quantityRemaining(),
            'remarks' => $this->remarks,
        ];
    }

    public static function parseExpirationMonthYear(?string $value): ?Carbon
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '-') {
            return null;
        }

        $value = preg_replace('/\s+/', ' ', $value) ?? $value;
        $value = str_ireplace(['Sept'], 'Sep', $value);

        try {
            $parsed = Carbon::parse('1 '.$value)->startOfMonth();

            return $parsed;
        } catch (\Throwable) {
            return null;
        }
    }
}
