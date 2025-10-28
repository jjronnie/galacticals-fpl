<x-app-layout>
    <div class="max-w-lg mx-auto bg-white shadow-md p-6 rounded-lg">
        <h2 class="text-xl font-semibold mb-4">Import FPL League</h2>

        @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            {{ $errors->first() }}
        </div>
        @endif

        @if (session('status'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('admin.league.store') }}" x-data="{ loading: false }"
            @submit="loading = true">
            @csrf
            <div>
                <label class="block mb-2 font-medium">FPL League ID</label>
                <input type="text" name="league_id" class="w-full border p-2 rounded" required>
            </div>

            <button type="submit"
                class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center justify-center space-x-2 w-full"
                :disabled="loading" x-bind:class="{ 'opacity-70 cursor-not-allowed': loading }">

                <template x-if="!loading">
                    <span>Import League</span>
                </template>

                <template x-if="loading" x-cloak>
                    <div class="flex items-center space-x-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span>Importing... Please wait</span>
                    </div>
                </template>
            </button>

            <p class="text-sm text-gray-500 mt-2" x-show="loading" x-cloak>
                Fetching league and manager data from FPL, this might take up to a minute.
            </p>

        </form>
    </div>
</x-app-layout>