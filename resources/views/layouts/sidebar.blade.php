<nav class="fixed bottom-0 left-0 right-0 bg-card z-50 rounded-t-2xl">


    <div class="flex justify-around items-center h-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @guest



        <a href="{{ route('home') }}" class="flex flex-col items-center justify-center w-full p-2 text-gray-400 transition-colors duration-200 
                  {{ request()->routeIs('home') ? 'text-white' : 'hover:text-white' }}">
            <i data-lucide="house" class="w-5 h-5"></i>
            <span class="text-xs font-medium mt-1">Home</span>
        </a>
        @endguest

        @auth

        <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center w-full p-2 text-gray-400 transition-colors duration-200 
                  {{ request()->routeIs('dashboard') ? 'text-white' : 'hover:text-white' }}">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span class="text-xs font-medium mt-1">Dashboard</span>
        </a>



        @if(auth()->user()->isAdmin())


        <a href="{{ route('admin.index') }}" class="flex flex-col items-center justify-center w-full p-2 text-gray-400 transition-colors duration-200 
                  {{ request()->routeIs('admin.index') ? 'text-white' : 'hover:text-white' }}">
            <i data-lucide="user" class="w-5 h-5"></i>
            <span class="text-xs font-medium mt-1">Users</span>
        </a>



        @endif





        @endauth


        <a href="{{ route('public.leagues.list') }}" class="flex flex-col items-center justify-center w-full p-2 text-gray-400 transition-colors duration-200 
                  {{ request()->routeIs('public.leagues.*') ? 'text-white' : 'hover:text-white' }}">
            <i data-lucide="trophy" class="w-5 h-5"></i>
            <span class="text-xs font-medium mt-1">Leagues</span>
        </a>

        @auth
        <a href="{{ route('table') }}" class="flex flex-col items-center justify-center w-full p-2 text-gray-400 transition-colors duration-200 
                  {{ request()->routeIs('table') ? 'text-white' : 'hover:text-white' }}">
            <i data-lucide="list" class="w-5 h-5"></i>
            <span class="text-xs font-medium mt-1">History</span>
        </a>
        @endauth
        @guest

        {{-- Login link --}}
        @if (!request()->routeIs('login'))
        <a href="{{ route('login') }}" class="flex flex-col items-center justify-center w-full p-2 text-gray-400 transition-colors duration-200
       {{ request()->routeIs('login') ? 'text-white' : 'hover:text-white' }}">
            <i data-lucide="log-in" class="w-5 h-5"></i>
            <span class="text-xs font-medium mt-1">Login</span>
        </a>
        @endif

        {{-- Register link --}}
        {{-- @if (!request()->routeIs('register'))
        <a href="{{ route('register') }}" class="flex flex-col items-center justify-center w-full p-2 text-gray-400 transition-colors duration-200
       {{ request()->routeIs('register') ? 'text-white' : 'hover:text-white' }}">
            <i data-lucide="user-plus" class="w-5 h-5"></i>
            <span class="text-xs font-medium mt-1">Register</span>
        </a>
        @endif --}}


        @endguest
@guest
    

          <a href="{{ route('more') }}" class="flex flex-col items-center justify-center w-full p-2 text-gray-400 transition-colors duration-200 
                  {{ request()->routeIs('profile.*') ? 'text-white' : 'hover:text-white' }}">
            <i data-lucide="ellipsis" class="w-5 h-5"></i>
            <span class="text-xs font-medium mt-1">More</span>
        </a>

        @endguest

        @auth

        <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center w-full p-2 text-gray-400 transition-colors duration-200 
                  {{ request()->routeIs('profile.*') ? 'text-white' : 'hover:text-white' }}">
            <i data-lucide="ellipsis" class="w-5 h-5"></i>
            <span class="text-xs font-medium mt-1">More</span>
        </a>

        @endauth

    </div>
</nav>