<section class="rounded-2xl border border-gray-700 bg-card p-5" x-data="{ openAward: null }">
    <h2 class="text-lg font-semibold text-white">Awards</h2>

    @if (! empty($awards))
        <div class="mt-3 space-y-2">
            @foreach ($awards as $index => $award)
                @php
                    $awardTitle = is_array($award) ? (string) ($award['title'] ?? 'Award') : (string) $award;
                    $awardReason = is_array($award) ? (string) ($award['reason'] ?? 'No details available.') : 'No details available.';
                    $awardAchievedAt = is_array($award) ? (string) ($award['achieved_at'] ?? 'No date available.') : 'No date available.';
                @endphp

                <button
                    type="button"
                    class="flex w-full items-center justify-between rounded-lg border border-gray-700 bg-card px-3 py-2 text-left transition hover:border-gray-500"
                    @click="openAward = openAward === {{ $index }} ? null : {{ $index }}"
                >
                    <span class="text-sm font-semibold text-accent">{{ $awardTitle }}</span>
                    <span
                        class="text-xs font-semibold text-gray-300"
                        x-text="openAward === {{ $index }} ? 'Hide' : 'Details'"
                    >
                        Details
                    </span>
                </button>

                <div
                    x-cloak
                    x-show="openAward === {{ $index }}"
                    x-transition
                    class="rounded-lg border border-gray-700/80 bg-card/80 px-3 py-2 text-xs text-gray-300"
                >
                    <p><span class="font-semibold text-gray-200">Given for:</span> {{ $awardReason }}</p>
                    <p class="mt-1"><span class="font-semibold text-gray-200">Achieved:</span> {{ $awardAchievedAt }}</p>
                </div>
            @endforeach
        </div>
    @else
        <p class="mt-3 text-sm text-gray-400">No awards unlocked yet.</p>
    @endif
</section>
