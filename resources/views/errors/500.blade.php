<x-app-layout>
    <div class="flex min-h-screen">

        <!-- Sidebar -->






        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0 ">





            <!-- Page content -->
            <main class="flex-1 flex items-center justify-center px-4 py-12">
                <div class="text-center max-w-lg">
                    <div class="text-red-600 text-9xl font-extrabold mb-4">500</div>
                    <h2 class="text-3xl font-semibold text-gray-800 mb-2">Internal Server Error</h2>
                    <p class="text-gray-600 mb-6">
                        An unexpected error occurred on the server. Please try again later or contact support if the issue
                        persists.
                    </p>

                    <a href="{{ route('dashboard') }}"
                        class="inline-block px-6 py-3 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition">
                        Return to Dashboard
                    </a>
                </div>
            </main>          


        </div>
    </div>
</x-app-layout>
