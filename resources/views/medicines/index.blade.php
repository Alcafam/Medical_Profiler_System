<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ $archived ? 'Archived Medicines' : 'Medicine Inventory' }}
            </h2>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                @if ($archived)
                    <a href="{{ route('medicines.index') }}" class="inline-flex items-center justify-center px-4 py-2 border border-slate-300 rounded-md text-sm text-slate-700">Active inventory</a>
                @else
                    <a href="{{ route('medicines.index', ['archived' => 1]) }}" class="inline-flex items-center justify-center px-4 py-2 border border-slate-300 rounded-md text-sm text-slate-700">Archived</a>
                    <a href="{{ route('medicines.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-teal-700 text-white rounded-md text-sm">Add Medicine</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-teal-50 border border-teal-200 text-teal-800 px-4 py-3 rounded text-sm">{{ session('status') }}</div>
            @endif

            <p class="text-xs text-slate-500 mb-0">
                Row colors: light yellow = expires next month · light red = expires this month · dark red = already expired.
            </p>

            <div class="space-y-3 sm:hidden">
                @forelse ($medicines as $medicine)
                    <article @class(['shadow-sm rounded-lg p-4 space-y-2', $medicine->expiryRowClass() ?: 'bg-white'])>
                        <h3 class="font-semibold break-words">{{ $medicine->generic_name }}</h3>
                        <p class="text-sm mb-0">{{ $medicine->brand_name }} · {{ $medicine->dosage_strength ?: '—' }}</p>
                        <p class="text-sm mb-0">Exp: {{ $medicine->expirationLabel() }}</p>
                        <p class="text-sm mb-0">
                            Qty {{ $medicine->quantity }} · Disp {{ $medicine->quantity_dispensed }} · Rem {{ $medicine->quantityRemaining() }}
                        </p>
                        @if ($medicine->remarks)
                            <p class="text-xs opacity-80 mb-0">{{ $medicine->remarks }}</p>
                        @endif
                        <div class="flex gap-3 text-sm pt-1">
                            <a href="{{ route('medicines.edit', $medicine) }}" class="underline">Edit</a>
                            @if ($archived)
                                <form action="{{ route('medicines.restore', $medicine) }}" method="POST" onsubmit="return confirm('Restore this medicine?')">
                                    @csrf
                                    <button class="underline">Restore</button>
                                </form>
                            @else
                                <form action="{{ route('medicines.destroy', $medicine) }}" method="POST" onsubmit="return confirm('Archive this medicine?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="underline">Archive</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="bg-white shadow-sm rounded-lg p-6 text-sm text-slate-500 text-center">No medicines found.</div>
                @endforelse
            </div>

            <div class="hidden sm:block bg-white shadow-sm rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-slate-200 table-fixed">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-3 text-left w-[15%]">Generic Name</th>
                            <th class="px-3 py-3 text-left">Brand Name</th>
                            <th class="px-3 py-3 text-left">Dosage Strength</th>
                            <th class="px-3 py-3 text-left">Expiration Date</th>
                            <th class="px-3 py-3 text-right">Quantity</th>
                            <th class="px-3 py-3 text-right">QTY Dispensed</th>
                            <th class="px-3 py-3 text-right">QTY Remaining</th>
                            <th class="px-3 py-3 text-left w-[15%]">Remarks</th>
                            <th class="px-3 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($medicines as $medicine)
                            <tr @class([$medicine->expiryRowClass()])>
                                <td class="px-3 py-3 w-[15%] whitespace-normal break-words">{{ $medicine->generic_name }}</td>
                                <td class="px-3 py-3">{{ $medicine->brand_name }}</td>
                                <td class="px-3 py-3">{{ $medicine->dosage_strength ?: '—' }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $medicine->expirationLabel() }}</td>
                                <td class="px-3 py-3 text-right">{{ number_format($medicine->quantity) }}</td>
                                <td class="px-3 py-3 text-right">{{ number_format($medicine->quantity_dispensed) }}</td>
                                <td class="px-3 py-3 text-right font-medium">{{ number_format($medicine->quantityRemaining()) }}</td>
                                <td class="px-3 py-3 w-[15%] whitespace-normal break-words">{{ $medicine->remarks ?: '—' }}</td>
                                <td class="px-3 py-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('medicines.edit', $medicine) }}" class="underline">Edit</a>
                                    @if ($archived)
                                        <form action="{{ route('medicines.restore', $medicine) }}" method="POST" class="inline" onsubmit="return confirm('Restore this medicine?')">
                                            @csrf
                                            <button class="underline">Restore</button>
                                        </form>
                                    @else
                                        <form action="{{ route('medicines.destroy', $medicine) }}" method="POST" class="inline" onsubmit="return confirm('Archive this medicine?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="underline">Archive</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-slate-500">No medicines found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $medicines->links() }}</div>
        </div>
    </div>
</x-app-layout>
