<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'sadmin@mail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
                'role' => UserRole::SuperAdmin,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
                'role' => UserRole::Admin,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'encoder@mail.com'],
            [
                'name' => 'Encoder',
                'password' => Hash::make('12345678'),
                'role' => UserRole::Encoder,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
