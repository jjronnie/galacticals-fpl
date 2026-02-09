@props([
    'message' => 'This profile is verified because they confirmed ownership of the team.',
])

<span x-data="{ open: false }" class="relative inline-flex">
    <button
        type="button"
        class="inline-flex items-center gap-1 rounded-full border border-sky-400/60 bg-sky-500/20 px-2.5 py-1 text-xs font-semibold text-sky-200 hover:bg-sky-500/30"
        @click="open = !open"
        @click.away="open = false"
    >
        <i data-lucide="badge-check" class="h-3.5 w-3.5"></i>
        Verified
    </button>

    <span
        x-show="open"
        x-transition
        x-cloak
        class="absolute left-0 top-full z-20 mt-2 w-72 rounded-lg border border-sky-500/40 bg-primary px-3 py-2 text-center text-xs text-sky-100 shadow-xl"
    >
        {{ $message }}
    </span>
</span>
