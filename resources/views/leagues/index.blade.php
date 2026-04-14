<x-app-layout>
    <main class="mx-auto max-w-7xl space-y-8">
        <x-adsense />

        <section class="rounded-2xl border border-gray-700 bg-card p-6">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Leagues on {{ config('app.name') }}</h1>
                    <p class="mt-2 text-sm text-gray-300">{{ $total ?? 0 }} league(s) available</p>
                </div>

                @guest
                    <a href="{{ route('register') }}" target="_blank" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500">
                        Create account for your league
                    </a>
                @endguest
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($leagues as $league)
                <article class="rounded-2xl border border-gray-700 bg-card p-5">
                    <h2 class="text-lg font-semibold text-white">{{ $league->name }}</h2>
                    <p class="mt-1 text-xs text-gray-400">Classic League</p>
                    <a
                        href="{{ route('public.leagues.show', ['slug' => $league->slug]) }}"
                        class="mt-4 inline-flex rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-gray-200 hover:bg-secondary"
                    >
                        View Overview
                    </a>
                </article>
            @empty
                <div class="rounded-2xl border border-gray-700 bg-card p-8 text-center text-gray-400 md:col-span-2 xl:col-span-3">
                    No leagues have been created yet.
                </div>
            @endforelse
        </section>

        <x-adsense />
        <x-back-to-top />
    </main>
</x-app-layout>
