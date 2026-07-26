<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 min-w-0">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Encode Visit</h2>
            <div class="text-sm text-slate-500 font-mono break-all d-flex flex-wrap align-items-baseline gap-2">
                <span>{{ $client->system_id }}</span>
                <span>·</span>
                <h2 class="font-bold text-slate-800 leading-tight break-words mb-0 text-xl">{{ strtoupper($visit->displayName()) }}</h2>
            </div>
            <p class="text-xs text-slate-500 mt-1 mb-0">
                Visit: {{ $visit->visited_at?->format('Y-m-d H:i') }}
            </p>
        </div>
    </x-slot>

    @push('floating')
        <a
            href="{{ route('clients.show', $client) }}"
            class="encode-back-fab"
            title="Back to client history"
            aria-label="Back to client history"
            style="position:fixed;top:1.25rem;right:1.25rem;z-index:1100;display:inline-flex;align-items:center;justify-content:center;width:3.5rem;height:3.5rem;border-radius:50%;background-color:#2563eb;color:#fff;box-shadow:0 6px 10px rgba(15,23,42,.18),0 2px 4px rgba(15,23,42,.12);text-decoration:none;"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="22" height="22" aria-hidden="true">
                <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
            </svg>
        </a>
    @endpush

    <div class="py-6 sm:py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-teal-50 border border-teal-200 text-teal-800 px-4 py-3 rounded text-sm">{{ session('status') }}</div>
            @endif

            {{-- Row 1: History accordion --}}
            <div
                class="bg-white shadow-sm rounded-lg border border-slate-200 overflow-hidden"
                x-data="{ openId: null }"
            >
                <div class="px-4 py-3 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-800 mb-0">History</h3>
                </div>

                @forelse ($historyVisits as $historyVisit)
                    <div @class(['border-b border-slate-100' => ! $loop->last])>
                        <button
                            type="button"
                            class="w-100 d-flex align-items-center justify-content-between gap-3 px-4 py-3 text-start bg-transparent border-0 hover:bg-slate-50"
                            @click="openId = openId === {{ $historyVisit->id }} ? null : {{ $historyVisit->id }}"
                            :aria-expanded="openId === {{ $historyVisit->id }} ? 'true' : 'false'"
                        >
                            <span class="d-flex align-items-center flex-wrap gap-2 min-w-0">
                                <span class="text-sm font-medium text-slate-800">
                                    {{ $historyVisit->visited_at?->format('Y-m-d H:i') ?? '—' }}
                                </span>
                                @if ($visit->is($historyVisit))
                                    <span class="text-xs uppercase tracking-wide text-teal-700 bg-teal-50 border border-teal-200 px-2 py-0.5 rounded">Current</span>
                                @endif
                            </span>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                class="w-4 h-4 text-slate-400 shrink-0 transition-transform"
                                :class="openId === {{ $historyVisit->id }} ? 'rotate-180' : ''"
                                aria-hidden="true"
                            >
                                <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div
                            x-show="openId === {{ $historyVisit->id }}"
                            x-cloak
                            class="px-4 pb-4 border-t border-slate-100 bg-slate-50/50"
                        >
                            @include('clients._visit-preview', [
                                'visit' => $historyVisit,
                                'client' => $client,
                                'previewSlugs' => $previewSlugs,
                                'previewLabels' => $previewLabels,
                            ])
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-sm text-slate-500 text-center">No visits yet.</div>
                @endforelse
            </div>

            {{-- Row 2: Station tabs --}}
            <div class="bg-white shadow-sm rounded-lg p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex gap-2 overflow-x-auto pb-1 -mx-1 px-1 snap-x">
                    @foreach ($stations as $station)
                        <a
                            href="{{ route('clients.visits.encode', ['client' => $client, 'visit' => $visit, 'station' => $station->id, 'view' => $station->id]) }}"
                            @class([
                                'px-3 py-1.5 rounded-md text-sm border whitespace-nowrap snap-start shrink-0',
                                'bg-teal-700 text-white border-teal-700' => (int) request('view', $activeStationId) === (int) $station->id,
                                'bg-white text-slate-700 border-slate-300' => (int) request('view', $activeStationId) !== (int) $station->id,
                            ])
                        >
                            {{ $station->name }}
                        </a>
                    @endforeach
                    @if ($unassigned->isNotEmpty())
                        <a
                            href="{{ route('clients.visits.encode', ['client' => $client, 'visit' => $visit, 'station' => $activeStationId, 'view' => 0]) }}"
                            @class([
                                'px-3 py-1.5 rounded-md text-sm border whitespace-nowrap snap-start shrink-0',
                                'bg-teal-700 text-white border-teal-700' => (int) request('view', $activeStationId) === 0,
                                'bg-white text-slate-700 border-slate-300' => (int) request('view', $activeStationId) !== 0,
                            ])
                        >
                            Unassigned
                        </a>
                    @endif
                </div>

                <form method="POST" action="{{ route('clients.visits.store', $client) }}">
                    @csrf
                    <button class="w-full sm:w-auto px-3 py-1.5 rounded-md text-sm border border-teal-700 text-teal-800 hover:bg-teal-50 whitespace-nowrap">
                        New Visit
                    </button>
                </form>
            </div>

            {{-- Row 3: Encode form --}}
            @php $viewStationId = (int) request('view', $activeStationId ?: $stations->first()?->id); @endphp

            @foreach ($stations as $station)
                @if ((int) $viewStationId === (int) $station->id)
                    <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6 space-y-5">
                        <h3 class="text-lg font-semibold text-slate-800">{{ $station->name }}</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                            @forelse ($station->formFields as $field)
                                <div @class(['md:col-span-2' => $field->type->value === 'textarea'])>
                                    <x-autosave-field
                                        :field="$field"
                                        :value="$values->get($field->id)"
                                        :editable="true"
                                        :save-url="route('clients.visits.fields.save', [$client, $visit, $field])"
                                    />
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 md:col-span-2">No fields assigned to this station.</p>
                            @endforelse

                            @if ($station->formFields->contains(fn ($field) => in_array($field->slug, ['height_cm', 'weight_kg'], true)))
                                <div class="md:col-span-2 js-bmi-panel rounded-lg border border-slate-200 bg-slate-50 p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-400">BMI</p>
                                        <p class="mt-1 text-lg font-semibold text-slate-800 js-bmi-value">{{ $visit->bmi() ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-400">BMI Category</p>
                                        <p class="mt-1 text-lg font-semibold text-slate-800 js-bmi-category">{{ $visit->bmiCategory() ?? '—' }}</p>
                                    </div>
                                    <p class="sm:col-span-2 text-xs text-slate-500 mb-0">Calculated from Height and Weight (Asian criteria). Not saved to the database.</p>
                                </div>
                            @endif

                            @if ($station->formFields->contains(fn ($field) => in_array($field->slug, ['systolic', 'diastolic'], true)))
                                <div class="md:col-span-2 js-bp-panel rounded-lg border border-slate-200 bg-slate-50 p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-400">Blood Pressure (mmHg)</p>
                                        <p class="mt-1 text-lg font-semibold text-slate-800 js-bp-value">{{ $visit->bloodPressure() ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-400">BP Category</p>
                                        <p @class([
                                            'mt-1 text-lg font-semibold js-bp-category',
                                            'text-rose-600' => $visit->bpCategory() === 'Hypertensive Crisis',
                                            'text-slate-800' => $visit->bpCategory() !== 'Hypertensive Crisis',
                                        ])>{{ $visit->bpCategory() ?? '—' }}</p>
                                    </div>
                                    <p class="sm:col-span-2 text-xs text-slate-500 mb-0">Calculated from Systolic/Diastolic. Not saved to the database.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach

            @if ($unassigned->isNotEmpty() && $viewStationId === 0)
                <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6 space-y-5">
                    <h3 class="text-lg font-semibold text-slate-800">Unassigned Fields</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
                        @foreach ($unassigned as $field)
                            <div @class(['md:col-span-2' => $field->type->value === 'textarea'])>
                                <x-autosave-field
                                    :field="$field"
                                    :value="$values->get($field->id)"
                                    :editable="true"
                                    :save-url="route('clients.visits.fields.save', [$client, $visit, $field])"
                                />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
