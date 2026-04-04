<x-app-layout>
    <div class="space-y-6" x-data="{ selectedEvent: '{{ $currentEvent ?? '' }}' }">
        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <x-page-title title="Fixtures" />

            <div class="flex items-center gap-3">
                <label for="event-select" class="text-sm font-medium text-gray-400">Gameweek:</label>
                <select
                    id="event-select"
                    x-model="selectedEvent"
                    @change="window.location.href = '{{ route('admin.data.fixtures') }}?event=' + selectedEvent"
                    class="rounded-lg border border-gray-600 bg-gray-800 px-3 py-2 text-sm font-semibold text-white focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                >
                    @foreach($events as $ev)
                        <option value="{{ $ev }}" {{ ($currentEvent == $ev) ? 'selected' : '' }}>
                            Gameweek {{ $ev }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-gray-600 bg-card p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Fixtures</p>
                <p class="mt-2 text-2xl font-bold text-white">{{ number_format($totalFixtures) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-600 bg-card p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Finished</p>
                <p class="mt-2 text-2xl font-bold text-green-400">{{ number_format($finishedFixtures) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-600 bg-card p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Live</p>
                <p class="mt-2 text-2xl font-bold text-red-400">{{ number_format($liveFixtures) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-600 bg-card p-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Upcoming</p>
                <p class="mt-2 text-2xl font-bold text-gray-300">{{ number_format($upcomingFixtures) }}</p>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">
                    @if($currentEvent)
                        Gameweek {{ $currentEvent }} Fixtures
                    @else
                        All Fixtures
                    @endif
                </h2>
                <span class="text-xs text-gray-500">{{ $fixtures->total() }} fixtures</span>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm text-gray-200">
                    <thead>
                        <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2 text-left">Date</th>
                            <th class="px-3 py-2 text-left">Home</th>
                            <th class="px-3 py-2 text-center">Score</th>
                            <th class="px-3 py-2 text-left">Away</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-left">Min</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fixtures as $fixture)
                            <tr class="border-b border-gray-800/80">
                                <td class="px-3 py-3 text-xs text-gray-300">
                                    {{ $fixture->kickoff_time ? $fixture->kickoff_time->format('d M, H:i') : 'TBC' }}
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-2">
                                        @if($fixture->homeTeam)
                                            <img src="{{ route('img.team', $fixture->homeTeam->id) }}" alt="{{ $fixture->homeTeam->short_name }}" class="h-7 w-7 rounded object-contain" loading="lazy" />
                                        @endif
                                        <span class="font-semibold text-white">
                                            {{ $fixture->homeTeam?->name ?? 'TBD' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($fixture->isFinished())
                                        <span class="font-bold text-white">
                                            {{ $fixture->team_h_score ?? '-' }} - {{ $fixture->team_a_score ?? '-' }}
                                        </span>
                                    @elseif($fixture->isLive())
                                        <span class="font-bold text-red-400">
                                            {{ $fixture->team_h_score ?? 0 }} - {{ $fixture->team_a_score ?? 0 }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-500">
                                            {{ $fixture->kickoff_time ? $fixture->kickoff_time->format('H:i') : '-' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-2">
                                        @if($fixture->awayTeam)
                                            <img src="{{ route('img.team', $fixture->awayTeam->id) }}" alt="{{ $fixture->awayTeam->short_name }}" class="h-7 w-7 rounded object-contain" loading="lazy" />
                                        @endif
                                        <span class="font-semibold text-white">
                                            {{ $fixture->awayTeam?->name ?? 'TBD' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    @if($fixture->isFinished())
                                        <span class="rounded-full bg-green-900/40 px-2 py-1 text-xs font-semibold text-green-300">FT</span>
                                    @elseif($fixture->isLive())
                                        <span class="rounded-full bg-red-900/40 px-2 py-1 text-xs font-semibold text-red-300 animate-pulse">{{ $fixture->minutes }}'</span>
                                    @else
                                        <span class="rounded-full bg-gray-700 px-2 py-1 text-xs font-semibold text-gray-300">Upcoming</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-xs text-gray-400">
                                    {{ $fixture->minutes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-8 text-center text-gray-400">
                                    No fixtures found for this gameweek. Run the fixtures sync to populate data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $fixtures->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
