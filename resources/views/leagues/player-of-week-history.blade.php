<x-app-layout>
    <main class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('dashboard') }}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-primary text-gray-200 transition hover:bg-secondary hover:text-white"
                        aria-label="Back to dashboard"
                    >
                        <i data-lucide="chevron-left" class="h-5 w-5"></i>
                    </a>
                    <div>
                        <h1 class="text-xl font-bold text-white">Player of the Week History</h1>
                        <p class="text-xs text-gray-400">{{ $league->name }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($playerOfWeekHistory as $row)
                    <article class="rounded-xl border border-gray-700 bg-primary px-4 py-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <svg viewBox="0 0 64 64" class="h-10 w-10 shrink-0">
                                    <path
                                        d="M23 8h18l5 7 8 3-5 13-8-3v26H23V28l-8 3-5-13 8-3 5-7z"
                                        fill="{{ $row['team_color'] }}"
                                    />
                                </svg>
                                <div class="min-w-0">
                                    <p class="truncate text-base font-semibold text-white">{{ $row['web_name'] }}</p>
                                    <p class="truncate text-sm text-gray-400">{{ $row['team_name'] ?? ($row['team_short_name'] ?? 'Unknown') }}</p>
                                </div>
                            </div>

                            <div class="shrink-0 text-right">
                                <p class="text-xs font-semibold text-gray-300">GW{{ $row['gameweek'] }}</p>
                                <p class="mt-1 rounded-md bg-accent/20 px-3 py-1 text-sm font-semibold text-accent">{{ $row['points'] }} pts</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="rounded-xl border border-gray-700 bg-primary px-4 py-6 text-center text-sm text-gray-400">
                        No player of the week history available yet.
                    </p>
                @endforelse
            </div>
        </section>
    </main>
</x-app-layout>
