<x-app-layout>
    <x-adsense/>

    <div class="max-w-md mx-auto bg-card rounded-md shadow-xl overflow-hidden sm:rounded-xl p-8">

        <h2 class="text-2xl font-extrabold text-white mb-6">
            Confirm League Details
        </h2>

        {{-- Message with left border --}}
        <div class="bg-gray-800/60 text-gray-200 p-4 rounded-lg mb-6 border-l-4 border-white">
            <p class="text-sm mb-2">
                The league ID you entered is associated with the following league:
            </p>

            <p class="text-lg font-bold text-white">
                “{{ $preview['name'] }}”
            </p>
        </div>

        <p class="text-sm text-gray-300 mb-6">
            Do you want to proceed and fetch its data?
        </p>

        {{-- Actions --}}
        <form method="POST" action="{{ route('leagues.confirm.action') }}" class="space-y-3">
            @csrf

            <input type="hidden" name="token" value="{{ session('league_preview_token') }}">


            <button
                type="submit"
                name="action"
                value="yes"
                class="w-full px-4 py-3 bg-primary hover:bg-secondary text-white font-bold rounded-lg shadow-md transition duration-200">
                Yes, proceed
            </button>

            <button
                type="submit"
                name="action"
                value="no"
                class="w-full px-4 py-3 bg-gray-700 hover:bg-gray-600 text-white font-bold rounded-lg shadow-sm transition duration-200">
                No, cancel
            </button>
        </form>

    </div>
</x-app-layout>
