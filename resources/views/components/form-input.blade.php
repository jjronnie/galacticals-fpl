@props([
'label' => '',
'name' => '',
'type' => 'text',
'placeholder' => '',
'required' => false,
])

<div class="mb-4">
    <label for="{{ $name }}" class="label">
        {{ $label }}
        @if($required)
        <span class="text-red-600">*</span>
        @endif
    </label>

    <input type="{{ $type }}" placeholder="{{ $placeholder }}" name="{{ $name }}" id="{{ $name }}" {{ $required ? 'required' : '' }} {{
        $attributes->merge(['class' => 'input']) }}
    />
</div>