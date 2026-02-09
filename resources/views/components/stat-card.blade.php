@props([
    'title' => '',
    'sub_title' => '',
    'value' => '',
    'icon' => '',
    'color' => 'blue',
    'tooltip' => null,
])

<div {{ $attributes->merge(['class' => 'bg-card rounded-2xl shadow-sm  p-6 border border-gray-600']) }}>
    <div class="flex items-center">
        @if ($icon)
            <div class="flex-shrink-0">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary">
                    <i data-lucide="{{ $icon }}" class="h-8 w-8 text-accent"></i>
                </div>
            </div>
        @endif
        <div class="ml-4">
            @if ($value)
                <p class="text-xl font-bold text-white">{{ $value }}</p>
            @endif

            @if ($title)
                <div class="flex items-center gap-2">
                    <h3 class="text-1xl font-semibold text-gray-400">{{ $title }}</h3>
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
            @endif

            @if ($sub_title)
                <p class="text-sm text-muted text-gray-600">{{ $sub_title }}</p>
            @endif
        </div>
    </div>
</div>
