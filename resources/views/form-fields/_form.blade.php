@php
    /** @var \App\Models\FormField|null $field */
    $optionsText = old('options_text', isset($field) ? implode("\n", $field->options ?? []) : '');
@endphp

<div>
    <x-input-label for="label" value="Label" />
    <x-text-input id="label" name="label" class="mt-1 block w-full" :value="old('label', $field->label ?? '')" required />
    <x-input-error :messages="$errors->get('label')" class="mt-2" />
</div>

<div>
    <x-input-label for="type" value="Type" />
    <select id="type" name="type" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600" required>
        @foreach ($types as $type)
            <option value="{{ $type->value }}" @selected(old('type', $field->type->value ?? '') === $type->value)>{{ $type->label() }}</option>
        @endforeach
    </select>
</div>

<div>
    <x-input-label for="station_id" value="Station" />
    <select id="station_id" name="station_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">
        <option value="">Unassigned</option>
        @foreach ($stations as $station)
            <option value="{{ $station->id }}" @selected((string) old('station_id', $field->station_id ?? '') === (string) $station->id)>
                {{ $station->name }}
            </option>
        @endforeach
    </select>
</div>

<div>
    <x-input-label for="options_text" value="Select options (one per line)" />
    <textarea id="options_text" name="options_text" rows="4" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600">{{ $optionsText }}</textarea>
</div>

<div>
    <x-input-label for="sort_order" value="Sort order" />
    <x-text-input id="sort_order" name="sort_order" type="number" class="mt-1 block w-full" :value="old('sort_order', $field->sort_order ?? 0)" />
</div>

<div class="space-y-2 text-sm">
    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_required" value="1" @checked(old('is_required', $field->is_required ?? false)) class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
        Required
    </label>
    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_searchable" value="1" @checked(old('is_searchable', $field->is_searchable ?? false)) class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
        Searchable
    </label>
    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $field->is_active ?? true)) class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
        Active
    </label>
</div>
