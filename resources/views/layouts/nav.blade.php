<header class="relative top-0 z-30 flex items-center bg-primary px-4 py-3 text-white"
    x-data="{ sidebarOpen: false, quickAccessOpen: false, notificationOpen: false, adminMenuOpen: false, logoutModalOpen: false }"
    @resize.window="if (window.innerWidth >= 1024) { sidebarOpen = true; adminMenuOpen = false; }"
    @open-logout-modal.window="logoutModalOpen = true">

    <!-- Logo -->
    <a href="/" class="flex items-center space-x-2">
        <x-logo class="w-12 h-12" />
    </a>

    <!-- Right Section -->
    <div class="ml-auto flex items-center space-x-4 relative">
        @guest
        <a href="{{ route('login') }}" class="btn-sm">
            Login
        </a>

        <a href="{{ route('register') }}" class="btn-success-sm">
            Get Started
        </a>
        @endguest

        <!-- Right Side: Novas Logo + Powered By -->
        <a href="https://getnovas.com" target="_blank" rel="noopener noreferrer nofollow" class="flex items-center gap-2">
            <img src="{{ asset('assets/img/novas.png') }}" alt="Novas" class="h-10 w-auto">
            <div class="flex flex-col justify-center h-10 leading-none">
                <span class="font-bold text-base">Novas 360</span>
                <span class="text-[12px] text-gray-400 font-semibold">powered by </span>
            </div>
        </a>
    </div>

    @auth
        @if (auth()->user()->isAdmin())
            <!-- Mobile menu backdrop -->
            <div
                x-show="adminMenuOpen"
                x-transition:enter="transition-opacity ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="adminMenuOpen = false"
                x-cloak
                class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden"
            ></div>

            <!-- Slide-in drawer from left -->
            <div
                id="admin-mobile-menu"
                x-show="adminMenuOpen"
                x-transition:enter="transition-transform ease-out duration-300"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition-transform ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                x-cloak
                class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] bg-card shadow-2xl lg:hidden"
            >
                <div class="flex h-full flex-col">
                    <div class="flex items-center justify-between border-b border-gray-800 px-4 py-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Administration</p>
                            <p class="text-sm font-bold text-white">Control Panel</p>
                        </div>
                        <button
                            @click="adminMenuOpen = false"
                            class="rounded-lg p-2 text-gray-400 hover:bg-primary hover:text-white"
                        >
                            <i data-lucide="x" class="h-5 w-5"></i>
                        </button>
                    </div>

                    <nav class="flex-1 space-y-1 overflow-y-auto p-3 text-sm">
                        <a href="{{ route('admin.index') }}" @click="adminMenuOpen = false" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.index') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                            Users
                        </a>
                        <a href="{{ route('admin.data') }}" @click="adminMenuOpen = false" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.data') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                            Data Sync
                        </a>
                        <a href="{{ route('admin.teams') }}" @click="adminMenuOpen = false" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.teams*') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                            Teams & Players
                        </a>
                        <a href="{{ route('admin.data.fixtures') }}" @click="adminMenuOpen = false" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.data.fixtures') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                            Fixtures
                        </a>
                        <a href="{{ route('admin.jobs.index') }}" @click="adminMenuOpen = false" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.jobs.index') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                            Jobs
                        </a>
                        <a href="{{ route('admin.data.leagues') }}" @click="adminMenuOpen = false" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.data.leagues') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                            Leagues
                        </a>
                        <a href="{{ route('admin.managers.index') }}" @click="adminMenuOpen = false" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.managers.index') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                            Claimed Profiles
                        </a>
                        <a href="{{ route('admin.managers.all') }}" @click="adminMenuOpen = false" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.managers.all*') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                            All Managers
                        </a>
                        <a href="{{ route('admin.complaints.index') }}" @click="adminMenuOpen = false" class="flex items-center justify-between rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.complaints.*') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                            <span>Complaints</span>
                            @if ($openComplaintsCount > 0)
                                <span aria-label="Open complaints" class="inline-flex min-w-6 items-center justify-center rounded-full bg-red-600 px-1.5 py-0.5 text-xs font-semibold text-white">
                                    {{ $openComplaintsCount }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('admin.verifications.index') }}" @click="adminMenuOpen = false" class="flex items-center justify-between rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.verifications.*') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                            <span>Verifications</span>
                            @if ($pendingVerificationsCount > 0)
                                <span aria-label="Pending verifications" class="inline-flex min-w-6 items-center justify-center rounded-full bg-secondary px-1.5 py-0.5 text-xs font-semibold text-white">
                                    {{ $pendingVerificationsCount }}
                                </span>
                            @endif
                        </a>
                    </nav>
                </div>
            </div>
        @endif
    @endauth
</header>


 @if (! request()->routeIs(['login', 'register']))
                <div class="px-4 pb-2 sm:px-4 lg:px-4">
                    <x-adsense />
                </div>
            @endif
