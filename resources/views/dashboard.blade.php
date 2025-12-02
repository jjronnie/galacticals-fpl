<x-app-layout>

    <x-adsense />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if (session('status'))
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                {{ session('status') }}
            </div>
            @endif
            @if (session('error'))
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                {{ session('error') }}
            </div>
            @endif

            <!-- Header Section -->
            <div class="bg-card rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <h1 class="text-2xl font-bold text-white mb-4">
                        {{ $league->name }} FPL League
                    </h1>

                    <div class="flex flex-wrap gap-3">
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
                                    <svg class="animate-spin h-6 w-6 text-green-500" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4">
                                        </circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>Updating your league...</span>
                                </div>
                            </div>

                        </div>

                        @php
                        $userLeague = auth()->user()->league;
                        @endphp

                        @if($userLeague)
                        <a href="{{ route('public.leagues.show', ['slug' => $userLeague->slug]) }}" class="btn">
                            View My League
                        </a>

                    </div>

                    <p class="text-sm p-3 text-gray-400">
                        Last Updated: {{ $lastUpdated->diffForHumans() ?? 'No data yet' }}
                    </p>



                    @endif
                </div>
            </div>

            <!-- Season Highlights -->
            <div>
                <x-page-title title="Season Highlights" />
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

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
                        value="{{ $mostLeads > 0 ? $leadManager . ' (' . $mostLeads . ' times)' : 'N/A' }}"
                        icon="star" />

                    @php
                    $mostLast = collect($stats['most_gw_last'])->max() ?? 0;
                    $lastManager = collect($stats['most_gw_last'])->filter(fn($count) => $count ==
                    $mostLast)->keys()->implode(', ');
                    @endphp
                    <x-stat-card title="Most GW Last"
                        value="{{ $mostLast > 0 ? $lastManager . ' (' . $mostLast . ' times)' : 'N/A' }}"
                        icon="arrow-down" />

                </div>
            </div>

            @guest


            <!-- Additional Stats Sections - Ready to implement -->

            <!-- Performance Metrics -->
            <div>
                <x-page-title title="Performance Metrics" />
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                    {{-- Example stat cards you can implement --}}
                    {{--
                    <x-stat-card title="Average GW Score" value="{{ $stats['average_gw_score'] ?? 'N/A' }}"
                        icon="bar-chart" /> --}}

                    {{--
                    <x-stat-card title="Total Transfers Made" value="{{ $stats['total_transfers'] ?? 'N/A' }}"
                        icon="refresh-cw" /> --}}

                    {{--
                    <x-stat-card title="Chips Used" value="{{ $stats['chips_used'] ?? 'N/A' }}" icon="zap" /> --}}

                    {{--
                    <x-stat-card title="Bench Points Left" value="{{ $stats['total_bench_points'] ?? 'N/A' }}"
                        icon="users" /> --}}

                    {{--
                    <x-stat-card title="Captain Success Rate" value="{{ $stats['captain_success_rate'] ?? 'N/A' }}"
                        icon="award" /> --}}

                    {{--
                    <x-stat-card title="Overall Average Rank" value="{{ $stats['average_overall_rank'] ?? 'N/A' }}"
                        icon="trending-up" /> --}}

                </div>
            </div>

            <!-- Manager Records -->
            <div>
                <x-page-title title="Manager Records" />
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                    {{--
                    <x-stat-card title="Most Consistent" value="{{ $stats['most_consistent_manager'] ?? 'N/A' }}"
                        icon="activity" /> --}}

                    {{--
                    <x-stat-card title="Biggest Climber" value="{{ $stats['biggest_climber'] ?? 'N/A' }}"
                        icon="arrow-up" /> --}}

                    {{--
                    <x-stat-card title="Biggest Faller" value="{{ $stats['biggest_faller'] ?? 'N/A' }}"
                        icon="arrow-down" /> --}}

                    {{--
                    <x-stat-card title="Most Transfers" value="{{ $stats['most_transfers_manager'] ?? 'N/A' }}"
                        icon="shuffle" /> --}}

                    {{--
                    <x-stat-card title="Best Captain Picker" value="{{ $stats['best_captain_picker'] ?? 'N/A' }}"
                        icon="target" /> --}}

                    {{--
                    <x-stat-card title="Most Wildcards Used" value="{{ $stats['most_wildcards'] ?? 'N/A' }}"
                        icon="maximize" /> --}}

                </div>
            </div>

            <!-- Team Statistics -->
            <div>
                <x-page-title title="Team Statistics" />
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

                    {{--
                    <x-stat-card title="Most Owned Player" value="{{ $stats['most_owned_player'] ?? 'N/A' }}"
                        icon="user-check" /> --}}

                    {{--
                    <x-stat-card title="Most Captained Player" value="{{ $stats['most_captained_player'] ?? 'N/A' }}"
                        icon="shield" /> --}}

                    {{--
                    <x-stat-card title="Best Differential" value="{{ $stats['best_differential'] ?? 'N/A' }}"
                        icon="trending-up" /> --}}

                    {{--
                    <x-stat-card title="Worst Differential" value="{{ $stats['worst_differential'] ?? 'N/A' }}"
                        icon="trending-down" /> --}}

                </div>
            </div>

            <!-- League Milestones -->
            <div>
                <x-page-title title="League Milestones" />
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                    {{--
                    <x-stat-card title="First 100+ Score" value="{{ $stats['first_100_score'] ?? 'N/A' }}"
                        icon="flag" /> --}}

                    {{--
                    <x-stat-card title="Green Arrows" value="{{ $stats['total_green_arrows'] ?? 'N/A' }}"
                        icon="arrow-up-circle" /> --}}

                    {{--
                    <x-stat-card title="Red Arrows" value="{{ $stats['total_red_arrows'] ?? 'N/A' }}"
                        icon="arrow-down-circle" /> --}}

                    {{--
                    <x-stat-card title="Highest Weekly Gain" value="{{ $stats['highest_weekly_gain'] ?? 'N/A' }}"
                        icon="plus-circle" /> --}}

                    {{--
                    <x-stat-card title="Biggest Weekly Drop" value="{{ $stats['biggest_weekly_drop'] ?? 'N/A' }}"
                        icon="minus-circle" /> --}}

                    {{--
                    <x-stat-card title="Most Points on Bench" value="{{ $stats['most_bench_points_gw'] ?? 'N/A' }}"
                        icon="frown" /> --}}

                </div>
            </div>


            @endguest

        </div>
    </div>
    <x-adsense />
</x-app-layout>