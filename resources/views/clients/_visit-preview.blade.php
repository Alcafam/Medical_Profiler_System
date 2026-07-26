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
@endphp

<div class="space-y-3">
    <div class="min-w-0 border-b border-slate-100 pb-3">
        <p class="text-xs uppercase tracking-wide text-slate-400 mb-1">Preview</p>
        <h3 class="mt-0 font-semibold text-slate-900 break-words text-base">{{ $fullName }}</h3>
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
            <dd class="text-slate-800 break-words mb-0">{{ $fullName }}</dd>
        </div>

        @foreach ($displaySlugs as $slug)
            <div>
                <div class="d-flex gap-2">
                    <dt class="text-slate-500 shrink-0 mb-0">{{ $previewLabels[$slug] ?? $slug }}:</dt>
                    <dd class="text-slate-800 break-words mb-0">
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
                            <dd class="text-slate-800 mb-0">
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
                            <dd @class([
                                'mb-0',
                                'text-rose-600 font-semibold' => $bpCategory === 'Hypertensive Crisis',
                                'text-slate-800' => $bpCategory !== 'Hypertensive Crisis',
                            ])>
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
    </dl>
</div>
