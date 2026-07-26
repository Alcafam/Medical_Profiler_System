<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientFieldValue;
use App\Models\FormField;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::query()->where('email', 'encoder@mail.com')->first()
            ?? User::query()->where('email', 'sadmin@mail.com')->first()
            ?? User::query()->first();

        $fields = FormField::query()
            ->whereIn('slug', [
                'last_name',
                'first_name',
                'sex',
                'height_cm',
                'weight_kg',
                'client_type',
                'department',
                'temperature',
                'heart_rate_bpm',
                'spo2',
                'respiratory_rate',
                'systolic',
                'diastolic',
                'cbg',
                'fasting_state',
                'history',
                'current_conditions',
                'current_medications',
                'allergies',
                'notes',
            ])
            ->get()
            ->keyBy('slug');

        $csvPath = database_path('data/medical_records_sample.csv');

        if (! is_file($csvPath)) {
            $this->command?->error("CSV not found: {$csvPath}");

            return;
        }

        ClientFieldValue::query()->delete();
        Visit::query()->delete();
        Client::withTrashed()->forceDelete();

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            $this->command?->error('Unable to open CSV file.');

            return;
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);

            return;
        }

        $headers = array_map(fn ($header) => trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $header)), $headers);
        $created = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $record = [];
            foreach ($headers as $index => $header) {
                $record[$header] = trim((string) ($row[$index] ?? ''));
            }

            $lastName = $record['Last Name'] ?? '';
            $firstName = $record['First Name'] ?? '';

            if ($lastName === '' && $firstName === '') {
                continue;
            }

            $client = Client::query()->create([
                'system_id' => Client::generateSystemId(),
                'created_by' => $creator?->id,
            ]);

            $visit = Visit::query()->create([
                'client_id' => $client->id,
                'visited_at' => $client->created_at ?? now(),
                'created_by' => $creator?->id,
            ]);

            $values = [
                'last_name' => $lastName,
                'first_name' => $firstName,
                'sex' => $this->normalizeSex($record['Sex'] ?? ''),
                'height_cm' => $this->nullableNumber($record['Height (cm)'] ?? ''),
                'weight_kg' => $this->nullableNumber($record['Weight (kg)'] ?? ''),
                'client_type' => 'member',
                'department' => 'Adult',
                'temperature' => $this->nullableNumber($record['Temperature (Celsius)'] ?? ''),
                'heart_rate_bpm' => $this->nullableNumber($record['Heart Rate (bpm)'] ?? ''),
                'spo2' => $this->nullableNumber($record['SPO2'] ?? ''),
                'respiratory_rate' => $this->nullableNumber($record['Respiratory Rate'] ?? ''),
                'systolic' => $this->nullableNumber($record['Systolic'] ?? ''),
                'diastolic' => $this->nullableNumber($record['Diastolic'] ?? ''),
                'cbg' => $this->nullableNumber($record['CBG'] ?? ''),
                'fasting_state' => $this->normalizeFastingState($record['Fasting State'] ?? ''),
                'history' => $record['History'] ?? '',
                'current_conditions' => $record['Current Conditions'] ?? '',
                'current_medications' => $record['Current Medications'] ?? '',
                'allergies' => $record['Allergies'] ?? '',
                'notes' => $record['Notes'] ?? '',
            ];

            foreach ($values as $slug => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                $field = $fields->get($slug);
                if (! $field) {
                    continue;
                }

                ClientFieldValue::query()->create([
                    'client_id' => $client->id,
                    'visit_id' => $visit->id,
                    'form_field_id' => $field->id,
                    'value' => (string) $value,
                    'version' => 1,
                    'updated_by' => $creator?->id,
                ]);
            }

            $created++;
        }

        fclose($handle);

        $this->command?->info("Seeded {$created} clients from medical_records_sample.csv");
    }

    private function normalizeSex(string $sex): string
    {
        $sex = trim($sex);

        return in_array($sex, ['Male', 'Female'], true) ? $sex : '';
    }

    private function normalizeFastingState(string $value): string
    {
        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'fasting' => 'Fasting',
            'non-fasting', 'non fasting', 'nonfasting' => 'Non-Fasting',
            'known diabetic', 'known-diabetic' => 'Known Diabetic',
            default => '',
        };
    }

    private function nullableNumber(string $value): string
    {
        $value = trim($value);

        if ($value === '' || ! is_numeric($value)) {
            return '';
        }

        return $value;
    }

    /**
     * @param  array<int, string|null>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
