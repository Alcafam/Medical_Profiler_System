<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Encoder = 'encoder';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Encoder => 'Encoder',
        };
    }

    public function canManageForm(): bool
    {
        return $this === self::SuperAdmin;
    }

    public function canManageStations(): bool
    {
        return $this === self::SuperAdmin;
    }

    public function canManageUsers(): bool
    {
        return $this === self::SuperAdmin;
    }

    public function canUseGrid(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin], true);
    }

    public function canExport(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin], true);
    }

    public function canSoftDeleteClients(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin], true);
    }

    public function canBulkCreateVisits(): bool
    {
        return $this === self::SuperAdmin;
    }
}
