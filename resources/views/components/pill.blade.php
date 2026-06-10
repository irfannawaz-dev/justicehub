@props(['color' => null, 'bg' => null, 'borderColor' => null])

@php
    $styles = '';
    if ($color) $styles .= "color: {$color};";
    if ($bg) $styles .= "background: {$bg}; border-color: {$bg};";
    if ($borderColor) $styles .= "border-color: {$borderColor};";
@endphp

<span class="pill" @if($styles) style="{{ $styles }}" @endif>
    {{ $slot }}
</span>
