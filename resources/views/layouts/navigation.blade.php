<nav x-data="{ open: false }" class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center min-w-0">
                    <a href="{{ route('clients.index') }}" class="text-base sm:text-lg font-semibold text-teal-800 tracking-tight truncate max-w-[10rem] sm:max-w-none">
                        {{ config('app.name') }}
                    </a>
                </div>

                <div class="hidden space-x-6 lg:space-x-8 md:-my-px md:ms-8 md:flex">
                    <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                        Clients
                    </x-nav-link>

                    @if (auth()->user()->canUseGrid())
                        <x-nav-link :href="route('clients.grid')" :active="request()->routeIs('clients.grid')">
                            Spreadsheet
                        </x-nav-link>
                    @endif

                    @if (auth()->user()->canViewInventory())
                        <x-nav-link :href="route('medicines.index')" :active="request()->routeIs('medicines.*')">
                            Inventory
                        </x-nav-link>
                    @endif

                    @if (auth()->user()->canManageStations())
                        <x-nav-link :href="route('stations.index')" :active="request()->routeIs('stations.*')">
                            Stations
                        </x-nav-link>
                        <x-nav-link :href="route('form-fields.index')" :active="request()->routeIs('form-fields.*')">
                            Form Builder
                        </x-nav-link>
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                            Users
                        </x-nav-link>
                        <x-nav-link :href="route('system-flow.index')" :active="request()->routeIs('system-flow.*')">
                            System Flow
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden md:flex md:items-center md:ms-6 gap-3">
                <span class="text-xs uppercase tracking-wide text-slate-500 bg-slate-100 px-2 py-1 rounded whitespace-nowrap">
                    {{ auth()->user()->role->label() }}
                </span>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-slate-600 bg-white hover:text-slate-800 focus:outline-none transition">
                            <div class="max-w-[8rem] truncate">{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center md:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none" aria-label="Toggle navigation">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden md:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">Clients</x-responsive-nav-link>
            @if (auth()->user()->canUseGrid())
                <x-responsive-nav-link :href="route('clients.grid')" :active="request()->routeIs('clients.grid')">Spreadsheet</x-responsive-nav-link>
            @endif
            @if (auth()->user()->canViewInventory())
                <x-responsive-nav-link :href="route('medicines.index')" :active="request()->routeIs('medicines.*')">Inventory</x-responsive-nav-link>
            @endif
            @if (auth()->user()->canManageStations())
                <x-responsive-nav-link :href="route('stations.index')" :active="request()->routeIs('stations.*')">Stations</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('form-fields.index')" :active="request()->routeIs('form-fields.*')">Form Builder</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">Users</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('system-flow.index')" :active="request()->routeIs('system-flow.*')">System Flow</x-responsive-nav-link>
            @endif
        </div>
        <div class="pt-4 pb-1 border-t border-slate-200">
            <div class="px-4 space-y-1">
                <div class="font-medium text-base text-slate-800 break-words">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-slate-500 break-all">{{ Auth::user()->email }}</div>
                <div class="text-xs uppercase tracking-wide text-slate-500 bg-slate-100 px-2 py-1 rounded inline-block">
                    {{ auth()->user()->role->label() }}
                </div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Profile</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        Log Out
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
