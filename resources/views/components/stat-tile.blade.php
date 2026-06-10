@props(['label', 'value', 'color' => 'var(--ink)', 'icon' => null])

<div style="text-align: center;">
    @if($icon)
    <div style="margin-bottom: 6px;">
        <x-dynamic-component :component="'lucide-' . $icon" style="width: 16px; height: 16px; color: {{ $color }}; margin: 0 auto;" />
    </div>
    @endif
    <div class="serif" style="font-size: 24px; font-weight: 400; color: {{ $color }}; letter-spacing: -0.01em; line-height: 1;">
        {{ $value }}
    </div>
    <div class="label-cap" style="font-size: 9px; margin-top: 6px;">{{ $label }}</div>
</div>
