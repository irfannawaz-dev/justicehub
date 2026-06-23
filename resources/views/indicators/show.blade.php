<x-layouts.app>
@php
    $ragColors = ['green' => 'var(--moss)', 'amber' => 'var(--ochre)', 'red' => 'var(--burgundy)'];
    $ragLabels = ['green' => 'On Track', 'amber' => 'Attention', 'red' => 'Off Track'];
    $ragTints  = ['green' => 'var(--moss-tint)', 'amber' => 'var(--ochre-tint)', 'red' => 'var(--burgundy-tint)'];
    $rc   = $ragColors[$indicator->rag]  ?? 'var(--ink-3)';
    $rl   = $ragLabels[$indicator->rag]  ?? 'Unknown';
    $rt   = $ragTints[$indicator->rag]   ?? 'var(--paper)';
    $actualDisplay = $indicator->unit === '%' ? round($indicator->actual) . '%' : number_format($indicator->actual);
    $targetDisplay = $indicator->unit === '%' ? round($indicator->target) . '%' : number_format($indicator->target);
    $levelColors = [
        'Goal'      => 'var(--forest)', 'Outcome 1' => 'var(--ochre)', 'Outcome 2' => 'var(--ochre)',
        'Outcome 3' => 'var(--ochre)',  'Output 1'  => 'var(--ink-2)', 'Output 2'  => 'var(--ink-2)',
        'Output 3'  => 'var(--ink-2)',  'Output 4'  => 'var(--ink-2)',
    ];
    $lc = $levelColors[$indicator->level] ?? 'var(--ink-3)';
@endphp

<div style="padding: 28px 36px 64px; max-width: 1400px; margin: 0 auto;">

    {{-- Breadcrumb --}}
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 22px; font-size: 12px; color: var(--ink-3);">
        <a href="{{ route('indicators.index') }}" style="color: var(--ink-3); text-decoration: none; display: flex; align-items: center; gap: 4px;">
            <x-lucide-arrow-left style="width:12px;height:12px;" /> Results Framework
        </a>
        <span style="color: var(--rule);">/</span>
        <span class="mono" style="color: var(--ink-2);">{{ $indicator->code }}</span>
    </div>

    {{-- ═══ Hero Header ═══ --}}
    <div style="display: grid; grid-template-columns: 1fr auto; gap: 28px; align-items: start; margin-bottom: 28px; padding-bottom: 24px; border-bottom: 1px solid var(--rule);">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <span class="mono" style="font-size: 13px; font-weight: 700; color: {{ $lc }};">{{ $indicator->code }}</span>
                <span style="font-size: 10px; padding: 2px 8px; background: {{ $rt }}; color: {{ $rc }}; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase;">{{ $rl }}</span>
                @if($indicator->priority === 'P0')
                <span style="font-size: 10px; padding: 2px 8px; background: var(--burgundy-tint); color: var(--burgundy); font-weight: 700; letter-spacing: 0.06em;">P0 PRIORITY</span>
                @endif
            </div>
            <h1 class="serif" style="font-size: 30px; font-weight: 400; letter-spacing: -0.015em; line-height: 1.22; margin: 0 0 10px 0; max-width: 820px;">
                {{ $indicator->name }}
            </h1>
            <div style="display: flex; align-items: center; gap: 18px; font-size: 12px; color: var(--ink-3);">
                <span style="display: flex; align-items: center; gap: 5px;">
                    <x-lucide-layers style="width:11px;height:11px;" /> {{ $indicator->level }}
                </span>
                <span style="display: flex; align-items: center; gap: 5px;">
                    <x-lucide-refresh-cw style="width:11px;height:11px;" /> {{ ucfirst($indicator->cadence ?? 'monthly') }}
                </span>
                @if($indicator->source_line)
                <span style="display: flex; align-items: center; gap: 5px;">
                    <x-lucide-database style="width:11px;height:11px;" /> {{ $indicator->source_line }}
                </span>
                @endif
            </div>
        </div>

        {{-- RAG value block --}}
        <div class="card" style="padding: 20px 28px; text-align: center; border-top: 4px solid {{ $rc }}; min-width: 160px;">
            <div class="label-cap" style="font-size: 9px; margin-bottom: 6px; color: {{ $rc }};">Current value</div>
            <div class="serif" style="font-size: 44px; font-weight: 500; line-height: 1; color: {{ $rc }};">{{ $actualDisplay }}</div>
            <div style="font-size: 11px; color: var(--ink-3); margin-top: 6px;">of {{ $targetDisplay }} target</div>
            <div style="height: 5px; background: var(--rule); border-radius: 3px; margin-top: 12px; overflow: hidden;">
                <div style="height: 100%; width: {{ $indicator->pct }}%; background: {{ $rc }}; border-radius: 3px; transition: width 0.6s;"></div>
            </div>
            <div style="font-size: 10.5px; color: {{ $rc }}; margin-top: 5px; font-weight: 600;">{{ $indicator->pct }}% achieved</div>
        </div>
    </div>

    {{-- ═══ Two-column layout ═══ --}}
    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">

        {{-- Left column --}}
        <div style="display: flex; flex-direction: column; gap: 22px;">

            {{-- Trend chart --}}
            <div class="card" style="padding: 20px 22px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 14px;">
                    <div>
                        <div class="label-cap" style="font-size: 9px; margin-bottom: 4px;">Performance trend</div>
                        <div style="font-size: 12px; color: var(--ink-3);">
                            {{ count($indicator->trend_labels) }} data points · dashed line = target
                        </div>
                    </div>
                    <span id="trendChange" class="mono" style="font-size: 11px; color: var(--ink-3);"></span>
                </div>
                <div style="height: 220px; position: relative;">
                    <canvas id="indTrendChart"></canvas>
                </div>
            </div>

            {{-- Snapshots table --}}
            @if($snapshots->count())
            <div class="card" style="padding: 20px 22px;">
                <div class="label-cap" style="font-size: 9px; margin-bottom: 14px;">Historical snapshots</div>
                <table style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--rule);">
                            <th style="text-align: left; padding: 0 0 8px 0; color: var(--ink-3); font-weight: 600; letter-spacing: 0.04em; font-size: 10px; text-transform: uppercase;">Period</th>
                            <th style="text-align: right; padding: 0 0 8px 0; color: var(--ink-3); font-weight: 600; letter-spacing: 0.04em; font-size: 10px; text-transform: uppercase;">Value</th>
                            <th style="text-align: right; padding: 0 0 8px 0; color: var(--ink-3); font-weight: 600; letter-spacing: 0.04em; font-size: 10px; text-transform: uppercase;">vs Target</th>
                            <th style="text-align: right; padding: 0 0 8px 0; color: var(--ink-3); font-weight: 600; letter-spacing: 0.04em; font-size: 10px; text-transform: uppercase;">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($snapshots->sortByDesc('month_iso') as $snap)
                        @php
                            $snapPct = $indicator->target > 0 ? round(($snap->value / $indicator->target) * 100) : 100;
                            $snapRag = $snapPct >= 90 ? 'var(--moss)' : ($snapPct >= 70 ? 'var(--ochre)' : 'var(--burgundy)');
                        @endphp
                        <tr style="border-bottom: 1px solid var(--rule-2);">
                            <td style="padding: 9px 0; font-weight: 500;">{{ $snap->month_label ?? $snap->month_iso }}</td>
                            <td style="padding: 9px 0; text-align: right; font-family: monospace; font-size: 13px;">
                                {{ $indicator->unit === '%' ? round($snap->value) . '%' : number_format($snap->value) }}
                            </td>
                            <td style="padding: 9px 0; text-align: right;">
                                <span style="font-size: 11px; color: {{ $snapRag }}; font-weight: 600;">{{ $snapPct }}%</span>
                            </td>
                            <td style="padding: 9px 0; text-align: right; font-size: 11px; color: var(--ink-3);">
                                {{ $snap->notes ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Linked evidence --}}
            @if($indicator->evidence->count())
            <div class="card" style="padding: 20px 22px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <div class="label-cap" style="font-size: 9px;">Linked evidence ({{ $indicator->evidence->count() }})</div>
                    <a href="{{ route('evidence.index') }}" style="font-size: 11px; color: var(--forest); text-decoration: none;">View all evidence →</a>
                </div>
                @foreach($indicator->evidence as $ev)
                <div style="display: flex; align-items: flex-start; gap: 12px; padding: 10px 0; border-bottom: 1px solid var(--rule-2);">
                    <div style="width: 28px; height: 28px; background: {{ $ev->verified ? 'var(--moss-tint)' : 'var(--paper)' }}; color: {{ $ev->verified ? 'var(--moss)' : 'var(--ink-3)' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        @if($ev->verified)
                            <x-lucide-badge-check style="width:14px;height:14px;" />
                        @else
                            <x-lucide-file-text style="width:14px;height:14px;" />
                        @endif
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 13px; font-weight: 500; line-height: 1.3;">{{ $ev->title }}</div>
                        <div style="font-size: 11px; color: var(--ink-3); margin-top: 2px;">
                            {{ $ev->issuer }} ·
                            <span class="mono" style="font-size: 10px;">{{ $ev->evidence_uid }}</span>
                            @if($ev->verified)
                            · <span style="color: var(--moss); font-weight: 600;">Verified</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

        </div>

        {{-- Right column --}}
        <div style="display: flex; flex-direction: column; gap: 16px;">

            {{-- Methodology --}}
            <div class="card" style="padding: 18px 20px;">
                <div class="label-cap" style="font-size: 9px; margin-bottom: 10px;">Methodology</div>
                <p style="font-size: 13px; color: var(--ink-2); line-height: 1.6; margin: 0;">
                    {{ $indicator->meta['methodology'] ?? $indicator->name }}
                </p>
            </div>

            {{-- Quick stats --}}
            <div class="card" style="padding: 18px 20px;">
                <div class="label-cap" style="font-size: 9px; margin-bottom: 12px;">Indicator details</div>
                <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                    @foreach([
                        ['Code', $indicator->code],
                        ['Level', $indicator->level],
                        ['Priority', $indicator->priority ?? 'P1'],
                        ['Cadence', ucfirst($indicator->cadence ?? 'monthly')],
                        ['Unit', $indicator->unit ?: '—'],
                        ['Type', $indicator->type ?? '—'],
                        ['Snapshots', $snapshots->count() . ' recorded'],
                        ['Evidence', $indicator->evidence->count() . ' linked'],
                    ] as [$label, $val])
                    <tr style="border-bottom: 1px solid var(--rule-2);">
                        <td style="padding: 7px 0; color: var(--ink-3); font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em; width: 40%;">{{ $label }}</td>
                        <td style="padding: 7px 0; font-weight: 500;">{{ $val }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>

            {{-- Siblings at same level --}}
            @if($siblings->count())
            <div class="card" style="padding: 18px 20px;">
                <div class="label-cap" style="font-size: 9px; margin-bottom: 12px;">Other {{ $indicator->level }} indicators</div>
                @foreach($siblings as $sib)
                @php
                    $sibRag = ['green' => 'var(--moss)', 'amber' => 'var(--ochre)', 'red' => 'var(--burgundy)'][$sib->ragStatus()] ?? 'var(--ink-3)';
                @endphp
                <a href="{{ route('indicators.show', $sib) }}" style="display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px solid var(--rule-2); text-decoration: none; color: inherit;">
                    <span style="width: 6px; height: 6px; background: {{ $sibRag }}; border-radius: 50%; flex-shrink: 0;"></span>
                    <span class="mono" style="font-size: 10px; color: var(--ink-3); flex-shrink: 0; width: 36px;">{{ $sib->code }}</span>
                    <span style="font-size: 12px; line-height: 1.3; color: var(--ink-2);">{{ Str::limit($sib->name, 48) }}</span>
                </a>
                @endforeach
            </div>
            @endif

        </div>
    </div>

</div>

<script>
(function () {
    var labels = @json($indicator->trend_labels);
    var values = @json($indicator->trend_values);
    var rc     = '{{ $rc }}';
    var target = {{ $indicator->target }};

    var barColors = labels.map(function (_, i) {
        return i === labels.length - 1 ? rc : 'rgba(0,0,0,0.12)';
    });

    var trendChange = values.length >= 2 ? (values[values.length - 1] - values[0]) : 0;
    var sign = trendChange >= 0 ? '+' : '';
    var el = document.getElementById('trendChange');
    if (el) el.textContent = sign + Math.round(trendChange) + ' since ' + (labels[0] || '');

    new Chart(document.getElementById('indTrendChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{ data: values, backgroundColor: barColors, borderRadius: 2 }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                annotation: { annotations: {
                    targetLine: {
                        type: 'line', yMin: target, yMax: target,
                        borderColor: rc, borderWidth: 1, borderDash: [4, 3],
                        label: {
                            display: true,
                            content: 'TARGET {{ $targetDisplay }}',
                            position: 'end', font: { size: 9 },
                            color: rc, backgroundColor: 'transparent'
                        }
                    }
                }}
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { beginAtZero: true, ticks: { font: { size: 10 }, maxTicksLimit: 5 } }
            }
        }
    });
})();
</script>
</x-layouts.app>
