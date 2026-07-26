<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Form Builder</h2>
            <a href="{{ route('form-fields.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-teal-700 text-white rounded-md text-sm w-full sm:w-auto">Add Field</a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-teal-50 border border-teal-200 text-teal-800 px-4 py-3 rounded text-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded text-sm">{{ $errors->first() }}</div>
            @endif

            <div class="space-y-3 lg:hidden">
                @foreach ($fields as $field)
                    <article class="bg-white shadow-sm rounded-lg p-4 space-y-2">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-800 break-words">{{ $field->label }}</h3>
                            <p class="text-xs text-slate-400 font-mono break-all">{{ $field->slug }}</p>
                        </div>
                        <p class="text-sm text-slate-600">
                            {{ $field->type->label() }}
                            · {{ $field->station?->name ?? 'Unassigned' }}
                        </p>
                        <p class="text-xs text-slate-500">
                            @if ($field->is_system) System · @endif
                            @if ($field->is_required) Required · @endif
                            @if ($field->is_searchable) Searchable · @endif
                            {{ $field->is_active ? 'Active' : 'Inactive' }}
                        </p>
                        <div class="flex gap-3 text-sm pt-1">
                            <a href="{{ route('form-fields.edit', $field) }}" class="text-teal-700 hover:underline">Edit</a>
                            @unless ($field->is_system)
                                <form action="{{ route('form-fields.destroy', $field) }}" method="POST" onsubmit="return confirm('Delete this field?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-rose-600 hover:underline">Delete</button>
                                </form>
                            @endunless
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="hidden lg:block bg-white shadow-sm rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left">Label</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-left">Station</th>
                            <th class="px-4 py-3 text-left">Flags</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($fields as $field)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $field->label }}</div>
                                    <div class="text-xs text-slate-400 font-mono">{{ $field->slug }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $field->type->label() }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $field->station?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-slate-500">
                                    @if ($field->is_system) System · @endif
                                    @if ($field->is_required) Required · @endif
                                    @if ($field->is_searchable) Searchable · @endif
                                    {{ $field->is_active ? 'Active' : 'Inactive' }}
                                </td>
                                <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('form-fields.edit', $field) }}" class="text-teal-700 hover:underline">Edit</a>
                                    @unless ($field->is_system)
                                        <form action="{{ route('form-fields.destroy', $field) }}" method="POST" class="inline" onsubmit="return confirm('Delete this field?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-rose-600 hover:underline">Delete</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
