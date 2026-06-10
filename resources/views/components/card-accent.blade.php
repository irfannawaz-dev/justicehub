@props(['color' => 'var(--forest)', 'title' => null, 'padding' => '20px 24px', 'style' => ''])

<div class="card-accent" style="border-left-color: {{ $color }}; padding: {{ $padding }}; {{ $style }}">
    @if($title)
        <div class="label-cap" style="font-size: 9.5px; margin-bottom: 14px;">{{ $title }}</div>
    @endif
    {{ $slot }}
</div>
