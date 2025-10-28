<x-app-layout>

    <x-adsense />

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


            <div class="text-lg text-center font-bold text-gray-800 bg-white  p-4 rounded-lg shadow-md ">
                You are managing: {{ $league->name }} Classic League
            </div>


            <div x-cloak x-data="{ updating: false }">
                <form action="{{ route('admin.league.update') }}" method="POST"
                    @submit.prevent="updating = true; $el.submit()">
                    @csrf
                    <button type="submit" class="btn">
                        Update My League Data
                    </button>
                </form>

                <!-- Overlay -->
                <div x-show="updating"
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white text-black p-6 rounded-lg flex items-center gap-4">
                        <svg class="animate-spin h-6 w-6 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Updating your league...</span>
                    </div>
                </div>
            </div>

            @php
            $userLeague = auth()->user()->league; // Assuming User hasOne League
            @endphp

            @if($userLeague)
            <a href="{{ route('public.stats.show', ['slug' => $userLeague->slug]) }}"
                class="btn">
                View My League
            </a>
            @endif


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
                    <h3 class="text-xl font-bold text-yellow-500 mb-4">Mediocres</h3>
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
                    <h3 class="text-xl font-bold text-purple-500 mb-4">Last Men Standing (Never Last)</h3>
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
                    <h3 class="text-xl font-bold text-red-600 mb-4">🔴 Hall of Shame (Last 3+ Times)</h3>
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

                <x-adsense />

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


            {{--
            <x-page-title title="Season Standings" />
            @if($standings->count() > 0)
            <x-table :headers="['Pos', 'Team', 'Total ']">
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
            @endif --}}



        </div>
    </div>
    <x-adsense />
</x-app-layout>