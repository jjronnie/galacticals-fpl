<x-app-layout>
    <div class="flex min-h-screen">    

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">

           



            <main class="flex-1 flex items-center justify-center px-4 py-12">
                <div class="text-center max-w-lg">
                    <div class="text-orange-500 text-9xl font-extrabold mb-4">419</div>
                    <h2 class="text-3xl font-semibold text-gray-800 mb-2">Page Expired</h2>
                    <p class="text-gray-600 mb-6">Your session has expired. Please refresh the page or log in again.</p>
                    <a href="{{ route('login') }}"
                        class="inline-block px-6 py-3 bg-orange-500 text-white text-sm font-medium rounded-md hover:bg-orange-600 transition">
                        Re-login
                    </a>
                </div>
            </main>
            


        </div>
    </div>
</x-app-layout>
