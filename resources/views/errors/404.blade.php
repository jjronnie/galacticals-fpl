<x-app-layout>
    <div class="flex min-h-screen">

      


        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0 ">





            <!-- Page content -->
            <main class="flex-1 flex items-center justify-center px-4 py-12">
                <div class="text-center max-w-lg">
                    <div style="font-size: 12rem" class="text-secondary font-extrabold mb-4" > 404</div>
                    <h2 class="text-3xl font-semibold  mb-2">Oops! Page Not Found</h2>
                    <p class=" mb-6">
                        The page you are looking for might have been removed, renamed, or does not exist.
                    </p>

                    @auth
                    <a href="{{ route('dashboard') }}"
                        class="btn">
                        Return to Dashboard
                    </a>
                    @else
                    <a href="{{ route('home') }}"
                        class="btn">
                        Return to Home 
                    </a>
                    @endauth
                </div>
            </main>





        </div>
    </div>
</x-app-layout>