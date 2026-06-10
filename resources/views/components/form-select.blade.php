@props([
    'name',
    'label' => null,
    'required' => false,
    'options' => [],
    'lookupGroup' => null,
    'lookupParent' => null,
    'selected' => null,
    'placeholder' => 'Select...',
    'hint' => null,
])

@php
    if ($lookupGroup) {
        $options = \App\Models\Lookup::selectOptions($lookupGroup, $lookupParent);
    }
    $selectedValue = old($name, $selected);
@endphp

<div>
    @if($label)
    <label for="{{ $name }}" style="display: block; margin-bottom: 6px; font-size: 10px; font-weight: 500; letter-spacing: 0.06em; text-transform: uppercase; color: var(--ink-3);">
        {{ $label }}
        @if($required)<span style="color: var(--burgundy);"> *</span>@endif
    </label>
    @endif

    <select
        id="{{ $name }}"
        name="{{ $name }}"
        class="inp"
        {{ $required ? 'required' : '' }}
        {{ $attributes }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $value => $optLabel)
            <option value="{{ $value }}" {{ $selectedValue == $value ? 'selected' : '' }}>
                {{ $optLabel }}
            </option>
        @endforeach
    </select>

    @if($hint)
    <div style="font-size: 10.5px; color: var(--ink-4); margin-top: 4px;">{{ $hint }}</div>
    @endif

    @if($errors ?? false)
    @error($name)
    <div style="font-size: 11px; color: var(--burgundy); margin-top: 4px;">{{ $message }}</div>
    @enderror
    @endif
</div>
