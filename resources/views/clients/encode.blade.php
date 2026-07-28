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
            href="{{ route('clients.index') }}"
            class="encode-back-fab"
            title="Back to clients"
            aria-label="Back to clients"
            style="position:fixed;top:1.25rem;right:1.25rem;z-index:1100;display:inline-flex;align-items:center;justify-content:center;width:3.5rem;height:3.5rem;border-radius:50%;background-color:#2563eb;color:#fff;box-shadow:0 6px 10px rgba(15,23,42,.18),0 2px 4px rgba(15,23,42,.12);text-decoration:none;"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="22" height="22" aria-hidden="true">
                <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 1 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
            </svg>
        </a>
    @endpush

    @if (! empty($consultationLockHeld))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (typeof window.startConsultationLockHeartbeat === 'function') {
                    window.startConsultationLockHeartbeat({
                        heartbeatUrl: @js($consultationHeartbeatUrl),
                        releaseUrl: @js($consultationReleaseUrl),
                    });
                }
            });
        </script>
    @endif

    <div class="py-6 sm:py-8">
        <div class="container-fluid encode-layout-shell px-3 px-sm-4 px-lg-5">
            @if (session('status'))
                <div class="bg-teal-50 border border-teal-200 text-teal-800 px-4 py-3 rounded text-sm mb-3">{{ session('status') }}</div>
            @endif

            @php $viewStationId = (int) request('view', $activeStationId ?: $stations->first()?->id); @endphp

            <div class="row g-3 align-items-start">
                {{-- Left: History (30%) --}}
                <div class="col-30">
                    <div
                        class="bg-white shadow-sm rounded-lg border border-slate-200 overflow-hidden"
                        x-data="{ openId: {{ $visit->id }} }"
                    >
                        <div class="px-4 py-3 border-b border-slate-100">
                            <h3 class="text-sm font-semibold text-slate-800 mb-0">History</h3>
                        </div>

                        <div class="overflow-auto" style="max-height: 70vh;">
                            @forelse ($historyVisits as $historyVisit)
                                @php $isCurrentVisit = $visit->is($historyVisit); @endphp
                                <div @class([
                                    'border-b border-slate-100' => ! $loop->last,
                                    'bg-emerald-100' => $isCurrentVisit,
                                    'bg-amber-50' => ! $isCurrentVisit,
                                ])>
                                    <button
                                        type="button"
                                        @class([
                                            'w-100 d-flex align-items-center justify-content-between gap-3 px-4 py-3 text-start border-0',
                                            'bg-transparent hover:bg-emerald-200/60' => $isCurrentVisit,
                                            'bg-transparent hover:bg-amber-100' => ! $isCurrentVisit,
                                        ])
                                        @click="openId = openId === {{ $historyVisit->id }} ? null : {{ $historyVisit->id }}"
                                        :aria-expanded="openId === {{ $historyVisit->id }} ? 'true' : 'false'"
                                    >
                                        <span class="d-flex align-items-center flex-wrap gap-2 min-w-0">
                                            <span @class([
                                                'text-sm font-medium',
                                                'text-emerald-900' => $isCurrentVisit,
                                                'text-amber-950' => ! $isCurrentVisit,
                                            ])>
                                                {{ $historyVisit->visited_at?->format('Y-m-d H:i') ?? '—' }}
                                            </span>
                                            @if ($isCurrentVisit)
                                                <span class="text-xs uppercase tracking-wide text-emerald-800 bg-emerald-50 border border-emerald-300 px-2 py-0.5 rounded">Current</span>
                                            @endif
                                        </span>
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            @class([
                                                'w-4 h-4 shrink-0 transition-transform',
                                                'text-emerald-700' => $isCurrentVisit,
                                                'text-amber-600' => ! $isCurrentVisit,
                                            ])
                                            :class="openId === {{ $historyVisit->id }} ? 'rotate-180' : ''"
                                            aria-hidden="true"
                                        >
                                            <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    <div
                                        x-show="openId === {{ $historyVisit->id }}"
                                        x-cloak
                                        @class([
                                            'px-4 pb-4 border-t',
                                            'border-emerald-200 bg-emerald-50/80' => $isCurrentVisit,
                                            'border-amber-100 bg-amber-50/80' => ! $isCurrentVisit,
                                        ])
                                    >
                                        @include('clients._visit-preview', [
                                            'visit' => $historyVisit,
                                            'client' => $client,
                                            'previewSlugs' => $previewSlugs,
                                            'previewLabels' => $previewLabels,
                                            'liveSync' => $isCurrentVisit,
                                        ])
                                    </div>
                                </div>
                            @empty
                                <div class="px-4 py-6 text-sm text-slate-500 text-center">No visits yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Right: Station tabs + Form (70%) --}}
                <div class="col-70">
                    <div class="d-flex flex-column gap-3">
                        {{-- Station div --}}
                        <div class="bg-white shadow-sm rounded-lg p-3 sm:p-4">
                            <div class="d-flex align-items-center justify-content-between gap-3">
                                <div class="d-flex gap-2 overflow-auto pb-1 min-w-0">
                                    @foreach ($stations as $station)
                                        <a
                                            href="{{ route('clients.visits.encode', ['client' => $client, 'visit' => $visit, 'station' => $station->id, 'view' => $station->id]) }}"
                                            @class([
                                                'px-3 py-1.5 rounded-md text-sm border whitespace-nowrap shrink-0',
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
                                                'px-3 py-1.5 rounded-md text-sm border whitespace-nowrap shrink-0',
                                                'bg-teal-700 text-white border-teal-700' => (int) request('view', $activeStationId) === 0,
                                                'bg-white text-slate-700 border-slate-300' => (int) request('view', $activeStationId) !== 0,
                                            ])
                                        >
                                            Unassigned
                                        </a>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('clients.visits.store', $client) }}" class="shrink-0 ms-auto">
                                    @csrf
                                    <button class="px-3 py-1.5 rounded-md text-sm border border-teal-700 text-teal-800 hover:bg-teal-50 whitespace-nowrap">
                                        New Visit
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Form div --}}
                        @foreach ($stations as $station)
                            @if ((int) $viewStationId === (int) $station->id)
                                <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                                    <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ $station->name }}</h3>

                                    <div class="d-flex flex-column gap-4">
                                        @forelse ($station->formFields as $field)
                                            <div>
                                                <x-autosave-field
                                                    :field="$field"
                                                    :value="$values->get($field->id)"
                                                    :editable="true"
                                                    :save-url="route('clients.visits.fields.save', [$client, $visit, $field])"
                                                />
                                            </div>
                                        @empty
                                            @unless (in_array($station->name, ['Pharmacy', 'Consultation'], true))
                                                <p class="text-sm text-slate-500 mb-0">No fields assigned to this station.</p>
                                            @endunless
                                        @endforelse

                                        @if ($station->name === 'Consultation')
                                            @php
                                                $dispositionValue = $visit->disposition?->value ?? \App\Enums\VisitDisposition::Active->value;
                                            @endphp
                                            <div
                                                class="rounded-lg border border-slate-200 bg-slate-50 p-4"
                                                x-data="consultationDisposition({
                                                    url: @js(route('clients.visits.disposition', [$client, $visit])),
                                                    initial: @js($dispositionValue),
                                                    reloadOnChange: false,
                                                })"
                                            >
                                                <label class="block text-sm font-medium text-slate-800 mb-2">Disposition</label>
                                                <select
                                                    x-model="disposition"
                                                    @change="save()"
                                                    :disabled="saving"
                                                    :class="disposition === 'active'
                                                        ? 'border-emerald-500 bg-emerald-50 text-emerald-900 focus:border-emerald-600 focus:ring-emerald-600'
                                                        : 'border-slate-300 bg-white text-slate-800 focus:border-teal-600 focus:ring-teal-600'"
                                                    class="w-full rounded-md text-sm shadow-sm"
                                                >
                                                    @foreach (\App\Enums\VisitDisposition::cases() as $disposition)
                                                        <option value="{{ $disposition->value }}">{{ $disposition->label() }}</option>
                                                    @endforeach
                                                </select>
                                                <p class="text-xs mt-2 mb-0" :class="statusClass" x-text="statusText"></p>
                                            </div>

                                            @include('clients._recommended-medicines')
                                        @endif

                                        @if ($station->name === 'Pharmacy')
                                            @include('clients._dispensed-medicines')
                                        @endif

                                        @if ($station->name === 'Blood Glucose')
                                            <div
                                                class="rounded-lg border border-slate-200 bg-slate-50 p-4"
                                                x-data="consultationQueueToggle({
                                                    url: @js(route('clients.visits.consultation-queue', [$client, $visit])),
                                                    initial: @js($visit->isQueuedForConsultation()),
                                                    locked: @js($visit->disposition !== null && $visit->disposition !== \App\Enums\VisitDisposition::Active),
                                                })"
                                            >
                                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                                                    <div>
                                                        <p class="text-sm font-medium text-slate-800 mb-0">Send to consultation</p>
                                                        <p class="text-xs text-slate-500 mb-0">Adds this patient to the consultation queue when enabled.</p>
                                                    </div>
                                                    <label class="d-inline-flex align-items-center gap-2 text-sm" style="cursor:pointer;">
                                                        <input
                                                            type="checkbox"
                                                            class="rounded border-slate-300 text-teal-700 focus:ring-teal-600"
                                                            x-model="queued"
                                                            @change="save()"
                                                            :disabled="saving || locked"
                                                        >
                                                        <span x-text="queued ? 'Queued' : 'Not queued'"></span>
                                                    </label>
                                                </div>
                                                <p class="text-xs mt-2 mb-0" :class="statusClass" x-text="statusText"></p>
                                                <p class="text-xs text-amber-700 mt-1 mb-0" x-show="locked" x-cloak>
                                                    Queue lock: disposition is already completed.
                                                </p>
                                            </div>
                                        @endif

                                        @if ($station->formFields->contains(fn ($field) => in_array($field->slug, ['height_cm', 'weight_kg'], true)))
                                            <div class="js-bmi-panel rounded-lg border border-slate-200 bg-slate-50 p-4">
                                                <div class="row g-3">
                                                    <div class="col-12 col-sm-6">
                                                        <p class="text-xs uppercase tracking-wide text-slate-400 mb-1">BMI</p>
                                                        <p class="mt-0 text-lg font-semibold text-slate-800 js-bmi-value mb-0">{{ $visit->bmi() ?? '—' }}</p>
                                                    </div>
                                                    <div @class([
                                                        'col-12 col-sm-6 rounded px-2 py-2',
                                                        \App\Support\BmiCalculator::categoryBackgroundClass($visit->bmiCategory()),
                                                    ])>
                                                        <p class="text-xs uppercase tracking-wide opacity-70 mb-1">BMI Category</p>
                                                        <p class="mt-0 text-lg font-semibold js-bmi-category mb-0">{{ $visit->bmiCategory() ?? '—' }}</p>
                                                    </div>
                                                    <div class="col-12">
                                                        <p class="text-xs text-slate-500 mb-0">Calculated from Height and Weight (Asian criteria). Not saved to the database.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($station->formFields->contains(fn ($field) => in_array($field->slug, ['systolic', 'diastolic'], true)))
                                            <div class="js-bp-panel rounded-lg border border-slate-200 bg-slate-50 p-4">
                                                <div class="row g-3">
                                                    <div class="col-12 col-sm-6">
                                                        <p class="text-xs uppercase tracking-wide text-slate-400 mb-1">Blood Pressure (mmHg)</p>
                                                        <p class="mt-0 text-lg font-semibold text-slate-800 js-bp-value mb-0">{{ $visit->bloodPressure() ?? '—' }}</p>
                                                    </div>
                                                    <div @class([
                                                        'col-12 col-sm-6 rounded px-2 py-2',
                                                        \App\Support\BpCategoryCalculator::categoryBackgroundClass($visit->bpCategory()),
                                                    ])>
                                                        <p class="text-xs uppercase tracking-wide opacity-70 mb-1">BP Category</p>
                                                        <p class="mt-0 text-lg font-semibold js-bp-category mb-0">{{ $visit->bpCategory() ?? '—' }}</p>
                                                    </div>
                                                    <div class="col-12">
                                                        <p class="text-xs text-slate-500 mb-0">Calculated from Systolic/Diastolic. Not saved to the database.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if ($unassigned->isNotEmpty() && $viewStationId === 0)
                            <div class="bg-white shadow-sm rounded-lg p-4 sm:p-6">
                                <h3 class="text-lg font-semibold text-slate-800 mb-4">Unassigned Fields</h3>
                                <div class="d-flex flex-column gap-4">
                                    @foreach ($unassigned as $field)
                                        <div>
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
            </div>
        </div>
    </div>
</x-app-layout>
