<x-app-layout>
    <div class="space-y-6">
        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.teams') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-primary text-gray-200 transition hover:bg-secondary hover:text-white">
                    <i data-lucide="chevron-left" class="h-5 w-5"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-white">{{ $team->name }} — Squad</h1>
                    <p class="text-xs text-gray-400">{{ $team->players_count }} players</p>
                </div>
            </div>
        </div>

        <section class="rounded-2xl border border-gray-700 bg-card p-5">
            <div class="mb-4 flex items-center gap-3">
                <img src="{{ route('img.team', $team->id) }}" alt="{{ $team->short_name }}" class="h-10 w-10 rounded object-contain" loading="lazy" />
                <div>
                    <p class="text-lg font-bold text-white">{{ $team->name }}</p>
                    <p class="text-sm text-gray-400">{{ $team->short_name }} · Code: {{ $team->code ?? '-' }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-gray-200">
                    <thead>
                        <tr class="border-b border-gray-700 text-xs uppercase tracking-wide text-gray-400">
                            <th class="px-3 py-2 text-left">ID</th>
                            <th class="px-3 py-2 text-left">Photo</th>
                            <th class="px-3 py-2 text-left">Player</th>
                            <th class="px-3 py-2 text-right">Pos</th>
                            <th class="px-3 py-2 text-right">Points</th>
                            <th class="px-3 py-2 text-right">Selected %</th>
                            <th class="px-3 py-2 text-right">Form</th>
                            <th class="px-3 py-2 text-right">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($players as $player)
                            <tr class="border-b border-gray-800/80">
                                <td class="px-3 py-2 text-xs text-gray-400">{{ $player->id }}</td>
                                <td class="px-3 py-2">
                                    <img src="{{ route('img.player', $player->id) }}" alt="{{ $player->web_name }}" class="h-8 w-8 rounded object-contain" loading="lazy" />
                                </td>
                                <td class="px-3 py-2">
                                    <p class="font-semibold text-white">{{ $player->web_name }}</p>
                                    <p class="text-xs text-gray-400">{{ $player->first_name }} {{ $player->second_name }}</p>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <span class="rounded-full bg-gray-700 px-2 py-0.5 text-xs font-semibold text-gray-300">
                                        {{ ['', 'GKP', 'DEF', 'MID', 'FWD'][$player->element_type] ?? '?' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-white">{{ $player->total_points }}</td>
                                <td class="px-3 py-2 text-right text-gray-300">{{ number_format((float) $player->selected_by_percent, 1) }}%</td>
                                <td class="px-3 py-2 text-right text-gray-300">{{ number_format((float) $player->form, 2) }}</td>
                                <td class="px-3 py-2 text-right text-gray-300">£{{ number_format($player->now_cost / 10, 1) }}m</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-8 text-center text-gray-400">No players found for this team.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $players->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
