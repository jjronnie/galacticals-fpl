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
                        <span class="group relative inline-flex cursor-help">
                            <i data-lucide="info" class="h-3.5 w-3.5 text-gray-500"></i>
                            <span class="pointer-events-none absolute right-0 top-5 z-20 hidden w-56 rounded-lg border border-gray-600 bg-primary px-2 py-1 text-xs text-gray-200 shadow-lg group-hover:block">
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
