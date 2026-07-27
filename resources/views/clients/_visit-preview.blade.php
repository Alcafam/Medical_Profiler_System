@php
    $previewLabels = $previewLabels ?? [
        'client_type' => 'Patient Type',
        'department' => 'Department',
        'last_name' => 'Last Name',
        'first_name' => 'First Name',
        'date_of_birth' => 'Date of Birth',
        'sex' => 'Sex',
        'height_cm' => 'Height (cm)',
        'weight_kg' => 'Weight (kg)',
        'temperature' => 'Temperature (Celsius)',
        'heart_rate_bpm' => 'Heart Rate (BPM)',
        'spo2' => 'SPO2',
        'respiratory_rate' => 'Respiratory Rate',
        'systolic' => 'Systolic',
        'diastolic' => 'Diastolic',
        'cbg' => 'CBG',
        'fasting_state' => 'Fasting State',
        'history' => 'History',
        'current_conditions' => 'Current Conditions',
        'current_medications' => 'Current Medications',
        'allergies' => 'Allergies',
        'notes' => 'Notes',
        'patient_condition' => 'Patient Condition',
    ];

    $previewSlugs = $previewSlugs ?? [
        'client_type',
        'department',
        'last_name',
        'first_name',
        'date_of_birth',
        'sex',
        'height_cm',
        'weight_kg',
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
        'patient_condition',
    ];

    $hiddenSlugs = ['first_name', 'last_name', 'client_type', 'department'];
    $displaySlugs = collect($previewSlugs)->reject(fn ($slug) => in_array($slug, $hiddenSlugs, true))->values();

    $field = function (string $slug) use ($visit): string {
        $value = $visit->fieldValue($slug);

        return $value !== null ? $value : '—';
    };

    $last = $field('last_name');
    $first = $field('first_name');
    if ($last === '—' && $first === '—') {
        $fullName = '—';
    } elseif ($last === '—') {
        $fullName = $first;
    } elseif ($first === '—') {
        $fullName = $last;
    } else {
        $fullName = $last.', '.$first;
    }

    $department = $field('department');
    $clientType = $field('client_type');
    $meta = collect([$department, $clientType])->filter(fn ($v) => $v !== '—')->values();
    if ($meta->isNotEmpty()) {
        $fullName .= ' ('.$meta->implode(' - ').')';
    }

    $dob = $field('date_of_birth');
    $age = $visit->age();
    $dobDisplay = $dob === '—' ? '—' : ($age !== null ? $dob.' ('.$age.')' : $dob);

    $bmi = $visit->bmi();
    $bmiCategory = $visit->bmiCategory();
    $bloodPressure = $visit->bloodPressure();
    $bpCategory = $visit->bpCategory();
    $liveSync = (bool) ($liveSync ?? false);
@endphp

<div @class(['space-y-3', 'js-current-visit-preview' => $liveSync])>
    <div class="min-w-0 border-b border-slate-100 pb-3">
        <p class="text-xs uppercase tracking-wide text-slate-400 mb-1">Preview</p>
        <h3 class="mt-0 font-semibold text-slate-900 break-words text-base js-preview-full-name">{{ $fullName }}</h3>
        @isset($client)
            <p class="mt-1 font-mono text-xs text-slate-500 break-all mb-0">{{ $client->system_id }}</p>
        @endisset
        <p class="mt-1 text-xs text-slate-500 mb-0">
            Visit: {{ $visit->visited_at?->format('Y-m-d H:i') ?? '—' }}
        </p>
    </div>

    <dl class="space-y-2 text-sm mb-0">
        <div class="d-flex gap-2">
            <dt class="text-slate-500 shrink-0 mb-0">Name:</dt>
            <dd class="text-slate-800 break-words mb-0 js-preview-full-name">{{ $fullName }}</dd>
        </div>

        @foreach ($displaySlugs as $slug)
            <div>
                <div class="d-flex gap-2">
                    <dt class="text-slate-500 shrink-0 mb-0">{{ $previewLabels[$slug] ?? $slug }}:</dt>
                    <dd class="text-slate-800 break-words mb-0" data-preview-slug="{{ $slug }}">
                        @if ($slug === 'date_of_birth')
                            {{ $dobDisplay }}
                        @else
                            {{ $field($slug) }}
                        @endif
                    </dd>
                </div>

                @if ($slug === 'weight_kg')
                    <div class="mt-2 space-y-2">
                        <div class="d-flex gap-2">
                            <dt class="text-slate-500 shrink-0 mb-0">BMI:</dt>
                            <dd class="text-slate-800 mb-0 js-preview-bmi">
                                {{ ($bmi ?? '—').' ('.($bmiCategory ?? '—').')' }}
                            </dd>
                        </div>
                        <hr class="my-3 border-slate-200">
                    </div>
                @endif

                @if ($slug === 'diastolic')
                    <div class="mt-2 space-y-2">
                        <div class="d-flex gap-2">
                            <dt class="text-slate-500 shrink-0 mb-0">Blood Pressure (mmHg):</dt>
                            <dd
                                class="mb-0 js-preview-bp {{ $bpCategory === 'Hypertensive Crisis' ? 'text-rose-600 font-semibold' : 'text-slate-800' }}"
                            >
                                {{ ($bloodPressure ?? '—').' ('.($bpCategory ?? '—').')' }}
                            </dd>
                        </div>
                        <hr class="my-3 border-slate-200">
                    </div>
                @endif

                @if ($slug === 'fasting_state')
                    <hr class="my-3 border-slate-200">
                @endif
            </div>
        @endforeach

        @if ($visit->relationLoaded('medicineRecommendations') && $visit->medicineRecommendations->isNotEmpty())
            <div class="pt-2 border-t border-slate-100">
                <p class="text-xs uppercase tracking-wide text-slate-400 mb-1">Recommended medicines</p>
                <ul class="mb-0 ps-3 text-sm text-slate-800">
                    @foreach ($visit->medicineRecommendations as $rec)
                        <li>
                            {{ $rec->medicine?->displayLabel() ?? 'Medicine' }}
                            @if ($rec->quantity) — qty {{ $rec->quantity }} @endif
                            @if ($rec->instructions) ({{ $rec->instructions }}) @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($visit->relationLoaded('medicineDispenses') && $visit->medicineDispenses->isNotEmpty())
            <div class="pt-2 border-t border-slate-100">
                <p class="text-xs uppercase tracking-wide text-slate-400 mb-1">Dispensed medicines</p>
                <ul class="mb-0 ps-3 text-sm text-slate-800">
                    @foreach ($visit->medicineDispenses as $dispense)
                        <li>
                            {{ $dispense->medicine?->displayLabel() ?? 'Medicine' }}
                            — qty {{ $dispense->quantity }}
                            @if ($dispense->remarks) ({{ $dispense->remarks }}) @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </dl>
</div>
