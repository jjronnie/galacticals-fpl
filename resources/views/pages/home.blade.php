<x-app-layout>
    <main class="mx-auto max-w-6xl space-y-10 p-4 sm:p-6">
        <x-adsense />

        <section class="rounded-3xl border border-gray-700 bg-card p-8 text-center">
            <h1 class="text-4xl font-extrabold text-white sm:text-5xl">
                Follow Your FPL League Story, Week by Week
            </h1>
            <p class="mx-auto mt-4 max-w-3xl text-base text-gray-300 sm:text-lg">
                This app turns your Fantasy Premier League mini-league into a fun story.
                See who is rising, who is falling, who keeps winning gameweeks, and which managers are making smart choices.
            </p>

            <div class="mt-6 flex flex-wrap justify-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-green-600 px-5 py-2 text-sm font-semibold text-white hover:bg-green-500">
                        Open Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="rounded-lg bg-green-600 px-5 py-2 text-sm font-semibold text-white hover:bg-green-500">
                        Create Free Account
                    </a>
                @endauth

                <a href="{{ route('public.leagues.list') }}" class="rounded-lg border border-accent px-5 py-2 text-sm font-semibold text-accent hover:bg-accent hover:text-primary">
                    Explore Leagues
                </a>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-gray-700 bg-card p-5">
                <h2 class="text-lg font-semibold text-white">League Summary</h2>
                <p class="mt-2 text-sm text-gray-300">Get a clear view of leaders, biggest wins, lowest scores, and rivalry moments.</p>
            </article>

            <article class="rounded-2xl border border-gray-700 bg-card p-5">
                <h2 class="text-lg font-semibold text-white">Gameweek Tables</h2>
                <p class="mt-2 text-sm text-gray-300">Open any gameweek and see who performed best, who struggled, and the full standings.</p>
            </article>

            <article class="rounded-2xl border border-gray-700 bg-card p-5">
                <h2 class="text-lg font-semibold text-white">Personal Profiles</h2>
                <p class="mt-2 text-sm text-gray-300">Claim your team once and get your own dashboard with points, transfers, chips, and trends.</p>
            </article>

            <article class="rounded-2xl border border-gray-700 bg-card p-5">
                <h2 class="text-lg font-semibold text-white">Shareable Pages</h2>
                <p class="mt-2 text-sm text-gray-300">Share league pages and profile pages with simple links and short codes.</p>
            </article>
        </section>

        <x-adsense />

        <section id="join-steps" class="rounded-3xl border border-gray-700 bg-card p-8">
            <h2 class="text-2xl font-bold text-white">How It Works</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl bg-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-accent">Step 1</p>
                    <h3 class="mt-2 text-lg font-semibold text-white">Create an Account</h3>
                    <p class="mt-2 text-sm text-gray-300">Sign up and confirm your email to unlock your dashboard.</p>
                </div>

                <div class="rounded-2xl bg-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-accent">Step 2</p>
                    <h3 class="mt-2 text-lg font-semibold text-white">Add Your League</h3>
                    <p class="mt-2 text-sm text-gray-300">Paste your classic league ID and let the app import standings.</p>
                    <a href="{{ route('find') }}" class="mt-3 inline-flex text-xs font-semibold text-accent hover:underline">How to find league ID</a>
                </div>

                <div class="rounded-2xl bg-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-accent">Step 3</p>
                    <h3 class="mt-2 text-lg font-semibold text-white">Claim Your Profile</h3>
                    <p class="mt-2 text-sm text-gray-300">Search your FPL team and claim it for personal weekly analytics.</p>
                </div>
            </div>
        </section>

        <x-adsense />

        <section class="rounded-3xl border border-gray-700 bg-card p-8">
            <h2 class="text-2xl font-bold text-white">What You Can Track</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl bg-card p-4 text-sm text-gray-200">Longest top streak in your league</div>
                <div class="rounded-xl bg-card p-4 text-sm text-gray-200">Most-owned and most-captained players per gameweek</div>
                <div class="rounded-xl bg-card p-4 text-sm text-gray-200">Transfer trends and chip usage</div>
                <div class="rounded-xl bg-card p-4 text-sm text-gray-200">Bench points, captaincy impact, and team value changes</div>
            </div>
        </section>

        <x-adsense />

        <x-back-to-top />
    </main>
</x-app-layout>
