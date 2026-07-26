<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Create Client</h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-4 sm:p-6 space-y-4">
                <p class="text-slate-600 text-sm">
                    A system ID and first visit will be created automatically. After creation you will encode this visit’s registration details.
                </p>
                <form method="POST" action="{{ route('clients.store') }}">
                    @csrf
                    <button class="w-full sm:w-auto px-4 py-2 bg-teal-700 text-white rounded-md text-sm hover:bg-teal-800">
                        Create client & start encoding
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
