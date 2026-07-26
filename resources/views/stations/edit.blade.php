<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Edit Station</h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('stations.update', $station) }}" class="bg-white shadow-sm sm:rounded-lg p-4 sm:p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $station->name)" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="sort_order" value="Sort order" />
                    <x-text-input id="sort_order" name="sort_order" type="number" class="mt-1 block w-full" :value="old('sort_order', $station->sort_order)" />
                </div>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $station->is_active)) class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                    Active
                </label>
                <div class="flex flex-col-reverse sm:flex-row gap-2">
                    <button class="px-4 py-2 bg-teal-700 text-white rounded-md text-sm text-center">Update</button>
                    <a href="{{ route('stations.index') }}" class="px-4 py-2 border rounded-md text-sm text-center">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
