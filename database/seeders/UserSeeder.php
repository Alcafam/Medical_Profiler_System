<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Station;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('12345678');

        User::query()->updateOrCreate(
            ['email' => 'sadmin@mail.com'],
            [
                'name' => 'Super Admin',
                'password' => $password,
                'role' => UserRole::SuperAdmin,
                'station_id' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'Admin',
                'password' => $password,
                'role' => UserRole::Admin,
                'station_id' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $stations = Station::query()->orderBy('sort_order')->get();

        foreach ($stations as $station) {
            $slug = Str::slug($station->name, '.');

            User::query()->updateOrCreate(
                ['email' => "encoder.{$slug}@mail.com"],
                [
                    'name' => "{$station->name} Encoder",
                    'password' => $password,
                    'role' => UserRole::Encoder,
                    'station_id' => $station->id,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        // Remove legacy single-encoder account from older seeds.
        User::query()->where('email', 'encoder@mail.com')->delete();
    }
}
