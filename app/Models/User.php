<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active', 'station_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isEncoder(): bool
    {
        return $this->role === UserRole::Encoder;
    }

    public function canManageForm(): bool
    {
        return $this->role->canManageForm();
    }

    public function canManageStations(): bool
    {
        return $this->role->canManageStations();
    }

    public function canManageUsers(): bool
    {
        return $this->role->canManageUsers();
    }

    public function canUseGrid(): bool
    {
        return $this->role->canUseGrid();
    }

    public function canExport(): bool
    {
        return $this->role->canExport();
    }

    public function canSoftDeleteClients(): bool
    {
        return $this->role->canSoftDeleteClients();
    }

    public function canBulkCreateVisits(): bool
    {
        return $this->role->canBulkCreateVisits();
    }

    public function canManageInventory(): bool
    {
        return $this->role->canManageInventory();
    }

    /**
     * Encoders and admins may edit any active field.
     */
    public function canEditField(FormField $field): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->isEncoder();
    }

    /**
     * Query params to open the encode page on this user's default station.
     *
     * @return array{station?: int, view?: int}
     */
    public function encodeStationQuery(): array
    {
        if ($this->isEncoder() && $this->station_id) {
            return [
                'station' => (int) $this->station_id,
                'view' => (int) $this->station_id,
            ];
        }

        return [];
    }

    public function isConsultationEncoder(): bool
    {
        return $this->isEncoder() && $this->station?->name === 'Consultation';
    }
}
