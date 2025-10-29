<nav class="fixed bottom-0 left-0 right-0 bg-primary border-t border-gray-700 z-50">
    <div class="flex justify-around items-center h-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

          <a href="{{ route('home') }}" 
           class="flex flex-col items-center justify-center w-full p-2 text-white transition-colors duration-200 
                  {{ request()->routeIs('home') ? 'text-yellow-400' : 'hover:text-yellow-200' }}">
            <i data-lucide="house" class="w-5 h-5"></i>
            <span class="text-xs font-medium mt-1">Home</span>
        </a>
        
        <a href="{{ route('dashboard') }}" 
           class="flex flex-col items-center justify-center w-full p-2 text-white transition-colors duration-200 
                  {{ request()->routeIs('dashboard') ? 'text-yellow-400' : 'hover:text-yellow-200' }}">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span class="text-xs font-medium mt-1">Dashboard</span>
        </a>

       


           <a href="{{ route('public.leagues.list') }}" 
           class="flex flex-col items-center justify-center w-full p-2 text-white transition-colors duration-200 
                  {{ request()->routeIs('public.leagues.') ? 'text-yellow-400' : 'hover:text-yellow-200' }}">
            <i data-lucide="trophy" class="w-5 h-5"></i>
            <span class="text-xs font-medium mt-1">Leagues</span>
        </a>
 

          <a href="{{ route('table') }}" 
           class="flex flex-col items-center justify-center w-full p-2 text-white transition-colors duration-200 
                  {{ request()->routeIs('table') ? 'text-yellow-400' : 'hover:text-yellow-200' }}">
            <i data-lucide="list" class="w-5 h-5"></i>
            <span class="text-xs font-medium mt-1">Standings</span>
        </a>
      

        <a href="{{ route('profile.edit') }}" 
           class="flex flex-col items-center justify-center w-full p-2 text-white transition-colors duration-200 
                  {{ request()->routeIs('profile.*') ? 'text-yellow-400' : 'hover:text-yellow-200' }}">
            <i data-lucide="ellipsis" class="w-5 h-5"></i>
            <span class="text-xs font-medium mt-1">More</span>
        </a>
        
        </div>
</nav>