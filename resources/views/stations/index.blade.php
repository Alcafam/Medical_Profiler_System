<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Stations</h2>
            <a href="{{ route('stations.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-teal-700 text-white rounded-md text-sm w-full sm:w-auto">Add Station</a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-teal-50 border border-teal-200 text-teal-800 px-4 py-3 rounded text-sm">{{ session('status') }}</div>
            @endif

            <div class="space-y-3 sm:hidden">
                @foreach ($stations as $station)
                    <article class="bg-white shadow-sm rounded-lg p-4 space-y-2">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-semibold text-slate-800 break-words">{{ $station->name }}</h3>
                            <span class="text-xs text-slate-500 shrink-0">#{{ $station->sort_order }}</span>
                        </div>
                        <p class="text-sm text-slate-600">{{ $station->is_active ? 'Active' : 'Inactive' }}</p>
                        <div class="flex gap-3 text-sm">
                            <a href="{{ route('stations.edit', $station) }}" class="text-teal-700 hover:underline">Edit</a>
                            <form action="{{ route('stations.destroy', $station) }}" method="POST" onsubmit="return confirm('Delete this station?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-rose-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="hidden sm:block bg-white shadow-sm rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left">Order</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($stations as $station)
                            <tr>
                                <td class="px-4 py-3">{{ $station->name }}</td>
                                <td class="px-4 py-3">{{ $station->sort_order }}</td>
                                <td class="px-4 py-3">{{ $station->is_active ? 'Active' : 'Inactive' }}</td>
                                <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('stations.edit', $station) }}" class="text-teal-700 hover:underline">Edit</a>
                                    <form action="{{ route('stations.destroy', $station) }}" method="POST" class="inline" onsubmit="return confirm('Delete this station?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-rose-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
