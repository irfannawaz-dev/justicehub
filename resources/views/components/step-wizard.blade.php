@props(['steps' => [], 'current' => 1])

<style>
    .wiz-step .wiz-circle {
        width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: var(--paper); border: 1px solid var(--rule); color: var(--ink-3);
        transition: all 150ms;
    }
    .wiz-step .wiz-circle .wiz-icon-done { display: none; }
    .wiz-step .wiz-circle .wiz-icon-default { display: flex; }
    .wiz-step .wiz-label {
        font-size: 11px; font-weight: 500; color: var(--ink-3); letter-spacing: 0.02em;
    }
    .wiz-step.active .wiz-circle {
        background: var(--forest); border-color: var(--forest); color: var(--cream);
    }
    .wiz-step.active .wiz-label { font-weight: 600; color: var(--forest); }
    .wiz-step.done .wiz-circle {
        background: var(--moss); border-color: var(--moss); color: var(--cream);
    }
    .wiz-step.done .wiz-circle .wiz-icon-done { display: flex; }
    .wiz-step.done .wiz-circle .wiz-icon-default { display: none; }
    .wiz-step.done .wiz-label { color: var(--moss); }
</style>

<div class="card" style="padding: 20px 28px; margin-bottom: 22px;">
    <div style="position: relative;">
        {{-- Progress line --}}
        <div style="position: absolute; top: 18px; left: 8%; right: 8%; height: 2px; background: var(--rule); z-index: 0;">
            <div id="wiz-progress-bar" style="height: 100%; width: {{ (($current - 1) / (count($steps) - 1)) * 100 }}%; background: var(--forest); transition: width 200ms;"></div>
        </div>

        <div style="display: grid; grid-template-columns: repeat({{ count($steps) }}, 1fr); position: relative; z-index: 1;">
            @foreach($steps as $index => $step)
                @php
                    $n = $index + 1;
                    $isCurrent = $n === $current;
                    $isComplete = $n < $current;
                @endphp
                <div class="wiz-step {{ $isCurrent ? 'active' : '' }} {{ $isComplete ? 'done' : '' }}"
                     data-wizard-step="{{ $n }}"
                     style="display: flex; flex-direction: column; align-items: center; gap: 7px;">
                    <div class="wiz-circle">
                        <span class="wiz-icon-done"><x-lucide-check-circle-2 style="width: 16px; height: 16px;" /></span>
                        <span class="wiz-icon-default">
                            @if(isset($step['icon']))
                                <x-dynamic-component :component="'lucide-' . $step['icon']" style="width: 15px; height: 15px;" />
                            @else
                                <span style="font-size: 13px; font-weight: 500;">{{ $n }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="wiz-label">{{ $step['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
