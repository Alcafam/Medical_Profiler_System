<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div class="min-w-0">
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">Client History</h2>
                <p class="text-sm text-slate-500 font-mono break-all mt-1">
                    {{ $client->system_id }}
                    ·
                    <span class="font-semibold text-slate-800 font-sans">{{ strtoupper($client->displayName()) }}</span>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('clients.index') }}" class="px-4 py-2 bg-white border border-slate-300 rounded-md text-sm text-slate-700 hover:bg-slate-50">
                    Back
                </a>
                <form method="POST" action="{{ route('clients.visits.store', $client) }}">
                    @csrf
                    <button class="px-4 py-2 bg-teal-700 text-white rounded-md text-sm hover:bg-teal-800">
                        New Visit
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-teal-50 border border-teal-200 text-teal-800 px-4 py-3 rounded text-sm">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm rounded-lg border border-slate-200 p-4 text-sm text-slate-600">
                Each visit keeps its own form values. Creating a new visit copies only
                last name, first name, date of birth, sex, and client type from the previous visit.
            </div>

            <div class="bg-white shadow-sm rounded-lg border border-slate-200 overflow-hidden">
                <table class="min-w-full text-sm divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Visit Date</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Name (this visit)</th>
                            <th class="px-4 py-3 text-left font-medium text-slate-600">Created By</th>
                            <th class="px-4 py-3 text-right font-medium text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($client->visits as $visit)
                            <tr @class(['bg-teal-50/40' => $client->latestVisit?->is($visit)])>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $visit->visited_at?->format('Y-m-d H:i') }}
                                    @if ($client->latestVisit?->is($visit))
                                        <span class="ms-2 text-xs uppercase tracking-wide text-teal-700">Latest</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $visit->displayName() }}</td>
                                <td class="px-4 py-3">{{ $visit->creator?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('clients.visits.encode', [$client, $visit]) }}" class="text-teal-700 hover:underline font-medium">
                                        Open / Encode
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">No visits yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
