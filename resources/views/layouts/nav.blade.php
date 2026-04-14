<header class="relative top-0 z-30 flex items-center bg-primary px-4 py-3 text-white">

    <!-- Logo -->
    <a href="/" class="flex items-center space-x-2">
        <x-logo class="w-12 h-12" />
    </a>

    <!-- Right Section -->
    <div class="ml-auto flex items-center space-x-4 relative">
        @auth
            @if (auth()->user()->isAdmin())
                <button id="admin-menu-btn" class="lg:hidden p-2 rounded-lg text-gray-300 hover:bg-primary hover:text-white" aria-label="Open admin menu">
                    <i data-lucide="menu" class="h-6 w-6"></i>
                </button>
            @endif
        @endauth

        @guest
            <a href="{{ route('login') }}" class="btn-sm">
                Login
            </a>

            <a href="{{ route('register') }}" class="btn-success-sm">
                Get Started
            </a>
        @endguest

        @auth
        <!-- Right Side: Novas Logo + Powered By -->
        <button id="novas-btn" class="flex items-center gap-2 hover:opacity-80 transition">
            <img src="{{ asset('assets/img/novas.png') }}" alt="Novas" class="h-8 w-auto">
            <div class="flex flex-col justify-center h-8 leading-none">
                <span class="font-bold text-sm">Novas 360</span>
                <span class="text-[10px] text-gray-400 font-semibold">getnovas.com</span>
            </div>
        </button>
        @endauth

    </div>

    @auth
        @if (auth()->user()->isAdmin())
            <!-- Mobile menu backdrop -->
            <div id="admin-backdrop" class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden" style="display: none;"></div>

            <!-- Slide-in drawer from left -->
            <div id="admin-mobile-menu" class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] bg-card shadow-2xl lg:hidden" style="transform: translateX(-100%); transition: transform 0.3s ease;">
                <div class="flex h-full flex-col">
                    <div class="flex items-center justify-between border-b border-gray-800 px-4 py-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Administration</p>
                            <p class="text-sm font-bold text-white">Control Panel</p>
                        </div>
                        <button id="admin-close-btn" class="rounded-lg p-2 text-gray-400 hover:bg-primary hover:text-white">
                            <i data-lucide="x" class="h-5 w-5"></i>
                        </button>
                    </div>

                    <nav class="flex-1 space-y-1 overflow-y-auto p-3 text-sm">
                        <a href="{{ route('admin.index') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.index') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                            Users
                        </a>
                        <a href="{{ route('admin.data') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.data') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                            Data Sync
                        </a>
                        <a href="{{ route('admin.teams') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.teams*') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                            Teams & Players
                        </a>
                        <a href="{{ route('admin.data.fixtures') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.data.fixtures') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                            Fixtures
                        </a>
                        <a href="{{ route('admin.jobs.index') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.jobs.index') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                            Jobs
                        </a>
                        <a href="{{ route('admin.data.leagues') }}" class="block rounded-lg px-3 py-2 font-medium {{ request()->routeIs('admin.data.leagues') ? 'bg-primary text-white' : 'text-gray-300 hover:bg-primary hover:text-white' }}">
                            Leagues
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
                </div>
            </div>
        @endif
    @endauth
</header>

<!-- Novas Modal -->
<div id="novas-modal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm p-4" style="display: none;">
    <div class="w-full max-w-md bg-card rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
        <div class="p-6 sm:p-8 text-center">
            <img src="{{ asset('assets/img/novas.png') }}" alt="Novas 360" class="h-16 w-auto mx-auto mb-4">
            <div class="inline-block px-3 py-1 mb-4 rounded-full bg-accent/10 border border-accent/20">
                <span class="text-xs font-semibold text-accent">Sponsored</span>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold text-white mb-3">
                FPL Galaxy is Proudly Sponsored by <span class="text-accent">Novas 360</span>
            </h3>
            <p class="text-sm sm:text-base text-gray-300 mb-6">
                An inventory management and POS software designed for small and medium enterprises to track records and grow their business.
            </p>
            <div class="space-y-3">
                <a href="https://getnovas.com" target="_blank" rel="noopener noreferrer" class="block w-full px-6 py-3 rounded-xl bg-accent hover:bg-accent/90 text-primary font-semibold transition">
                    Sign Up Now
                </a>
                <button id="novas-modal-close" class="block w-full px-6 py-3 rounded-xl border border-gray-600 text-gray-300 font-medium hover:bg-white/5 transition">
                    Maybe Later
                </button>
            </div>
            <p class="mt-4 text-xs text-gray-500">
                Get a 60-day free trial when you sign up today!
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Novas modal
    const novasBtn = document.getElementById('novas-btn');
    const novasModal = document.getElementById('novas-modal');
    const novasModalClose = document.getElementById('novas-modal-close');
    
    if (novasBtn && novasModal) {
        novasBtn.addEventListener('click', function() {
            novasModal.style.display = 'flex';
        });
    }
    if (novasModalClose && novasModal) {
        novasModalClose.addEventListener('click', function() {
            novasModal.style.display = 'none';
        });
    }
    if (novasModal) {
        novasModal.addEventListener('click', function(e) {
            if (e.target === novasModal) {
                novasModal.style.display = 'none';
            }
        });
    }
    
    // Admin menu (vanilla JS for drawer toggle)
    const adminMenuBtn = document.getElementById('admin-menu-btn');
    const adminMobileMenu = document.getElementById('admin-mobile-menu');
    const adminBackdrop = document.getElementById('admin-backdrop');
    const adminCloseBtn = document.getElementById('admin-close-btn');
    
    function openAdminMenu() {
        if (adminMobileMenu) {
            adminMobileMenu.style.transform = 'translateX(0)';
            adminMobileMenu.style.display = 'block';
        }
        if (adminBackdrop) {
            adminBackdrop.style.display = 'block';
        }
    }
    
    function closeAdminMenu() {
        if (adminMobileMenu) {
            adminMobileMenu.style.transform = 'translateX(-100%)';
            setTimeout(function() {
                if (adminMobileMenu) {
                    adminMobileMenu.style.display = 'none';
                }
            }, 300);
        }
        if (adminBackdrop) {
            adminBackdrop.style.display = 'none';
        }
    }
    
    if (adminMenuBtn) {
        adminMenuBtn.addEventListener('click', openAdminMenu);
    }
    if (adminBackdrop) {
        adminBackdrop.addEventListener('click', closeAdminMenu);
    }
    if (adminCloseBtn) {
        adminCloseBtn.addEventListener('click', closeAdminMenu);
    }
});
</script>