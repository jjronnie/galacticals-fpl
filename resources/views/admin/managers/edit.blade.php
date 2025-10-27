<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Manager - {{ $league->name }}
        </h2>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <form method="POST" action="{{ route('admin.managers.update', [$league, $manager]) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Manager Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $manager->name) }}" required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="team_name" class="block text-gray-700 text-sm font-bold mb-2">Team Name (Optional)</label>
                    <input type="text" name="team_name" id="team_name" value="{{ old('team_name', $manager->team_name) }}"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ $manager->is_active ? 'checked' : '' }} class="rounded">
                        <span class="ml-2 text-sm text-gray-700">Active Manager</span>
                    </label>
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.leagues.show', $league) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Update Manager
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>