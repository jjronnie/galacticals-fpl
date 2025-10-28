<header class="nav" x-data="{ sidebarOpen: false, quickAccessOpen: false, notificationOpen: false }"
    @resize.window="if (window.innerWidth >= 1024) sidebarOpen = true">

   <!-- Mobile Menu Button -->
<a href="/"  class="flex items-center space-x-2">
    
    <i data-lucide="shirt"></i>
    <span class="font-bold">FPL Galaxy</span>
</a>


    <!-- Clock -->
    <div class="hidden md:flex items-center space-x-2 bg-blue-50 rounded-lg px-3 py-2 ml-4">
        <i data-lucide="clock" class="w-4 h-4 text-blue-600"></i>
        <div class="text-sm font-medium text-blue-600" id="clockDisplay">--:--:--</div>
    </div>

    <!-- Right Section -->
    <div class="ml-auto flex items-center space-x-4 relative">

    

        <!-- Notifications -->
        <div class="relative" @click.away="notificationOpen = false">
            <button class="p-2 rounded-lg  hover:bg-gray-100 transition-colors"
                @click="notificationOpen = !notificationOpen">
                <i data-lucide="bell"></i>
                <span
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full leading-none">0</span>
            </button>

            <div x-show="notificationOpen" x-transition x-cloak
                class="absolute right-0 top-full mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-200 z-30 p-4"
                @click.away="notificationOpen = false">

                

                <h3 class="text-sm text-center font-semibold text-gray-800 mb-4"></h3>

                <p class="text-center">No Notifications </p>

              
            </div>
        </div>

        <!-- Profile -->
        <div x-data="{ open: false, showLogoutModal: false }" class="relative">
            <!-- Trigger -->
            <button @click="open = !open" class="flex items-center space-x-3 pl-2 focus:outline-none">
                @php
                $photo = auth()->user()->profile_photo_path;
                @endphp

                @if ($photo)
                @if (Str::startsWith($photo, ['http://', 'https://']))
                <!-- Show remote (Google) photo -->
                <img src="{{ $photo }}" alt="Profile" class="w-8 h-8 rounded-full object-cover">
                @else
                <!-- Show locally stored photo -->
                <img src="{{ asset('storage/' . $photo) }}" alt="Profile" class="w-8 h-8 rounded-full object-cover">
                @endif
                @else
                <!-- Fallback icon -->
                <div class="p-2 rounded-lg text-blue-600 hover:bg-gray-100 transition-colors">
                    <i data-lucide="circle-user-round"></i>
                </div>
                @endif
            </button>


            <!-- Profile Dropdown -->
            <div x-show="open" @click.away="open = false" x-transition x-cloak
                class="absolute right-0 mt-4 w-72 bg-white text-gray-800 rounded-lg shadow-xl z-30">

                <!-- Account Info -->
                <div class="flex items-center space-x-3 p-4 border-b">
                    <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-600">
                        <i data-lucide="circle-user-round" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <p class="font-semibold">{{ ucfirst(auth()->user()->name) ?? '' }}</p>
                        <p class="text-sm text-gray-500">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                </div>

               

                <!-- Menu Items -->
                <nav class="py-2">
                    <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm hover:bg-gray-100">
                        <i data-lucide="settings" class="w-4 h-4 mr-2"></i> Settings
                    </a>
                    
                </nav>

                <!-- Logout -->
                <button @click="showLogoutModal = true; open = false"
                    class="w-full flex items-center px-4 py-2 text-sm hover:bg-gray-100 text-red-600 border-t">
                    <i data-lucide="log-out" class="w-4 h-4 mr-2"></i> Log out
                </button>
            </div>

            <!-- Logout Modal -->
            <div x-show="showLogoutModal" x-transition x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-6" @click.away="showLogoutModal = false">
                    <h2 class="text-lg font-semibold text-gray-800">Confirm Logout</h2>
                    <p class="text-sm text-gray-600 mt-2">Are you sure you want to logout?</p>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button @click="showLogoutModal = false"
                            class="px-4 py-2 text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 rounded">Cancel</button>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div>
</header>