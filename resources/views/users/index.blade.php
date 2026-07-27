<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Users</h2>
            <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-teal-700 text-white rounded-md text-sm w-full sm:w-auto">Add User</a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-teal-50 border border-teal-200 text-teal-800 px-4 py-3 rounded text-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded text-sm">{{ $errors->first() }}</div>
            @endif

            {{-- Mobile cards --}}
            <div class="space-y-3 md:hidden">
                @foreach ($users as $user)
                    <article class="bg-white shadow-sm rounded-lg p-4 space-y-2">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-semibold text-slate-800 break-words">{{ $user->name }}</h3>
                                <p class="text-sm text-slate-500 break-all">{{ $user->email }}</p>
                            </div>
                            <span class="text-xs uppercase tracking-wide text-slate-500 bg-slate-100 px-2 py-1 rounded shrink-0">
                                {{ $user->role->label() }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-600">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                            @if ($user->station)
                                · {{ $user->station->name }}
                            @endif
                        </p>
                        <div class="flex gap-3 text-sm pt-1">
                            <a href="{{ route('users.edit', $user) }}" class="text-teal-700 hover:underline">Edit</a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete this user?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-rose-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="hidden md:block bg-white shadow-sm rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left whitespace-nowrap">Name</th>
                            <th class="px-4 py-3 text-left whitespace-nowrap">Email</th>
                            <th class="px-4 py-3 text-left whitespace-nowrap">Role</th>
                            <th class="px-4 py-3 text-left whitespace-nowrap">Station</th>
                            <th class="px-4 py-3 text-left whitespace-nowrap">Status</th>
                            <th class="px-4 py-3 text-right whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($users as $user)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $user->name }}</td>
                                <td class="px-4 py-3 break-all">{{ $user->email }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $user->role->label() }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $user->station?->name ?? '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $user->is_active ? 'Active' : 'Inactive' }}</td>
                                <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('users.edit', $user) }}" class="text-teal-700 hover:underline">Edit</a>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-rose-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="overflow-x-auto">{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
