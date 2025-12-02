<x-app-layout>
    <div class="flex min-h-screen">

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0 ">
            <!-- Page content -->
            <main class="flex-1 flex items-center justify-center px-4 py-12">
                <div class="text-center max-w-lg">
                    <div class="text-yellow-500 text-9xl font-extrabold mb-4">403</div>
                    <h2 class="text-3xl font-semibold text-gray-800 mb-2">Access Denied</h2>
                    <p class="text-gray-600 mb-6">You don’t have permission to view this page.</p>
                    <a href="{{ route('dashboard') }}"
                        class="inline-block px-6 py-3 bg-yellow-500 text-white text-sm font-medium rounded-md hover:bg-yellow-600 transition">
                        Return to Dashboard
                    </a>
                </div>
            </main>


            <!-- Footer -->
            @include('layouts.footer')


        </div>
    </div>
</x-app-layout>
