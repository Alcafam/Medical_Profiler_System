<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
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
}
