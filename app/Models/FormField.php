<?php

namespace App\Models;

use App\Enums\FieldType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormField extends Model
{
    protected $fillable = [
        'station_id',
        'slug',
        'label',
        'type',
        'options',
        'is_required',
        'is_system',
        'is_searchable',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => FieldType::class,
            'options' => 'array',
            'is_required' => 'boolean',
            'is_system' => 'boolean',
            'is_searchable' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ClientFieldValue::class);
    }
}
