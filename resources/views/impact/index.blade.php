<x-layouts.app>

<div style="padding: 24px 34px 64px; max-width: 1600px; margin: 0 auto;">

    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 22px;">
        <div>
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">Reporting & Impact</div>
            <h1 class="serif" style="font-size: 34px; font-weight: 400; letter-spacing: -0.02em; margin: 0;">
                Impact Reports
            </h1>
            <p style="margin: 6px 0 0 0; font-size: 13px; color: var(--ink-3);">
                Programme performance · Data as of April 2026
            </p>
        </div>
    </div>

    {{-- ═══ Programme Summary KPIs ═══ --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 16px;">
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--forest);">
            <div class="label-cap" style="font-size: 9px;">Total Cases</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px;">{{ number_format($metrics['total_cases']) }}</div>
            <div style="font-size: 11px; color: var(--ink-3);">{{ $metrics['active_cases'] }} active · {{ $metrics['resolved_cases'] }} resolved</div>
        </div>
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--moss);">
            <div class="label-cap" style="font-size: 9px;">SLA Met</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px; color: {{ $metrics['sla_met_pct'] >= 90 ? 'var(--moss)' : ($metrics['sla_met_pct'] >= 70 ? 'var(--ochre)' : 'var(--burgundy)') }};">{{ $metrics['sla_met_pct'] }}<span style="font-size: 16px;">%</span></div>
        </div>
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--ochre);">
            <div class="label-cap" style="font-size: 9px;">Underserved Clients</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px;">{{ $metrics['underserved_pct'] }}<span style="font-size: 16px;">%</span></div>
            <div style="font-size: 11px; color: var(--ink-3);">Target: 60%+</div>
        </div>
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--burgundy);">
            <div class="label-cap" style="font-size: 9px;">Outreach Participants</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px;">{{ number_format($metrics['outreach_participants']) }}</div>
            <div style="font-size: 11px; color: var(--ink-3);">{{ $metrics['outreach_sessions'] }} sessions</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 22px;">
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--forest);">
            <div class="label-cap" style="font-size: 9px;">Avg Feedback Score</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px; color: var(--forest);">{{ $metrics['avg_feedback'] }}<span style="font-size: 16px; color: var(--ink-3);">/5</span></div>
        </div>
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--moss);">
            <div class="label-cap" style="font-size: 9px;">Evidence Verified</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px;">{{ $metrics['evidence_verified'] }}<span style="font-size: 16px; color: var(--ink-3);">/{{ $metrics['evidence_total'] }}</span></div>
        </div>
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--ochre);">
            <div class="label-cap" style="font-size: 9px;">Staff Compliance</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px;">{{ $metrics['staff_compliance_pct'] }}<span style="font-size: 16px;">%</span></div>
            <div style="font-size: 11px; color: var(--ink-3);">{{ $metrics['staff_compliant'] }}/{{ $metrics['staff_total'] }} compliant</div>
        </div>
        <div class="card" style="padding: 16px 18px;">
            <div class="label-cap" style="font-size: 9px;">Indicators On Track</div>
            <div style="display: flex; gap: 8px; align-items: flex-end; margin-top: 6px;">
                <div style="text-align: center;">
                    <div class="serif" style="font-size: 24px; color: var(--moss);">{{ $ragCounts['green'] }}</div>
                    <div style="font-size: 9px; color: var(--moss); font-weight: 600;">Green</div>
                </div>
                <div style="text-align: center;">
                    <div class="serif" style="font-size: 24px; color: var(--ochre);">{{ $ragCounts['amber'] }}</div>
                    <div style="font-size: 9px; color: var(--ochre); font-weight: 600;">Amber</div>
                </div>
                <div style="text-align: center;">
                    <div class="serif" style="font-size: 24px; color: var(--burgundy);">{{ $ragCounts['red'] }}</div>
                    <div style="font-size: 9px; color: var(--burgundy); font-weight: 600;">Red</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Two-column: P0 Indicators + Export Wizard ═══ --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">

        {{-- P0 Indicators --}}
        <div class="card" style="padding: 18px;">
            <div style="font-size: 11px; font-weight: 600; color: var(--ink-3); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 12px;">
                Priority P0 Indicators
            </div>
            @foreach($p0Indicators as $ind)
            @php
                $ragColor = match($ind->rag) { 'green' => 'var(--moss)', 'amber' => 'var(--ochre)', 'red' => 'var(--burgundy)', default => 'var(--ink-3)' };
                $ragBg    = match($ind->rag) { 'green' => 'rgba(74,122,92,0.1)', 'amber' => 'rgba(184,115,25,0.12)', 'red' => 'rgba(139,30,30,0.1)', default => 'var(--surface-2)' };
            @endphp
            <div style="display: flex; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid var(--rule-2);">
                <span class="mono" style="font-size: 11px; font-weight: 700; color: var(--forest); min-width: 44px;">{{ $ind->code }}</span>
                <div style="flex: 1; font-size: 12px; color: var(--ink-2);">{{ Str::limit($ind->name, 52) }}</div>
                <div style="text-align: right; min-width: 80px;">
                    <div class="mono" style="font-size: 12px; font-weight: 700; color: {{ $ragColor }};">{{ number_format((float)$ind->actual, 0) }}{{ $ind->unit === '%' ? '%' : '' }}</div>
                    <div style="font-size: 10px; color: var(--ink-4);">/ {{ number_format((float)$ind->target, 0) }}</div>
                </div>
                <span style="padding: 2px 7px; border-radius: 10px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; background: {{ $ragBg }}; color: {{ $ragColor }};">
                    {{ strtoupper($ind->rag) }}
                </span>
            </div>
            @endforeach
            <div style="margin-top: 12px; text-align: right;">
                <a href="{{ route('indicators.index') }}" style="font-size: 11px; color: var(--forest); text-decoration: none; font-weight: 500;">
                    View all {{ $metrics['indicators_total'] }} indicators →
                </a>
            </div>
        </div>

        {{-- Export Wizard --}}
        <div class="card" style="padding: 18px;">
            <div style="font-size: 11px; font-weight: 600; color: var(--ink-3); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 14px;">
                Generate Report
            </div>
            <form method="POST" action="{{ route('impact.export') }}" x-data="{ template: 'quarterly' }">
                @csrf
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 11px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;">Report Template</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        @foreach([
                            ['quarterly',  'Quarterly Review',  'Q1–Q4 progress update'],
                            ['annual',     'Annual Report',     'Full-year summary'],
                            ['donor',      'Donor Report',      'Formatted for funders'],
                            ['me-update',  'M&E Update',        'Indicator performance only'],
                        ] as [$val, $lbl, $desc])
                        <label style="cursor: pointer;">
                            <input type="radio" name="template" value="{{ $val }}" x-model="template" style="display: none;">
                            <div :style="template === '{{ $val }}' ? 'border-color: var(--forest); background: rgba(74,122,92,0.05);' : 'border-color: var(--rule);'"
                                 style="padding: 10px 12px; border: 2px solid; border-radius: 8px; transition: all 0.15s; cursor: pointer;">
                                <div style="font-size: 12px; font-weight: 600; color: var(--ink-1);">{{ $lbl }}</div>
                                <div style="font-size: 10px; color: var(--ink-3); margin-top: 2px;">{{ $desc }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 600; color: var(--ink-2); margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.05em;">Period</label>
                        <select name="period" style="width: 100%; padding: 8px 10px; border: 1px solid var(--rule); border-radius: 6px; font-size: 13px; background: var(--surface); color: var(--ink-1);">
                            <option value="Q1-2026">Q1 2026 (Jan–Mar)</option>
                            <option value="Q2-2026">Q2 2026 (Apr–Jun)</option>
                            <option value="FY-2025-26">FY 2025–26</option>
                            <option value="FY-2024-25">FY 2024–25</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 600; color: var(--ink-2); margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.05em;">Scope</label>
                        <select name="scope" style="width: 100%; padding: 8px 10px; border: 1px solid var(--rule); border-radius: 6px; font-size: 13px; background: var(--surface); color: var(--ink-1);">
                            <option value="all">All Hubs (Programme)</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 10px;">
                    <x-lucide-download style="width: 13px; height: 13px;" /> Download PDF Report
                </button>
            </form>

            <div style="margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--rule-2);">
                <div style="font-size: 10px; color: var(--ink-4); text-transform: uppercase; letter-spacing: 0.06em; font-weight: 600; margin-bottom: 8px;">Quick Links</div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <a href="{{ route('indicators.index') }}" style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--forest); text-decoration: none; padding: 4px 0;">
                        <x-lucide-bar-chart-2 style="width:12px;height:12px;" /> Indicators Registry
                    </a>
                    <a href="{{ route('evidence.index') }}" style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--forest); text-decoration: none; padding: 4px 0;">
                        <x-lucide-file-check style="width:12px;height:12px;" /> Evidence Register
                    </a>
                    <a href="{{ route('feedback.index') }}" style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--forest); text-decoration: none; padding: 4px 0;">
                        <x-lucide-message-square style="width:12px;height:12px;" /> Client Feedback
                    </a>
                    <a href="{{ route('learning.index') }}" style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--forest); text-decoration: none; padding: 4px 0;">
                        <x-lucide-lightbulb style="width:12px;height:12px;" /> Learning &amp; VfM
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

</x-layouts.app>
