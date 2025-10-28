<x-app-layout>

      <x-adsense/>
            
        <x-page-title title="Add Scores for GW {{ $nextGw }} ({{ $league->current_season_year }}/{{ $league->current_season_year + 1 }}) "/>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.gameweek.store') }}">
                    @csrf
                    
                    @foreach ($managers as $manager)
                        <div class="mb-4">
                            <x-input-label for="score_{{ $manager->id }}" :value="__('Points for ' . $manager->name)" />
                            <x-text-input id="score_{{ $manager->id }}" class="block mt-1 w-full" type="number" name="scores[{{ $manager->id }}]" :value="old('scores.'.$manager->id)" required autofocus min="0" />
                            <x-input-error :messages="$errors->get('scores.' . $manager->id)" class="mt-2" />
                        </div>
                    @endforeach

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button>
                            {{ __('Submit GW Scores') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>