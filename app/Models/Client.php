<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'system_id',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class)->orderByDesc('visited_at')->orderByDesc('id');
    }

    public function latestVisit(): HasOne
    {
        return $this->hasOne(Visit::class)->latestOfMany(['visited_at', 'id']);
    }

    public function fieldValues(): HasMany
    {
        return $this->hasMany(ClientFieldValue::class);
    }

    public function displayName(): string
    {
        $this->loadMissing('latestVisit.fieldValues.formField');

        return $this->latestVisit?->displayName() ?? $this->system_id;
    }

    public function age(): ?int
    {
        $this->loadMissing('latestVisit.fieldValues.formField');

        return $this->latestVisit?->age();
    }

    public function bmi(): ?float
    {
        $this->loadMissing('latestVisit.fieldValues.formField');

        return $this->latestVisit?->bmi();
    }

    public function bmiCategory(): ?string
    {
        $this->loadMissing('latestVisit.fieldValues.formField');

        return $this->latestVisit?->bmiCategory();
    }

    public function bloodPressure(): ?string
    {
        $this->loadMissing('latestVisit.fieldValues.formField');

        return $this->latestVisit?->bloodPressure();
    }

    public function bpCategory(): ?string
    {
        $this->loadMissing('latestVisit.fieldValues.formField');

        return $this->latestVisit?->bpCategory();
    }

    public function fieldValue(string $slug): ?string
    {
        $this->loadMissing('latestVisit.fieldValues.formField');

        return $this->latestVisit?->fieldValue($slug);
    }

    public static function generateSystemId(): string
    {
        return DB::transaction(function () {
            $prefix = 'MED-'.now()->format('Ymd').'-';
            $latest = static::withTrashed()
                ->where('system_id', 'like', $prefix.'%')
                ->orderByDesc('system_id')
                ->value('system_id');

            $sequence = 1;
            if ($latest) {
                $sequence = ((int) substr($latest, -4)) + 1;
            }

            return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
        });
    }
}
