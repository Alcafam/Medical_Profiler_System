<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Add User</h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('users.store') }}" class="bg-white shadow-sm sm:rounded-lg p-4 sm:p-6 space-y-4">
                @csrf
                <div>
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name')" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="role" value="Role" />
                    <select id="role" name="role" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-600 focus:ring-teal-600" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="password" value="Password" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Confirm Password" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
                </div>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                    Active
                </label>
                <div class="flex flex-col-reverse sm:flex-row gap-2">
                    <button class="px-4 py-2 bg-teal-700 text-white rounded-md text-sm text-center">Save</button>
                    <a href="{{ route('users.index') }}" class="px-4 py-2 border rounded-md text-sm text-center">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
