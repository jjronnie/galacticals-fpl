@props([
    'title' => '',
    'color' => 'blue',
    'tooltip' => null,
])

<div class="rounded-lg border-2 border-gray-700 bg-card p-4">
    <div class="mb-1 flex items-center gap-2">
        <p class="text-sm font-bold uppercase text-{{ $color }}-400">
            {{ $title }}
        </p>
        @if ($tooltip)
            <span
                class="relative inline-flex"
                x-data="{ open: false, placement: 'center', setPlacement() { this.placement = 'center'; this.$nextTick(() => { const tooltip = this.$refs.tooltip; if (!tooltip) { return; } const rect = tooltip.getBoundingClientRect(); const gutter = 8; if (rect.left < gutter) { this.placement = 'left'; return; } if (rect.right > window.innerWidth - gutter) { this.placement = 'right'; return; } this.placement = 'center'; }); } }"
                @mouseleave="open = false"
                @click.away="open = false"
            >
                <button
                    type="button"
                    class="inline-flex cursor-help text-gray-500 hover:text-gray-300"
                    @mouseenter="open = true; setPlacement()"
                    @focus="open = true; setPlacement()"
                    @blur="open = false"
                    @click.prevent="open = !open; if (open) { setPlacement(); }"
                    aria-label="More information"
                >
                    <i data-lucide="info" class="h-3.5 w-3.5"></i>
                </button>
                <span
                    x-ref="tooltip"
                    x-cloak
                    x-show="open"
                    x-transition.opacity.duration.150ms
                    :class="{
                        'left-1/2 -translate-x-1/2': placement === 'center',
                        'left-0': placement === 'left',
                        'right-0': placement === 'right'
                    }"
                    class="pointer-events-none absolute top-5 z-40 w-56 max-w-[calc(100vw-1rem)] rounded-lg border border-gray-600 bg-primary px-2 py-1 text-xs text-gray-200 shadow-lg"
                >
                    {{ $tooltip }}
                </span>
            </span>
        @endif
    </div>

    <div class="text-sm leading-relaxed text-gray-200">
        {{ $slot }}
    </div>
</div>
