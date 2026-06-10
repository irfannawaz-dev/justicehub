@props(['color' => 'var(--burgundy)', 'textColor' => 'var(--cream)'])

<span class="mono" style="font-size: 10px; padding: 1px 7px; background: {{ $color }}; color: {{ $textColor }}; font-weight: 600; letter-spacing: 0.02em;">
    {{ $slot }}
</span>
