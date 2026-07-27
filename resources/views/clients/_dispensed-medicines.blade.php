<div
    class="rounded-lg border border-slate-200 bg-slate-50 p-4"
    x-data="medicineDispensePanel({
        storeUrl: @js(route('clients.visits.medicine-dispenses.store', [$client, $visit])),
        destroyUrlTemplate: @js(preg_replace('#/\d+$#', '/__ID__', route('clients.visits.medicine-dispenses.destroy', [$client, $visit, 0]))),
        medicines: @js($medicineOptions),
        initialItems: @js($dispensedMedicines),
    })"
    @click.outside="open = false"
>
    <div class="mb-3">
        <h4 class="text-sm font-semibold text-slate-800 mb-0">Dispense medicines</h4>
        <p class="text-xs text-slate-500 mb-0">Select from inventory and enter pieces given. Updates QTY Dispensed / Remaining.</p>
    </div>

    @if (!empty($recommendedMedicines) && count($recommendedMedicines))
        <div class="mb-3 rounded-md border border-teal-200 bg-teal-50 px-3 py-2">
            <p class="text-xs font-semibold text-teal-900 mb-1">From Consultation</p>
            <ul class="mb-0 ps-3 text-xs text-teal-900">
                @foreach ($recommendedMedicines as $rec)
                    <li>
                        {{ $rec['label'] }}
                        @if (!empty($rec['quantity'])) — qty {{ $rec['quantity'] }} @endif
                        @if (!empty($rec['instructions'])) ({{ $rec['instructions'] }}) @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex flex-column gap-3">
        <div class="position-relative">
            <label class="block text-sm font-medium text-slate-700 mb-1">Search medicine (in stock)</label>
            <input
                type="text"
                class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600"
                placeholder="Generic, brand, or strength…"
                x-model="query"
                @focus="open = true"
                @input="open = true; selectedId = ''"
                autocomplete="off"
            >
            <div
                x-show="open && filtered.length"
                x-cloak
                class="position-absolute start-0 end-0 mt-1 bg-white border border-slate-200 rounded-md shadow-lg overflow-auto z-10"
                style="max-height: 14rem;"
            >
                <template x-for="medicine in filtered" :key="medicine.id">
                    <button
                        type="button"
                        class="w-100 text-start px-3 py-2 border-0 bg-transparent hover:bg-slate-50"
                        @click="selectMedicine(medicine)"
                    >
                        <span class="d-block text-sm text-slate-800" x-text="medicine.label"></span>
                        <span class="d-block text-xs" :class="expiryClass(medicine.expiry_status)">
                            Exp <span x-text="medicine.expiration_label"></span>
                            · Rem <span x-text="medicine.quantity_remaining"></span>
                        </span>
                    </button>
                </template>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-sm-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Qty (pieces)</label>
                <input type="number" min="1" class="w-full rounded-md border-slate-300 text-sm shadow-sm" x-model="quantity">
            </div>
            <div class="col-12 col-sm-8">
                <label class="block text-sm font-medium text-slate-700 mb-1">Remarks (optional)</label>
                <input type="text" class="w-full rounded-md border-slate-300 text-sm shadow-sm" x-model="remarks">
            </div>
        </div>

        <template x-if="selectedMedicine()">
            <p class="text-xs mb-0" :class="expiryClass(selectedMedicine().expiry_status)">
                Selected stock remaining: <span x-text="selectedMedicine().quantity_remaining"></span>
                · Exp <span x-text="selectedMedicine().expiration_label"></span>
            </p>
        </template>

        <div>
            <button
                type="button"
                class="px-3 py-1.5 rounded-md text-sm bg-teal-700 text-white"
                @click="add()"
                :disabled="saving"
            >
                Dispense
            </button>
            <p class="text-xs mt-2 mb-0" :class="statusClass" x-text="statusText"></p>
        </div>

        <div class="border-t border-slate-200 pt-3">
            <p class="text-xs uppercase tracking-wide text-slate-400 mb-2">Dispensed this visit</p>
            <template x-if="items.length === 0">
                <p class="text-sm text-slate-500 mb-0">Nothing dispensed yet.</p>
            </template>
            <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                <template x-for="item in items" :key="item.id">
                    <li class="d-flex align-items-start justify-content-between gap-3 rounded-md border border-slate-200 bg-white px-3 py-2">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800 mb-0 break-words" x-text="item.label"></p>
                            <p class="text-xs text-slate-500 mb-0">
                                Qty <span x-text="item.quantity"></span>
                                <span x-show="item.remarks"> · <span x-text="item.remarks"></span></span>
                            </p>
                        </div>
                        <button type="button" class="text-rose-600 text-sm shrink-0" @click="remove(item)" :disabled="saving">Undo</button>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</div>
