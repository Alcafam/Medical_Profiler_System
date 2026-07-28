<div
    class="rounded-lg border border-slate-200 bg-slate-50 p-4"
    x-data="medicineRecommendationsPanel({
        storeUrl: @js(route('clients.visits.medicine-recommendations.store', [$client, $visit])),
        destroyUrlTemplate: @js(preg_replace('#/\d+$#', '/__ID__', route('clients.visits.medicine-recommendations.destroy', [$client, $visit, 0]))),
        medicines: @js($medicineOptions),
        initialItems: @js($recommendedMedicines),
    })"
    @click.outside="open = false"
>
    <div class="mb-3">
        <h4 class="text-sm font-semibold text-slate-800 mb-0">Prescription</h4>
        <p class="text-xs text-slate-500 mb-0">Pick from inventory for Pharmacy to dispense. Does not change stock.</p>
    </div>

    <div class="d-flex flex-column gap-3">
        <div class="position-relative">
            <label class="block text-sm font-medium text-slate-700 mb-1">Search medicine</label>
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
                        <span class="d-block text-xs text-slate-600">
                            Dosage <span x-text="medicine.dosage_strength || '—'"></span>
                        </span>
                        <span class="d-block text-xs" :class="expiryClass(medicine.expiry_status)">
                            Exp <span x-text="medicine.expiration_label"></span>
                            · Stock <span x-text="medicine.quantity_remaining"></span>
                        </span>
                    </button>
                </template>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-sm-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Qty (optional)</label>
                <input type="number" min="1" class="w-full rounded-md border-slate-300 text-sm shadow-sm" x-model="quantity">
            </div>
            <div class="col-12 col-sm-8">
                <label class="block text-sm font-medium text-slate-700 mb-1">sig</label>
                <input type="text" class="w-full rounded-md border-slate-300 text-sm shadow-sm" x-model="instructions" placeholder="e.g. 1 tab 3x a day for 7 days">
            </div>
        </div>

        <div>
            <button
                type="button"
                class="px-3 py-1.5 rounded-md text-sm bg-teal-700 text-white"
                @click="add()"
                :disabled="saving"
            >
                Add to prescription
            </button>
            <p class="text-xs mt-2 mb-0" :class="statusClass" x-text="statusText"></p>
        </div>

        <div class="border-t border-slate-200 pt-3">
            <p class="text-xs uppercase tracking-wide text-slate-400 mb-2">This visit</p>
            <template x-if="items.length === 0">
                <p class="text-sm text-slate-500 mb-0">No medicines prescribed yet.</p>
            </template>
            <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                <template x-for="item in items" :key="item.id">
                    <li class="d-flex align-items-start justify-content-between gap-3 rounded-md border border-slate-200 bg-white px-3 py-2">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800 mb-0 break-words" x-text="item.label"></p>
                            <p class="text-xs text-slate-500 mb-0">
                                <span x-show="item.quantity">Qty <span x-text="item.quantity"></span></span>
                                <span x-show="item.quantity && item.instructions"> · </span>
                                <span x-show="item.instructions">sig <span x-text="item.instructions"></span></span>
                            </p>
                            <p class="text-xs mb-0" :class="expiryClass(item.expiry_status)">
                                Exp <span x-text="item.expiration_label"></span>
                            </p>
                        </div>
                        <button type="button" class="text-rose-600 text-sm shrink-0" @click="remove(item)" :disabled="saving">Remove</button>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</div>
