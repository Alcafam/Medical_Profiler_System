<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientFieldValue extends Model
{
    protected $fillable = [
        'client_id',
        'visit_id',
        'form_field_id',
        'value',
        'version',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function formField(): BelongsTo
    {
        return $this->belongsTo(FormField::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
