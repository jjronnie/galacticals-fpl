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
                        Search &amp; Claim
                    </a>
                @endif
                <a href="{{ route('profile.index') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('profile.index') || request()->routeIs('profile.section') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    Profile
                </a>
                <a href="{{ route('profile.edit') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('profile.edit') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    Settings
                </a>

                <p class="pt-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Administration</p>
                <a href="{{ route('admin.index') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.index') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    Users
                </a>
                <a href="{{ route('admin.data') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.data') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    Data Sync
                </a>
                <a href="{{ route('admin.data.leagues') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.data.leagues') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                     Leagues
                </a>
                <a href="{{ route('admin.data.observer') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.data.observer') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    PL Teams and Players
                </a>
                <a href="{{ route('admin.managers.index') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.managers.index') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    Claimed Profiles
                </a>
                <a href="{{ route('admin.managers.all') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.managers.all*') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    All Managers
                </a>
                <a href="{{ route('admin.complaints.index') }}" class="flex items-center justify-between rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.complaints.*') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    <span>Complaints</span>
                    @if ($openComplaintsCount > 0)
                        <span aria-label="Open complaints" class="inline-flex min-w-6 items-center justify-center rounded-full bg-red-600 px-1.5 py-0.5 text-xs font-semibold text-white">
                            {{ $openComplaintsCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('admin.verifications.index') }}" class="flex items-center justify-between rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.verifications.*') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                    <span>Verifications</span>
                    @if ($pendingVerificationsCount > 0)
                        <span aria-label="Pending verifications" class="inline-flex min-w-6 items-center justify-center rounded-full bg-secondary px-1.5 py-0.5 text-xs font-semibold text-white">
                            {{ $pendingVerificationsCount }}
                        </span>
                    @endif
                </a>
            </nav>
        </aside>
    @endif

    @php
        $hasClaimedProfile = auth()->user()->hasClaimedProfile();
        $isHomeActive = request()->routeIs('home');
        $isLeaguesActive = request()->routeIs('public.leagues.*');
        $isDashboardActive = request()->routeIs('dashboard');
        $isClaimActive = request()->routeIs('profile.search');
        $isProfileActive = request()->routeIs('profile.index') || request()->routeIs('profile.section');
        $isMoreActive = request()->routeIs('profile.edit');
        $authBottomNavColumns = $hasClaimedProfile ? 'grid-cols-5' : 'grid-cols-6';
    @endphp

    <nav class="fixed bottom-3 left-1/2 z-50 w-[calc(100%-1rem)] max-w-md -translate-x-1/2 rounded-2xl border border-white/10 bg-card/80 backdrop-blur-xl shadow-[0_12px_36px_rgba(2,6,23,0.45)]">
        <div class="grid {{ $authBottomNavColumns }} items-center gap-1 px-1 py-2">
            <a
                href="{{ route('home') }}"
                aria-label="Home"
                class="group flex w-full flex-col items-center justify-center rounded-xl px-2 py-1.5 transition {{ $isHomeActive ? 'text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
            >
                <i data-lucide="house" class="h-5 w-5"></i>
                @if ($isHomeActive)
                    <span class="mt-1 text-[10px] font-semibold leading-none">Home</span>
                @endif
            </a>

            <a
                href="{{ route('public.leagues.list') }}"
                aria-label="Leagues"
                class="group flex w-full flex-col items-center justify-center rounded-xl px-2 py-1.5 transition {{ $isLeaguesActive ? 'text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
            >
                <i data-lucide="trophy" class="h-5 w-5"></i>
                @if ($isLeaguesActive)
                    <span class="mt-1 text-[10px] font-semibold leading-none">Leagues</span>
                @endif
            </a>

            <a
                href="{{ route('dashboard') }}"
                class="justify-self-center rounded-full border border-white/20 bg-primary/90 p-2 shadow-[0_10px_24px_rgba(2,6,23,0.5)] transition hover:scale-[1.03] hover:border-accent hover:bg-secondary {{ $isDashboardActive ? 'ring-2 ring-accent/70' : '' }}"
                aria-label="Dashboard"
                title="Dashboard"
            >
                <img
                    src="{{ asset('assets/img/logo-light.webp') }}"
                    alt="Dashboard"
                    class="h-8 w-8 rounded-full object-cover"
                >
            </a>

            @if (! $hasClaimedProfile)
                <a
                    href="{{ route('profile.search') }}"
                    aria-label="Claim"
                    class="group flex w-full flex-col items-center justify-center rounded-xl px-2 py-1.5 transition {{ $isClaimActive ? 'text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <i data-lucide="search" class="h-5 w-5"></i>
                    @if ($isClaimActive)
                        <span class="mt-1 text-[10px] font-semibold leading-none">Claim</span>
                    @endif
                </a>
            @endif

            <a
                href="{{ route('profile.index') }}"
                aria-label="My Team"
                class="group flex w-full flex-col items-center justify-center rounded-xl px-2 py-1.5 transition {{ $isProfileActive ? 'text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
            >
                <i data-lucide="users-round" class="h-5 w-5"></i>
                @if ($isProfileActive)
                    <span class="mt-1 text-[10px] font-semibold leading-none">My Team</span>
                @endif
            </a>

            <a
                href="{{ route('profile.edit') }}"
                aria-label="More"
                class="group flex w-full flex-col items-center justify-center rounded-xl px-2 py-1.5 transition {{ $isMoreActive ? 'text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
            >
                <i data-lucide="ellipsis" class="h-5 w-5"></i>
                @if ($isMoreActive)
                    <span class="mt-1 text-[10px] font-semibold leading-none">More</span>
                @endif
            </a>
        </div>
    </nav>
@else
    @php
        $isHomeActive = request()->routeIs('home');
        $isLeaguesActive = request()->routeIs('public.leagues.*');
        $isRegisterActive = request()->routeIs('register');
        $isLoginActive = request()->routeIs('login');
    @endphp

    <nav class="fixed bottom-3 left-1/2 z-50 w-[calc(100%-1rem)] max-w-md -translate-x-1/2 rounded-2xl border border-white/10 bg-card/80 backdrop-blur-xl shadow-[0_12px_36px_rgba(2,6,23,0.45)]">
        <div class="grid grid-cols-4 items-center gap-1 px-1 py-2">
            <a
                href="{{ route('home') }}"
                aria-label="Home"
                class="group flex w-full flex-col items-center justify-center rounded-xl px-2 py-1.5 transition {{ $isHomeActive ? 'text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
            >
                <i data-lucide="house" class="h-5 w-5"></i>
                @if ($isHomeActive)
                    <span class="mt-1 text-[10px] font-semibold leading-none">Home</span>
                @endif
            </a>
            <a
                href="{{ route('public.leagues.list') }}"
                aria-label="Leagues"
                class="group flex w-full flex-col items-center justify-center rounded-xl px-2 py-1.5 transition {{ $isLeaguesActive ? 'text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
            >
                <i data-lucide="trophy" class="h-5 w-5"></i>
                @if ($isLeaguesActive)
                    <span class="mt-1 text-[10px] font-semibold leading-none">Leagues</span>
                @endif
            </a>
            <a
                href="{{ route('register') }}"
                aria-label="Register"
                class="group flex w-full flex-col items-center justify-center rounded-xl px-2 py-1.5 transition {{ $isRegisterActive ? 'text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
            >
                <i data-lucide="user-plus" class="h-5 w-5"></i>
                @if ($isRegisterActive)
                    <span class="mt-1 text-[10px] font-semibold leading-none">Register</span>
                @endif
            </a>
            <a
                href="{{ route('login') }}"
                aria-label="Login"
                class="group flex w-full flex-col items-center justify-center rounded-xl px-2 py-1.5 transition {{ $isLoginActive ? 'text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
            >
                <i data-lucide="log-in" class="h-5 w-5"></i>
                @if ($isLoginActive)
                    <span class="mt-1 text-[10px] font-semibold leading-none">Login</span>
                @endif
            </a>
        </div>
    </nav>
@endauth
