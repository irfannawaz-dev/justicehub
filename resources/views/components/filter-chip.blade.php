@props(['label', 'value' => 'all', 'active' => false, 'count' => null])

<button
    {{ $attributes }}
    class="pill"
    style="cursor: pointer; font-family: inherit; {{ $active ? 'background: var(--forest); color: var(--cream); border-color: var(--forest);' : '' }}"
>
    {{ $label }}
    @if($count !== null)
        <span class="mono" style="font-size: 10px; opacity: 0.7;">{{ $count }}</span>
    @endif
</button>
