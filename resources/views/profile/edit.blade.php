<x-app-layout x-data="{ logoutModalOpen: false }">

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

                        <button @click="$dispatch('open-logout-modal')" class="flex items-center justify-between w-full py-4 px-4 text-gray-200 hover:bg-white/5 transition">
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
