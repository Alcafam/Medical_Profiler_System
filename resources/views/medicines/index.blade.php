<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ $archived ? 'Archived Medicines' : 'Medicine Inventory' }}
            </h2>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    @if ($archived)
                    <a href="{{ route('medicines.index', array_filter(['q' => $q ?: null])) }}" class="inline-flex items-center justify-center px-4 py-2 border border-slate-300 rounded-md text-sm text-slate-700">Active inventory</a>
                @else
                    <a href="{{ route('medicines.index', array_filter(['archived' => 1, 'q' => $q ?: null])) }}" class="inline-flex items-center justify-center px-4 py-2 border border-slate-300 rounded-md text-sm text-slate-700">Archived</a>
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
                Color coding as of <strong>{{ now()->format('F Y') }}</strong>:
                light yellow = expires next month · light red = expires this month · dark red = already expired.
            </p>

            <form method="GET" action="{{ route('medicines.index') }}" class="js-live-search bg-white shadow-sm rounded-lg p-3 sm:p-4 border border-slate-200">
                @if ($archived)
                    <input type="hidden" name="archived" value="1">
                @endif
                <label for="inventory-search" class="block text-sm font-medium text-slate-700 mb-1">Search inventory</label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input
                        id="inventory-search"
                        type="search"
                        name="q"
                        value="{{ $q }}"
                        placeholder="Generic name, brand, strength, or remarks…"
                        class="block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600 text-sm"
                        autocomplete="off"
                    >
                    @if ($q !== '')
                        <a
                            href="{{ route('medicines.index', $archived ? ['archived' => 1] : []) }}"
                            class="inline-flex items-center justify-center px-4 py-2 border border-slate-300 rounded-md text-sm text-slate-700 whitespace-nowrap"
                        >
                            Clear
                        </a>
                    @endif
                </div>
            </form>

            <div class="space-y-3 sm:hidden">
                @forelse ($medicines as $medicine)
                    <article @class(['shadow-sm rounded-lg p-4 space-y-2 text-slate-800', $medicine->expiryRowClass()])>
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
                <table class="w-full text-sm divide-y divide-slate-200" style="table-layout: fixed;">
                    <colgroup>
                        <col style="width: 15%;">
                        <col style="width: 12%;">
                        <col style="width: 12%;">
                        <col style="width: 10%;">
                        <col style="width: 8%;">
                        <col style="width: 9%;">
                        <col style="width: 9%;">
                        <col style="width: 15%;">
                        <col style="width: 10%;">
                    </colgroup>
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-3 text-left">Generic Name</th>
                            <th class="px-3 py-3 text-left">Brand Name</th>
                            <th class="px-3 py-3 text-left">Dosage Strength</th>
                            <th class="px-3 py-3 text-left">Expiration Date</th>
                            <th class="px-3 py-3 text-right">Quantity</th>
                            <th class="px-3 py-3 text-right">QTY Dispensed</th>
                            <th class="px-3 py-3 text-right">QTY Remaining</th>
                            <th class="px-3 py-3 text-left">Remarks</th>
                            <th class="px-3 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($medicines as $medicine)
                            @php $rowBg = $medicine->expiryRowClass(); @endphp
                            <tr class="group">
                                <td class="px-3 py-3 break-words whitespace-normal align-top text-slate-800 {{ $rowBg }}">{{ $medicine->generic_name }}</td>
                                <td class="px-3 py-3 break-words whitespace-normal align-top text-slate-800 {{ $rowBg }}">{{ $medicine->brand_name }}</td>
                                <td class="px-3 py-3 break-words whitespace-normal align-top text-slate-800 {{ $rowBg }}">{{ $medicine->dosage_strength ?: '—' }}</td>
                                <td class="px-3 py-3 whitespace-nowrap align-top text-slate-800 {{ $rowBg }}">{{ $medicine->expirationLabel() }}</td>
                                <td class="px-3 py-3 text-right align-top text-slate-800 {{ $rowBg }}">{{ number_format($medicine->quantity) }}</td>
                                <td class="px-3 py-3 text-right align-top text-slate-800 {{ $rowBg }}">{{ number_format($medicine->quantity_dispensed) }}</td>
                                <td class="px-3 py-3 text-right font-medium align-top text-slate-800 {{ $rowBg }}">{{ number_format($medicine->quantityRemaining()) }}</td>
                                <td class="px-3 py-3 break-words whitespace-normal align-top text-slate-800 {{ $rowBg }}">{{ $medicine->remarks ?: '—' }}</td>
                                <td class="px-3 py-3 text-right space-x-2 whitespace-nowrap align-top text-slate-800 {{ $rowBg }}">
                                    <a href="{{ route('medicines.edit', $medicine) }}" class="underline text-teal-800">Edit</a>
                                    @if ($archived)
                                        <form action="{{ route('medicines.restore', $medicine) }}" method="POST" class="inline" onsubmit="return confirm('Restore this medicine?')">
                                            @csrf
                                            <button class="underline text-teal-800">Restore</button>
                                        </form>
                                    @else
                                        <form action="{{ route('medicines.destroy', $medicine) }}" method="POST" class="inline" onsubmit="return confirm('Archive this medicine?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="underline text-rose-700">Archive</button>
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
