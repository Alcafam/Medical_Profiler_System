<?php

namespace Database\Seeders;

use App\Models\Medicine;
use Illuminate\Database\Seeder;

class MedicineInventorySeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/medical_inventory.csv');

        if (! is_readable($path)) {
            $this->command?->warn('Medicine inventory CSV not found: '.$path);

            return;
        }

        if (Medicine::query()->exists()) {
            $this->command?->info('Medicines already seeded; skipping.');

            return;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return;
        }

        // Header may span multiple lines because of a quoted Quantity column.
        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);

            return;
        }

        $created = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $generic = trim((string) ($row[0] ?? ''));
            if ($generic === '') {
                continue;
            }

            $brand = trim((string) ($row[1] ?? ''));
            $strength = trim((string) ($row[2] ?? ''));
            $expirationRaw = trim((string) ($row[3] ?? ''));
            $quantity = $this->parseInt($row[4] ?? null);
            $dispensed = $this->parseInt($row[5] ?? null) ?? 0;
            $remainingCsv = $this->parseInt($row[6] ?? null);
            $remarks = trim((string) ($row[7] ?? ''));

            if ($quantity === null && $remainingCsv !== null) {
                $quantity = $remainingCsv + $dispensed;
            }

            if ($quantity === null) {
                $quantity = 0;
            }

            if ($dispensed > $quantity) {
                $dispensed = $quantity;
            }

            Medicine::query()->create([
                'generic_name' => $generic,
                'brand_name' => $brand !== '' ? $brand : $generic,
                'dosage_strength' => $strength !== '' ? $strength : null,
                'expiration_date' => Medicine::parseExpirationMonthYear($expirationRaw),
                'quantity' => $quantity,
                'quantity_dispensed' => $dispensed,
                'remarks' => $remarks !== '' ? $remarks : null,
            ]);

            $created++;
        }

        fclose($handle);

        $this->command?->info("Seeded {$created} medicines from inventory CSV.");
    }

    private function parseInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '' || $raw === '-') {
            return null;
        }

        $raw = str_replace([',', ' '], '', $raw);

        if (! is_numeric($raw)) {
            return null;
        }

        return max(0, (int) $raw);
    }
}
