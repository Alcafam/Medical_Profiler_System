<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">Consultation Queue</h2>
                <p class="text-sm text-slate-500 mt-0.5 mb-0">Patients sent from Blood Glucose for consultation</p>
            </div>
        </div>
    </x-slot>

    <div
        class="py-6 sm:py-8"
        @if ($tab === 'active')
            x-data="consultationQueueLocks({
                pollUrl: @js($locksPollUrl),
                currentUserId: @js($currentUserId),
                initialLocks: @js($initialLocks),
            })"
        @endif
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-teal-50 border border-teal-200 text-teal-800 px-4 py-3 rounded text-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded text-sm">{{ $errors->first() }}</div>
            @endif

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex gap-2">
                    <a
                        href="{{ route('clients.index', ['tab' => 'active', 'q' => $search]) }}"
                        @class([
                            'px-3 py-1.5 rounded-md text-sm border',
                            'bg-teal-700 text-white border-teal-700' => $tab === 'active',
                            'bg-white text-slate-700 border-slate-300' => $tab !== 'active',
                        ])
                    >
                        Active
                        <span class="ms-1 opacity-80">({{ $activeCount }})</span>
                    </a>
                    <a
                        href="{{ route('clients.index', ['tab' => 'completed', 'q' => $search]) }}"
                        @class([
                            'px-3 py-1.5 rounded-md text-sm border',
                            'bg-teal-700 text-white border-teal-700' => $tab === 'completed',
                            'bg-white text-slate-700 border-slate-300' => $tab !== 'completed',
                        ])
                    >
                        Completed
                        <span class="ms-1 opacity-80">({{ $completedCount }})</span>
                    </a>
                </div>

                <form method="GET" action="{{ route('clients.index') }}" class="flex gap-2 w-full sm:w-auto">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Search name or ID…"
                        class="w-full sm:w-64 rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600 text-sm"
                    >
                    <button class="px-3 py-1.5 rounded-md text-sm bg-slate-800 text-white shrink-0">Search</button>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-3 text-left whitespace-nowrap">Log Time</th>
                            <th class="px-3 py-3 text-left whitespace-nowrap">Name</th>
                            <th class="px-3 py-3 text-left whitespace-nowrap">Age</th>
                            <th class="px-3 py-3 text-left whitespace-nowrap">Sex</th>
                            <th class="px-3 py-3 text-left whitespace-nowrap">Weight</th>
                            <th class="px-3 py-3 text-left whitespace-nowrap">Height</th>
                            <th class="px-3 py-3 text-left whitespace-nowrap">BMI</th>
                            <th class="px-3 py-3 text-left whitespace-nowrap">BMI Category</th>
                            <th class="px-3 py-3 text-left whitespace-nowrap">BP (mmHg)</th>
                            <th class="px-3 py-3 text-left whitespace-nowrap">BP Category</th>
                            <th class="px-3 py-3 text-left whitespace-nowrap">Disposition</th>
                            <th class="px-3 py-3 text-right whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($visits as $visit)
                            @php
                                $dispositionValue = $visit->disposition?->value ?? \App\Enums\VisitDisposition::Active->value;
                            @endphp
                            <tr
                                data-visit-id="{{ $visit->id }}"
                                @if ($tab === 'active')
                                    :class="$store.consultationLocks.isLockedByOther({{ $visit->id }}) ? 'bg-sky-50' : ''"
                                @endif
                            >
                                <td class="px-3 py-3 whitespace-nowrap font-medium text-slate-800">
                                    {{ $visit->waitingLabel() }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <div class="font-medium text-slate-800">{{ $visit->displayName() }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $visit->client?->system_id }}</div>
                                    @if ($tab === 'active')
                                        <div
                                            class="mt-1"
                                            x-show="$store.consultationLocks.lockFor({{ $visit->id }})"
                                            x-cloak
                                        >
                                            <span
                                                class="inline-flex items-center text-[11px] uppercase tracking-wide px-2 py-0.5 rounded border"
                                                :class="$store.consultationLocks.lockFor({{ $visit->id }})?.is_mine
                                                    ? 'bg-teal-50 text-teal-800 border-teal-200'
                                                    : 'bg-sky-100 text-sky-900 border-sky-300'"
                                                x-text="$store.consultationLocks.lockFor({{ $visit->id }})?.is_mine
                                                    ? 'You are treating'
                                                    : ('In consult — ' + ($store.consultationLocks.lockFor({{ $visit->id }})?.locked_by_name || 'Consultant'))"
                                            ></span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $visit->age() ?? '—' }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $visit->fieldValue('sex') ?? '—' }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $visit->fieldValue('weight_kg') ?? '—' }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $visit->fieldValue('height_cm') ?? '—' }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $visit->bmi() ?? '—' }}</td>
                                <x-bmi-category
                                    :category="$visit->bmiCategory()"
                                    as="td"
                                    class="px-3 py-3 whitespace-nowrap"
                                />
                                <td class="px-3 py-3 whitespace-nowrap">{{ $visit->bloodPressure() ?? '—' }}</td>
                                <x-bp-category
                                    :category="$visit->bpCategory()"
                                    as="td"
                                    class="px-3 py-3 whitespace-nowrap"
                                />
                                <td
                                    class="px-3 py-3 min-w-[14rem]"
                                    x-data="consultationDisposition({
                                        url: @js(route('clients.visits.disposition', [$visit->client, $visit])),
                                        initial: @js($dispositionValue),
                                    })"
                                >
                                    <select
                                        x-model="disposition"
                                        @change="save()"
                                        @if ($tab === 'active')
                                            :disabled="saving || $store.consultationLocks.isLockedByOther({{ $visit->id }})"
                                        @else
                                            :disabled="saving"
                                        @endif
                                        :class="disposition === 'active'
                                            ? 'border-emerald-500 bg-emerald-50 text-emerald-900 focus:border-emerald-600 focus:ring-emerald-600'
                                            : 'border-slate-300 bg-white text-slate-800 focus:border-teal-600 focus:ring-teal-600'"
                                        class="w-full rounded-md text-sm shadow-sm"
                                    >
                                        @foreach ($dispositions as $disposition)
                                            <option value="{{ $disposition->value }}">{{ $disposition->label() }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs mt-1 mb-0" :class="statusClass" x-text="statusText"></p>
                                </td>
                                <td class="px-3 py-3 text-right whitespace-nowrap">
                                    @if ($tab === 'active')
                                        <span
                                            x-show="$store.consultationLocks.isLockedByOther({{ $visit->id }})"
                                            x-cloak
                                            class="inline-flex items-center px-3 py-1.5 rounded-md text-sm border border-slate-300 text-slate-400 cursor-not-allowed"
                                        >
                                            In consult
                                        </span>
                                        <a
                                            x-show="! $store.consultationLocks.isLockedByOther({{ $visit->id }})"
                                            href="{{ route('clients.encode', $visit->client) }}"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md text-sm bg-teal-700 text-white hover:bg-teal-800"
                                        >
                                            Encode
                                        </a>
                                    @else
                                        <a
                                            href="{{ route('clients.encode', $visit->client) }}"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md text-sm bg-teal-700 text-white hover:bg-teal-800"
                                        >
                                            Encode
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-4 py-10 text-center text-slate-500">
                                    @if ($tab === 'active')
                                        No patients waiting for consultation.
                                    @else
                                        No completed dispositions yet.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="overflow-x-auto">{{ $visits->links() }}</div>
        </div>
    </div>
</x-app-layout>
