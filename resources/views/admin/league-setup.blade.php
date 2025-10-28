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

        <form method="POST" action="{{ route('admin.league.store') }}">
            @csrf
            <div>
                <label class="block mb-2 font-medium">FPL League ID</label>
                <input type="text" name="league_id" class="w-full border p-2 rounded" required>
            </div>

            <button class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Import League
            </button>
        </form>
    </div>
</x-app-layout>
