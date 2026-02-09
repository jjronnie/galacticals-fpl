@props([
    'message' => 'This profile is verified because they confirmed ownership of the team.',
])

<span x-data="{ open: false }" class="inline-flex">
    <button
        type="button"
        class="inline-flex items-center gap-1 rounded-full border border-sky-400/60 bg-sky-500/20 px-1 py-1 text-xs font-semibold text-sky-200 hover:bg-sky-500/30"
        @click="open = true"
        :aria-expanded="open.toString()"
        aria-label="Show verification details"
    >
        <i data-lucide="badge-check" class="h-4 w-4"></i>
        
    </button>

    <div
        x-show="open"
        x-transition
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
        @click.self="open = false"
        @keydown.escape.window="open = false"
    >
        <div class="w-full max-w-md rounded-xl border border-sky-500/50 bg-primary px-5 py-4 text-center shadow-2xl">
            <p class="text-sm text-sky-100">{{ $message }}</p>
            <button
                type="button"
                class="mt-4 rounded-lg bg-sky-600 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-500"
                @click="open = false"
            >
                Close
            </button>
        </div>
    </div>
</span>
