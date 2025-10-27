<div class="w-80 lg:w-64 bg-primary text-white flex flex-col fixed top-0 left-0 h-screen z-40 lg:z-[10000] transform transition-transform duration-300 -translate-x-full lg:translate-x-0"
    id="sidebar">
    <!-- Sidebar Header -->

    <div class="sidebar-header">
        <div class="flex items-center space-x-3">
            <div class="w-full h-12  rounded-lg flex items-center justify-center text-white font-bold text-lg">
                <span>
                    <a href="{{ url('/') }}">
                        <img src="{{ asset('novas4.png') }}" alt="LOGO">
                    </a>
                </span>
            </div>

        </div>
        <button class="lg:hidden p-1 rounded-md hover:bg-blue-900 transition-colors" id="closeSidebar">

            <i data-lucide="x" class="w-4 h-4 text-white"></i>
        </button>
    </div>

    <!-- Scrollable Navigation Area -->



    <div class="flex-1 overflow-y-auto no-scrollbar">
        <nav class="p-4 space-y-1">
            {{-- Dashboard --}}

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
                class="sidebar-link {{ request()->routeIs('dashboard') ? 'sidebar-link-active' : '' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4 text-white"></i>
                <span>Dashboard</span>
            </a>
            <div class="space-y-1">

             


         


                <a href="#" class="sidebar-link">
                    <i data-lucide="truck" class="w-4 h-4 text-white"></i>
                    <span>Purchases</span>
                </a>

          

                <a href="#" class="sidebar-link">
                    <i data-lucide="wrench" class="w-4 h-4 text-white"></i>
                    <span>Tools</span>
                </a>

                <a href="#" class="sidebar-link">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 text-white"></i>
                    <span>Statistics</span>
                </a>


         






                <!-- Sidebar Footer -->
                <div class="p-4 border-t border-blue-900">
                    <a href="/support.html"
                        class="flex items-center space-x-3 bg-blue-700 hover:bg-blue-800 text-white rounded-lg px-3 py-2 transition-colors no-underline">
                        <i data-lucide="ticket" class="w-5 h-5 text-white"></i>
                        <span class="text-sm font-medium">Raise a Ticket</span>
                    </a>
                </div>

            </div>
    </div>
    </nav>

</div>