<nav class="fixed bottom-0 left-0 right-0 bg-card z-50 rounded-t-2xl">

@auth
    <div class="flex justify-around items-center h-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

       

        

        <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center w-full p-2 text-gray-400 transition-colors duration-200 
                  {{ request()->routeIs('dashboard') ? 'text-white' : 'hover:text-white' }}">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span class="text-xs font-medium mt-1">Dashboard</span>
        </a>

       


        <a href="{{ route('admin.index') }}" class="flex flex-col items-center justify-center w-full p-2 text-gray-400 transition-colors duration-200 
                  {{ request()->routeIs('admin.index') ? 'text-white' : 'hover:text-white' }}">
            <i data-lucide="user" class="w-5 h-5"></i>
            <span class="text-xs font-medium mt-1">Users</span>
        </a>

         <a href="{{ route('public.leagues.list') }}" class="flex flex-col items-center justify-center w-full p-2 text-gray-400 transition-colors duration-200 
                  {{ request()->routeIs('public.leagues.*') ? 'text-white' : 'hover:text-white' }}">
            <i data-lucide="trophy" class="w-5 h-5"></i>
            <span class="text-xs font-medium mt-1">Leagues</span>
        </a>

      

      @endauth

    </div>
</nav>