@props([
    'field',
    'value' => null,
    'editable' => true,
    'saveUrl',
    'stationId' => null,
])

@php
    $currentValue = $value?->value;
    $version = $value?->version ?? 0;
    $editor = $value?->editor?->name;
    $updatedAt = optional($value?->updated_at)?->diffForHumans();
@endphp

<div
    class="space-y-1"
    data-field-slug="{{ $field->slug }}"
    x-data="autosaveField({
        url: @js($saveUrl),
        initialValue: @js($currentValue),
        initialVersion: @js($version),
        stationId: @js($stationId),
    })"
>
    <div class="flex flex-wrap items-center justify-between gap-1 sm:gap-2">
        <label class="block text-sm font-medium text-slate-700 break-words pe-2">
            {{ $field->label }}
            @if ($field->is_required)<span class="text-rose-600">*</span>@endif
        </label>
        <span class="text-xs shrink-0" :class="{
            'text-slate-400': status === 'idle',
            'text-amber-600': status === 'pending' || status === 'saving',
            'text-teal-700': status === 'saved',
            'text-rose-600': status === 'error' || status === 'conflict',
        }" x-text="{
            idle: '',
            pending: 'Unsaved…',
            saving: 'Saving…',
            saved: 'Saved',
            error: 'Save failed',
            conflict: 'Conflict',
        }[status]"></span>
    </div>

    @if ($editable)
        @if ($field->type->value === 'textarea')
            <textarea
                rows="3"
                class="w-full min-w-0 rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600 text-base sm:text-sm"
                x-model="value"
                @input="queueSave()"
            ></textarea>
        @elseif ($field->type->value === 'select')
            <select
                class="w-full min-w-0 rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600 text-base sm:text-sm"
                x-model="value"
                @change="queueSave()"
            >
                <option value="">Select…</option>
                @foreach ($field->options ?? [] as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        @else
            <input
                type="{{ $field->type->value === 'number' ? 'number' : ($field->type->value === 'date' ? 'date' : 'text') }}"
                class="w-full min-w-0 rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600 text-base sm:text-sm"
                x-model="value"
                @input="queueSave()"
                step="any"
            />
        @endif
    @else
        <div class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 min-h-[42px] break-words">
            <span x-text="value || '—'"></span>
            <span class="ms-2 text-xs text-slate-400">(read-only)</span>
        </div>
    @endif

    <p class="text-xs text-slate-500 break-words" x-show="lastEditor || @js((bool) $editor)">
        Last edited by
        <span x-text="lastEditor || @js($editor)"></span>
        <span x-show="lastSavedAt || @js((bool) $updatedAt)">
            · <span x-text="lastSavedAt || @js($updatedAt)"></span>
        </span>
    </p>

    <div x-show="conflict" x-cloak class="rounded-md border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900 space-y-2">
        <p class="font-medium">This field was updated by someone else.</p>
        <p class="break-words">
            <span class="font-medium" x-text="conflict?.updated_by"></span>
            saved
            “<span x-text="conflict?.current_value || 'empty'"></span>”
            at <span x-text="conflict?.updated_at"></span>.
        </p>
        <p class="break-words">Your value: “<span x-text="conflict?.your_value || 'empty'"></span>”</p>
        <div class="flex flex-col sm:flex-row gap-2">
            <button type="button" class="px-3 py-1.5 rounded bg-white border border-amber-400 text-amber-900" @click="keepTheirs()">
                Keep theirs
            </button>
            <button type="button" class="px-3 py-1.5 rounded bg-teal-700 text-white" @click="overwriteMine()">
                Overwrite with mine
            </button>
        </div>
    </div>
</div>
