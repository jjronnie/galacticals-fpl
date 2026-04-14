<x-app-layout>
    <main class="mx-auto max-w-6xl">
        <x-adsense />

        <section class="py-8 text-center sm:py-12">
            <p class="text-xs font-semibold uppercase tracking-widest text-accent">Premier League Fantasy Tracker</p>
            <h1 class="mt-2 text-3xl font-extrabold leading-tight text-white sm:text-4xl">
                Your League Story, Week by Week
            </h1>
            <p class="mx-auto mt-3 max-w-lg text-sm text-gray-400 sm:text-base">
                Track every gameweek, see who's rising and falling, and discover which managers are making the smartest moves.
            </p>

            <div class="mt-6 flex flex-wrap justify-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-500">
                        Open Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="rounded-lg bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-500">
                        Create Free Account
                    </a>
                @endauth

                <a href="{{ route('public.leagues.list') }}" class="rounded-lg border border-gray-600 px-5 py-2.5 text-sm font-semibold text-gray-300 transition hover:border-accent hover:text-accent">
                    Explore Leagues
                </a>

                <a href="{{ route('fixtures') }}" class="rounded-lg border border-gray-600 px-5 py-2.5 text-sm font-semibold text-gray-300 transition hover:border-accent hover:text-accent">
                    View Fixtures
                </a>
            </div>
        </section>

        <section class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <article class="group relative overflow-hidden rounded-2xl border border-gray-700 bg-card p-6 transition hover:border-gray-600">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-accent/5 blur-xl transition group-hover:bg-accent/10"></div>
                <div class="relative">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-accent/10">
                        <svg class="h-5 w-5 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">League Summary</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-400">See leaders, biggest wins, lowest scores, and rivalry moments at a glance.</p>
                </div>
            </article>

            <article class="group relative overflow-hidden rounded-2xl border border-gray-700 bg-card p-6 transition hover:border-gray-600">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-green-600/5 blur-xl transition group-hover:bg-green-600/10"></div>
                <div class="relative">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-green-600/10">
                        <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Gameweek Tables</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-400">Open any gameweek and see who performed best and who struggled.</p>
                </div>
            </article>

            <article class="group relative overflow-hidden rounded-2xl border border-gray-700 bg-card p-6 transition hover:border-gray-600">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-purple-600/5 blur-xl transition group-hover:bg-purple-600/10"></div>
                <div class="relative">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-purple-600/10">
                        <svg class="h-5 w-5 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Personal Profiles</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-400">Claim your team for personal analytics with points, transfers, chips, and trends.</p>
                </div>
            </article>

            <article class="group relative overflow-hidden rounded-2xl border border-gray-700 bg-card p-6 transition hover:border-gray-600">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-yellow-600/5 blur-xl transition group-hover:bg-yellow-600/10"></div>
                <div class="relative">
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-yellow-600/10">
                        <svg class="h-5 w-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Shareable Pages</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-400">Share league and profile pages with simple links and short codes.</p>
                </div>
            </article>
        </section>

        <x-adsense />

        <section class="mt-8 rounded-3xl border border-gray-700 bg-card p-8">
            <h2 class="text-xl font-bold text-white">How It Works</h2>
            <div class="mt-6 grid gap-6 md:grid-cols-3">
                <div class="relative rounded-2xl border border-gray-700/50 bg-gray-800/30 p-6">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent/10 text-sm font-extrabold text-accent">1</span>
                    <h3 class="mt-4 text-base font-bold text-white">Create an Account</h3>
                    <p class="mt-2 text-sm text-gray-400">Sign up and confirm your email to unlock your personal dashboard.</p>
                </div>

                <div class="relative rounded-2xl border border-gray-700/50 bg-gray-800/30 p-6">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent/10 text-sm font-extrabold text-accent">2</span>
                    <h3 class="mt-4 text-base font-bold text-white">Add Your League</h3>
                    <p class="mt-2 text-sm text-gray-400">Paste your classic league ID and let the app import all standings.</p>
                    <a href="{{ route('find') }}" class="mt-3 inline-flex text-xs font-semibold text-accent hover:underline">How to find league ID</a>
                </div>

                <div class="relative rounded-2xl border border-gray-700/50 bg-gray-800/30 p-6">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent/10 text-sm font-extrabold text-accent">3</span>
                    <h3 class="mt-4 text-base font-bold text-white">Claim Your Profile</h3>
                    <p class="mt-2 text-sm text-gray-400">Search your FPL team and claim it for weekly personal analytics.</p>
                </div>
            </div>
        </section>

        <x-adsense />

        <section class="mt-8 rounded-3xl border border-gray-700 bg-card p-8">
            <h2 class="text-xl font-bold text-white">What You Can Track</h2>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <div class="flex items-start gap-3 rounded-xl border border-gray-700/50 bg-gray-800/30 p-4">
                    <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-green-600/20">
                        <svg class="h-3.5 w-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-300">Longest top streak in your league</span>
                </div>
                <div class="flex items-start gap-3 rounded-xl border border-gray-700/50 bg-gray-800/30 p-4">
                    <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-green-600/20">
                        <svg class="h-3.5 w-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-300">Most-owned and most-captained players per gameweek</span>
                </div>
                <div class="flex items-start gap-3 rounded-xl border border-gray-700/50 bg-gray-800/30 p-4">
                    <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-green-600/20">
                        <svg class="h-3.5 w-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-300">Transfer trends and chip usage</span>
                </div>
                <div class="flex items-start gap-3 rounded-xl border border-gray-700/50 bg-gray-800/30 p-4">
                    <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-green-600/20">
                        <svg class="h-3.5 w-3.5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-300">Bench points, captaincy impact, and team value changes</span>
                </div>
            </div>
        </section>

        <x-adsense />

        <x-back-to-top />
    </main>
</x-app-layout>