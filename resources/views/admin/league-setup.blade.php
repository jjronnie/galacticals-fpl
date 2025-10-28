<x-app-layout>
    {{-- A container for a light background --}}
    <div class="py-12 bg-gray-50"> 
        {{-- Main Card Container --}}
        <div class="max-w-md mx-auto bg-white shadow-xl overflow-hidden sm:rounded-xl p-8 transition duration-300 transform hover:shadow-2xl">
            
            <h2 class="text-2xl font-extrabold text-gray-900 mb-2 text-center">
                 Import Your FPL League
            </h2>

            <p class="text-center text-sm text-gray-600 mb-6">
                Enter your FPL Classic League ID below to fetch and analyze your league's data.
            </p>

            <hr class="mb-6 border-gray-200">

            {{-- Status and Error Messages --}}
            @if ($errors->any())
                <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 font-medium border-l-4 border-red-500 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <p>Error: {{ $errors->first() }}</p>
                </div>
            @endif

            @if (session('status'))
                <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 font-medium border-l-4 border-green-500 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <p>Success: {{ session('status') }}</p>
                </div>
            @endif
            
            {{-- Form --}}
            <form method="POST" action="{{ route('admin.league.store') }}" x-data="{ loading: false }"
                @submit="loading = true" class="space-y-6">
                @csrf
                
                <div>
                    <label for="league_id" class="block text-sm font-bold text-gray-700 mb-2">
                        FPL Classic League ID:
                    </label>
                    <input type="text" id="league_id" name="league_id" placeholder="e.g., 1234567"
                        class="w-full border-gray-300 p-3 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition duration-150" required>
                </div>

                <button type="submit"
                    class="w-full px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-200 ease-in-out btn flex items-center justify-center"
                    :disabled="loading" x-bind:class="{ 'opacity-60 cursor-not-allowed': loading, 'shadow-md': loading }">

                    <template x-if="!loading">
                        <span class="flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.239"></path><path d="M12 12v9"></path><path d="m8 17l4 4 4-4"></path></svg>
                            Import League
                        </span>
                    </template>

                    <template x-if="loading" x-cloak>
                        <div class="flex items-center justify-center space-x-3">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            <span>Importing... Please wait</span>
                        </div>
                    </template>
                </button>

                <p class="text-xs text-gray-500 mt-2 text-center" x-show="loading" x-cloak>
                    Fetching league and manager data from FPL, this process may take up to a minute.
                </p>

            </form>
            
            <div class="mt-4 text-center border-t pt-4 border-gray-100">
                <a class="text-sm text-indigo-600 hover:text-indigo-700 font-semibold transition duration-150 ease-in-out inline-flex items-center" href="{{ route('find') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><path d="M12 17h.01"></path></svg>
                    Need help finding your League ID? Click here!
                </a>
            </div>

        </div>
    </div>
</x-app-layout>