<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="robots" content="index, follow" />
    <title>FPL Managers Stats Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

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

    <!--adsense script auto ads-->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1640926658118061"
        crossorigin="anonymous"></script>
</head>

<body class="min-h-screen text-gray-200 bg-black">

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

    <main class="max-w-5xl mx-auto p-4 space-y-12">

        <x-adsense />

        <!-- 1. Hero Section: Explanation, CTA, and Focus -->
        <section class="mt-12 text-center">
            <h2 class="text-5xl md:text-6xl font-extrabold text-white leading-tight mb-4">
                Make Your<span class="text-indigo-400"> FPL Mini-League</span> More Fun
            </h2>
            <p class="text-xl text-gray-400 mb-8 max-w-3xl mx-auto">
                Go beyond total points. This platform provides advanced, gameweek-by-gameweek statistics to track
                manager performance, consistency, and rivalry history within your private FPL mini-leagues.
            </p>

            <!-- CTA Links -->
            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-6">
                <a href="#join-steps"
                    class="px-8 py-3 text-lg font-bold text-white bg-green-600 rounded-full shadow-lg hover:bg-green-700 transition duration-300 transform hover:scale-105">
                    Get Started
                </a>
                <a href="{{route ('public.leagues.list') }}"
                    class="px-8 py-3 text-lg font-bold text-indigo-400 border-2 border-indigo-400 rounded-full shadow-lg hover:bg-indigo-700 hover:text-white transition duration-300 transform hover:scale-105">
                    Leagues
                </a>
            </div>
        </section>

        <!-- 2. Features/Value Proposition Section -->
        <section class="grid md:grid-cols-3 gap-6 pt-6">
            <div class="glass text-center">
                <p class="text-4xl text-indigo-400 mb-3">📈</p>
                <h3 class="text-xl font-bold mb-2 text-white">Advanced Metrics</h3>
                <p class="text-gray-400">See head-to-head records, manager consistency scores, and detailed season
                    summaries.</p>
            </div>
            <div class="glass text-center">
                <p class="text-4xl text-indigo-400 mb-3">🛡️</p>
                <h3 class="text-xl font-bold mb-2 text-white">Rivalry Tracking</h3>
                <p class="text-gray-400">Find out who your league's biggest rival is and who they dominate season after
                    season.</p>
            </div>
            <div class="glass text-center">
                <p class="text-4xl text-indigo-400 mb-3">🔒</p>
                <h3 class="text-xl font-bold mb-2 text-white">Free & Private</h3>
                <p class="text-gray-400">Admins can create and manage their mini-league for free, securely and
                    privately.</p>
            </div>
        </section>

        <x-adsense />

        <!-- 3. How to Join/Admin Setup Illustration -->
        <section id="join-steps" class="pt-12">
            <h2 class="mb-8 text-3xl font-bold text-center border-b border-gray-700 pb-2">How It Works: Setup in 3
                Simple Steps</h2>

            <div class="grid md:grid-cols-3 gap-8">

                <!-- Step 1: Admin Registration -->
                <div class="glass flex flex-col items-center text-center p-6">
                    <div
                        class="w-12 h-12 flex items-center justify-center bg-indigo-600 rounded-full text-white text-2xl font-black mb-4">
                        1</div>
                    <h4 class="text-xl font-bold text-white mb-2">Admin Registers</h4>
                    <p class="text-gray-400 mb-4">
                        A league administrator registers for a free account to manage their mini-league.
                    </p>
                    <a href="{{ route('register') }}" class="text-indigo-400 font-semibold hover:text-indigo-300">
                        Start Here &rarr;
                    </a>
                </div>

                <!-- Step 2: League Creation -->
                <div class="glass flex flex-col items-center text-center p-6">
                    <div
                        class="w-12 h-12 flex items-center justify-center bg-indigo-600 rounded-full text-white text-2xl font-black mb-4">
                        2</div>
                    <h4 class="text-xl font-bold text-white mb-2">Create Your League</h4>
                    <p class="text-gray-400 mb-4">
                        Admins enter their FPL league name and ID to initialize the stats tracker on the dashboard.
                    </p>
                    <span class="text-sm text-gray-500">Requires FPL League ID</span>
                </div>

                <!-- Step 3: Data Input & Sharing -->
                <div class="glass flex flex-col items-center text-center p-6">
                    <div
                        class="w-12 h-12 flex items-center justify-center bg-indigo-600 rounded-full text-white text-2xl font-black mb-4">
                        3</div>
                    <h4 class="text-xl font-bold text-white mb-2">Track & Share</h4>
                    <p class="text-gray-400 mb-4">
                        Admins periodically input Gameweek scores. Once entered, the public link is ready for sharing
                        with all managers!
                    </p>
                    <a href="{{route ('public.leagues.list') }}"
                        class="text-green-400 font-semibold hover:text-green-300 blink">
                        View Example Stats
                    </a>
                </div>
            </div>
        </section>

        <x-adsense />



    </main>
    <x-consent-banner />

    <footer class="py-6 mt-12 text-center text-gray-500 text-sm border-t border-gray-800">
        © <span id="year"></span>
        <a href="https://techtowerinc.com" class="text-gray-400 hover:text-white transition">TechTower Inc.</a>. All
        rights reserved.
    </footer>

    <script>
        document.getElementById("year").textContent = new Date().getFullYear();
    </script>
</body>

</html>