<?php

namespace Database\Seeders;

use App\Enums\FieldType;
use App\Models\FormField;
use App\Models\Station;
use Illuminate\Database\Seeder;

class FormFieldSeeder extends Seeder
{
    public function run(): void
    {
        $stations = Station::query()
            ->whereIn('name', [
                'Registration',
                'Vitals',
                'Blood Glucose',
                'Consultation',
                'Pharmacy',
            ])
            ->get()
            ->keyBy('name');

        $registration = $stations->get('Registration');
        $vitals = $stations->get('Vitals');
        $bloodGlucose = $stations->get('Blood Glucose');
        $consultation = $stations->get('Consultation');

        $fields = [
            // Registration
            [
                'slug' => 'client_type',
                'label' => 'Patient Type',
                'type' => FieldType::Select,
                'options' => ['visitor', 'member'],
                'station_id' => $registration?->id,
                'is_required' => true,
                'is_searchable' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'department',
                'label' => 'Department',
                'type' => FieldType::Select,
                'options' => ['Adult', 'Single Professional', 'Young People'],
                'station_id' => $registration?->id,
                'is_required' => true,
                'is_searchable' => true,
                'sort_order' => 2,
            ],
            [
                'slug' => 'last_name',
                'label' => 'Last Name',
                'type' => FieldType::Text,
                'station_id' => $registration?->id,
                'is_required' => true,
                'is_searchable' => true,
                'sort_order' => 3,
            ],
            [
                'slug' => 'first_name',
                'label' => 'First Name',
                'type' => FieldType::Text,
                'station_id' => $registration?->id,
                'is_required' => true,
                'is_searchable' => true,
                'sort_order' => 4,
            ],
            [
                'slug' => 'date_of_birth',
                'label' => 'Date of Birth',
                'type' => FieldType::Date,
                'station_id' => $registration?->id,
                'is_required' => true,
                'is_searchable' => true,
                'sort_order' => 5,
            ],
            [
                'slug' => 'sex',
                'label' => 'Sex',
                'type' => FieldType::Select,
                'options' => ['Male', 'Female'],
                'station_id' => $registration?->id,
                'is_required' => true,
                'is_searchable' => true,
                'sort_order' => 6,
            ],
            [
                'slug' => 'height_cm',
                'label' => 'Height (cm)',
                'type' => FieldType::Number,
                'station_id' => $registration?->id,
                'is_required' => false,
                'is_searchable' => false,
                'sort_order' => 7,
            ],
            [
                'slug' => 'weight_kg',
                'label' => 'Weight (kg)',
                'type' => FieldType::Number,
                'station_id' => $registration?->id,
                'is_required' => false,
                'is_searchable' => false,
                'sort_order' => 8,
            ],

            // Vitals
            [
                'slug' => 'temperature',
                'label' => 'Temperature (Celsius)',
                'type' => FieldType::Number,
                'station_id' => $vitals?->id,
                'is_required' => false,
                'is_searchable' => false,
                'sort_order' => 1,
            ],
            [
                'slug' => 'heart_rate_bpm',
                'label' => 'Heart Rate (BPM)',
                'type' => FieldType::Number,
                'station_id' => $vitals?->id,
                'is_required' => false,
                'is_searchable' => false,
                'sort_order' => 2,
            ],
            [
                'slug' => 'spo2',
                'label' => 'SPO2',
                'type' => FieldType::Number,
                'station_id' => $vitals?->id,
                'is_required' => false,
                'is_searchable' => false,
                'sort_order' => 3,
            ],
            [
                'slug' => 'respiratory_rate',
                'label' => 'Respiratory Rate',
                'type' => FieldType::Number,
                'station_id' => $vitals?->id,
                'is_required' => false,
                'is_searchable' => false,
                'sort_order' => 4,
            ],
            [
                'slug' => 'systolic',
                'label' => 'Systolic',
                'type' => FieldType::Number,
                'station_id' => $vitals?->id,
                'is_required' => false,
                'is_searchable' => false,
                'sort_order' => 5,
            ],
            [
                'slug' => 'diastolic',
                'label' => 'Diastolic',
                'type' => FieldType::Number,
                'station_id' => $vitals?->id,
                'is_required' => false,
                'is_searchable' => false,
                'sort_order' => 6,
            ],

            // Blood Glucose
            [
                'slug' => 'cbg',
                'label' => 'CBG',
                'type' => FieldType::Number,
                'station_id' => $bloodGlucose?->id,
                'is_required' => false,
                'is_searchable' => false,
                'sort_order' => 1,
            ],
            [
                'slug' => 'fasting_state',
                'label' => 'Fasting State',
                'type' => FieldType::Select,
                'options' => ['Fasting', 'Non-Fasting', 'Known Diabetic'],
                'station_id' => $bloodGlucose?->id,
                'is_required' => false,
                'is_searchable' => false,
                'sort_order' => 2,
            ],

            // Consultation
            [
                'slug' => 'history',
                'label' => 'History',
                'type' => FieldType::Textarea,
                'station_id' => $consultation?->id,
                'is_required' => false,
                'is_searchable' => false,
                'sort_order' => 1,
            ],
            [
                'slug' => 'current_conditions',
                'label' => 'Current Conditions',
                'type' => FieldType::Textarea,
                'station_id' => $consultation?->id,
                'is_required' => false,
                'is_searchable' => false,
                'sort_order' => 2,
            ],
            [
                'slug' => 'current_medications',
                'label' => 'Current Medications',
                'type' => FieldType::Textarea,
                'station_id' => $consultation?->id,
                'is_required' => false,
                'is_searchable' => false,
                'sort_order' => 3,
            ],
            [
                'slug' => 'allergies',
                'label' => 'Allergies',
                'type' => FieldType::Textarea,
                'station_id' => $consultation?->id,
                'is_required' => false,
                'is_searchable' => false,
                'sort_order' => 4,
            ],
            [
                'slug' => 'notes',
                'label' => 'Notes',
                'type' => FieldType::Textarea,
                'station_id' => $consultation?->id,
                'is_required' => false,
                'is_searchable' => false,
                'sort_order' => 5,
            ],
            [
                'slug' => 'patient_condition',
                'label' => 'Patient Condition',
                'type' => FieldType::Select,
                'options' => ['Improved', 'Un-improved', 'Recovered'],
                'station_id' => $consultation?->id,
                'is_required' => false,
                'is_searchable' => false,
                'sort_order' => 6,
            ],
        ];

        $activeSlugs = [];

        foreach ($fields as $field) {
            $activeSlugs[] = $field['slug'];

            FormField::query()->updateOrCreate(
                ['slug' => $field['slug']],
                [
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'options' => $field['options'] ?? null,
                    'station_id' => $field['station_id'],
                    'is_required' => $field['is_required'],
                    'is_system' => true,
                    'is_searchable' => $field['is_searchable'],
                    'is_active' => true,
                    'sort_order' => $field['sort_order'],
                ]
            );
        }

        FormField::query()
            ->where('is_system', true)
            ->whereNotIn('slug', $activeSlugs)
            ->update(['is_active' => false]);
    }
}
