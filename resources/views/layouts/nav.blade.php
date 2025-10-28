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

        <!-- Quick Access -->
        <div class="relative" @click.away="quickAccessOpen = false">
            <button class="p-2 rounded-lg hover:bg-gray-100 transition-colors bg-blue-50 text-blue-600"
                @click="quickAccessOpen = !quickAccessOpen">
                <i data-lucide="zap" class="w-5 h-5"></i>
            </button>

            <div x-show="quickAccessOpen" x-transition x-cloak
                class="absolute right-0 top-full mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 z-30">
                <div class="p-4">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Quick Access</h3>
                    <div class="space-y-2">
                        <a href="#" class="quick-access-item">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            <span class="text-sm">Add Employee</span>
                        </a>

                        <a href="#" class="quick-access-item">
                            <i data-lucide="list-todo" class="w-4 h-4"></i>
                            <span class="text-sm">Inspection</span>
                        </a>


                        <a href="#" class="quick-access-item">
                            <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                            <span class="text-sm">Mark Attendance</span>
                        </a>

                        <a href="#" class="quick-access-item">
                            <i data-lucide="clipboard-pen-line" class="w-4 h-4"></i>
                            <span class="text-sm">Apply For Leave</span>
                        </a>

                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="relative" @click.away="notificationOpen = false">
            <button class="p-2 rounded-lg  hover:bg-gray-100 transition-colors"
                @click="notificationOpen = !notificationOpen">
                <i data-lucide="bell"></i>
                <span
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full leading-none">3</span>
            </button>

            <div x-show="notificationOpen" x-transition x-cloak
                class="absolute right-0 top-full mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-200 z-30 p-4"
                @click.away="notificationOpen = false">

                <div class="flex items-center gap-3">
                    <label for="toggleSound" class="text-sm font-medium text-gray-700">Alert Sound</label>

                    <button id="toggleSound" type="button"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-green-500 transition-colors duration-200 ease-in-out focus:outline-none"
                        role="switch" aria-checked="true">
                        <span aria-hidden="true"
                            class="inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-5"
                            id="toggleThumb"></span>
                    </button>
                </div>

                <h3 class="text-sm text-center font-semibold text-gray-800 mb-4"></h3>

                <p class="text-center">No Notifications Found</p>

                <ul class="space-y-4 text-sm text-gray-700 max-h-80 overflow-y-auto">
                    <li class="flex items-start space-x-3">
                        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-green-500 text-white">
                            <i data-lucide="info" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">Application Error</p>
                            <p class="text-xs text-gray-500">Just now</p>
                        </div>
                    </li>
                    <li class="flex items-start space-x-3">
                        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-yellow-400 text-white">
                            <i data-lucide="settings" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">Settings</p>
                            <p class="text-xs text-gray-500">Private message</p>
                        </div>
                    </li>
                    <li class="flex items-start space-x-3">
                        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-500 text-white">
                            <i data-lucide="user-plus" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">New user registration</p>
                            <p class="text-xs text-gray-500">2 days ago</p>
                        </div>
                    </li>
                </ul>

                <div class="mt-4 flex items-center justify-between border-t pt-3">
                    <button class="text-sm text-blue-600 hover:underline font-medium"
                        @click="alert('Marking all as read...')">Mark all as read</button>
                    <button class="text-sm text-red-500 hover:underline font-medium"
                        @click="alert('Clearing notifications...')">Clear all</button>
                </div>
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