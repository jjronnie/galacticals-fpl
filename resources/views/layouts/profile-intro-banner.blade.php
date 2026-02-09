@php
    $shouldShowProfileIntro = ! auth()->check() || ! auth()->user()->hasClaimedProfile();
@endphp

@if ($shouldShowProfileIntro)
    <section class="mb-6 rounded-2xl border border-cyan-700/50 bg-cyan-900/20 p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wide text-cyan-300">Introducing Individual Profiles</h2>
                <p class="mt-1 text-sm text-cyan-100/90">
                    Claim your FPL team profile to unlock personal stats, charts, awards, and a shareable public page.
                </p>
            </div>

            <div>
                @auth
                    <a href="{{ route('profile.search') }}" class="inline-flex rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-primary hover:bg-cyan-300">
                        Claim Yours Now
                    </a>
                @else
                    <a href="{{ route('register') }}" class="inline-flex rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-primary hover:bg-cyan-300">
                        Claim Yours Now
                    </a>
                @endauth
            </div>
        </div>
    </section>
@endif
