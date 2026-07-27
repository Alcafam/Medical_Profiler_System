<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Add Medicine</h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('medicines.store') }}" class="bg-white shadow-sm sm:rounded-lg p-4 sm:p-6 space-y-4">
                @csrf
                @include('medicines._form', ['medicine' => new \App\Models\Medicine(['quantity' => 0, 'quantity_dispensed' => 0])])
                <div class="flex flex-col-reverse sm:flex-row gap-2">
                    <button class="px-4 py-2 bg-teal-700 text-white rounded-md text-sm text-center">Save</button>
                    <a href="{{ route('medicines.index') }}" class="px-4 py-2 border rounded-md text-sm text-center">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
