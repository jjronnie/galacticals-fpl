<header class=" bg-navgradient text-white px-4 py-3 flex items-center   top-0 z-30"
    x-data="{ sidebarOpen: false, quickAccessOpen: false, notificationOpen: false }"
    @resize.window="if (window.innerWidth >= 1024) sidebarOpen = true">

    <!-- Logo -->
    <a href="/" class="flex items-center space-x-2">
        <x-logo class="w-12 h-12" />
        <span class="font-bold">FPL Galaxy</span>
    </a>

       <!-- Clock -->
    <div class="hidden md:flex items-center space-x-2 bg-navgradient rounded-lg px-3 py-2 ml-4">
        <i data-lucide="clock" class="w-4 h-4 text-white"></i>
        <div class="text-sm font-medium text-white" id="clockDisplay">--:--:--</div>
    </div>

    @guest
        

    <div class="hidden md:flex items-center space-x-2 p-2">
        <a href="{{ route('home') }}" class="text-sm  text-white hover:underline " >How it works?</a>
    </div>
    @endguest


    <!-- Right Section -->
    <div class="ml-auto flex items-center space-x-4 relative">

        @guest
        <!-- SHOW THIS WHEN USER IS NOT LOGGED IN -->
        <a href="{{ route('login') }}"
            class="btn-sm">
            Login
        </a>

        <a href="{{ route('register') }}"
            class="btn-success-sm">
            Get Started
        </a>
        @endguest

        @auth
        <!-- NOTIFICATIONS -->
        <div class="relative" @click.away="notificationOpen = false">
            <button class="p-2 rounded-lg hover:bg-card transition-colors"
                @click="notificationOpen = !notificationOpen">
                <i data-lucide="bell"></i>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">0</span>
            </button>

            <!-- Notifications Dropdown -->
            <div x-show="notificationOpen" x-transition x-cloak
                class="absolute right-0 top-full mt-2 w-80 bg-card text-white rounded-xl shadow-xl border border-secondary z-30">

                <div class="p-4 border-b border-secondary-light">
                    <h3 class="text-sm text-center font-semibold">Notifications</h3>
                </div>

                <div class="p-4 text-center text-accent">
                    No Notifications
                </div>
            </div>
        </div>

        <!-- PROFILE DROPDOWN -->
        <div x-data="{ open: false, showLogoutModal: false }" class="relative">

            <button @click="open = !open" class="flex items-center space-x-3 pl-2 focus:outline-none">
                @php $photo = auth()->user()->profile_photo_path; @endphp

                @if ($photo)
                @if (Str::startsWith($photo, ['http://', 'https://']))
                <img src="{{ $photo }}" class="w-8 h-8 rounded-full object-cover">
                @else
                <img src="{{ asset('storage/' . $photo) }}" class="w-8 h-8 rounded-full object-cover">
                @endif
                @else
                <div class="p-2 rounded-lg text-accent bg-card">
                    <i data-lucide="circle-user-round"></i>
                </div>
                @endif
            </button>

            <!-- Profile Dropdown Box -->
            <div x-show="open" @click.away="open = false" x-transition x-cloak
                class="absolute right-0 mt-4 w-72 bg-card text-white rounded-lg shadow-xl border border-secondary z-30">

                <!-- Account Header -->
                <div class="flex items-center space-x-3 p-4 border-b border-secondary-light">
                    <div class="w-12 h-12 bg-secondary rounded-full flex items-center justify-center text-accent">
                        <i data-lucide="circle-user-round" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <p class="font-semibold">{{ ucfirst(auth()->user()->name) }}</p>
                        <p class="text-sm text-accent">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <!-- Menu Items -->
                <nav class="py-2">
                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center px-4 py-2 text-sm hover:bg-secondary hover:text-white transition">
                        <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                        Settings
                    </a>
                </nav>

                <!-- Logout -->
                <button @click="showLogoutModal = true; open = false"
                    class="w-full flex items-center px-4 py-2 text-sm border-t border-secondary-light text-red-300 hover:bg-secondary hover:text-white transition">
                    <i data-lucide="log-out" class="w-4 h-4 mr-2"></i>
                    Log out
                </button>
            </div>

            <!-- Logout Modal -->
            <div x-show="showLogoutModal" x-transition x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">

                <div class="bg-card text-white rounded-lg shadow-lg w-full max-w-sm p-6 border border-secondary"
                    @click.away="showLogoutModal = false">

                    <h2 class="text-lg font-semibold">Confirm Logout</h2>
                    <p class="text-sm text-accent mt-2">Are you sure you want to logout?</p>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button @click="showLogoutModal = false"
                            class="px-4 py-2 text-sm bg-secondary text-white hover:bg-card rounded">
                            Cancel
                        </button>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded">
                                Logout
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
        @endauth

    </div>
</header>