@php
    $identityLabels = [
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

    $previewPayload = $clients->getCollection()->mapWithKeys(function ($client) use ($previewSlugs) {
        $map = ($client->latestVisit?->fieldValues ?? collect())->keyBy(fn ($v) => $v->formField?->slug);

        return [
            $client->id => [
                'id' => $client->id,
                'system_id' => $client->system_id,
                'encode_url' => route('clients.encode', $client),
                'visit_date' => optional($client->latestVisit?->visited_at)?->format('Y-m-d'),
                'age' => $client->age(),
                'bmi' => $client->bmi(),
                'bmi_category' => $client->bmiCategory(),
                'blood_pressure' => $client->bloodPressure(),
                'bp_category' => $client->bpCategory(),
                'fields' => collect($previewSlugs)->mapWithKeys(
                    fn ($slug) => [$slug => $map->get($slug)?->value]
                )->all(),
            ],
        ];
    })->all();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Clients</h2>
            <div class="flex flex-wrap gap-2">
                @if (auth()->user()->canExport())
                    <a href="{{ route('clients.export') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-md text-sm text-slate-700 hover:bg-slate-50 w-full sm:w-auto">
                        Export Excel
                    </a>
                @endif
                <a href="{{ route('clients.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-teal-700 border border-transparent rounded-md text-sm text-white hover:bg-teal-800 w-full sm:w-auto">
                    New Client
                </a>
            </div>
        </div>
    </x-slot>

    @if ($isEncoder)
        <div
            class="flex-1 min-h-0 overflow-hidden d-flex flex-column"
            x-data="{
                selectedId: null,
                clients: @js($previewPayload),
                labels: @js(collect($previewSlugs)->reject(fn ($slug) => in_array($slug, ['first_name', 'last_name', 'client_type', 'department'], true))->mapWithKeys(fn ($slug) => [$slug => $identityLabels[$slug] ?? $slug])->all()),
                select(id) { this.selectedId = id },
                get selected() { return this.selectedId ? this.clients[this.selectedId] : null },
                field(slug) {
                    const value = this.selected?.fields?.[slug];
                    return value && String(value).trim() !== '' ? value : '—';
                },
                displayField(slug) {
                    const value = this.field(slug);
                    if (slug !== 'date_of_birth' || value === '—') return value;
                    const age = this.selected?.age;
                    return age != null ? value + ' (' + age + ')' : value;
                },
                fullName() {
                    const last = this.field('last_name');
                    const first = this.field('first_name');
                    let name;
                    if (last === '—' && first === '—') name = '—';
                    else if (last === '—') name = first;
                    else if (first === '—') name = last;
                    else name = last + ', ' + first;

                    const department = this.field('department');
                    const clientType = this.field('client_type');
                    const meta = [department, clientType].filter((v) => v !== '—');
                    if (meta.length) name += ' (' + meta.join(' - ') + ')';

                    return name;
                }
            }"
        >
            @if (session('status'))
                <div class="mx-3 mt-3 mb-0 bg-teal-50 border border-teal-200 text-teal-800 px-4 py-2 rounded text-sm">{{ session('status') }}</div>
            @endif

            <div class="container-fluid flex-fill min-h-0 py-3 encoder-clients-shell">
                <div class="row g-3 h-100">
                    {{-- Preview: width 30% --}}
                    <div class="col-30">
                        <div class="col-panel bg-white shadow-sm rounded-lg border border-slate-200 p-4">
                            <div class="h-100 overflow-auto">
                                <template x-if="!selected">
                                    <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center px-3 py-5">
                                        <p class="text-sm font-medium text-slate-700 mb-1">Client preview</p>
                                        <p class="text-sm text-slate-500 mb-0">Select a row to view client details.</p>
                                    </div>
                                </template>

                                <template x-if="selected">
                                    <div class="space-y-4">
                                        <div class="d-flex align-items-start justify-content-between gap-3 border-b border-slate-100 pb-4">
                                            <div class="min-w-0">
                                                <p class="text-xs uppercase tracking-wide text-slate-400 mb-1">Preview</p>
                                                <h3 class="mt-0 font-semibold text-slate-900 break-words" x-text="fullName()"></h3>
                                                <p class="mt-1 font-mono text-xs text-slate-500 break-all mb-0" x-text="selected.system_id"></p>
                                                <p class="mt-1 text-xs text-slate-500 mb-0" x-show="selected.visit_date">
                                                    Latest visit: <span x-text="selected.visit_date"></span>
                                                </p>
                                            </div>
                                            <a
                                                :href="selected.encode_url"
                                                class="shrink-0 inline-flex items-center justify-center px-3 py-1.5 rounded-md bg-teal-700 text-white text-sm hover:bg-teal-800"
                                            >
                                                Encode
                                            </a>
                                        </div>

                                        <dl class="space-y-2 text-sm mb-0">
                                            <template x-for="slug in Object.keys(labels)" :key="slug">
                                                <div>
                                                    <div class="d-flex gap-2">
                                                        <dt class="text-slate-500 shrink-0 mb-0"><span x-text="labels[slug]"></span>:</dt>
                                                        <dd class="text-slate-800 break-words mb-0" x-text="displayField(slug)"></dd>
                                                    </div>
                                                    <template x-if="slug === 'weight_kg'">
                                                        <div class="mt-2 space-y-2">
                                                            <div class="d-flex gap-2">
                                                                <dt class="text-slate-500 shrink-0 mb-0">BMI:</dt>
                                                                <dd
                                                                    class="text-slate-800 mb-0"
                                                                    x-text="(selected.bmi ?? '—') + ' (' + (selected.bmi_category ?? '—') + ')'"
                                                                ></dd>
                                                            </div>
                                                            <hr class="my-3 border-slate-200">
                                                        </div>
                                                    </template>
                                                    <template x-if="slug === 'diastolic'">
                                                        <div class="mt-2 space-y-2">
                                                            <div class="d-flex gap-2">
                                                                <dt class="text-slate-500 shrink-0 mb-0">Blood Pressure (mmHg):</dt>
                                                                <dd
                                                                    class="mb-0"
                                                                    :class="selected.bp_category === 'Hypertensive Crisis' ? 'text-rose-600 font-semibold' : 'text-slate-800'"
                                                                    x-text="(selected.blood_pressure ?? '—') + ' (' + (selected.bp_category ?? '—') + ')'"
                                                                ></dd>
                                                            </div>
                                                            <hr class="my-3 border-slate-200">
                                                        </div>
                                                    </template>
                                                    <template x-if="slug === 'fasting_state'">
                                                        <hr class="my-3 border-slate-200">
                                                    </template>
                                                </div>
                                            </template>
                                        </dl>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Table: width 70% --}}
                    <div class="col-70">
                        <div class="col-panel gap-3">
                            <form method="GET" class="js-live-search shrink-0 bg-white shadow-sm rounded-lg p-3 border border-slate-200">
                                <input
                                    type="search"
                                    name="q"
                                    value="{{ $search }}"
                                    placeholder="Search as you type by ID, name, DOB…"
                                    class="w-100 rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600 text-sm"
                                    autocomplete="off"
                                />
                            </form>

                            <div class="encoder-clients-table flex-fill min-h-0 bg-white shadow-sm rounded-lg border border-slate-200">
                                <table class="mb-0 text-sm">
                                    <thead class="bg-slate-50 sticky-top">
                                        <tr>
                                            <th class="px-4 py-3 text-start font-medium text-slate-600 text-nowrap">Last Name</th>
                                            <th class="px-4 py-3 text-start font-medium text-slate-600 text-nowrap">First Name</th>
                                            <th class="px-4 py-3 text-start font-medium text-slate-600 text-nowrap">Age</th>
                                            <th class="px-4 py-3 text-start font-medium text-slate-600 text-nowrap">Sex</th>
                                            <th class="px-4 py-3 text-start font-medium text-slate-600 text-nowrap">Height (cm)</th>
                                            <th class="px-4 py-3 text-start font-medium text-slate-600 text-nowrap">Weight (kg)</th>
                                            <th class="px-4 py-3 text-start font-medium text-slate-600 text-nowrap">BMI</th>
                                            <th class="px-4 py-3 text-start font-medium text-slate-600 text-nowrap">BMI Category</th>
                                            <th class="px-4 py-3 text-start font-medium text-slate-600 text-nowrap">BP (mmHg)</th>
                                            <th class="px-4 py-3 text-start font-medium text-slate-600 text-nowrap">BP Category</th>
                                            <th class="px-4 py-3 text-end font-medium text-slate-600 text-nowrap">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($clients as $client)
                                            @php
                                                $map = ($client->latestVisit?->fieldValues ?? collect())->keyBy(fn ($v) => $v->formField?->slug);
                                                $bpCategory = $client->bpCategory();
                                            @endphp
                                            <tr
                                                class="cursor-pointer"
                                                :class="selectedId === {{ $client->id }} ? 'bg-teal-50' : 'hover:bg-slate-50'"
                                                @click="select({{ $client->id }})"
                                            >
                                                <td class="px-4 py-3 text-nowrap">{{ $map->get('last_name')?->value ?: '—' }}</td>
                                                <td class="px-4 py-3 text-nowrap">{{ $map->get('first_name')?->value ?: '—' }}</td>
                                                <td class="px-4 py-3 text-nowrap">{{ $client->age() ?? '—' }}</td>
                                                <td class="px-4 py-3 text-nowrap">{{ $map->get('sex')?->value ?: '—' }}</td>
                                                <td class="px-4 py-3 text-nowrap">{{ $map->get('height_cm')?->value ?: '—' }}</td>
                                                <td class="px-4 py-3 text-nowrap">{{ $map->get('weight_kg')?->value ?: '—' }}</td>
                                                <td class="px-4 py-3 text-nowrap">{{ $client->bmi() ?? '—' }}</td>
                                                <td class="px-4 py-3 text-nowrap">{{ $client->bmiCategory() ?? '—' }}</td>
                                                <td class="px-4 py-3 text-nowrap">{{ $client->bloodPressure() ?? '—' }}</td>
                                                <td @class([
                                                    'px-4 py-3 text-nowrap',
                                                    'text-rose-600 font-semibold' => $bpCategory === 'Hypertensive Crisis',
                                                ])>{{ $bpCategory ?? '—' }}</td>
                                                <td class="px-4 py-3 text-end text-nowrap" @click.stop>
                                                    <a href="{{ route('clients.encode', $client) }}" class="text-teal-700 hover:underline font-medium">Encode</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="px-4 py-8 text-center text-slate-500">No clients found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="shrink-0 overflow-x-auto">{{ $clients->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="flex-1 overflow-y-auto py-6 sm:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
                @if (session('status'))
                    <div class="bg-teal-50 border border-teal-200 text-teal-800 px-4 py-3 rounded text-sm">{{ session('status') }}</div>
                @endif

                @if (auth()->user()->canBulkCreateVisits())
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-red-800 mb-1">Super Admin: bulk new visits</p>
                            <p class="text-sm text-red-700 mb-0">
                                Create a blank form for all {{ number_format($clientTotalCount ?? 0) }} clients.
                                Identity is kept; clinical fields start empty. Previous visits stay in history.
                            </p>
                        </div>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center shrink-0 px-4 py-2 bg-red-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-500"
                            onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-bulk-create-visits' }))"
                        >
                            Start new forms for all clients
                        </button>
                    </div>
                @endif

                <form method="GET" class="js-live-search bg-white shadow-sm rounded-lg p-4">
                    <input
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Search as you type by ID, name, DOB, address…"
                        class="w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600 text-sm"
                        autocomplete="off"
                    />
                </form>

                <div class="space-y-3 lg:hidden">
                    @forelse ($clients as $client)
                        @php
                            $map = ($client->latestVisit?->fieldValues ?? collect())->keyBy(fn ($v) => $v->formField?->slug);
                            $name = trim(($map->get('first_name')?->value.' '.$map->get('last_name')?->value));
                        @endphp
                        <article class="bg-white shadow-sm rounded-lg p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-mono text-xs text-slate-500 break-all">{{ $client->system_id }}</p>
                                    <h3 class="font-semibold text-slate-800 break-words">{{ $name !== '' ? $name : '—' }}</h3>
                                </div>
                                <div class="flex flex-col items-end gap-2 shrink-0 text-sm">
                                    <a href="{{ route('clients.encode', $client) }}" class="text-teal-700 hover:underline font-medium">Encode</a>
                                    <a href="{{ route('clients.show', $client) }}" class="text-slate-600 hover:underline">History</a>
                                    @if (auth()->user()->canSoftDeleteClients())
                                        <form action="{{ route('clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Soft delete this client?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-rose-600 hover:underline">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                @foreach ($identitySlugs as $slug)
                                    @continue(in_array($slug, ['first_name', 'last_name'], true))
                                    <div @class(['col-span-full' => $slug === 'address'])>
                                        <dt class="text-xs uppercase tracking-wide text-slate-400">{{ $identityLabels[$slug] ?? $slug }}</dt>
                                        <dd class="text-slate-700 break-words">{{ $map->get($slug)?->value ?: '—' }}</dd>
                                    </div>
                                    @if ($slug === 'weight_kg')
                                        <div>
                                            <dt class="text-xs uppercase tracking-wide text-slate-400">BMI</dt>
                                            <dd class="text-slate-700">{{ $client->bmi() ?? '—' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs uppercase tracking-wide text-slate-400">BMI Category</dt>
                                            <dd class="text-slate-700">{{ $client->bmiCategory() ?? '—' }}</dd>
                                        </div>
                                    @endif
                                    @if ($slug === 'diastolic')
                                        <div>
                                            <dt class="text-xs uppercase tracking-wide text-slate-400">Blood Pressure (mmHg)</dt>
                                            <dd class="text-slate-700">{{ $client->bloodPressure() ?? '—' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs uppercase tracking-wide text-slate-400">BP Category</dt>
                                            <dd @class([
                                                'text-slate-700',
                                                'text-rose-600 font-semibold' => $client->bpCategory() === 'Hypertensive Crisis',
                                            ])>{{ $client->bpCategory() ?? '—' }}</dd>
                                        </div>
                                    @endif
                                @endforeach
                            </dl>
                        </article>
                    @empty
                        <div class="bg-white shadow-sm rounded-lg px-4 py-8 text-center text-slate-500 text-sm">No clients found.</div>
                    @endforelse
                </div>

                <div class="hidden lg:block bg-white shadow-sm rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-slate-600 whitespace-nowrap sticky left-0 bg-slate-50 z-10">System ID</th>
                                @foreach ($identitySlugs as $slug)
                                    <th class="px-4 py-3 text-left font-medium text-slate-600 whitespace-nowrap">{{ $identityLabels[$slug] ?? $slug }}</th>
                                    @if ($slug === 'weight_kg')
                                        <th class="px-4 py-3 text-left font-medium text-slate-600 whitespace-nowrap">BMI</th>
                                        <th class="px-4 py-3 text-left font-medium text-slate-600 whitespace-nowrap">BMI Category</th>
                                    @endif
                                    @if ($slug === 'diastolic')
                                        <th class="px-4 py-3 text-left font-medium text-slate-600 whitespace-nowrap">BP (mmHg)</th>
                                        <th class="px-4 py-3 text-left font-medium text-slate-600 whitespace-nowrap">BP Category</th>
                                    @endif
                                @endforeach
                                <th class="px-4 py-3 text-right font-medium text-slate-600 whitespace-nowrap sticky right-0 bg-slate-50 z-10">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($clients as $client)
                                @php
                                    $map = ($client->latestVisit?->fieldValues ?? collect())->keyBy(fn ($v) => $v->formField?->slug);
                                @endphp
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-mono text-xs whitespace-nowrap sticky left-0 bg-white z-10">{{ $client->system_id }}</td>
                                    @foreach ($identitySlugs as $slug)
                                        <td @class([
                                            'px-4 py-3',
                                            'whitespace-nowrap' => $slug !== 'address',
                                            'max-w-[16rem] break-words' => $slug === 'address',
                                        ])>{{ $map->get($slug)?->value ?: '—' }}</td>
                                        @if ($slug === 'weight_kg')
                                            <td class="px-4 py-3 whitespace-nowrap">{{ $client->bmi() ?? '—' }}</td>
                                            <td class="px-4 py-3 whitespace-nowrap">{{ $client->bmiCategory() ?? '—' }}</td>
                                        @endif
                                        @if ($slug === 'diastolic')
                                            <td class="px-4 py-3 whitespace-nowrap">{{ $client->bloodPressure() ?? '—' }}</td>
                                            <td @class([
                                                'px-4 py-3 whitespace-nowrap',
                                                'text-rose-600 font-semibold' => $client->bpCategory() === 'Hypertensive Crisis',
                                            ])>{{ $client->bpCategory() ?? '—' }}</td>
                                        @endif
                                    @endforeach
                                    <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap sticky right-0 bg-white z-10">
                                        <a href="{{ route('clients.encode', $client) }}" class="text-teal-700 hover:underline">Encode</a>
                                        <a href="{{ route('clients.show', $client) }}" class="text-slate-600 hover:underline">History</a>
                                        @if (auth()->user()->canSoftDeleteClients())
                                            <form action="{{ route('clients.destroy', $client) }}" method="POST" class="inline" onsubmit="return confirm('Soft delete this client?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-rose-600 hover:underline">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="16" class="px-4 py-8 text-center text-slate-500">No clients found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="overflow-x-auto">{{ $clients->links() }}</div>
            </div>
        </div>
    @endif

    @if (auth()->user()->canBulkCreateVisits())
        <x-modal name="confirm-bulk-create-visits" :show="$errors->has('confirmation')" focusable>
            <form
                method="POST"
                action="{{ route('clients.visits.bulk-store') }}"
                class="p-6"
                x-data="{ confirmation: @js(old('confirmation', '')) }"
            >
                @csrf

                <h2 class="text-lg font-medium text-slate-900">
                    Start new forms for all clients?
                </h2>

                <div class="mt-3 space-y-2 text-sm text-slate-600">
                    <p class="mb-0">
                        This will create a new visit for
                        <span class="font-semibold text-slate-800">{{ number_format($clientTotalCount ?? 0) }}</span>
                        client{{ ($clientTotalCount ?? 0) === 1 ? '' : 's' }}.
                    </p>
                    <ul class="list-disc ps-5 space-y-1 mb-0">
                        <li>Identity fields (name, DOB, sex, patient type) are copied from each client’s latest visit.</li>
                        <li>Clinical fields will be blank on the new forms.</li>
                        <li>Previous visits remain available in history.</li>
                        <li>This cannot be undone. Running it again creates another round of visits.</li>
                    </ul>
                </div>

                <div class="mt-6">
                    <x-input-label for="bulk_visit_confirmation" value='Type CREATE to confirm' />
                    <x-text-input
                        id="bulk_visit_confirmation"
                        name="confirmation"
                        type="text"
                        class="mt-1 block w-full"
                        x-model="confirmation"
                        autocomplete="off"
                        placeholder="CREATE"
                    />
                    <x-input-error :messages="$errors->get('confirmation')" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button type="button" x-on:click="$dispatch('close')">
                        Cancel
                    </x-secondary-button>

                    <x-danger-button
                        class="ms-0"
                        x-bind:disabled="confirmation !== 'CREATE'"
                        x-bind:class="confirmation !== 'CREATE' ? 'opacity-50 cursor-not-allowed' : ''"
                    >
                        Create blank visits
                    </x-danger-button>
                </div>
            </form>
        </x-modal>
    @endif
</x-app-layout>
