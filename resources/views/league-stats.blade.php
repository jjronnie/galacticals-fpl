<x-app-layout>
    <main class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <x-adsense />
        <span class="p-2"></span>

        <x-gif-advert />




        <section class="mt-8">

            <h1 class=" text-xl font-extrabold gap-2 text-center">
                {{ $league->name }}
            </h1>

            <h2 class="mb-6 p-2 text-2xl font-bold text-center"> {{ $league->season }}/{{ $league->season +1 }} Season
                Stats </h2>


            @include('share')



            <div class="p-6 text-center">

                <a href="#current" class="btn">
                    View Current GW
                </a>

            </div>




            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">





                <x-gw-stat-card title="MOST GW LEADS" color="green">
                    @forelse($stats['most_gw_leads'] as $name => $count)
                    {{ $name }} - {{ $count }} TIMES <br>
                    @empty
                    - - -
                    @endforelse
                </x-gw-stat-card>

                <x-gw-stat-card title="MOST GW LAST" color="red">
                    @forelse($stats['most_gw_last'] as $name => $count)
                    {{ $name }} - {{ $count }} TIMES <br>
                    @empty
                    - - -
                    @endforelse
                </x-gw-stat-card>


                <x-gw-stat-card title="HIGHEST GW POINTS" color="green">
                    {{ $stats['highest_gw_score']['points'] }} - By {{ $stats['highest_gw_score']['manager'] }}
                    (GW-{{ $stats['highest_gw_score']['gw'] }})
                </x-gw-stat-card>

                <x-gw-stat-card title="LEAST GW POINTS" color="red">
                    {{ $stats['lowest_gw_score']['points'] }} - By {{ $stats['lowest_gw_score']['manager'] }}
                    (GW-{{ $stats['lowest_gw_score']['gw'] }})
                </x-gw-stat-card>

                <x-gw-stat-card title="THE BLOWOUT - BIGGEST POINTS DIFF IN A GW" color="green">
                    @if ($stats['the_blowout']['difference'] > 0)
                    <p class="text-sm">
                        {{ $stats['the_blowout']['difference'] }} Points Difference
                        (GW-{{ $stats['the_blowout']['gw'] }})
                    </p>
                    <p class="text-xs text-gray-400">
                        Highest: {{ $stats['the_blowout']['highest_scorer'] }}
                        ({{ $stats['the_blowout']['highest_points'] }} pts)
                    </p>
                    <p class="text-xs text-gray-400">
                        Lowest: {{ $stats['the_blowout']['lowest_scorer'] }}
                        ({{ $stats['the_blowout']['lowest_points'] }} pts)
                    </p>
                    @else
                    <p class="text-sm">No Gameweek data available yet.</p>
                    @endif
                </x-gw-stat-card>

                <x-gw-stat-card title="LONGEST TOP STREAK" color="green">
                    @if ($stats['longest_top_streak']['length'] > 0)
                    <p class="text-lg font-semibold">
                        {{ $stats['longest_top_streak']['manager'] }}
                    </p>

                    <p class="text-sm">
                        {{ $stats['longest_top_streak']['length'] }} consecutive GWs
                        (@GW {{ $stats['longest_top_streak']['start_gw'] }}
                        to GW {{ $stats['longest_top_streak']['end_gw'] }})
                    </p>
                    @else
                    <p class="text-sm">Not enough data yet.</p>
                    @endif
                </x-gw-stat-card>




                <x-gw-stat-card title="MEDIOCRES - NEVER BEST OR LAST" color="yellow">
                    @forelse($stats['mediocres'] as $name)
                    - {{ $name }} <br>
                    @empty
                    - - Never Best or Worst
                    @endforelse
                </x-gw-stat-card>

                <x-gw-stat-card title="LEAGUE ZONES" color="green">
                    <ul class="list-disc list-inside space-y-2 text-sm">

                        <li>
                            <span class="text-blue-300 font-semibold">Champions League:</span>
                            <ul class="ml-4 text-gray-300">
                                @foreach ($stats['league_zones']['champions_league'] as $manager)
                                <li>- {{ $manager }}</li>
                                @endforeach
                            </ul>
                        </li>

                        <li>
                            <span class="text-yellow-300 font-semibold">Europa League:</span>
                            <ul class="ml-4 text-gray-300">
                                @foreach ($stats['league_zones']['europa_league'] as $manager)
                                <li>- {{ $manager }}</li>
                                @endforeach
                            </ul>
                        </li>

                        <li>
                            <span class="text-red-300 font-semibold">Relegation Zone:</span>
                            <ul class="ml-4 text-gray-300">
                                @foreach ($stats['league_zones']['relegation_zone'] as $manager)
                                <li>- {{ $manager }}</li>
                                @endforeach
                            </ul>
                        </li>

                    </ul>
                </x-gw-stat-card>

                <x-gw-stat-card title="WOODEN SPOON TROPHY CONTENDERS" color="green">
                    @if (!empty($stats['wooden_spoon_contenders']))
                    <ul class="list-disc list-inside text-sm pl-2">
                        @foreach ($stats['wooden_spoon_contenders'] as $contender)
                        <li>{{ $contender }}</li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-sm">No managers in the league yet.</p>
                    @endif
                </x-gw-stat-card>

                <x-gw-stat-card title="ONLY MEN STANDING - HAVEN’T BEEN LAST BEFORE" color="purple">
                    @forelse($stats['men_standing'] as $name)
                    <p class="text-sm">- {{ $name }}</p>
                    @empty
                    <p class="text-sm">All managers have been last at least once!</p>
                    @endforelse
                </x-gw-stat-card>

                <x-gw-stat-card title="HALL OF SHAME - HAVE BEEN LAST 3 TIMES OR MORE" color="purple">
                    @forelse($stats['hall_of_shame'] as $name => $count)
                    <p class="text-sm">- {{ $name }} {{ $count }} Times</p>
                    @empty
                    <p class="text-sm">No one is in the Hall of Shame!</p>
                    @endforelse
                </x-gw-stat-card>

                <x-gw-stat-card title="THE 100+ KINGS" color="pink">
                    @forelse($stats['hundred_plus_league'] as $entry)
                    <p class="text-sm">- {{ $entry }}</p>
                    @empty
                    <p class="text-sm">No 100+ scores yet!</p>
                    @endforelse
                </x-gw-stat-card>









            </div>
        </section>

        <x-adsense />

        <h2 id="performance" class="mb-6 text-2xl font-bold text-center">
            GameWeek Performance
        </h2>

        <div class="grid gap-4 sm:grid-cols-1 lg:grid-cols-3">
            @forelse ($gwPerformance as $gw)
            <x-gw-card :gw="$gw" />
            @empty
            <div class="text-center py-8 text-accent">
                No GameWeek data available yet.
            </div>
            @endforelse
        </div>



        <span id="current" class="">
        </span>
        @include('share')











        <x-adsense />

        @guest


        <div class="flex my-6 justify-center">
            <a href="{{ route('register') }}" target="_blank"
                class="py-2 px-6 text-white font-semibold bg-green-600 rounded-lg shadow-md hover:bg-purple-700 transition duration-200 blink">
                Create account for your league
            </a>


        </div>
        @endguest

        <x-back-to-top />


    </main>
</x-app-layout>