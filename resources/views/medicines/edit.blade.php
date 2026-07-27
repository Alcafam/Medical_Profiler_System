<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Edit Medicine</h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('medicines.update', $medicine) }}" class="bg-white shadow-sm sm:rounded-lg p-4 sm:p-6 space-y-4">
                @csrf
                @method('PUT')
                @include('medicines._form', ['medicine' => $medicine])
                <div class="flex flex-col-reverse sm:flex-row gap-2">
                    <button class="px-4 py-2 bg-teal-700 text-white rounded-md text-sm text-center">Update</button>
                    <a href="{{ route('medicines.index', $medicine->isArchived() ? ['archived' => 1] : []) }}" class="px-4 py-2 border rounded-md text-sm text-center">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
