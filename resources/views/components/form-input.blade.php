@props([
    'name',
    'label' => null,
    'type' => 'text',
    'required' => false,
    'value' => null,
    'placeholder' => '',
    'hint' => null,
    'mono' => false,
])

@php
    $inputValue = old($name, $value);
@endphp

<div>
    @if($label)
    <label for="{{ $name }}" style="display: block; margin-bottom: 6px; font-size: 10px; font-weight: 500; letter-spacing: 0.06em; text-transform: uppercase; color: var(--ink-3);">
        {{ $label }}
        @if($required)<span style="color: var(--burgundy);"> *</span>@endif
    </label>
    @endif

    @if($type === 'textarea')
        <textarea
            id="{{ $name }}"
            name="{{ $name }}"
            class="inp {{ $mono ? 'mono' : '' }}"
            style="min-height: 88px; resize: vertical;"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes }}
        >{{ $inputValue }}</textarea>
    @else
        <input
            type="{{ $type }}"
            id="{{ $name }}"
            name="{{ $name }}"
            class="inp {{ $mono ? 'mono' : '' }}"
            value="{{ $inputValue }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes }}
        >
    @endif

    @if($hint)
    <div style="font-size: 10.5px; color: var(--ink-4); margin-top: 4px;">{{ $hint }}</div>
    @endif

    @if($errors ?? false)
    @error($name)
    <div style="font-size: 11px; color: var(--burgundy); margin-top: 4px;">{{ $message }}</div>
    @enderror
    @endif
</div>
