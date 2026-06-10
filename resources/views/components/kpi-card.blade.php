@props([
    'label',
    'value',
    'unit' => '',
    'trend' => null,
    'trendUp' => true,
    'color' => 'var(--forest)',
    'iconBg' => 'rgba(22,48,41,0.06)',
    'icon' => null,
    'sparkId' => null,
])

<div class="card md-kpi-card">
    <div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <div class="label-cap" style="font-size: 9.5px;">{{ $label }}</div>
            @if($icon)
            <div style="width: 32px; height: 32px; background: {{ $iconBg }}; display: flex; align-items: center; justify-content: center;">
                <x-dynamic-component :component="'lucide-' . $icon" style="width: 15px; height: 15px; color: {{ $color }};" />
            </div>
            @endif
        </div>
        <div style="display: flex; align-items: baseline; gap: 6px;">
            <span class="serif" style="font-size: 32px; font-weight: 400; letter-spacing: -0.02em; color: var(--ink); line-height: 1;">
                {{ $value }}
            </span>
            @if($unit)
            <span style="font-size: 13px; color: var(--ink-3);">{{ $unit }}</span>
            @endif
        </div>
        @if($trend !== null)
        <div style="display: flex; align-items: center; gap: 4px; margin-top: 8px; font-size: 12px; color: {{ $trendUp ? 'var(--moss)' : 'var(--burgundy)' }};">
            @if($trendUp)
                <x-lucide-trending-up style="width: 13px; height: 13px;" />
            @else
                <x-lucide-trending-down style="width: 13px; height: 13px;" />
            @endif
            <span>{{ $trend }}</span>
        </div>
        @endif
    </div>
    @if($sparkId)
    <div style="margin-top: 12px;">
        <canvas id="{{ $sparkId }}" height="50"></canvas>
    </div>
    @endif
</div>
