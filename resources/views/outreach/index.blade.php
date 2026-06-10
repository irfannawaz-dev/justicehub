<x-layouts.app>
@php $canWrite = auth()->user()->canWrite(); @endphp

<div style="padding: 28px 36px 60px; max-width: 1640px; margin: 0 auto;">

    {{-- ═══ Editorial Header ═══ --}}
    <div style="margin-bottom: 28px; padding-bottom: 22px; border-bottom: 1px solid var(--rule);">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 32px;">
            <div style="flex: 1; max-width: 820px;">
                <div class="label-cap" style="margin-bottom: 8px; font-size: 9.5px;">Service Delivery &middot; Outreach &amp; Legal Awareness</div>
                <h1 class="serif" style="font-size: 42px; font-weight: 400; letter-spacing: -0.018em; line-height: 1.02; margin: 0;">
                    Outreach &amp; <em style="color: var(--forest);">Awareness</em> Activities
                </h1>
                <div style="font-size: 13.5px; color: var(--ink-2); margin-top: 12px; line-height: 1.6; max-width: 660px;">
                    Community sessions, legal literacy workshops, and paralegal-led dialogues &mdash; tracked against outputs
                    <span class="mono" style="font-size: 12px;">O2.2</span>,
                    <span class="mono" style="font-size: 12px;">O2.3</span>, and
                    <span class="mono" style="font-size: 12px;">OP3.1</span>.
                </div>
            </div>
            <div style="display: flex; gap: 10px; flex-shrink: 0;">
                @if($canWrite)
                <button class="btn-ghost" onclick="jhOpenModal('outreach-pulse')">
                    <x-lucide-plus style="width:13px;height:13px;" /> Log pulse response
                </button>
                <button class="btn-primary" onclick="jhOpenModal('outreach-new')">
                    <x-lucide-plus style="width:14px;height:14px;" /> Log New Activity
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ 5 KPI Cards ═══ --}}
    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; margin-bottom: 30px;">

        {{-- O2.2 Sessions --}}
        <div class="card" style="padding: 18px 20px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 10px;">
                <div class="label-cap" style="font-size: 9.5px;">O2.2 Sessions</div>
                <x-lucide-megaphone style="width:15px;height:15px;color:var(--ink-4);" />
            </div>
            <div class="serif" style="font-size: 40px; font-weight: 400; line-height: 1; margin-bottom: 8px;">{{ $totals['sessions'] }}</div>
            <div style="font-size: 11px; color: var(--ink-3); line-height: 1.5;">
                This quarter
            </div>
        </div>

        {{-- O2.3 Participants Reached --}}
        <div class="card" style="padding: 18px 20px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 10px;">
                <div class="label-cap" style="font-size: 9.5px;">O2.3 Participants Reached</div>
                <x-lucide-users style="width:15px;height:15px;color:var(--ink-4);" />
            </div>
            <div class="serif" style="font-size: 40px; font-weight: 400; line-height: 1; margin-bottom: 8px;">{{ number_format($totals['participants']) }}</div>
            <div style="font-size: 11px; color: var(--ink-3); line-height: 1.5;">
                {{ $totals['female'] }} female &middot; {{ $femalePct }}% of total
            </div>
        </div>

        {{-- O2.1 Understanding Gain --}}
        @php $onTarget = $understandingGainPct >= 80; @endphp
        <div class="card" style="padding: 18px 20px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 10px;">
                <div class="label-cap" style="font-size: 9.5px;">O2.1 Understanding Gain</div>
                <x-lucide-trending-up style="width:15px;height:15px;color:var(--ink-4);" />
            </div>
            <div class="serif" style="font-size: 40px; font-weight: 400; line-height: 1; margin-bottom: 8px; color: {{ $onTarget ? 'var(--forest)' : 'var(--burgundy)' }};">
                {{ $understandingGainPct }}%
            </div>
            <div style="font-size: 11px; color: var(--ink-3); line-height: 1.5; display: flex; align-items: center; gap: 5px;">
                <span style="font-size: 10px; font-weight: 600; padding: 1px 6px; background: {{ $onTarget ? 'var(--moss-tint)' : 'var(--burgundy-tint)' }}; color: {{ $onTarget ? 'var(--moss)' : 'var(--burgundy)' }}; letter-spacing: 0.04em;">
                    {{ $onTarget ? 'On target' : 'Below target' }}
                </span>
                <span>from {{ $pulseRespondents }} pulse responses &middot; target 80%</span>
            </div>
        </div>

        {{-- OP3.1 Paralegal-Led --}}
        <div class="card" style="padding: 18px 20px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 10px;">
                <div class="label-cap" style="font-size: 9.5px;">OP3.1 Paralegal-Led</div>
                <x-lucide-award style="width:15px;height:15px;color:var(--ink-4);" />
            </div>
            <div class="serif" style="font-size: 40px; font-weight: 400; line-height: 1; margin-bottom: 8px;">{{ $paralegalLed }}</div>
            <div style="font-size: 11px; color: var(--ink-3); line-height: 1.5;">
                {{ $paralegalPct }}% of all activities
            </div>
        </div>

        {{-- Marginalised Reached --}}
        <div class="card" style="padding: 18px 20px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 10px;">
                <div class="label-cap" style="font-size: 9.5px;">Marginalised Reached</div>
                <x-lucide-circle-dot style="width:15px;height:15px;color:var(--ink-4);" />
            </div>
            <div class="serif" style="font-size: 40px; font-weight: 400; line-height: 1; margin-bottom: 8px;">{{ $marginalised }}</div>
            <div style="font-size: 11px; color: var(--ink-3); line-height: 1.5;">
                {{ $totals['minority'] }} minority &middot; {{ $totals['disability'] }} PwD
            </div>
        </div>
    </div>

    {{-- ═══ Hub Filter ═══ --}}
    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 18px; flex-wrap: wrap;">
        <span class="label-cap" style="font-size: 9.5px; color: var(--ink-3); white-space: nowrap;">Filter by Hub</span>
        <button onclick="orHubFilter('all', this)" data-or-hub="all"
                style="padding: 6px 12px; font-size: 11.5px; font-weight: 600; font-family: inherit; cursor: pointer; border: 1px solid var(--rule); letter-spacing: 0.02em; transition: all 120ms; background: var(--forest); color: var(--cream); border-color: var(--forest);">
            All hubs
        </button>
        @foreach($hubs as $hub)
        <button onclick="orHubFilter('{{ $hub->id }}', this)" data-or-hub="{{ $hub->id }}"
                style="padding: 6px 12px; font-size: 11.5px; font-weight: 500; font-family: inherit; cursor: pointer; border: 1px solid var(--rule); letter-spacing: 0.02em; transition: all 120ms; background: transparent; color: var(--ink-2);">
            {{ $hub->id }}
        </button>
        @endforeach
    </div>

    {{-- ═══ Activity Table ═══ --}}
    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 40px;">

        {{-- Table header --}}
        <div style="display: grid; grid-template-columns: 160px 1fr 110px 80px 80px 80px 60px 150px 200px; border-bottom: 2px solid var(--rule);">
            @foreach(['Activity · Date','Topic & Location','Hub','Total','Female','Minority','PwD','Pulse · O2.1','Facilitator · Flags'] as $col)
            <div style="padding: 10px 14px; font-size: 9.5px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink-3); background: var(--paper);">
                {{ $col }}
            </div>
            @endforeach
        </div>

        {{-- Rows --}}
        @forelse($activities as $a)
        @php
            $typeColor = match($a->type) {
                'Legal Literacy'    => 'var(--forest)',
                'Paralegal Outreach'=> 'var(--ochre)',
                'Awareness'         => 'var(--moss)',
                default             => 'var(--ink-3)',
            };
            // Per-activity pulse
            $actRespondents = $a->pulseSurveys->sum('respondent_count') ?: $a->pulseSurveys->count();
            $actGained      = $a->pulseSurveys->filter(fn($ps) => (float)$ps->post_score > (float)$ps->pre_score)->sum('respondent_count');
            $actGained      = $actGained ?: $a->pulseSurveys->filter(fn($ps) => (float)$ps->post_score > (float)$ps->pre_score)->count();
            $actGainPct     = $actRespondents > 0 ? round(($actGained / $actRespondents) * 100) : null;
            $gainColor      = !$actGainPct ? 'var(--ink-4)' : ($actGainPct >= 80 ? 'var(--forest)' : ($actGainPct >= 60 ? 'var(--ochre)' : 'var(--burgundy)'));
        @endphp
        <div data-or-hub="{{ $a->hub_id }}"
             style="display: grid; grid-template-columns: 160px 1fr 110px 80px 80px 80px 60px 150px 200px; align-items: center;
                    border-bottom: 1px solid var(--rule-2); transition: background 100ms;"
             onmouseenter="this.style.background='var(--paper)'" onmouseleave="this.style.background=''">

            {{-- ACTIVITY · DATE --}}
            <div style="padding: 13px 14px;">
                <div class="mono" style="font-size: 11px; color: var(--ink-3); margin-bottom: 2px;">{{ $a->outreach_uid }}</div>
                <div style="font-size: 13px; font-weight: 600; color: {{ $typeColor }}; margin-bottom: 2px;">{{ $a->type }}</div>
                <div class="mono" style="font-size: 10.5px; color: var(--ink-4);">{{ $a->date->format('Y-m-d') }}</div>
            </div>

            {{-- TOPIC & LOCATION --}}
            <div style="padding: 13px 14px;">
                <div style="font-size: 13px; color: var(--ink); margin-bottom: 4px; line-height: 1.35;">{{ $a->topic ?: '—' }}</div>
                <div style="display: flex; align-items: center; gap: 4px; font-size: 11px; color: var(--forest);">
                    <x-lucide-map-pin style="width:10px;height:10px;flex-shrink:0;" />
                    {{ $a->location }}
                </div>
            </div>

            {{-- HUB --}}
            <div style="padding: 13px 14px; text-align: center;">
                <span class="mono" style="font-size: 11.5px; font-weight: 600; color: var(--forest);">{{ $a->hub_id }}</span>
            </div>

            {{-- TOTAL --}}
            <div style="padding: 13px 14px; text-align: center;">
                <span class="serif" style="font-size: 22px; font-weight: 500; color: var(--ink);">{{ $a->total_participants }}</span>
            </div>

            {{-- FEMALE --}}
            <div style="padding: 13px 14px; text-align: center;">
                <span class="serif" style="font-size: 22px; font-weight: 500; color: var(--ink);">{{ $a->female_participants }}</span>
            </div>

            {{-- MINORITY --}}
            <div style="padding: 13px 14px; text-align: center;">
                <span class="serif" style="font-size: 22px; font-weight: 500; color: {{ $a->minority_participants > 0 ? 'var(--ochre)' : 'var(--ink-4)' }};">{{ $a->minority_participants }}</span>
            </div>

            {{-- PWD --}}
            <div style="padding: 13px 14px; text-align: center;">
                <span style="font-size: 15px; font-weight: 500; color: {{ $a->disability_participants > 0 ? 'var(--ink-2)' : 'var(--ink-4)' }};">{{ $a->disability_participants }}</span>
            </div>

            {{-- PULSE · O2.1 --}}
            <div style="padding: 13px 14px;">
                @if($actRespondents > 0 && $actGainPct !== null)
                <div style="font-size: 14px; font-weight: 600; color: {{ $gainColor }}; margin-bottom: 2px;">{{ $actGainPct }}%
                    <span style="font-size: 11px; font-weight: 400; color: var(--ink-3); margin-left: 2px;">gained</span>
                </div>
                <div style="font-size: 10.5px; color: var(--ink-3);">{{ $actRespondents }} of {{ $a->total_participants }} responded</div>
                @else
                <span style="font-size: 11px; color: var(--ink-4); font-style: italic;">No responses</span>
                @endif
            </div>

            {{-- FACILITATOR · FLAGS --}}
            <div style="padding: 13px 14px;">
                <div style="font-size: 13px; color: var(--ink-2); margin-bottom: 6px;">{{ $a->facilitator }}</div>
                <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                    @if($a->naz_promoted)
                    <span style="font-size: 9.5px; padding: 2px 6px; background: var(--moss-tint); color: var(--moss); font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; white-space: nowrap;">NAZ ASSIST</span>
                    @endif
                    @if($a->slacc)
                    <span style="font-size: 9.5px; padding: 2px 6px; background: var(--ochre-tint); color: var(--ochre); font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; white-space: nowrap;">SLACC</span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div style="padding: 48px; text-align: center; color: var(--ink-3);">
            <x-lucide-megaphone style="width:28px;height:28px;color:var(--ink-4);margin:0 auto 12px;" />
            <div style="font-size: 13px;">No outreach activities recorded yet.</div>
        </div>
        @endforelse
    </div>

    {{-- ═══ Pulse Survey Section ═══ --}}
    <div style="margin-bottom: 36px;">
        {{-- Section header --}}
        <div style="display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 22px; padding-bottom: 16px; border-bottom: 1px solid var(--rule);">
            <div>
                <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 8px;">Outcome 2.1 &middot; Outreach Pulse Surveys</div>
                <h2 class="serif" style="font-size: 32px; font-weight: 400; margin: 0; letter-spacing: -0.015em; line-height: 1.05;">
                    Did <em style="color: var(--forest);">understanding</em> rise?
                </h2>
                <div style="font-size: 13px; color: var(--ink-3); margin-top: 8px; max-width: 680px; line-height: 1.6;">
                    Pre/post pulse surveys captured at the end of every legal-literacy session. We score each respondent on a 1&ndash;5 self-rated understanding scale, before and after.
                </div>
            </div>
            <div style="text-align: right; flex-shrink: 0;">
                <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 4px;">Indicator O2.1</div>
                <div class="serif" style="font-size: 52px; font-weight: 400; line-height: 1; color: {{ $understandingGainPct >= 80 ? 'var(--forest)' : 'var(--burgundy)' }};">{{ $understandingGainPct }}%</div>
                <div style="font-size: 11px; color: var(--ink-3); margin-top: 4px;">reported increased understanding &middot; target 80%</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

            {{-- Left: Gain Distribution --}}
            <div class="card" style="padding: 24px;">
                <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 18px;">Understanding Gain Distribution</div>

                @php
                    $gainLabels = [
                        4 => ['+4 points', 'transformative gain', 'var(--forest)'],
                        3 => ['+3 points', 'large gain',          'var(--forest)'],
                        2 => ['+2 points', 'moderate gain',       'var(--forest)'],
                        1 => ['+1 point',  'small gain',          'var(--ochre)'],
                        0 => ['No change', '',                    'var(--ink-3)'],
                    ];
                    $totalResp = array_sum($gainDist) ?: 1;
                @endphp

                <div style="display: flex; flex-direction: column; gap: 14px;">
                    @foreach($gainLabels as $pts => [$label, $sublabel, $color])
                    @php
                        $cnt = $gainDist[$pts] ?? 0;
                        $barPct = round(($cnt / $maxGainDist) * 100);
                        $sharePct = round(($cnt / $totalResp) * 100);
                    @endphp
                    <div style="display: flex; align-items: center; gap: 12px;">
                        {{-- Color swatch --}}
                        <div style="width: 12px; height: 12px; background: {{ $color }}; flex-shrink: 0;"></div>
                        {{-- Label --}}
                        <div style="min-width: 140px; font-size: 12px; color: var(--ink-2);">
                            {{ $label }}{{ $sublabel ? ' &middot; ' . $sublabel : '' }}
                        </div>
                        {{-- Bar --}}
                        <div style="flex: 1; height: 8px; background: var(--rule-2);">
                            <div style="height: 100%; width: {{ $barPct }}%; background: {{ $color }}; transition: width 400ms;"></div>
                        </div>
                        {{-- Count + % --}}
                        <div style="font-size: 12px; font-weight: 600; min-width: 22px; text-align: right; color: var(--ink);">{{ $cnt }}</div>
                        <div style="font-size: 11px; color: var(--ink-3); min-width: 32px;">{{ $sharePct }}%</div>
                    </div>
                    @endforeach
                </div>

                {{-- 3 stat pills --}}
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; background: var(--rule-2); margin-top: 24px;">
                    <div style="padding: 12px 14px; background: var(--parchment-2);">
                        <div class="label-cap" style="font-size: 9px; margin-bottom: 4px;">Avg. gain</div>
                        <div class="serif" style="font-size: 20px; font-weight: 500;">+{{ $avgGain }} <span style="font-size: 11px; font-weight: 400; color: var(--ink-3);">pts</span></div>
                    </div>
                    <div style="padding: 12px 14px; background: var(--parchment-2);">
                        <div class="label-cap" style="font-size: 9px; margin-bottom: 4px;">Response rate</div>
                        <div class="serif" style="font-size: 20px; font-weight: 500;">{{ $responseRate }}%</div>
                    </div>
                    <div style="padding: 12px 14px; background: var(--parchment-2);">
                        <div class="label-cap" style="font-size: 9px; margin-bottom: 4px;">Would recommend</div>
                        <div class="serif" style="font-size: 20px; font-weight: 500;">{{ $wouldRecommend }}%</div>
                    </div>
                </div>
            </div>

            {{-- Right: Participant quotes --}}
            <div class="card" style="padding: 24px;">
                <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 18px;">What Participants Said</div>

                @if($recentComments->isEmpty())
                <div style="padding: 32px 0; text-align: center; color: var(--ink-4);">
                    <div style="font-size: 13px;">No comments recorded yet.</div>
                </div>
                @else
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    @foreach($recentComments as $c)
                    @php
                        $demo    = $c['demo'] ?? [];
                        $gender  = $demo['gender'] ?? null;
                        $ageBand = $demo['age_band'] ?? null;
                        $gainStr = $c['gain'] > 0 ? '+' . $c['gain'] : (string) $c['gain'];
                    @endphp
                    <div style="padding-left: 16px; border-left: 3px solid var(--rule);">
                        <div class="serif" style="font-size: 16px; font-weight: 400; color: var(--ink); line-height: 1.5; margin-bottom: 8px; letter-spacing: -0.005em;">
                            &ldquo;{{ $c['comment'] }}&rdquo;
                        </div>
                        <div style="font-size: 10.5px; color: var(--ink-4); line-height: 1.6;">
                            <span class="mono">{{ $c['hub_id'] }}</span>
                            @if($gender || $ageBand)
                            &middot; {{ implode(', ', array_filter([$gender, $ageBand])) }}
                            @endif
                            &middot; gain {{ $gainStr }}<br>
                            {{ $c['location'] }} &middot; {{ $c['date']?->format('Y-m-d') }}
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <div style="margin-top: 20px; padding-top: 14px; border-top: 1px solid var(--rule-2); font-size: 11px; color: var(--ink-4); font-style: italic; line-height: 1.5;">
                    All comments are anonymised. Demographics are self-reported and may be left undisclosed.
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Disaggregation note ═══ --}}
    <div style="padding: 14px 18px; background: var(--paper); border: 1px solid var(--rule); font-size: 11.5px; color: var(--ink-3); line-height: 1.55;">
        <strong style="color: var(--ink-2);">Disaggregation note:</strong>
        Participant counts are captured via paper attendance sheets at each session and digitised within 48 hours. Where individuals decline to self-identify (gender, minority status, disability), they are recorded as <em>undisclosed</em> and excluded from the respective column &mdash; not as zero.
    </div>

</div>

@if($canWrite)
{{-- ═══════════════════════════════════════════════════
     LOG NEW ACTIVITY MODAL
═══════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-outreach-new" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 640px; margin: 1.75rem auto;">
        <div class="modal-content" style="border: 1px solid var(--rule); border-radius: 4px; background: var(--parchment); box-shadow: 0 16px 48px rgba(0,0,0,.18); display: flex; flex-direction: column; max-height: 92vh;">

            {{-- Sticky header --}}
            <div style="padding: 22px 24px 18px; border-bottom: 1px solid var(--rule); flex-shrink: 0; background: var(--parchment);">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                    <div>
                        <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); letter-spacing: 0.09em; margin-bottom: 6px;">Service Delivery &middot; Awareness &amp; Legal Literacy</div>
                        <h2 class="serif" style="font-size: 30px; font-weight: 400; margin: 0; line-height: 1.1; letter-spacing: -0.015em;">
                            Log <em style="font-style: italic;">outreach</em> activity
                        </h2>
                    </div>
                    <button type="button" data-bs-dismiss="modal"
                            style="background:none; border:1px solid var(--rule); cursor:pointer; padding:6px 9px; color:var(--ink-3); line-height:1; border-radius:3px; flex-shrink:0; margin-top:4px; transition: border-color 120ms;"
                            onmouseenter="this.style.borderColor='var(--ink-2)'" onmouseleave="this.style.borderColor='var(--rule)'">
                        <x-lucide-x style="width:15px;height:15px;" />
                    </button>
                </div>
            </div>

            {{-- Scrollable body --}}
            <div style="flex:1; overflow-y:auto; padding:0;">
                <form method="POST" action="{{ route('outreach.store') }}" id="outreachNewForm">
                    @csrf

                    {{-- §1 Activity Details --}}
                    <div style="padding: 22px 24px; border-bottom: 1px solid var(--rule);">
                        <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.09em; color: var(--ink-2); margin-bottom: 3px;">Activity Details</div>
                        <div style="font-size: 12px; color: var(--ink-3); margin-bottom: 18px; font-style: italic;">What happened, when, and where</div>

                        {{-- Row 1: type + date --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Activity Type <span style="color:var(--burgundy);">*</span></label>
                                <select name="type" required
                                        style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px; appearance:auto; cursor:pointer;">
                                    <option value="">Choose type</option>
                                    <option value="Legal Literacy">Legal Literacy</option>
                                    <option value="Paralegal Outreach">Paralegal Outreach</option>
                                    <option value="Awareness">Awareness</option>
                                </select>
                            </div>
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Date Held <span style="color:var(--burgundy);">*</span></label>
                                <input type="date" name="date" required value="{{ now()->format('Y-m-d') }}"
                                       style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; box-sizing:border-box; border-radius:2px;">
                            </div>
                        </div>

                        {{-- Row 2: hub + facilitator --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Hub <span style="color:var(--burgundy);">*</span></label>
                                <select name="hub_id" required
                                        style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px; appearance:auto; cursor:pointer;">
                                    <option value="">Hub conducting the activity</option>
                                    @foreach($hubs as $hub)
                                    <option value="{{ $hub->id }}" {{ $hubs->count() === 1 ? 'selected' : '' }}>{{ $hub->id }} &ndash; {{ $hub->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Facilitator <span style="color:var(--burgundy);">*</span></label>
                                <input type="text" name="facilitator" required placeholder="Name (Role)"
                                       style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                                <div style="font-size:11px; color:var(--ink-4); margin-top:5px; font-style:italic;">Lead facilitator and role, e.g. &ldquo;T. Panhwar (Paralegal)&rdquo;</div>
                            </div>
                        </div>

                        {{-- Row 3: location + topic --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Location <span style="color:var(--burgundy);">*</span></label>
                                <input type="text" name="location" required placeholder="Venue, locality"
                                       style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Topic / Theme <span style="color:var(--burgundy);">*</span></label>
                                <input type="text" name="topic" required placeholder="e.g. Inheritance rights under Muslim Family Law"
                                       style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                            </div>
                        </div>
                    </div>

                    {{-- §2 Participation --}}
                    <div style="padding: 22px 24px; border-bottom: 1px solid var(--rule);">
                        <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.09em; color: var(--ink-2); margin-bottom: 3px;">Participation</div>
                        <div style="font-size: 12px; color: var(--ink-3); margin-bottom: 18px; font-style: italic;">Total reached and disaggregation &mdash; leave categories blank where participants declined to self-identify</div>

                        <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px;">
                            @foreach([
                                ['total_participants',    'Total',    true],
                                ['female_participants',   'Female',   false],
                                ['male_participants',     'Male',     false],
                                ['minority_participants', 'Minority', false],
                                ['disability_participants','PwD',     false],
                            ] as [$fname, $flabel, $required])
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">
                                    {{ $flabel }}{{ $required ? ' *' : '' }}
                                </label>
                                <input type="number" name="{{ $fname }}" value="0" min="0"
                                       {{ $required ? 'required' : '' }}
                                       style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:15px; font-family:monospace; box-sizing:border-box; border-radius:2px; text-align:center;">
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- §3 Programme Tagging --}}
                    <div style="padding: 22px 24px; border-bottom: 1px solid var(--rule);">
                        <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.09em; color: var(--ink-2); margin-bottom: 3px;">Programme Tagging</div>
                        <div style="font-size: 12px; color: var(--ink-3); margin-bottom: 16px; font-style: italic;">Tag if this activity is part of a flagship programme or campaign</div>

                        <label style="display:flex; align-items:flex-start; gap:13px; padding:14px 16px; border:1px solid var(--rule); background:var(--paper); cursor:pointer; margin-bottom:8px; transition:border-color 120ms;"
                               onmouseenter="this.style.borderColor='var(--forest)'" onmouseleave="this.style.borderColor='var(--rule)'">
                            <input type="checkbox" name="naz_promoted" value="1"
                                   style="width:15px; height:15px; margin-top:3px; flex-shrink:0; accent-color:var(--forest); cursor:pointer;">
                            <div>
                                <div style="font-size:13.5px; font-weight:600; color:var(--ink); margin-bottom:4px;">NAZ Assist (paralegal-led)</div>
                                <div style="font-size:11.5px; color:var(--ink-3); line-height:1.5;">Paralegal-driven legal awareness in underserved areas. Counts toward output OP3.1.</div>
                            </div>
                        </label>

                        <label style="display:flex; align-items:flex-start; gap:13px; padding:14px 16px; border:1px solid var(--rule); background:var(--paper); cursor:pointer; transition:border-color 120ms;"
                               onmouseenter="this.style.borderColor='var(--ochre)'" onmouseleave="this.style.borderColor='var(--rule)'">
                            <input type="checkbox" name="slacc" value="1"
                                   style="width:15px; height:15px; margin-top:3px; flex-shrink:0; accent-color:var(--ochre); cursor:pointer;">
                            <div>
                                <div style="font-size:13.5px; font-weight:600; color:var(--ink); margin-bottom:4px;">SLACC campaign</div>
                                <div style="font-size:11.5px; color:var(--ink-3); line-height:1.5;">Sindh Legal Aid Citizen Campaign &mdash; coordinated province-wide outreach effort.</div>
                            </div>
                        </label>
                    </div>

                    {{-- §4 Notes & Outcomes --}}
                    <div style="padding: 22px 24px 28px;">
                        <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.09em; color: var(--ink-2); margin-bottom: 14px;">Notes &amp; Outcomes</div>

                        <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">
                            Brief notes on session and outcomes
                        </label>
                        <textarea name="notes" rows="4"
                                  placeholder="What stood out? Did anyone request a follow-up consultation? Any safeguarding concerns?"
                                  style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; resize:vertical; box-sizing:border-box; border-radius:2px; outline:none; line-height:1.5;"></textarea>
                        <div style="font-size:11.5px; color:var(--ink-4); margin-top:6px; font-style:italic; line-height:1.5;">
                            Key questions raised, follow-up requests received, any incidents &mdash; max 2&ndash;3 sentences.
                        </div>
                    </div>

                </form>
            </div>

            {{-- Sticky footer --}}
            <div style="flex-shrink:0; padding:14px 24px; border-top:1px solid var(--rule); display:flex; justify-content:flex-end; gap:10px; background:var(--parchment);">
                <button type="button" data-bs-dismiss="modal" class="btn-ghost">Cancel</button>
                <button type="submit" form="outreachNewForm" class="btn-primary" style="display:inline-flex;align-items:center;gap:7px;">
                    <x-lucide-megaphone style="width:13px;height:13px;" /> Log activity
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════
     LOG PULSE RESPONSE MODAL
═══════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-outreach-pulse" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 560px; margin: 1.75rem auto;">
        <div class="modal-content" style="border: 1px solid var(--rule); border-radius: 4px; background: var(--parchment); box-shadow: 0 16px 48px rgba(0,0,0,.18); display: flex; flex-direction: column; max-height: 90vh;">

            {{-- Header --}}
            <div style="padding: 22px 24px 18px; border-bottom: 1px solid var(--rule); flex-shrink: 0; background: var(--parchment);">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                    <div>
                        <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 6px;">Outcome 2.1 &middot; Pulse Survey</div>
                        <h2 class="serif" style="font-size: 28px; font-weight: 400; margin: 0; line-height: 1.1;">Log Pulse Response</h2>
                    </div>
                    <button type="button" data-bs-dismiss="modal"
                            style="background:none; border:1px solid var(--rule); cursor:pointer; padding:6px 8px; color:var(--ink-3); border-radius:3px; flex-shrink:0; margin-top:2px;">
                        <x-lucide-x style="width:16px;height:16px;" />
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div style="flex:1; overflow-y:auto; padding: 22px 24px;">
                <form method="POST" action="" id="pulseForm">
                    @csrf

                    {{-- Activity picker --}}
                    <div style="margin-bottom: 18px;">
                        <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">
                            Session / Activity <span style="color:var(--burgundy);">*</span>
                        </label>
                        <select id="pulseActivitySelect" required onchange="pulseSetActivity(this.value)"
                                style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px; appearance:auto;">
                            <option value="">Select activity…</option>
                            @foreach($activities->take(30) as $a)
                            <option value="{{ $a->id }}">{{ $a->outreach_uid }} &ndash; {{ $a->date->format('Y-m-d') }} &ndash; {{ Str::limit($a->topic ?: $a->type, 40) }} ({{ $a->hub_id }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Scores --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">
                                Pre-session score (1–5) <span style="color:var(--burgundy);">*</span>
                            </label>
                            <input type="number" name="pre_score" required min="1" max="5" step="1" placeholder="1"
                                   style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:16px; font-family:monospace; box-sizing:border-box; border-radius:2px; text-align:center;">
                        </div>
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">
                                Post-session score (1–5) <span style="color:var(--burgundy);">*</span>
                            </label>
                            <input type="number" name="post_score" required min="1" max="5" step="1" placeholder="5"
                                   style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:16px; font-family:monospace; box-sizing:border-box; border-radius:2px; text-align:center;">
                        </div>
                    </div>

                    {{-- Will apply + Would recommend --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">
                                Will apply knowledge? <span style="color:var(--burgundy);">*</span>
                            </label>
                            <select name="will_apply" required
                                    style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px; appearance:auto;">
                                <option value="yes">Yes</option>
                                <option value="maybe">Maybe</option>
                                <option value="no">No</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Would recommend session?</label>
                            <select name="would_recommend"
                                    style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px; appearance:auto;">
                                <option value="yes">Yes</option>
                                <option value="maybe">Maybe</option>
                                <option value="no">No</option>
                            </select>
                        </div>
                    </div>

                    {{-- Demographics --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Gender (optional)</label>
                            <select name="gender"
                                    style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px; appearance:auto;">
                                <option value="">Undisclosed</option>
                                <option value="Female">Female</option>
                                <option value="Male">Male</option>
                                <option value="Non-binary">Non-binary</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Age band (optional)</label>
                            <select name="age_band"
                                    style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px; appearance:auto;">
                                <option value="">Undisclosed</option>
                                <option value="under-25">Under 25</option>
                                <option value="25-44">25–44</option>
                                <option value="45-64">45–64</option>
                                <option value="65+">65+</option>
                            </select>
                        </div>
                    </div>

                    {{-- Comment --}}
                    <div>
                        <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Comment (optional)</label>
                        <textarea name="comment" rows="3" placeholder="What did you learn today that you didn't know before?"
                                  style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; resize:vertical; box-sizing:border-box; border-radius:2px; line-height:1.5;"></textarea>
                    </div>
                </form>
            </div>

            {{-- Footer --}}
            <div style="flex-shrink:0; padding:14px 24px; border-top:1px solid var(--rule); display:flex; justify-content:flex-end; gap:10px; background:var(--parchment);">
                <button type="button" data-bs-dismiss="modal" class="btn-ghost">Cancel</button>
                <button type="submit" form="pulseForm" class="btn-primary" style="display:inline-flex;align-items:center;gap:7px;">
                    <x-lucide-bar-chart-2 style="width:13px;height:13px;" /> Save response
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
// ── Hub filter (client-side) ──────────────────────────────────
function orHubFilter(hubId, btn) {
    document.querySelectorAll('[data-or-hub]').forEach(b => {
        if (!b.matches('[data-or-hub].modal *') && b.tagName === 'BUTTON') {
            const isActive = b.dataset.orHub === hubId;
            b.style.background  = isActive ? 'var(--forest)' : 'transparent';
            b.style.color       = isActive ? 'var(--cream)'  : 'var(--ink-2)';
            b.style.borderColor = isActive ? 'var(--forest)' : 'var(--rule)';
            b.style.fontWeight  = isActive ? '600' : '500';
        }
    });
    document.querySelectorAll('[data-or-hub]:not(button)').forEach(row => {
        const show = hubId === 'all' || row.dataset.orHub === hubId;
        row.style.display = show ? '' : 'none';
    });
}

// ── Pulse modal: set form action to selected activity ─────────
function pulseSetActivity(activityId) {
    const form = document.getElementById('pulseForm');
    if (!activityId) { form.action = ''; return; }
    form.action = '/outreach/' + activityId + '/pulse';
}
</script>
</x-layouts.app>
