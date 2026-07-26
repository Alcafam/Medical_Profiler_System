<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Spreadsheet</h2>
            <a href="{{ route('clients.export') }}" class="inline-flex items-center justify-center px-4 py-2 bg-teal-700 text-white rounded-md text-sm hover:bg-teal-800 w-full sm:w-auto">
                Export Excel
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6">
        <div class="px-4 sm:px-6 lg:px-8">
            <p class="text-sm text-slate-500 mb-3">Edit cells directly. Changes autosave. Conflicts will ask you to keep theirs or overwrite.</p>

            <div class="bg-white shadow-sm rounded-lg overflow-auto border border-slate-200 max-h-[70vh] sm:max-h-[75vh]">
                <table class="min-w-full text-sm border-collapse">
                    <thead class="bg-slate-50 sticky top-0 z-20">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-slate-600 border-b border-r border-slate-200 whitespace-nowrap sticky left-0 bg-slate-50 z-30">System ID</th>
                            <th class="px-3 py-2 text-left font-medium text-slate-600 border-b border-r border-slate-200 whitespace-nowrap min-w-[140px]">Name</th>
                            @foreach ($fields as $field)
                                <th class="px-3 py-2 text-left font-medium text-slate-600 border-b border-r border-slate-200 whitespace-nowrap min-w-[140px]">
                                    {{ $field->label }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clients as $client)
                            @php $valueMap = ($client->latestVisit?->fieldValues ?? collect())->keyBy('form_field_id'); @endphp
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-3 py-2 font-mono text-xs border-b border-r border-slate-100 whitespace-nowrap align-top sticky left-0 bg-white z-10">
                                    <a href="{{ route('clients.encode', $client) }}" class="text-teal-700 hover:underline">{{ $client->system_id }}</a>
                                    @if ($client->latestVisit)
                                        <div class="text-[10px] text-slate-400 mt-1">{{ $client->latestVisit->visited_at?->format('Y-m-d') }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 border-b border-r border-slate-100 whitespace-nowrap align-top font-medium text-slate-800">
                                    {{ $client->displayName() }}
                                </td>
                                @foreach ($fields as $field)
                                    @php $cell = $valueMap->get($field->id); @endphp
                                    <td class="px-2 py-2 border-b border-r border-slate-100 align-top min-w-[140px]">
                                        <div
                                            x-data="autosaveField({
                                                url: @js(route('clients.grid.save', [$client, $field])),
                                                initialValue: @js($cell?->value),
                                                initialVersion: @js($cell?->version ?? 0),
                                            })"
                                            class="space-y-1"
                                        >
                                            @if ($field->type->value === 'select')
                                                <select class="w-full min-w-0 rounded border-slate-300 text-sm" x-model="value" @change="queueSave()">
                                                    <option value=""></option>
                                                    @foreach ($field->options ?? [] as $option)
                                                        <option value="{{ $option }}">{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif ($field->type->value === 'textarea')
                                                <textarea rows="2" class="w-full min-w-0 rounded border-slate-300 text-sm" x-model="value" @input="queueSave()"></textarea>
                                            @else
                                                <input
                                                    type="{{ $field->type->value === 'number' ? 'number' : ($field->type->value === 'date' ? 'date' : 'text') }}"
                                                    class="w-full min-w-0 rounded border-slate-300 text-sm"
                                                    x-model="value"
                                                    @input="queueSave()"
                                                    step="any"
                                                />
                                            @endif
                                            <div class="text-[10px] text-slate-400 flex justify-between gap-1">
                                                <span class="truncate" x-text="lastEditor || @js($cell?->editor?->name)"></span>
                                                <span :class="{
                                                    'text-amber-600': status === 'saving' || status === 'pending',
                                                    'text-teal-700': status === 'saved',
                                                    'text-rose-600': status === 'conflict' || status === 'error',
                                                }" x-text="{pending:'…',saving:'Saving',saved:'Saved',conflict:'Conflict',error:'Error',idle:''}[status]"></span>
                                            </div>
                                            <div x-show="conflict" x-cloak class="rounded border border-amber-300 bg-amber-50 p-2 text-xs text-amber-900 space-y-1">
                                                <p>Updated by <span x-text="conflict?.updated_by"></span></p>
                                                <p class="break-words">Theirs: <span x-text="conflict?.current_value || 'empty'"></span></p>
                                                <div class="flex flex-wrap gap-1">
                                                    <button type="button" class="px-2 py-1 bg-white border border-amber-400 rounded" @click="keepTheirs()">Keep theirs</button>
                                                    <button type="button" class="px-2 py-1 bg-teal-700 text-white rounded" @click="overwriteMine()">Overwrite</button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $fields->count() + 2 }}" class="px-4 py-8 text-center text-slate-500">No clients yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 overflow-x-auto">{{ $clients->links() }}</div>
        </div>
    </div>
</x-app-layout>
