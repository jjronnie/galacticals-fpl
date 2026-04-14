<div class="divide-y divide-gray-800/60">
    @forelse($groupedByDate as $date => $dateFixtures)
        <div class="px-3 py-3 sm:px-6 sm:py-4">
            <p class="mb-2 text-center text-[10px] font-semibold uppercase tracking-widest text-gray-500 sm:mb-3 sm:text-xs">{{ $date }}</p>

            <div class="space-y-1.5 sm:space-y-2">
                @foreach($dateFixtures as $fixture)
                    <div class="flex items-center justify-between gap-1 py-1.5 sm:gap-3 sm:py-2">
                        <div class="flex flex-1 min-w-0 items-center justify-end gap-1.5 sm:gap-3">
                            <div class="text-right min-w-0">
                                <span class="block truncate text-[11px] font-semibold text-white sm:text-sm">
                                    {{ $fixture->homeTeam?->name ?? 'TBD' }}
                                </span>
                            </div>
                            @if($fixture->homeTeam)
                                <img src="{{ route('img.team', $fixture->homeTeam->id) }}" alt="{{ $fixture->homeTeam->short_name }}" class="h-5 w-5 shrink-0 rounded object-contain sm:h-7 sm:w-7" loading="lazy" onerror="this.style.display='none'" />
                            @endif
                        </div>

                        <div class="flex min-w-[48px] flex-col items-center justify-center sm:min-w-[80px]">
                            @if($fixture->isFinished())
                                <span class="text-xs font-extrabold tracking-tight text-white sm:text-lg">
                                    {{ $fixture->team_h_score ?? '-' }} - {{ $fixture->team_a_score ?? '-' }}
                                </span>
                                <span class="text-[8px] font-bold uppercase tracking-wider text-gray-500 sm:text-[10px]">FT</span>
                            @elseif($fixture->isLive())
                                <span class="text-xs font-extrabold tracking-tight text-red-400 animate-pulse sm:text-lg">
                                    {{ $fixture->team_h_score ?? 0 }} - {{ $fixture->team_a_score ?? 0 }}
                                </span>
                                <span class="text-[8px] font-bold uppercase tracking-wider text-red-400 sm:text-[10px] live-fixture-minutes" data-fixture-id="{{ $fixture->fpl_fixture_id }}" data-start-minutes="{{ $fixture->minutes }}">{{ $fixture->minutes }}'</span>
                            @else
                                <span class="text-[11px] font-bold text-white sm:text-base">
                                    {{ $fixture->kickoff_time ? $fixture->kickoff_time->format('H:i') : 'TBC' }}
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-1 min-w-0 items-center justify-start gap-1.5 sm:gap-3">
                            @if($fixture->awayTeam)
                                <img src="{{ route('img.team', $fixture->awayTeam->id) }}" alt="{{ $fixture->awayTeam->short_name }}" class="h-5 w-5 shrink-0 rounded object-contain sm:h-7 sm:w-7" loading="lazy" onerror="this.style.display='none'" />
                            @endif
                            <div class="text-left min-w-0">
                                <span class="block truncate text-[11px] font-semibold text-white sm:text-sm">
                                    {{ $fixture->awayTeam?->name ?? 'TBD' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="py-12 text-center">
            <p class="text-sm font-semibold text-gray-400">No fixtures for Gameweek {{ $currentEvent ?? 'TBD' }}</p>
            <p class="mt-1 text-xs text-gray-600">Fixtures will appear once synced from the FPL API.</p>
        </div>
    @endforelse
</div>