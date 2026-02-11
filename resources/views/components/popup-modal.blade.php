<div x-data="{ open: false }" class="inline-block" x-cloak>
    @isset($trigger)
        <div @click="open = true">
            {{ $trigger }}
        </div>
    @else
        <button
            type="button"
            @click="open = true"
            class="inline-flex items-center gap-2 rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white hover:bg-secondary"
        >
            @if ($buttonText !== '')
                <span>{{ $buttonText }}</span>
            @endif
            @if ($buttonIcon !== '')
                <i data-lucide="{{ $buttonIcon }}" class="h-4 w-4"></i>
            @endif
        </button>
    @endisset

    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm"
        @click="open = false"
        @keydown.escape.window="open = false"
    ></div>

    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div
            @click.stop
            x-transition:enter="transform transition-all duration-200 ease-out"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transform transition-all duration-150 ease-in"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-3xl overflow-hidden rounded-2xl border border-gray-700 bg-card shadow-2xl"
        >
            <div class="flex items-center justify-between border-b border-gray-700 px-5 py-4">
                <h2 class="text-base font-semibold text-white">{{ $title }}</h2>
                <button
                    type="button"
                    @click="open = false"
                    class="rounded-lg bg-primary p-2 text-gray-300 hover:bg-secondary hover:text-white"
                    aria-label="Close modal"
                >
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>

            <div class="max-h-[75vh] overflow-y-auto px-5 py-4 text-sm text-gray-200">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
