@props([
    'title' => '',
    'sub_title' => '',
    'value' => '',
    'icon' => '',
    'color'=>'blue'
])

<div {{ $attributes->merge(['class' => 'bg-card rounded-2xl shadow-sm  p-6 border border-gray-600']) }}>
    <div class="flex items-center">
          @if ($icon)
        <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-primary  rounded-xl  flex items-center justify-center">
                <i data-lucide="{{ $icon }}" class="w-8 h-8 text-accent "></i>
            </div>

             
        </div>
        @endif
        <div class="ml-4">
                  {{-- Only display if value is provided --}}
            @if ($value)
                <p class="text-xl font-bold text-white">{{ $value }}</p>
            @endif

            {{-- Only display if title is provided --}}
            @if ($title)
                <h3 class="text-1xl font-semibold text-gray-400">{{ $title }}</h3>
                
            @endif
      
            {{-- Only display if sub_title is provided --}}
            @if ($sub_title)
                <p class="text-sm text-muted text-gray-600">{{ $sub_title }}</p>
            @endif
        </div>
    </div>


</div>
