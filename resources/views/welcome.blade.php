<x-app-layout>

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
    <!-- Advanced Metrics -->
    <div class="text-center rounded-3xl border-2 border-gray-700 bg-card p-6">
        <div class="flex justify-center text-indigo-400 mb-3">
            <i data-lucide="bar-chart-2" class="w-12 h-12"></i>
        </div>
        <h3 class="text-xl font-bold mb-2 text-white">Advanced Metrics</h3>
        <p class="text-gray-400">Track gameweek records, manager consistency scores, and detailed weekly summaries.</p>
    </div>

    <!-- Rivalry Tracking -->
    <div class="text-center rounded-3xl border-2 border-gray-700 bg-card p-6">
        <div class="flex justify-center text-indigo-400 mb-3">
            <i data-lucide="file-stack" class="w-12 h-12"></i>
        </div>
        <h3 class="text-xl font-bold mb-2 text-white">Rivalry Tracking</h3>
        <p class="text-gray-400">Identify your league’s toughest rivals and see who dominates each game week.</p>
    </div>

    <!-- Free & Private -->
    <div class="text-center rounded-3xl border-2 border-gray-700 bg-card p-6">
        <div class="flex justify-center text-indigo-400 mb-3">
            <i data-lucide="lock" class="w-12 h-12"></i>
        </div>
        <h3 class="text-xl font-bold mb-2 text-white">Free & Private</h3>
        <p class="text-gray-400">Create and manage your mini-league for free, with secure and private data access.</p>
    </div>
</section>


        <x-adsense />

        <!-- 3. How to Join/Admin Setup Illustration -->
        <section id="join-steps" class="pt-12">
            <h2 class="mb-8 text-3xl font-bold text-center border-b border-gray-700 pb-2">How It Works: Setup in 3
                Simple Steps</h2>

            <div class="grid md:grid-cols-3 gap-8">

                <!-- Step 1: Admin Registration -->
                <div class="rounded-3xl border-2 border-gray-700 bg-card flex flex-col items-center text-center p-6">
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
                <div class="rounded-3xl border-2 border-gray-700 bg-card flex flex-col items-center text-center p-6">
                    <div
                        class="w-12 h-12 flex items-center justify-center bg-indigo-600 rounded-full text-white text-2xl font-black mb-4">
                        2</div>
                    <h4 class="text-xl font-bold text-white mb-2">Create Your League</h4>
                    <p class="text-gray-400 mb-4">
                        Admins enter their FPL league ID to initialize the stats tracker on the dashboard.
                    </p>
                    <span class="text-sm text-gray-500">Requires FPL League ID</span>
                      <a href="{{ route('find') }}" class="text-indigo-400 font-semibold hover:text-indigo-300">
                       See How to get the ID &rarr;
                    </a>
                </div>

                <!-- Step 3: Data Input & Sharing -->
                <div class="rounded-3xl border-2 border-gray-700 bg-card flex flex-col items-center text-center p-6">
                    <div
                        class="w-12 h-12 flex items-center justify-center bg-indigo-600 rounded-full text-white text-2xl font-black mb-4">
                        3</div>
                    <h4 class="text-xl font-bold text-white mb-2">Track & Share</h4>
                    <p class="text-gray-400 mb-4">
                        The App will fetch data from FPL and generate detailed stats every gameweek with a dedicated page for your league
                    </p>
                    <a href="{{route ('public.leagues.list') }}"
                        class="text-green-400 font-semibold hover:text-green-300 blink">
                        View Leagues already enjoying
                    </a>
                </div>
            </div>
        </section>

        <x-adsense />

 <x-back-to-top/>

    </main>
</x-app-layout>