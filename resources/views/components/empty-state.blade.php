@props([
    'message' => 'No data found.',
    'icon' => 'inbox', // Default icon for general use
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-12 text-center border border-dashed border-gray-300 rounded-lg bg-gray-50']) }}>

    {{-- Icon --}}
    <div class="p-4 mb-4 bg-white border border-gray-200 rounded-full shadow-sm text-gray-400">
        {{-- You can customize the icon size/look here --}}
        <i data-lucide="{{ $icon }}" class="w-8 h-8"></i>
    </div>

    {{-- Message --}}
    <p class="text-lg font-medium text-gray-600">
        {{ $message }}
    </p>


</div>