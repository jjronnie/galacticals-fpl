<x-app-layout>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="p-4 sm:p-8 bg-card shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h2 class="text-2xl font-bold text-white mb-6">More</h2>
                    <div class="space-y-0 -mx-4">
                        @php
                        $links = [
                        ['action' => 'Profile dashboard', 'route' => 'profile.index'],
                        ['action' => 'Search and claim team', 'route' => 'profile.search'],
                        ['action' => 'View Fixtures', 'route' => 'fixtures'],
                        ['action' => 'How to find your league ID', 'route' => 'find'],
                        ['action' => 'FPL Galaxy Terms and conditions', 'route' => 'terms'],
                        ['action' => 'Privacy Policy', 'route' => 'privacy.policy'],
                        ['action' => 'How it works', 'route' => 'home'],
                        ];
                        @endphp

                        @foreach($links as $link)
                        <a href="{{ route($link['route']) }}" class="flex items-center justify-between py-4 px-4 text-gray-200 hover:bg-white/5 transition">
                            <span class="text-base font-medium">{{ $link['action'] }}</span>
                            <i data-lucide="chevron-right" class="w-5 h-5 text-gray-400"></i>
                        </a>
                        @endforeach

                        <button id="logout-btn" class="flex items-center justify-between w-full py-4 px-4 text-gray-200 hover:bg-white/5 transition">
                            <span class="text-base font-medium">Log out</span>
                            <i data-lucide="log-out" class="w-5 h-5 text-gray-400"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-4 sm:p-8 bg-card shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-card shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-card shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>



            <div class="p-4 sm:p-8 bg-card shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <div
                        class=" px-4 md:px-6 flex flex-col sm:flex-row justify-between items-center text-sm text-gray-200 space-y-2 sm:space-y-0">
                        <p>&copy; {{ date('Y') }} <a href="http://techtowerinc.com" target="_blank"
                                rel="noopener noreferrer"> TechTower Inc. </a>| All rights reserved.</p>

                        <p class="text-end">Version 2.1.7</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Logout Modal -->
<div id="logout-modal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm" style="display: none;">
    <div class="w-full max-w-sm mx-4 bg-card rounded-2xl border border-white/10 shadow-2xl overflow-hidden">
        <div class="p-6 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-red-500/20 flex items-center justify-center">
                <i data-lucide="log-out" class="w-7 h-7 text-red-500"></i>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Log out</h3>
            <p class="text-gray-400 text-sm mb-6">Are you sure you want to log out of your account?</p>
            <div class="flex gap-3">
                <button id="logout-cancel" class="flex-1 px-4 py-3 rounded-xl font-medium text-gray-300 bg-white/5 hover:bg-white/10 transition">
                    Cancel
                </button>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full px-4 py-3 rounded-xl font-medium bg-red-600 hover:bg-red-500 text-white transition">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('logout-btn');
    const logoutModal = document.getElementById('logout-modal');
    const logoutCancel = document.getElementById('logout-cancel');
    
    if (logoutBtn && logoutModal) {
        logoutBtn.addEventListener('click', function() {
            logoutModal.style.display = 'flex';
        });
    }
    if (logoutCancel && logoutModal) {
        logoutCancel.addEventListener('click', function() {
            logoutModal.style.display = 'none';
        });
    }
    if (logoutModal) {
        logoutModal.addEventListener('click', function(e) {
            if (e.target === logoutModal) {
                logoutModal.style.display = 'none';
            }
        });
    }
});
</script>
