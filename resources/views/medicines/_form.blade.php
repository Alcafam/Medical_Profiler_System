@php
    $month = old('expiration_month', $medicine->expiration_date?->month);
    $year = old('expiration_year', $medicine->expiration_date?->year);
    $years = range((int) date('Y') - 1, (int) date('Y') + 15);
@endphp

<div>
    <x-input-label for="generic_name" value="Generic Name" />
    <x-text-input id="generic_name" name="generic_name" class="mt-1 block w-full" :value="old('generic_name', $medicine->generic_name)" required />
    <x-input-error :messages="$errors->get('generic_name')" class="mt-2" />
</div>

<div>
    <x-input-label for="brand_name" value="Brand Name" />
    <x-text-input id="brand_name" name="brand_name" class="mt-1 block w-full" :value="old('brand_name', $medicine->brand_name)" required />
    <x-input-error :messages="$errors->get('brand_name')" class="mt-2" />
</div>

<div>
    <x-input-label for="dosage_strength" value="Dosage Strength" />
    <x-text-input id="dosage_strength" name="dosage_strength" class="mt-1 block w-full" :value="old('dosage_strength', $medicine->dosage_strength)" />
    <x-input-error :messages="$errors->get('dosage_strength')" class="mt-2" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="expiration_month" value="Expiration Month" />
        <select id="expiration_month" name="expiration_month" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600 text-sm">
            <option value="">—</option>
            @foreach (range(1, 12) as $m)
                <option value="{{ $m }}" @selected((int) $month === $m)>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('expiration_month')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="expiration_year" value="Expiration Year" />
        <select id="expiration_year" name="expiration_year" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600 text-sm">
            <option value="">—</option>
            @foreach ($years as $y)
                <option value="{{ $y }}" @selected((int) $year === $y)>{{ $y }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('expiration_year')" class="mt-2" />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="quantity" value="Quantity (per piece)" />
        <x-text-input id="quantity" name="quantity" type="number" min="0" class="mt-1 block w-full" :value="old('quantity', $medicine->quantity ?? 0)" required />
        <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="quantity_dispensed" value="QTY Dispensed" />
        <x-text-input id="quantity_dispensed" name="quantity_dispensed" type="number" min="0" class="mt-1 block w-full" :value="old('quantity_dispensed', $medicine->quantity_dispensed ?? 0)" required />
        <p class="text-xs text-slate-500 mt-1">QTY Remaining is calculated as Quantity − Dispensed.</p>
        <x-input-error :messages="$errors->get('quantity_dispensed')" class="mt-2" />
    </div>
</div>

<div>
    <x-input-label for="remarks" value="Remarks" />
    <textarea id="remarks" name="remarks" rows="3" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600 text-sm">{{ old('remarks', $medicine->remarks) }}</textarea>
    <x-input-error :messages="$errors->get('remarks')" class="mt-2" />
</div>
