<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="index, follow" />
    <title> FPL Managers Stats - GW </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/img/logo.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/logo.webp') }}">

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#001529">

    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
        var Tawk_API = Tawk_API || {},
      Tawk_LoadStart = new Date();
    (function () {
      var s1 = document.createElement("script"),
        s0 = document.getElementsByTagName("script")[0];
      s1.async = true;
      s1.src = "https://embed.tawk.to/67ada27b3a842732607e284f/1ijv45d63";
      s1.charset = "UTF-8";
      s1.setAttribute("crossorigin", "*");
      s0.parentNode.insertBefore(s1, s0);
    })();
    </script>
    <!--End of Tawk.to Script-->

    <meta name="mobile-web-app-capable" content="yes">

    <!--adsense script auto ads-->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1640926658118061"
        crossorigin="anonymous"></script>

    {{-- @include('frontend.scripts') --}}

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: "Nunito", sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 2px solid rgba(255, 255, 255, 0.18);
            border-radius: 1.5rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5), 0 0 20px rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
        }

        html {
            scroll-behavior: smooth;
        }

        @keyframes blink {

            0%,
            50%,
            100% {
                opacity: 1;
            }

            25%,
            75% {
                opacity: 0.4;
            }
        }

        .blink {
            animation: blink 1.5s infinite;
        }
    </style>
</head>

<body class="min-h-screen text-gray-200 bg-black">
    {{-- @include('frontend.adverts.adsense-top') --}}

    <header id="top" class="py-4 text-white bg-[#5B0E9B] shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">

            <h1 class="flex items-center gap-2 text-xl font-extrabold">
                <a href="/" class="flex items-center gap-2 hover:text-indigo-300 transition">
                    <x-logo class="w-12 h-12" />
                    FPL Galaxy
                </a>
            </h1>

            {{-- Login/Register Buttons (Right) --}}
            <nav class="flex space-x-4">
                {{-- Login Button --}}
                <a href="{{ route('login') }}"
                    class="px-3 py-1.5 text-sm font-semibold text-white bg-indigo-600 rounded-md 
                      hover:bg-indigo-700 transition duration-150 ease-in-out 
                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-[#5B0E9B]">
                    Login
                </a>

                {{-- Register Button (Hidden on small screens, shown on medium and up) --}}
                <a href="{{ route('register') }}" class=" md:inline-block px-3 py-1.5 text-sm font-semibold text-indigo-100 border border-indigo-100 rounded-md 
                      hover:bg-indigo-700 hover:text-white transition duration-150 ease-in-out">
                    Get Started
                </a>
            </nav>
        </div>
    </header>
    <main class="max-w-5xl mx-auto p-4 space-y-6">
        <x-adsense />




        <section class="mt-8">

            <h1 class=" text-xl font-extrabold gap-2 text-center">
                {{ $league->name }} Classic League
            </h1>

            <h2 class="mb-6 text-2xl font-bold text-center">Season Stats </h2>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="p-4 rounded-lg shadow-lg glass">
                    <p class="text-green-400 font-bold">MOST GW LEADS</p>
                    <p class="text-sm">
                        @forelse($stats['most_gw_leads'] as $name => $count)
                        {{ $name }} - {{ $count }} TIMES <br />
                        @empty
                        - - -
                        @endforelse
                    </p>
                </div>
                <div class="p-4 rounded-lg shadow-lg glass">
                    <p class="text-red-400 font-bold">MOST GW LAST</p>
                    <p class="text-sm">
                        @forelse($stats['most_gw_last'] as $name => $count)
                        {{ $name }} - {{ $count }} TIMES <br />
                        @empty
                        - - -
                        @endforelse
                    </p>
                </div>
                <div class="p-4 rounded-lg shadow-lg glass">
                    <p class="text-green-400 font-bold">HIGHEST GW POINTS</p>
                    <p class="text-sm">
                        {{ $stats['highest_gw_score']['points'] }} - By {{ $stats['highest_gw_score']['manager'] }}
                        (GW-{{ $stats['highest_gw_score']['gw'] }})
                    </p>
                </div>
                <div class="p-4 rounded-lg shadow-lg glass">
                    <p class="text-red-400 font-bold">LEAST GW POINTS</p>
                    <p class="text-sm">
                        {{ $stats['lowest_gw_score']['points'] }} - By {{ $stats['lowest_gw_score']['manager'] }} (GW-{{
                        $stats['lowest_gw_score']['gw'] }})
                    </p>
                </div>

                {{-- LONGEST TOP STREAK - Requires complex logic, skipped for minimal code --}}
                <div class="p-4 rounded-lg shadow-lg glass">
                    <p class="text-green-400 font-bold">LONGEST TOP STREAK</p>
                    <p class="text-sm">Feature Coming soon</p>
                </div>

                <div class="p-4 rounded-lg shadow-lg glass">
                    <p class="text-yellow-400 font-bold"> MEDIOCRES - NEVER BEST OR LAST</p>
                    <p class="text-sm">
                        @forelse($stats['mediocres'] as $name)
                        - {{ $name }} <br />
                        @empty
                        - - Never Best or Worst
                        @endforelse
                    </p>
                </div>

                <div class="p-4 rounded-lg shadow-lg glass sm:col-span-2 lg:col-span-3">
                    <p class="text-purple-400 font-bold">
                        ONLY MEN STANDING - HAVEN’T BEEN LAST BEFORE
                    </p>
                    @forelse($stats['men_standing'] as $name)
                    <p class="text-sm">- {{ $name }}</p>
                    @empty
                    <p class="text-sm">All managers have been last at least once!</p>
                    @endforelse
                </div>

                <div class="p-4 rounded-lg shadow-lg glass sm:col-span-2 lg:col-span-3">
                    <p class="text-purple-400 font-bold">
                        HALL OF SHAME - HAVE BEEN LAST 3 TIMES OR MORE
                    </p>
                    @forelse($stats['hall_of_shame'] as $name => $count)
                    <p class="text-sm">- {{ $name }} {{ $count }} Times</p>
                    @empty
                    <p class="text-sm">No one is in the Hall of Shame!</p>
                    @endforelse
                </div>

                <div class="p-4 rounded-lg shadow-lg glass sm:col-span-2 lg:col-span-3">
                    <p class="text-pink-400 font-bold">THE 100+ KINGS</p>
                    @forelse($stats['hundred_plus_league'] as $entry)
                    <p class="text-sm">- {{ $entry }}</p>
                    @empty
                    <p class="text-sm">No 100+ scores yet!</p>
                    @endforelse
                </div>
            </div>
        </section>

        <x-adsense />

        <h2 id="performance" class="mb-6 text-2xl font-bold text-center">
            GameWeek Performance
        </h2>

        <!-- Responsive Grid for GWs -->
        <div class="grid gap-4 sm:grid-cols-1 lg:grid-cols-3">
            @forelse ($gwPerformance as $gw)
            <div class="p-6 rounded-lg shadow-lg glass border-2 border-gray-700">
                <h2 class="mb-4 text-center text-white text-xl font-bold uppercase">
                    GameWeek {{ $gw['gameweek'] }}
                </h2>

                <div class="flex justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Best Manager(s)</p>
                        <p class="text-green-400 font-bold text-lg">
                            {{ implode(', ', $gw['best_managers']) }}
                        </p>
                        <p class="text-green-400 text-sm font-semibold">{{ $gw['best_points'] }}pts</p>
                    </div>

                    <div class="text-right">
                        <p class="text-gray-400 text-sm">Worst Manager(s)</p>
                        <p class="text-red-400 font-bold text-lg">
                            {{ implode(', ', $gw['worst_managers']) }}
                        </p>
                        <p class="text-red-400 text-sm font-semibold">{{ $gw['worst_points'] }}pts</p>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-400">
                No GameWeek data available yet.
            </div>
            @endforelse
        </div>


        <x-adsense />


        <section class="mt-8">
            {{-- League Title (Left/Center) --}}


            <h2 class="mb-6 text-2xl font-bold text-center">Table Standings</h2>
            <div class="overflow-x-auto glass">
                <table class="min-w-full text-left text-sm font-light">
                    <thead class="font-medium bg-white/10">
                        <tr>
                            <th scope="col" class="px-6 py-4">Rank</th>
                            <th scope="col" class="px-6 py-4">Manager Name</th>
                            <th scope="col" class="px-6 py-4">Total Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($standings as $index => $standing)
                        <tr class="border-b dark:border-neutral-500 {{ $index === 0 ? 'bg-green-600/30' : '' }}">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $index + 1 }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $standing['name'] }}</td>
                            <td class="whitespace-nowrap px-6 py-4 font-bold">{{ $standing['total_points'] }}</td>
                        </tr>
                        @empty
                        <tr class="border-b dark:border-neutral-500">
                            <td colspan="3" class="text-center py-8 text-gray-400">No managers or scores recorded yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>


        <x-adsense />

        <div class="flex my-6 justify-center">
            <a href="{{ route('register') }}" target="_blank"
                class="py-2 px-6 text-white font-semibold bg-green-600 rounded-lg shadow-md hover:bg-purple-700 transition duration-200 blink">
                Create account for your league
            </a>


        </div>

    </main>

    <x-consent-banner />

    <footer class="py-6 mt-8 text-center text-gray-500 text-sm border-t border-gray-800">
        © <span id="year"></span>
        <a href="https://techtowerinc.com" class="text-gray-400 hover:text-white transition">TechTower Inc.</a>. All
        rights reserved.
    </footer>

    <script>
        document.getElementById("year").textContent = new Date().getFullYear();
    </script>
</body>

</html>