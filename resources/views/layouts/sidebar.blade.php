@auth
    @if (auth()->user()->isAdmin())
        <aside class="hidden min-h-screen w-64 shrink-0 border-r border-gray-800 bg-card lg:flex lg:flex-col">
            <div class="border-b border-gray-800 px-5 py-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Admin Navigation</p>
                <p class="mt-1 text-sm font-bold text-white">Control Panel</p>
            </div>

            <nav class="flex-1 space-y-1 px-3 py-4 text-sm">
                <a href="{{ route('home') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('home') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    Home
                </a>
                <a href="{{ route('dashboard') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('dashboard') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    Dashboard
                </a>
                <a href="{{ route('public.leagues.list') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('public.leagues.*') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    Leagues
                </a>
                @if (! auth()->user()->hasClaimedProfile())
                    <a href="{{ route('profile.search') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('profile.search') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                        Search & Claim
                    </a>
                @endif
                <a href="{{ route('profile.index') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('profile.index') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    Profile
                </a>

                <p class="pt-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Administration</p>
                <a href="{{ route('admin.index') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.index') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    Users
                </a>
                <a href="{{ route('admin.data') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.data') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    Data Sync
                </a>
                <a href="{{ route('admin.data.observer') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.data.observer') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    DB Observer
                </a>
                <a href="{{ route('admin.managers.index') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.managers.index') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    Claimed Profiles
                </a>
                <a href="{{ route('admin.managers.all') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.managers.all*') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    All Managers
                </a>
                <a href="{{ route('admin.complaints.index') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.complaints.*') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    Complaints
                </a>
            </nav>
        </aside>

    @else
        <nav class="fixed bottom-0 left-0 right-0 z-50 rounded-t-2xl bg-card">
            <div class="flex items-center justify-around px-4 py-2">
                <a href="{{ route('home') }}" class="flex w-full flex-col items-center justify-center p-2 text-xs {{ request()->routeIs('home') ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                    <i data-lucide="house" class="h-5 w-5"></i>
                    Home
                </a>
                <a href="{{ route('dashboard') }}" class="flex w-full flex-col items-center justify-center p-2 text-xs {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                    <i data-lucide="layout-dashboard" class="h-5 w-5"></i>
                    Dashboard
                </a>
                <a href="{{ route('public.leagues.list') }}" class="flex w-full flex-col items-center justify-center p-2 text-xs {{ request()->routeIs('public.leagues.*') ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                    <i data-lucide="trophy" class="h-5 w-5"></i>
                    Leagues
                </a>
                @if (! auth()->user()->hasClaimedProfile())
                    <a href="{{ route('profile.search') }}" class="flex w-full flex-col items-center justify-center p-2 text-xs {{ request()->routeIs('profile.search') ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                        <i data-lucide="search" class="h-5 w-5"></i>
                        Claim
                    </a>
                @endif
                <a href="{{ route('profile.index') }}" class="flex w-full flex-col items-center justify-center p-2 text-xs {{ request()->routeIs('profile.*') ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                    <i data-lucide="user" class="h-5 w-5"></i>
                   My Team
                </a>
            </div>
        </nav>
    @endif
@else
    <nav class="fixed bottom-0 left-0 right-0 z-50 rounded-t-2xl bg-card">
        <div class="flex items-center justify-around px-4 py-2">
            <a href="{{ route('home') }}" class="flex w-full flex-col items-center justify-center p-2 text-xs {{ request()->routeIs('home') ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                <i data-lucide="house" class="h-5 w-5"></i>
                Home
            </a>
            <a href="{{ route('public.leagues.list') }}" class="flex w-full flex-col items-center justify-center p-2 text-xs {{ request()->routeIs('public.leagues.*') ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                <i data-lucide="trophy" class="h-5 w-5"></i>
                Leagues
            </a>
            <a href="{{ route('register') }}" class="flex w-full flex-col items-center justify-center p-2 text-xs {{ request()->routeIs('register') ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                <i data-lucide="user-plus" class="h-5 w-5"></i>
                Register
            </a>
            <a href="{{ route('login') }}" class="flex w-full flex-col items-center justify-center p-2 text-xs {{ request()->routeIs('login') ? 'text-white' : 'text-gray-400 hover:text-white' }}">
                <i data-lucide="log-in" class="h-5 w-5"></i>
                Login
            </a>
        </div>
    </nav>
@endauth
