@props(['photo', 'name', 'size' => 24])

@php
$photoSize = "w-{$size} h-{$size}";
@endphp



@if ($photo)
@if (Str::startsWith($photo, ['http://', 'https://']))
<img src="{{ $photo }}" alt="{{ $name }}" class="{{ $photoSize }} rounded-full object-cover">
@else
<img src="{{ asset('storage/' . $photo) }}" alt="{{ $name }}" class="{{ $photoSize }} rounded-full object-cover">
@endif
@else
<div class="{{ $photoSize }} rounded-full bg-gray-200 flex items-center justify-center">
    <i data-lucide="circle-user-round" class="text-gray-400"></i>
</div>
@endif