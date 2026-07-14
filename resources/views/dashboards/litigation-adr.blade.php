<x-layouts.app>
<div style="padding: 24px 34px 64px; max-width: 1600px; margin: 0 auto;">

    <style>
        @keyframes jh-fade-up { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes jh-count-pop { 0% { transform: scale(0.6); opacity: 0; } 60% { transform: scale(1.05); } 100% { transform: scale(1); opacity: 1; } }
        @keyframes jh-bar-grow { from { width: 0; } }
        @keyframes jh-border-glow { 0% { border-top-color: transparent; } 100% { border-top-color: var(--anim-color); } }
        .jh-anim-card { animation: jh-fade-up 0.6s ease both; }
        .jh-anim-card:nth-child(1) { animation-delay: 0.05s; }
        .jh-anim-card:nth-child(2) { animation-delay: 0.12s; }
        .jh-anim-card:nth-child(3) { animation-delay: 0.19s; }
        .jh-anim-card:nth-child(4) { animation-delay: 0.26s; }
        .jh-anim-num { animation: jh-count-pop 0.7s ease both; animation-delay: 0.3s; }
        .jh-anim-bar { animation: jh-bar-grow 1s ease both; }
        .jh-anim-row { animation: jh-fade-up 0.5s ease both; }

        .jh-scorecard:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
        .jh-scorecard { transition: transform 0.25s ease, box-shadow 0.25s ease; }

        .jh-progress-ring { transition: stroke-dashoffset 1.2s ease; }
    </style>

    {{-- ════════════════════════════════════════════════════════════
        ADR SECTION
        ════════════════════════════════════════════════════════════ --}}
    <div style="margin-bottom: 36px;">
        <div style="margin-bottom: 14px; animation: jh-fade-up 0.5s ease both;">
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">{{ __('dashboard.mediation_dispute_resolution') }}</div>
            <h2 class="serif" style="font-size: 26px; font-weight: 400; letter-spacing: -0.015em; margin: 0; line-height: 1.1;">
                {{ __('dashboard.mediation_scorecard') }} <em style="color: var(--ochre);">{{ __('dashboard.scorecard') }}</em>
            </h2>
            <div style="font-size: 12.5px; color: var(--ink-3); margin-top: 6px;">
                {{ __('dashboard.mediation_pathway_performance') }}
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
            {{-- Mediation Resolution Rate — Radial Gauge --}}
            <div class="card jh-scorecard jh-anim-card" style="padding: 20px 22px; border-top: 3px solid var(--ochre); display: flex; flex-direction: column; gap: 6px; min-height: 160px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div class="label-cap" style="font-size: 9.5px; line-height: 1.4;">{{ __('dashboard.mediation_resolution_rate') }}</div>
                    <x-lucide-heart-handshake style="width: 13px; height: 13px; color: var(--ink-4);" />
                </div>
                <div style="display: flex; align-items: center; gap: 16px; flex: 1;">
                    <div style="width: 80px; height: 80px; position: relative; flex-shrink: 0;">
                        <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                            <circle cx="18" cy="18" r="15.5" fill="none" stroke="#ebe4d2" stroke-width="3"></circle>
                            <circle cx="18" cy="18" r="15.5" fill="none" stroke="var(--ochre)" stroke-width="3"
                                stroke-dasharray="{{ $adrRate * 0.974 }}, 97.4"
                                stroke-linecap="round" class="jh-progress-ring" style="stroke-dashoffset: 97.4; animation: jh-ring-fill 1.5s ease forwards 0.4s;"></circle>
                        </svg>
                        <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;">
                            <span class="serif jh-anim-num" style="font-size: 20px; font-weight: 500;">{{ $adrRate }}%</span>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: var(--ink-3);">{{ __('dashboard.of_completed_via_mediation') }}</div>
                        <div style="font-size: 10px; color: var(--ink-4); margin-top: 4px;">{{ __('dashboard.target_pct', ['pct' => 70]) }}</div>
                    </div>
                </div>
            </div>

            {{-- Active Mediations --}}
            <div class="card jh-scorecard jh-anim-card" style="padding: 20px 22px; border-top: 3px solid var(--ochre); display: flex; flex-direction: column; gap: 10px; min-height: 160px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div class="label-cap" style="font-size: 9.5px; line-height: 1.4;">{{ __('dashboard.active_mediations') }}</div>
                    <x-lucide-gavel style="width: 13px; height: 13px; color: var(--ink-4);" />
                </div>
                <div class="serif jh-anim-num" style="font-size: 44px; font-weight: 400; line-height: 1; letter-spacing: -0.02em;">{{ $adrActive }}</div>
                <div style="margin-top: auto;">
                    <div style="display: flex; gap: 8px; align-items: center; font-size: 11px;">
                        <span style="display: inline-flex; align-items: center; gap: 3px; padding: 2px 7px; background: rgba(184,115,25,0.1); color: var(--ochre); font-weight: 600; border-radius: 10px;">
                            <x-lucide-shield style="width: 10px; height: 10px;" /> {{ $adrGbv }} GBV
                        </span>
                        <span style="color: var(--ink-4);">{{ __('dashboard.across_all_hubs') }}</span>
                    </div>
                </div>
            </div>

            {{-- Avg Days to Resolution --}}
            <div class="card jh-scorecard jh-anim-card" style="padding: 20px 22px; border-top: 3px solid var(--moss); display: flex; flex-direction: column; gap: 10px; min-height: 160px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div class="label-cap" style="font-size: 9.5px; line-height: 1.4;">{{ __('dashboard.avg_days_to_resolution') }}</div>
                    <x-lucide-clock style="width: 13px; height: 13px; color: var(--ink-4);" />
                </div>
                <div class="serif jh-anim-num" style="font-size: 44px; font-weight: 400; line-height: 1; letter-spacing: -0.02em;">{{ $adrAvgDays }}</div>
                <div style="margin-top: auto;">
                    <div style="height: 6px; background: var(--rule-2); border-radius: 3px; overflow: hidden; margin-bottom: 6px;">
                        <div class="jh-anim-bar" style="height: 100%; width: {{ $adrAvgDays > 0 ? min(100, round($adrAvgDays/45*100)) : 0 }}%; background: {{ $adrAvgDays <= 45 ? 'var(--moss)' : 'var(--burgundy)' }}; border-radius: 3px;"></div>
                    </div>
                    @if($adrAvgDays > 0 && $adrAvgDays <= 45)
                    <span style="font-size: 11px; font-weight: 600; color: var(--moss);">{{ __('dashboard.days_under_target', ['count' => 45 - $adrAvgDays]) }}</span>
                    @elseif($adrAvgDays > 45)
                    <span style="font-size: 11px; font-weight: 600; color: var(--burgundy);">{{ __('dashboard.days_over_target', ['count' => $adrAvgDays - 45]) }}</span>
                    @else
                    <span style="font-size: 11px; color: var(--ink-3);">{{ __('dashboard.no_resolved_cases') }}</span>
                    @endif
                    <span style="font-size: 11px; color: var(--ink-3);"> · {{ __('dashboard.target_pct', ['pct' => 45]) }}</span>
                </div>
            </div>

            {{-- Total Mediation Cases --}}
            <div class="card jh-scorecard jh-anim-card" style="padding: 20px 22px; border-top: 3px solid var(--moss); display: flex; flex-direction: column; gap: 10px; min-height: 160px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div class="label-cap" style="font-size: 9.5px; line-height: 1.4;">{{ __('dashboard.total_mediation_cases') }}</div>
                    <x-lucide-activity style="width: 13px; height: 13px; color: var(--ink-4);" />
                </div>
                <div class="serif jh-anim-num" style="font-size: 44px; font-weight: 400; line-height: 1; letter-spacing: -0.02em;">{{ $adrTotal }}</div>
                <div style="margin-top: auto;">
                    <div style="display: flex; gap: 6px; font-size: 11px;">
                        <span style="padding: 2px 7px; background: rgba(74,122,92,0.1); color: var(--moss); font-weight: 600; border-radius: 10px;">{{ $adrSettled }} {{ __('dashboard.settled') }}</span>
                        <span style="padding: 2px 7px; background: var(--rule-2); color: var(--ink-3); border-radius: 10px;">{{ $adrTotal - $adrSettled - $adrActive }} {{ __('dashboard.other') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ADR Breakdown Mini Charts --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 14px;">
            <div class="card jh-anim-card" style="padding: 18px 20px; animation-delay: 0.35s;">
                <div class="label-cap" style="font-size: 9px; margin-bottom: 12px;">{{ __('dashboard.mediation_case_outcome_split') }}</div>
                @php
                    $adrOther = $adrTotal - $adrSettled - $adrActive;
                    $chartAdrSplit = ['labels' => ['Settled','Active','Other'], 'values' => [$adrSettled, $adrActive, max($adrOther,0)], 'colors' => ['#4a7a5c','#b87319','#6b6a65']];
                @endphp
                <div data-chart="serviceMixPie" data-chart-config='{{ json_encode($chartAdrSplit) }}' style="height: 180px;"><canvas></canvas></div>
            </div>
            <div class="card jh-anim-card" style="padding: 18px 20px; animation-delay: 0.4s;">
                <div class="label-cap" style="font-size: 9px; margin-bottom: 12px;">{{ __('dashboard.mediation_performance_vs_target') }}</div>
                @php
                    $chartAdrPerf = ['labels' => ['Resolution Rate','Target'], 'values' => [$adrRate, 70], 'colors' => ['#b87319','#ebe4d2']];
                @endphp
                <div data-chart="resolutionBar" data-chart-config='{{ json_encode($chartAdrPerf) }}' style="height: 180px;"><canvas></canvas></div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
        LITIGATION SECTION
        ════════════════════════════════════════════════════════════ --}}
    <div style="margin-bottom: 36px;">
        <div style="margin-bottom: 14px; animation: jh-fade-up 0.5s ease both; animation-delay: 0.3s;">
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">{{ __('dashboard.court_representation') }}</div>
            <h2 class="serif" style="font-size: 26px; font-weight: 400; letter-spacing: -0.015em; margin: 0; line-height: 1.1;">
                {{ __('dashboard.litigation_scorecard') }} <em style="color: var(--burgundy);">{{ __('dashboard.scorecard') }}</em>
            </h2>
            <div style="font-size: 12.5px; color: var(--ink-3); margin-top: 6px;">
                {{ __('dashboard.court_pathway_performance') }}
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
            {{-- Favourable Outcome Rate — Radial Gauge --}}
            <div class="card jh-scorecard jh-anim-card" style="padding: 20px 22px; border-top: 3px solid var(--burgundy); display: flex; flex-direction: column; gap: 6px; min-height: 160px; animation-delay: 0.35s;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div class="label-cap" style="font-size: 9.5px; line-height: 1.4;">{{ __('dashboard.favourable_outcome_rate') }}</div>
                    <x-lucide-scale style="width: 13px; height: 13px; color: var(--ink-4);" />
                </div>
                <div style="display: flex; align-items: center; gap: 16px; flex: 1;">
                    <div style="width: 80px; height: 80px; position: relative; flex-shrink: 0;">
                        <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                            <circle cx="18" cy="18" r="15.5" fill="none" stroke="#ebe4d2" stroke-width="3"></circle>
                            <circle cx="18" cy="18" r="15.5" fill="none" stroke="var(--burgundy)" stroke-width="3"
                                stroke-dasharray="{{ $litFavRate * 0.974 }}, 97.4"
                                stroke-linecap="round" class="jh-progress-ring" style="stroke-dashoffset: 97.4; animation: jh-ring-fill 1.5s ease forwards 0.6s;"></circle>
                        </svg>
                        <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;">
                            <span class="serif jh-anim-num" style="font-size: 20px; font-weight: 500;">{{ $litFavRate }}%</span>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: var(--ink-3);">{{ __('dashboard.cases_with_favourable_judgment') }}</div>
                        <div style="font-size: 10px; color: var(--ink-4); margin-top: 4px;">{{ __('dashboard.target_pct', ['pct' => 60]) }}</div>
                    </div>
                </div>
            </div>

            {{-- Active Litigation --}}
            <div class="card jh-scorecard jh-anim-card" style="padding: 20px 22px; border-top: 3px solid var(--burgundy); display: flex; flex-direction: column; gap: 10px; min-height: 160px; animation-delay: 0.42s;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div class="label-cap" style="font-size: 9.5px; line-height: 1.4;">{{ __('dashboard.active_litigation') }}</div>
                    <x-lucide-gavel style="width: 13px; height: 13px; color: var(--ink-4);" />
                </div>
                <div class="serif jh-anim-num" style="font-size: 44px; font-weight: 400; line-height: 1; letter-spacing: -0.02em;">{{ $litActive }}</div>
                <div style="margin-top: auto;">
                    <div style="display: flex; gap: 6px; font-size: 11px;">
                        <span style="padding: 2px 7px; background: rgba(138,46,29,0.1); color: var(--burgundy); font-weight: 600; border-radius: 10px;">{{ $litCriminal }} {{ __('dashboard.criminal') }}</span>
                        <span style="padding: 2px 7px; background: rgba(22,48,41,0.08); color: var(--forest); font-weight: 600; border-radius: 10px;">{{ $litCivil }} {{ __('dashboard.civil') }}</span>
                    </div>
                </div>
            </div>

            {{-- Avg Days to Disposal --}}
            <div class="card jh-scorecard jh-anim-card" style="padding: 20px 22px; border-top: 3px solid var(--moss); display: flex; flex-direction: column; gap: 10px; min-height: 160px; animation-delay: 0.49s;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div class="label-cap" style="font-size: 9.5px; line-height: 1.4;">{{ __('dashboard.avg_days_to_disposal') }}</div>
                    <x-lucide-clock style="width: 13px; height: 13px; color: var(--ink-4);" />
                </div>
                <div class="serif jh-anim-num" style="font-size: 44px; font-weight: 400; line-height: 1; letter-spacing: -0.02em;">{{ $litAvgDays }}</div>
                <div style="margin-top: auto;">
                    <div style="height: 6px; background: var(--rule-2); border-radius: 3px; overflow: hidden; margin-bottom: 6px;">
                        <div class="jh-anim-bar" style="height: 100%; width: {{ $litAvgDays > 0 ? min(100, round($litAvgDays/210*100)) : 0 }}%; background: {{ $litAvgDays <= 210 ? 'var(--moss)' : 'var(--burgundy)' }}; border-radius: 3px;"></div>
                    </div>
                    @if($litAvgDays > 0 && $litAvgDays <= 210)
                    <span style="font-size: 11px; font-weight: 600; color: var(--moss);">{{ __('dashboard.days_under_target', ['count' => 210 - $litAvgDays]) }}</span>
                    @elseif($litAvgDays > 210)
                    <span style="font-size: 11px; font-weight: 600; color: var(--burgundy);">{{ __('dashboard.days_over_target', ['count' => $litAvgDays - 210]) }}</span>
                    @else
                    <span style="font-size: 11px; color: var(--ink-3);">{{ __('dashboard.no_resolved_cases') }}</span>
                    @endif
                    <span style="font-size: 11px; color: var(--ink-3);"> · {{ __('dashboard.target_pct', ['pct' => 210]) }}</span>
                </div>
            </div>

            {{-- Total Litigation --}}
            <div class="card jh-scorecard jh-anim-card" style="padding: 20px 22px; border-top: 3px solid var(--moss); display: flex; flex-direction: column; gap: 10px; min-height: 160px; animation-delay: 0.56s;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div class="label-cap" style="font-size: 9.5px; line-height: 1.4;">{{ __('dashboard.total_litigation_cases') }}</div>
                    <x-lucide-briefcase style="width: 13px; height: 13px; color: var(--ink-4);" />
                </div>
                <div class="serif jh-anim-num" style="font-size: 44px; font-weight: 400; line-height: 1; letter-spacing: -0.02em;">{{ $litTotal }}</div>
                <div style="margin-top: auto;">
                    <div style="display: flex; gap: 6px; font-size: 11px;">
                        <span style="padding: 2px 7px; background: rgba(74,122,92,0.1); color: var(--moss); font-weight: 600; border-radius: 10px;">{{ $litFavourable }} {{ __('dashboard.favourable') }}</span>
                        <span style="padding: 2px 7px; background: var(--rule-2); color: var(--ink-3); border-radius: 10px;">{{ $litTotal - $litFavourable - $litActive }} {{ __('dashboard.other') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Litigation Breakdown Mini Charts --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 14px;">
            <div class="card jh-anim-card" style="padding: 18px 20px; animation-delay: 0.6s;">
                <div class="label-cap" style="font-size: 9px; margin-bottom: 12px;">{{ __('dashboard.criminal_vs_civil_split') }}</div>
                @php $chartLitSplit = ['labels' => ['Criminal','Civil'], 'values' => [$litCriminal, $litCivil], 'colors' => ['#8a2e1d','#163029']]; @endphp
                <div data-chart="serviceMixPie" data-chart-config='{{ json_encode($chartLitSplit) }}' style="height: 180px;"><canvas></canvas></div>
            </div>
            <div class="card jh-anim-card" style="padding: 18px 20px; animation-delay: 0.65s;">
                <div class="label-cap" style="font-size: 9px; margin-bottom: 12px;">{{ __('dashboard.litigation_outcome_split') }}</div>
                @php
                    $litOther = $litTotal - $litFavourable - $litActive;
                    $chartLitOutcome = ['labels' => ['Favourable','Active','Other'], 'values' => [$litFavourable, $litActive, max($litOther,0)], 'colors' => ['#4a7a5c','#b87319','#6b6a65']];
                @endphp
                <div data-chart="serviceMixPie" data-chart-config='{{ json_encode($chartLitOutcome) }}' style="height: 180px;"><canvas></canvas></div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
        STAFF WORKLOAD TABLE
        ════════════════════════════════════════════════════════════ --}}
    <div style="margin-bottom: 32px;">
        <div style="margin-bottom: 14px; animation: jh-fade-up 0.5s ease both; animation-delay: 0.5s;">
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">{{ __('dashboard.team_capacity') }}</div>
            <h2 class="serif" style="font-size: 26px; font-weight: 400; letter-spacing: -0.015em; margin: 0; line-height: 1.1;">
                {{ __('dashboard.staff_workload') }}
            </h2>
            <div style="font-size: 12.5px; color: var(--ink-3); margin-top: 6px;">
                {{ __('dashboard.active_case_assignments') }}
            </div>
        </div>

        <div class="card" style="padding: 0; overflow: hidden; animation: jh-fade-up 0.6s ease both; animation-delay: 0.55s;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--rule);">
                        <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">{{ __('dashboard.staff') }}</th>
                        <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">{{ __('dashboard.role') }}</th>
                        <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">{{ __('dashboard.hub') }}</th>
                        <th style="text-align: right; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">{{ __('dashboard.active') }}</th>
                        <th style="text-align: right; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">{{ __('dashboard.capacity') }}</th>
                        <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">{{ __('dashboard.utilisation') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $s)
                    <tr class="jh-anim-row" style="border-bottom: 1px solid var(--rule-2); animation-delay: {{ 0.6 + $loop->index * 0.05 }}s;">
                        <td style="padding: 12px 14px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 30px; height: 30px; background: var(--forest); color: var(--ochre-2); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 500; border-radius: 50%;">
                                    {{ $s['initials'] }}
                                </div>
                                <span style="font-weight: 500;">{{ $s['name'] }}</span>
                            </div>
                        </td>
                        <td style="padding: 12px 14px;">
                            <x-pill>{{ $s['designation'] ?: $s['role'] }}</x-pill>
                        </td>
                        <td style="padding: 12px 14px; font-size: 12px; color: var(--ink-3);">{{ $s['hub'] }}</td>
                        <td style="padding: 12px 14px; text-align: right;" class="mono">{{ $s['active'] }}</td>
                        <td style="padding: 12px 14px; text-align: right; color: var(--ink-3);" class="mono">{{ $s['capacity'] }}</td>
                        <td style="padding: 12px 14px; width: 180px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="flex: 1; height: 6px; background: var(--rule-2); border-radius: 3px; overflow: hidden;">
                                    <div class="jh-anim-bar" style="height: 100%; width: {{ $s['utilization'] }}%; background: {{ $s['utilization'] >= 90 ? 'var(--burgundy)' : ($s['utilization'] >= 70 ? 'var(--ochre)' : 'var(--moss)') }}; border-radius: 3px; animation-delay: {{ 0.7 + $loop->index * 0.05 }}s;"></div>
                                </div>
                                <span class="mono" style="font-size: 11px; font-weight: 500; color: {{ $s['utilization'] >= 90 ? 'var(--burgundy)' : ($s['utilization'] >= 70 ? 'var(--ochre)' : 'var(--ink-2)') }};">{{ $s['utilization'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6"><x-empty-state icon="user-check" :message="__('dashboard.no_staff_records')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    @keyframes jh-ring-fill { to { stroke-dashoffset: 0; } }
</style>
</x-layouts.app>
