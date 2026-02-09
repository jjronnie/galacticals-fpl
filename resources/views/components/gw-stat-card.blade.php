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
            <span class="group relative inline-flex cursor-help">
                <i data-lucide="info" class="h-3.5 w-3.5 text-gray-500"></i>
                <span class="pointer-events-none absolute right-0 top-5 z-20 hidden w-56 rounded-lg border border-gray-600 bg-primary px-2 py-1 text-xs text-gray-200 shadow-lg group-hover:block">
                    {{ $tooltip }}
                </span>
            </span>
        @endif
    </div>

    <div class="text-sm leading-relaxed text-gray-200">
        {{ $slot }}
    </div>
</div>
