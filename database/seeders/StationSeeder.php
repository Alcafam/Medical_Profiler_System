<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    public function run(): void
    {
        $stations = [
            ['name' => 'Registration', 'sort_order' => 1],
            ['name' => 'Vitals', 'sort_order' => 2],
            ['name' => 'Blood Glucose', 'sort_order' => 3],
            ['name' => 'Consultation', 'sort_order' => 4],
            ['name' => 'Pharmacy', 'sort_order' => 5],
        ];

        foreach ($stations as $station) {
            Station::query()->updateOrCreate(
                ['name' => $station['name']],
                [
                    'sort_order' => $station['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
