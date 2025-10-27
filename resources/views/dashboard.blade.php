<x-app-layout>
    

    <x-page-title title="{{ $league->name }} (Season: {{ $league->current_season_year }}/{{ $league->current_season_year + 1 }})"
        />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if (session('status'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50  " role="alert">
                {{ session('status') }}
            </div>
            @endif
            @if (session('error'))
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50  " role="alert">
                {{ session('error') }}
            </div>
            @endif

            <div class="flex justify-between items-center bg-white  p-4 rounded-lg shadow-md">
                <div class="text-lg font-bold text-gray-800 ">
                    Current GW: <span class="text-indigo-600">{{ $league->current_gameweek }} / 38</span>
                </div>

                <div class="space-x-4">
                    <a href="{{ route('admin.gameweek.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ $league->current_gameweek < 38 ? 'Add Scores for GW ' . ($league->current_gameweek + 1) :
                            'Season Complete' }}
                    </a>

                    {{-- <form method="POST" action="{{ route('admin.season.next') }}" class="inline-block"
                        onsubmit="return confirm('Are you sure you want to advance to the next season? This action cannot be undone for the current league status.');">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 disabled:opacity-50 transition ease-in-out duration-150"
                            {{ $league->current_gameweek < 38 ? 'disabled' : '' }}>
                                Start New Season ({{ $league->current_season_year + 1 }}/{{
                                $league->current_season_year + 2 }})
                        </button>
                    </form> --}}
                </div>
            </div>

            <x-page-title title="Season Highlights" />
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                <x-stat-card title="Highest GW Score"
                    value="{{ $stats['highest_gw_score']['points'] > 0 ? $stats['highest_gw_score']['points'] . ' by ' . $stats['highest_gw_score']['manager'] . ' (GW ' . $stats['highest_gw_score']['gw'] . ')' : 'N/A' }}"
                    icon="trophy" />

                <x-stat-card title="Lowest GW Score"
                    value="{{ $stats['lowest_gw_score']['points'] < 9999 ? $stats['lowest_gw_score']['points'] . ' by ' . $stats['lowest_gw_score']['manager'] . ' (GW ' . $stats['lowest_gw_score']['gw'] . ')' : 'N/A' }}"
                    icon="trash-2" />

                @php
                $mostLeads = collect($stats['most_gw_leads'])->max() ?? 0;
                $leadManager = collect($stats['most_gw_leads'])->filter(fn($count) => $count ==
                $mostLeads)->keys()->implode(', ');
                @endphp
                <x-stat-card title="Most GW Leads"
                    value="{{ $mostLeads > 0 ? $leadManager . ' (' . $mostLeads . ' times)' : 'N/A' }}" icon="star" />

                @php
                $mostLast = collect($stats['most_gw_last'])->max() ?? 0;
                $lastManager = collect($stats['most_gw_last'])->filter(fn($count) => $count ==
                $mostLast)->keys()->implode(', ');
                @endphp
                <x-stat-card title="Most GW Last"
                    value="{{ $mostLast > 0 ? $lastManager . ' (' . $mostLast . ' times)' : 'N/A' }}"
                    icon="arrow-down" />
                    
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-6">

                <div class="bg-white  p-6 shadow-md rounded-lg">
                    <h3 class="text-xl font-bold text-yellow-500 mb-4">👑 Queen/King Mediocre</h3>
                    @if(count($stats['mediocres']) > 0)
                    <ul class="list-disc pl-5 text-gray-700 ">
                        @foreach($stats['mediocres'] as $name)
                        <li>{{ $name }}</li>
                        @endforeach
                    </ul>
                    @else
                    <x-empty-state message="Everyone has been Best or Worst at least once!" />
                    @endif
                </div>

                <div class="bg-white  p-6 shadow-md rounded-lg">
                    <h3 class="text-xl font-bold text-purple-500 mb-4">🛡️ Men Standing (Never Last)</h3>
                    @if(count($stats['men_standing']) > 0)
                    <ul class="list-disc pl-5 text-gray-700 ">
                        @foreach($stats['men_standing'] as $name)
                        <li>{{ $name }}</li>
                        @endforeach
                    </ul>
                    @else
                    <x-empty-state message="Everyone has been last at least once... Ouch!" />
                    @endif
                </div>

                <div class="bg-white  p-6 shadow-md rounded-lg">
                    <h3 class="text-xl font-bold text-red-600 mb-4">🔴 Hall of Shame (Last 2+ Times)</h3>
                    @if(count($stats['hall_of_shame']) > 0)
                    <ul class="list-disc pl-5 text-gray-700 ">
                        @foreach($stats['hall_of_shame'] as $name => $count)
                        <li>{{ $name }} ({{ $count }} Times)</li>
                        @endforeach
                    </ul>
                    @else
                    <x-empty-state message="No one has been last 2 or more times this season." />
                    @endif
                </div>

                <div class="bg-white  p-6 shadow-md rounded-lg lg:col-span-3">
                    <h3 class="text-xl font-bold text-pink-500 mb-4">💯 The 100+ League</h3>
                    @if(count($stats['hundred_plus_league']) > 0)
                    <ul class="list-disc pl-5 text-gray-700  grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($stats['hundred_plus_league'] as $entry)
                        <li>{{ $entry }}</li>
                        @endforeach
                    </ul>
                    @else
                    <x-empty-state message="No manager has hit the 100+ score mark yet." />
                    @endif
                </div>
            </div>


            <x-page-title title="Season Standings (Total Points)" />
            @if($standings->count() > 0)
            <x-table :headers="['Rank', 'Manager Name', 'Total Points']">
                @foreach($standings as $index => $standing)
                <x-table.row class="{{ $index === 0 ? 'bg-green-100 ' : '' }}">
                    <x-table.cell class="font-bold">{{ $index + 1 }}</x-table.cell>
                    <x-table.cell class="font-semibold">{{ $standing['name'] }}</x-table.cell>
                    <x-table.cell class="text-lg font-extrabold">{{ $standing['total_points'] }}</x-table.cell>
                </x-table.row>
                @endforeach
            </x-table>
            @else
            <x-empty-state message="No scores recorded yet. Add managers and their GW scores!" />
            @endif

            <x-page-title title="Manage Managers" />
            <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">
                <div class="bg-white  p-6 shadow-md rounded-lg">
                    <h3 class="text-lg font-bold text-gray-900  mb-4">Add New Manager</h3>
                    <form method="POST" action="{{ route('admin.manager.store') }}">
                        @csrf
                        <div class="flex space-x-2">
                            <div class="flex-grow">
                                <x-text-input id="manager_name" class="w-full" type="text" name="name"
                                    :value="old('name')" required placeholder="Manager's Name" />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                              <div class="flex-grow">
                                <x-text-input id="team_name" class="w-full" type="text" name="team_name"
                                    :value="old('team_name')" required placeholder="Team Name" />
                                <x-input-error :messages="$errors->get('team_name')" class="mt-2" />
                            </div>
                            
                            <x-primary-button>
                                {{ __('Add') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>

                <div class="bg-white p-6 shadow-md rounded-lg">
    <h3 class="text-lg font-bold text-gray-900 mb-4">Current Managers ({{ $managers->count() }})
    </h3>
    @if($managers->count() > 0)
    
    
        <x-table :headers="['Name', 'Team', 'Action']" >
            {{-- Loop through managers to create table rows --}}
            @foreach($managers as $manager)
            <x-table.row>
                
                {{-- Name Cell --}}
                <x-table.cell>
                    <span class="text-gray-700">{{ $manager->name ?? '-'}}</span>
                </x-table.cell>

                {{-- Team Cell --}}
                <x-table.cell>
                    <span class="text-gray-700">{{ $manager->team_name ?? '-' }}</span>
                </x-table.cell>

                {{-- Action Cell with Confirm Modal --}}
                <x-table.cell class="text-right"> {{-- Align action button to the right --}}
                    <x-confirm-modal :action="route('admin.manager.destroy', $manager)"
                        warning="Are you sure you want to delete this Manager? This action cannot be undone."
                        triggerIcon="trash" />
                </x-table.cell>
                
            </x-table.row>
            @endforeach
        </x-table>
    
    
    @else
    <x-empty-state message="No managers added yet. Start by adding a manager!" />
    @endif
</div>
            </div>

        </div>
    </div>
</x-app-layout>