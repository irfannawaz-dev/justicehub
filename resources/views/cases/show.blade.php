<x-layouts.app>
@php
    $user = auth()->user();
    $canApprove = $user->can('cases.approve');
    $canEdit = $user->can('cases.edit');
    $canWrite = $user->canWrite();
    $isResolved = in_array($case->status->value, ['Closed', 'Settlement']);
    $canResolve = $user->isHubCoordinator()
        ? ($case->assigned_to === $user->name)   // coordinator only if this case is theirs
        : $user->can('cases.approve');             // Head / Provincial Lead can always resolve
    $isPending = $case->status->value === 'Pending Approval';
    $isRejected = $case->status->value === 'Rejected';
    $initials = collect(explode(' ', $case->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->join('');
    $encounterCount = $case->serviceEncounters->count();
    $firstEncounterDate = $case->serviceEncounters->where('type', '!=', 'Intake')->sortBy('date')->first()?->date?->toDateString();
    $sla = $case->computeSlaStatus($firstEncounterDate);
    $pathways = \DB::table('case_pathway')->where('case_id', $case->id)->pluck('pathway_value');

    // ── Journey step logic ──
    $journeySteps = [
        ['num' => 1, 'name' => 'Awareness & Outreach',        'status' => 'completed'],
        ['num' => 2, 'name' => 'Intake / Contact',            'status' => 'completed'],
        ['num' => 3, 'name' => 'Case Creation & Screening',   'status' => $case->primary_issue ? 'completed' : 'current'],
        ['num' => 4, 'name' => 'Triage & Assignment',         'status' => $case->assigned_to ? 'completed' : ($case->primary_issue ? 'current' : 'pending')],
        ['num' => 5, 'name' => 'Case Management',             'status' => $encounterCount > 1 ? 'completed' : ($case->assigned_to ? 'current' : 'pending')],
        ['num' => 6, 'name' => 'Collaboration & Referrals',   'status' => $pathways->isEmpty() ? 'skipped' : 'completed'],
        ['num' => 7, 'name' => 'Resolution & Outcome',        'status' => $isResolved ? 'completed' : 'current'],
        ['num' => 8, 'name' => 'Follow-up & Aftercare',       'status' => 'pending'],
        ['num' => 9, 'name' => 'Reporting & Learning',        'status' => 'pending'],
    ];
    // Fix: only one step should be 'current' — pick the first non-completed, non-skipped
    $foundCurrent = false;
    foreach ($journeySteps as &$step) {
        if ($step['status'] === 'completed' || $step['status'] === 'skipped') continue;
        if (!$foundCurrent) { $step['status'] = 'current'; $foundCurrent = true; }
        else { $step['status'] = 'pending'; }
    }
    unset($step);
    $applicableSteps = collect($journeySteps)->where('status', '!=', 'skipped')->count();
    $completedSteps  = collect($journeySteps)->where('status', 'completed')->count();
    $currentStep     = collect($journeySteps)->firstWhere('status', 'current');
    $pctComplete     = $applicableSteps > 0 ? round(($completedSteps / $applicableSteps) * 100) : 0;

    // ── LAS core-services pillar matching ──
    $pathwayLower = $pathways->map(fn($p) => strtolower($p));
    $pillarLegal      = $pathwayLower->contains(fn($v) => str_contains($v, 'legal advice'));
    $pillarMediation  = $pathwayLower->contains(fn($v) => str_contains($v, 'mediation') || str_contains($v, 'adr'));
    $pillarNadra      = $pathwayLower->contains(fn($v) => str_contains($v, 'nadra') || str_contains($v, 'documentation'));
    $pillarCourt      = $pathwayLower->contains(fn($v) => str_contains($v, 'court') || str_contains($v, 'litigation') || str_contains($v, 'representation'));

    $hasApprovalHistory = $case->pathway_manager || $case->approval_decision;
@endphp

{{-- ═══ Animations ═══ --}}
<style>
    @keyframes jh-fade-up { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes jh-count-pop { 0% { transform: scale(0.6); opacity: 0; } 60% { transform: scale(1.05); } 100% { transform: scale(1); opacity: 1; } }
    .jh-anim-card { animation: jh-fade-up 0.6s ease both; }
    .jh-anim-card:nth-child(1) { animation-delay: 0.05s; }
    .jh-anim-card:nth-child(2) { animation-delay: 0.12s; }
    .jh-anim-card:nth-child(3) { animation-delay: 0.19s; }
    .jh-anim-card:nth-child(4) { animation-delay: 0.26s; }
    .jh-anim-card:nth-child(5) { animation-delay: 0.33s; }
    .jh-anim-card:nth-child(6) { animation-delay: 0.40s; }
    .jh-anim-card:nth-child(7) { animation-delay: 0.47s; }
    .jh-anim-card:nth-child(8) { animation-delay: 0.54s; }
    .jh-anim-card:nth-child(9) { animation-delay: 0.61s; }
    .jh-anim-num { animation: jh-count-pop 0.7s ease both; animation-delay: 0.3s; }
    .jh-anim-section { animation: jh-fade-up 0.5s ease both; }

    .jh-case-tab { outline: none; }
    .jh-case-tab:not(.active):hover { color: var(--ink-2) !important; border-bottom-color: var(--rule) !important; }
    .jh-case-tab.active { border-bottom-color: var(--forest) !important; color: var(--ink) !important; font-weight: 600 !important; }

    .jh-journey-step { transition: transform 0.15s ease, box-shadow 0.15s ease; }
    .jh-journey-step:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.06); }

    .jh-intake-row { display: grid; grid-template-columns: 180px 1fr; gap: 4px 14px; padding: 9px 0; border-bottom: 1px solid var(--rule-2); font-size: 13px; }
    .jh-intake-row:last-child { border-bottom: none; }
    .jh-intake-label { color: var(--ink-3); font-size: 12px; }
    .jh-intake-value { color: var(--ink-2); font-weight: 500; }
</style>

<div style="padding: 22px 34px 64px; max-width: 1600px; margin: 0 auto;">

    {{-- Back --}}
    <a href="{{ route('cases.index') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: var(--ink-3); margin-bottom: 18px; text-decoration: none;">
        <x-lucide-chevron-left style="width: 13px; height: 13px;" /> {{ __('cases.back_to_cases') }}
    </a>

    {{-- ═══════════════════════════════════════════════════════════
         1. HEADER CARD (kept)
         ═══════════════════════════════════════════════════════════ --}}
    <div class="card jh-anim-section" style="padding: 24px 28px; margin-bottom: 20px; animation-delay: 0.05s;">
        <div style="display: flex; gap: 24px; align-items: flex-start;">
            <div style="width: 72px; height: 72px; background: var(--forest); color: var(--ochre-2); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <div class="serif" style="font-size: 28px; font-weight: 400;">{{ $initials }}</div>
            </div>
            <div style="flex: 1;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;">
                    <span class="mono" style="font-size: 11px; color: var(--ink-3);">{{ $case->case_uid }} · {{ $case->case_ref }}</span>
                    @if($case->is_gbv)<x-pill color="var(--burgundy)" bg="rgba(138,46,29,0.08)" border-color="var(--burgundy)"><x-lucide-shield style="width:10px;height:10px;" /> {{ __('cases.gbv_safeguarding') }}</x-pill>@endif
                    @if($case->is_child)<x-pill color="var(--ochre)" bg="rgba(184,115,25,0.08)" border-color="var(--ochre)"><x-lucide-shield style="width:10px;height:10px;" /> {{ __('cases.child_protection') }}</x-pill>@endif
                    @if($case->is_minority)<x-pill><x-lucide-flag style="width:10px;height:10px;" /> {{ __('cases.minority_label') }}</x-pill>@endif
                    @if($case->is_disability)<x-pill><x-lucide-heart-handshake style="width:10px;height:10px;" /> {{ __('cases.pwd_label') }}</x-pill>@endif
                    @if($case->is_underserved)<x-pill color="var(--forest-3)" border-color="var(--forest-3)">{{ __('cases.underserved_population') }}</x-pill>@endif
                    @if($case->returning_client)<x-pill bg="var(--parchment-2)">{{ __('cases.returning_client') }}</x-pill>@endif
                </div>
                <h1 class="serif" style="font-size: 32px; font-weight: 400; letter-spacing: -0.015em; margin: 0 0 8px 0;">
                    {{ $case->name }}
                    <span style="color: var(--ink-3); font-size: 16px; font-weight: 300; margin-left: 12px; font-style: italic;">{{ strtolower($case->primary_issue) }}</span>
                </h1>
                <p style="font-size: 14px; color: var(--ink-2); margin: 0 0 14px 0; max-width: 720px; line-height: 1.5;">{{ $case->summary }}</p>
                <div style="display: flex; gap: 28px; font-size: 12px; color: var(--ink-3); flex-wrap: wrap;">
                    <span><x-lucide-users style="width:11px;height:11px;display:inline;vertical-align:-1px;margin-right:5px;" />{{ $case->gender }} · age {{ $case->age }}</span>
                    <span><x-lucide-map-pin style="width:11px;height:11px;display:inline;vertical-align:-1px;margin-right:5px;" />{{ $case->tehsil }}, {{ $case->district }}</span>
                    <span><x-lucide-building-2 style="width:11px;height:11px;display:inline;vertical-align:-1px;margin-right:5px;" />{{ $case->hub?->name ?? $case->hub_id }} Hub</span>
                    <span><x-lucide-calendar style="width:11px;height:11px;display:inline;vertical-align:-1px;margin-right:5px;" />{{ __('cases.intake') }} {{ $case->intake_date->format('M d, Y') }}</span>
                    <span><x-lucide-briefcase style="width:11px;height:11px;display:inline;vertical-align:-1px;margin-right:5px;" />{{ __('cases.assigned_label') }}: {{ $case->assigned_to }}</span>
                </div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px; align-items: flex-end;">
                @if($canWrite && !$isResolved)
                <div style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end;">
                    <a href="{{ route('cases.slip', $case) }}" target="_blank" class="btn-ghost" style="display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
                        <x-lucide-printer style="width:12px;height:12px;" /> {{ __('cases.print_slip') }}
                    </a>
                    @if($canEdit && !$pendingTransfer)
                    <button class="btn-ghost" onclick="jhOpenModal('reassign-case')">
                        <x-lucide-arrow-right-left style="width:12px;height:12px;" /> {{ __('cases.reassign') }}
                    </button>
                    @endif
                    @if($canResolve && !$isResolved && !$isPending)
                    <button class="btn-primary" style="background: var(--moss); border-color: var(--moss);" onclick="jhOpenModal('resolve-case')">
                        <x-lucide-check-circle-2 style="width:12px;height:12px;" /> {{ __('cases.mark_resolved') }}
                    </button>
                    @endif
                </div>
                @endif
                <div style="text-align: right;">
                    <div class="label-cap" style="font-size: 9px; margin-bottom: 2px;">{{ __('cases.case_status') }}</div>
                    <div class="serif" style="font-size: 20px; color: {{ $case->status->color() }};">{{ $case->status->value }}</div>
                    @if($case->disposition)
                    <div style="margin-top: 4px; display: inline-block; padding: 3px 9px; font-size: 10px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; background: {{ $case->disposition->color() }}15; color: {{ $case->disposition->color() }};">
                        {{ $case->disposition->label() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Pending Transfer Banner ═══ --}}
    @if($pendingTransfer)
    <div style="margin-bottom:16px; padding:12px 18px; background:var(--ochre-tint,#fdf8ee); border:1px solid var(--ochre); border-left:4px solid var(--ochre); display:flex; align-items:center; gap:14px;">
        <x-lucide-arrow-right-left style="width:16px;height:16px;color:var(--ochre);flex-shrink:0;" />
        <div style="flex:1;">
            <div style="font-size:12.5px; font-weight:600; color:var(--ink);">{{ __('cases.transfer_pending_approval') }}</div>
            <div style="font-size:12px; color:var(--ink-3); margin-top:2px;">
                @if($pendingTransfer->transfer_type === 'pathway')
                    Pathway: <strong>{{ $pendingTransfer->from_pathway }}</strong> → <strong>{{ $pendingTransfer->to_pathway }}</strong>
                    · Staff: <strong>{{ $pendingTransfer->from_assignee }}</strong> → <strong>{{ $pendingTransfer->to_assignee }}</strong>
                @else
                    Staff: <strong>{{ $pendingTransfer->from_assignee }}</strong> → <strong>{{ $pendingTransfer->to_assignee }}</strong>
                @endif
                · Requested by {{ $pendingTransfer->transferredBy->name }} on {{ $pendingTransfer->transfer_date->format('M d, Y') }}
                · <em>{{ $pendingTransfer->reason }}</em>
            </div>
        </div>
        @if($canApprove)
        <div style="display:flex;gap:8px;flex-shrink:0;">
            <form method="POST" action="{{ route('cases.transfer.approve', [$case, $pendingTransfer]) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-primary" style="background:var(--moss);border-color:var(--moss);font-size:11px;padding:5px 12px;">
                    <x-lucide-check style="width:11px;height:11px;" /> {{ __('common.approve') }}
                </button>
            </form>
            <button class="btn-ghost" style="font-size:11px;padding:5px 12px;" onclick="jhOpenModal('reject-transfer')">
                <x-lucide-x style="width:11px;height:11px;" /> {{ __('common.reject') }}
            </button>
        </div>
        @endif
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         2. SLA + KEY STATS STRIP (kept)
         ═══════════════════════════════════════════════════════════ --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px;">
        @php
            $slaColor  = $sla['status'] === 'met' ? 'var(--moss)' : ($sla['status'] === 'pending' ? 'var(--ochre)' : 'var(--burgundy)');
            $slaLabel  = $sla['status'] === 'met' ? 'Met' : ($sla['status'] === 'pending' ? 'Pending' : 'Breach');
            $slaHours  = $sla['hours_limit'];
            $slaDetail = match($sla['status']) {
                'met'     => 'First action ' . $sla['hours_taken'] . 'h after intake',
                'breach'  => $sla['first_encounter']
                                ? 'Overdue by ' . $sla['hours_overdue'] . 'h'
                                : 'No action — ' . $sla['hours_overdue'] . 'h overdue',
                'pending' => $sla['hours_remaining'] . 'h remaining',
                default   => '',
            };
        @endphp
        <div class="card-accent jh-anim-card" style="padding: 16px 18px; border-left-color: {{ $slaColor }};">
            <div class="label-cap" style="font-size: 9px;">{{ __('cases.sla_hour_label', ['hours' => $slaHours]) }} · {{ $case->urgency->value }}</div>
            <div style="display: flex; align-items: baseline; gap: 8px; margin-top: 6px;">
                @if($sla['status'] === 'met')
                    <x-lucide-check-circle-2 style="width:18px;height:18px;color:var(--moss);" />
                @elseif($sla['status'] === 'pending')
                    <x-lucide-clock style="width:18px;height:18px;color:var(--ochre);" />
                @else
                    <x-lucide-x-circle style="width:18px;height:18px;color:var(--burgundy);" />
                @endif
                <span class="serif jh-anim-num" style="font-size: 22px; color: {{ $slaColor }};">{{ $slaLabel }}</span>
            </div>
            <div style="font-size: 11px; color: var(--ink-3); margin-top: 4px;">{{ $slaDetail }}</div>
            <div style="font-size: 10px; color: var(--ink-4); margin-top: 2px;">
                {{ __('cases.deadline') }}: {{ $sla['deadline']->format('M d, Y H:i') }}
            </div>
        </div>
        <div class="card jh-anim-card" style="padding: 16px 18px;">
            <div class="label-cap" style="font-size: 9px;">{{ __('cases.service_encounters') }}</div>
            <div class="serif jh-anim-num" style="font-size: 22px; margin-top: 6px;">{{ $encounterCount }}</div>
            <div style="font-size: 11px; color: var(--ink-3);">{{ __('cases.logged_on_case') }}</div>
        </div>
        <div class="card jh-anim-card" style="padding: 16px 18px;">
            <div class="label-cap" style="font-size: 9px;">{{ __('cases.pathways_label') }}</div>
            <div class="serif jh-anim-num" style="font-size: 22px; margin-top: 6px;">{{ $pathways->count() }}</div>
            <div style="font-size: 11px; color: var(--ink-3);">{{ $pathways->join(' · ') }}</div>
        </div>
        <div class="card jh-anim-card" style="padding: 16px 18px;">
            <div class="label-cap" style="font-size: 9px;">{{ __('cases.risk_urgency') }}</div>
            <div class="serif jh-anim-num" style="font-size: 22px; margin-top: 6px; color: {{ $case->urgency->color() }};">{{ $case->urgency->value }}</div>
            <div style="font-size: 11px; color: var(--ink-3);">{{ __('cases.risk_suffix', ['level' => $case->risk->value]) }}</div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         3. END-TO-END USER JOURNEY PROGRESS BAR (NEW)
         ═══════════════════════════════════════════════════════════ --}}
    <div class="card jh-anim-section" style="padding: 22px 26px; margin-bottom: 20px; animation-delay: 0.2s;">
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 16px;">
            <div>
                <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">{{ __('cases.end_to_end_journey') }}</div>
                <div class="serif" style="font-size: 18px; font-weight: 400;">
                    @if($currentStep)
                        {{ __('cases.step_x_of_y', ['num' => $currentStep['num'], 'total' => 9]) }} <span style="color: var(--ink-3); font-weight: 300;">&middot; {{ $currentStep['name'] }}</span>
                    @else
                        {{ __('cases.journey_complete') }}
                    @endif
                </div>
            </div>
            <div style="text-align: right;">
                <div class="mono" style="font-size: 20px; font-weight: 600; color: var(--forest);">{{ $pctComplete }}%</div>
                <div style="font-size: 11px; color: var(--ink-3);">{{ __('cases.complete_pct') }} &middot; {{ __('cases.of_applicable_steps', ['count' => $applicableSteps]) }}</div>
            </div>
        </div>

        {{-- Progress bar --}}
        <div style="height: 6px; background: var(--rule-2); margin-bottom: 18px; overflow: hidden;">
            <div style="height: 100%; width: {{ $pctComplete }}%; background: var(--forest); transition: width 0.8s ease;"></div>
        </div>

        {{-- Step boxes --}}
        <div style="display: grid; grid-template-columns: repeat(9, 1fr); gap: 6px;">
            @foreach($journeySteps as $idx => $step)
                @php
                    if ($step['status'] === 'completed') {
                        $bg   = 'var(--ochre-tint)';
                        $bdr  = '1px solid var(--ochre)';
                        $clr  = 'var(--ink)';
                        $fw   = '500';
                    } elseif ($step['status'] === 'current') {
                        $bg   = 'var(--forest)';
                        $bdr  = '1px solid var(--forest)';
                        $clr  = '#fff';
                        $fw   = '700';
                    } elseif ($step['status'] === 'skipped') {
                        $bg   = 'var(--rule-2)';
                        $bdr  = '1px solid var(--rule)';
                        $clr  = 'var(--ink-4)';
                        $fw   = '400';
                    } else {
                        $bg   = 'var(--paper)';
                        $bdr  = '1px solid var(--rule)';
                        $clr  = 'var(--ink-4)';
                        $fw   = '400';
                    }
                @endphp
                <div class="jh-journey-step jh-anim-card" style="
                    background: {{ $bg }};
                    border: {{ $bdr }};
                    padding: 10px 8px;
                    text-align: center;
                    min-height: 72px;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    gap: 4px;
                ">
                    @if($step['status'] === 'completed')
                        <x-lucide-check style="width: 14px; height: 14px; color: var(--ochre);" />
                    @elseif($step['status'] === 'current')
                        <span style="font-size: 14px; font-weight: 700; color: #fff;">{{ $step['num'] }}</span>
                    @elseif($step['status'] === 'skipped')
                        <x-lucide-minus style="width: 12px; height: 12px; color: var(--ink-4);" />
                    @else
                        <span style="font-size: 12px; color: var(--ink-4);">{{ $step['num'] }}</span>
                    @endif
                    <div style="font-size: 9px; font-weight: {{ $fw }}; color: {{ $clr }}; line-height: 1.3; letter-spacing: 0.01em;">
                        {{ $step['name'] }}
                    </div>
                    @if($step['status'] === 'skipped')
                        <div class="mono" style="font-size: 8px; color: var(--ink-4); text-transform: uppercase; letter-spacing: 0.06em;">{{ __('cases.skipped_label') }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Legend --}}
        <div style="display: flex; gap: 20px; margin-top: 14px; font-size: 11px; color: var(--ink-3);">
            <span style="display: inline-flex; align-items: center; gap: 5px;">
                <span style="width: 10px; height: 10px; background: var(--forest); display: inline-block;"></span> {{ __('cases.current_legend') }}
            </span>
            <span style="display: inline-flex; align-items: center; gap: 5px;">
                <span style="width: 10px; height: 10px; background: var(--ochre-tint); border: 1px solid var(--ochre); display: inline-block;"></span> {{ __('cases.completed_legend') }}
            </span>
            <span style="display: inline-flex; align-items: center; gap: 5px;">
                <span style="width: 10px; height: 10px; background: var(--paper); border: 1px solid var(--rule); display: inline-block;"></span> {{ __('cases.pending_legend') }}
            </span>
            <span style="display: inline-flex; align-items: center; gap: 5px;">
                <span style="width: 10px; height: 10px; background: var(--rule-2); border: 1px solid var(--rule); display: inline-block;"></span> {{ __('cases.not_applicable') }}
            </span>
        </div>
    </div>

    {{-- ═══ Approval Panel (kept) ═══ --}}
    @if($isPending && $canApprove)
    <div class="jh-anim-section" style="margin-bottom: 22px; padding: 20px 24px; background: var(--ochre-tint); border: 1px solid var(--ochre); border-left: 4px solid var(--ochre); animation-delay: 0.25s;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 24px;">
            <div style="flex: 1;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <x-lucide-clock style="width:14px;height:14px;color:var(--ochre);" />
                    <div class="label-cap" style="font-size: 10px; color: var(--ochre);">{{ __('cases.approval_pending') }}</div>
                </div>
                <div class="serif" style="font-size: 18px; font-weight: 500; margin-bottom: 6px;">{{ __('cases.awaiting_sign_off', ['manager' => $case->pathway_manager ?? 'Manager']) }}</div>
                <div style="font-size: 12.5px; color: var(--ink-2); line-height: 1.55;">
                    This case is on the <strong>{{ $pathways->first() }}</strong> pathway and cannot proceed until reviewed.
                    @if($case->requested_at) Submitted {{ $case->requested_at->format('M d, H:i') }}.@endif
                </div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px; flex-shrink: 0;">
                <form method="POST" action="{{ route('cases.approve', $case) }}">
                    @csrf
                    <button type="submit" style="background:var(--moss);color:var(--cream);border:1px solid var(--moss);padding:10px 18px;font-size:12.5px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px;">
                        <x-lucide-check-circle-2 style="width:13px;height:13px;" /> {{ __('cases.approve_start_service') }}
                    </button>
                </form>
                <button onclick="jhOpenModal('reject-pathway')" style="background:transparent;color:var(--burgundy);border:1px solid var(--burgundy);padding:10px 18px;font-size:12.5px;font-weight:500;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px;">
                    <x-lucide-x-circle style="width:13px;height:13px;" /> {{ __('cases.reject_with_reason') }}
                </button>
            </div>
        </div>
    </div>
    @endif

    @if($isRejected)
    <div class="jh-anim-section" style="margin-bottom: 22px; padding: 20px 24px; background: var(--burgundy-tint); border: 1px solid var(--burgundy); border-left: 4px solid var(--burgundy); animation-delay: 0.25s;">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
            <x-lucide-x-circle style="width:14px;height:14px;color:var(--burgundy);" />
            <div class="label-cap" style="font-size: 10px; color: var(--burgundy);">{{ __('cases.pathway_rejected') }}</div>
        </div>
        <div class="serif" style="font-size: 18px; font-weight: 500; margin-bottom: 6px;">{{ __('cases.rejected_by_name', ['name' => $case->rejected_by]) }}</div>
        <div style="font-size: 12.5px; color: var(--ink-2); line-height: 1.55;">{{ $case->rejection_reason }}</div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         4. ENHANCED TAB NAVIGATION (6 tabs)
         ═══════════════════════════════════════════════════════════ --}}
    @php $activeTab = session('activeTab', 'overview'); @endphp
    <ul class="nav" role="tablist" style="display: flex; gap: 0; border-bottom: 1px solid var(--rule); margin-bottom: 22px; padding: 0; list-style: none;">
        @foreach([
            ['id' => 'overview',   'label' => __('cases.overview'),    'icon' => 'layout-dashboard'],
            ['id' => 'referrals',  'label' => __('cases.tab_pathway'), 'icon' => 'git-branch',       'count' => $pathways->count()],
            ['id' => 'documents',  'label' => __('cases.documents'),   'icon' => 'file-text',        'count' => $case->documents->count()],
            ['id' => 'feedback',   'label' => __('cases.feedback'),    'icon' => 'heart-handshake',  'count' => $case->feedback->count()],
            ['id' => 'complaints', 'label' => __('cases.complaints'),  'icon' => 'alert-triangle',   'count' => $case->complaints->count()],
            ['id' => 'messages',   'label' => __('cases.case_notes'),  'icon' => 'message-square',   'count' => $case->messages->count()],
            ['id' => 'outcome',    'label' => __('cases.outcome'),     'icon' => 'check-circle-2'],
        ] as $t)
        @php $isActive = $activeTab === $t['id']; @endphp
        <li class="nav-item" role="presentation">
            <button
                class="jh-case-tab {{ $isActive ? 'active' : '' }}"
                data-bs-toggle="tab"
                data-bs-target="#tab-{{ $t['id'] }}"
                role="tab"
                style="padding:12px 20px; border:none; border-bottom: 2px solid {{ $isActive ? 'var(--forest)' : 'transparent' }}; margin-bottom:-1px; cursor:pointer; font-family:inherit; display:inline-flex; align-items:center; gap:8px; font-size:13px; font-weight:{{ $isActive ? '600' : '500' }}; color:{{ $isActive ? 'var(--ink)' : 'var(--ink-3)' }}; background:transparent;"
            >
                <x-dynamic-component :component="'lucide-' . $t['icon']" style="width: 14px; height: 14px;" />
                {{ $t['label'] }}
                @if(isset($t['count']) && $t['count'] > 0)
                    <span class="mono" style="font-size: 10px; padding: 1px 6px; background: var(--rule-2); color: var(--ink-3);">{{ $t['count'] }}</span>
                @endif
            </button>
        </li>
        @endforeach
    </ul>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.jh-case-tab').forEach(function (tab) {
            tab.addEventListener('shown.bs.tab', function () {
                document.querySelectorAll('.jh-case-tab').forEach(function (t) {
                    var isActive = t === tab;
                    t.style.borderBottomColor = isActive ? 'var(--forest)' : 'transparent';
                    t.style.color             = isActive ? 'var(--ink)'    : 'var(--ink-3)';
                    t.style.fontWeight        = isActive ? '600'           : '500';
                });
                // Save active tab in URL hash without scrolling
                var target = tab.getAttribute('data-bs-target');
                if (target) history.replaceState(null, '', target.replace('#tab-', '#'));
            });
        });

        // On load: restore tab from URL hash
        var hash = location.hash.replace('#', '');
        if (hash) {
            var target = document.querySelector('[data-bs-target="#tab-' + hash + '"]');
            if (target) {
                bootstrap.Tab.getOrCreateInstance(target).show();
            }
        }
    });

    // Mediation stepper
    function jhMstep(step) {
        [1,2,3].forEach(function(n) {
            var panel  = document.getElementById('mstep-panel-' + n);
            var circle = document.getElementById('mstep-circle-' + n);
            if (!panel) return;
            panel.style.display = (n === step) ? '' : 'none';
        });
    }

    // Consent radio button visual toggle
    function jhConsentStyle(radio) {
        var name = radio.name;
        document.querySelectorAll('input[name="' + name + '"]').forEach(function(r) {
            var span = r.nextElementSibling;
            span.style.background   = '';
            span.style.color        = '';
            span.style.borderColor  = 'var(--rule)';
        });
        var active = radio.nextElementSibling;
        if (radio.value === 'agreed')   { active.style.background = 'var(--forest)';   active.style.color = 'var(--cream)'; active.style.borderColor = 'var(--forest)'; }
        if (radio.value === 'declined') { active.style.background = 'var(--burgundy)'; active.style.color = '#fff';         active.style.borderColor = 'var(--burgundy)'; }
        if (radio.value === 'awaiting') { active.style.background = 'var(--ochre)';    active.style.color = '#fff';         active.style.borderColor = 'var(--ochre)'; }

        // Instantly unlock "Open mediation diary" if any party agreed
        var anyAgreed = document.querySelectorAll('input[value="agreed"]:checked').length > 0;
        var diaryBtn  = document.getElementById('jh-open-diary-btn');
        var lockNotice = document.getElementById('jh-consent-lock-notice');
        if (diaryBtn) {
            diaryBtn.style.background = anyAgreed ? 'var(--forest)' : 'var(--rule-2)';
            diaryBtn.style.color      = anyAgreed ? 'var(--cream)'  : 'var(--ink-4)';
            diaryBtn.style.cursor     = anyAgreed ? 'pointer'       : 'default';
            diaryBtn.disabled         = !anyAgreed;
        }
        if (lockNotice) lockNotice.style.display = anyAgreed ? 'none' : 'flex';
    }
    </script>

    {{-- ═══════════════════════════════════════════════════════════
         TAB CONTENT
         ═══════════════════════════════════════════════════════════ --}}
    <div class="tab-content">

        {{-- ─────────────────────────────────────────────────────
             TAB 1: OVERVIEW (NEW comprehensive two-column layout)
             ───────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ $activeTab === 'overview' ? 'show active' : '' }}" id="tab-overview" role="tabpanel">
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">

                {{-- ══ LEFT COLUMN ══ --}}
                <div style="display: flex; flex-direction: column; gap: 18px;">

                    {{-- Intake & Assessment --}}
                    <div class="card jh-anim-section" style="padding: 22px 26px; animation-delay: 0.1s;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                            <x-lucide-clipboard-list style="width: 15px; height: 15px; color: var(--forest);" />
                            <div class="label-cap" style="font-size: 10px;">{{ __('cases.intake_assessment') }}</div>
                            @if($canEdit)
                            <button onclick="jhOpenModal('edit-intake')" style="margin-left:auto; padding:4px 12px; font-size:11px; font-weight:600; background:none; border:1px solid var(--rule); color:var(--ink-3); cursor:pointer; font-family:inherit; display:inline-flex; align-items:center; gap:5px;">
                                <x-lucide-pencil style="width:11px;height:11px;" /> {{ __('common.edit') }}
                            </button>
                            @endif
                        </div>
                        <div>
                            <div class="jh-intake-row">
                                <div class="jh-intake-label">{{ __('cases.date_time_intake') }}</div>
                                <div class="jh-intake-value">{{ $case->intake_date->format('M d, Y') }} {{ $case->intake_time ?? '' }}</div>
                            </div>
                            <div class="jh-intake-row">
                                <div class="jh-intake-label">{{ __('cases.intake_mode') }}</div>
                                <div class="jh-intake-value">{{ $case->mode ?? '---' }}</div>
                            </div>
                            <div class="jh-intake-row">
                                <div class="jh-intake-label">{{ __('cases.referral_source') }}</div>
                                <div class="jh-intake-value">{{ $case->referral_source ?? '---' }}</div>
                            </div>
                            <div class="jh-intake-row">
                                <div class="jh-intake-label">{{ __('cases.preferred_language') }}</div>
                                <div class="jh-intake-value">{{ $case->language ?? '---' }}</div>
                            </div>
                            <div class="jh-intake-row">
                                <div class="jh-intake-label">{{ __('cases.primary_legal_issue') }}</div>
                                <div class="jh-intake-value">{{ $case->primary_issue ?? '---' }}</div>
                            </div>
                            <div class="jh-intake-row">
                                <div class="jh-intake-label">{{ __('cases.secondary_issue') }}</div>
                                <div class="jh-intake-value">{{ $case->secondary_issue ?? '---' }}</div>
                            </div>
                            <div class="jh-intake-row">
                                <div class="jh-intake-label">{{ __('cases.urgency') }}</div>
                                <div class="jh-intake-value" style="color: {{ $case->urgency->color() }};">{{ $case->urgency->value }}</div>
                            </div>
                            <div class="jh-intake-row">
                                <div class="jh-intake-label">{{ __('cases.consent_obtained') }}</div>
                                <div class="jh-intake-value">
                                    @if($case->consent)
                                        <span style="color: var(--moss);">
                                            <x-lucide-check-circle-2 style="width:12px;height:12px;display:inline;vertical-align:-1px;margin-right:4px;" />
                                            {!! __('cases.yes_consent_recorded') !!}
                                        </span>
                                    @else
                                        <span style="color: var(--burgundy);">
                                            <x-lucide-x-circle style="width:12px;height:12px;display:inline;vertical-align:-1px;margin-right:4px;" />
                                            No{{ $case->no_consent_reason ? ' — ' . $case->no_consent_reason : '' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="jh-intake-row">
                                <div class="jh-intake-label">{{ __('cases.recurring_client') }}</div>
                                <div class="jh-intake-value">
                                    @if($case->returning_client)
                                        <span style="color: var(--ochre);">
                                            <x-lucide-refresh-cw style="width:12px;height:12px;display:inline;vertical-align:-1px;margin-right:4px;" />
                                            {!! __('cases.yes_returning') !!}
                                        </span>
                                    @else
                                        <span style="color: var(--ink-4);">No</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Issue Description --}}
                        @if($case->issue_description)
                        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--rule-2);">
                            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 8px;">{{ __('cases.issue_description') }}</div>
                            <div style="font-size: 13px; color: var(--ink-2); line-height: 1.65; white-space: pre-wrap;">{{ $case->issue_description }}</div>
                        </div>
                        @endif
                    </div>

                    {{-- Safeguarding & Vulnerability --}}
                    <div class="card jh-anim-section" style="padding: 22px 26px; animation-delay: 0.18s;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                            <x-lucide-shield-check style="width: 15px; height: 15px; color: var(--burgundy);" />
                            <div class="label-cap" style="font-size: 10px;">{{ __('cases.safeguarding_vulnerability') }}</div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                            @php
                                $safeguardItems = [
                                    ['label' => 'GBV-Related',             'value' => $case->is_gbv,        'icon' => 'shield'],
                                    ['label' => 'Child-Related',           'value' => $case->is_child,      'icon' => 'baby'],
                                    ['label' => 'Disability',              'value' => $case->is_disability,  'icon' => 'heart-handshake'],
                                    ['label' => 'Minority',                'value' => $case->is_minority,    'icon' => 'flag'],
                                    ['label' => 'Underserved Population',  'value' => $case->is_underserved, 'icon' => 'users'],
                                ];
                            @endphp
                            @foreach($safeguardItems as $sg)
                            <div style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: {{ $sg['value'] ? 'rgba(138,46,29,0.04)' : 'var(--parchment)' }}; border: 1px solid {{ $sg['value'] ? 'rgba(138,46,29,0.15)' : 'var(--rule-2)' }};">
                                <x-dynamic-component :component="'lucide-' . $sg['icon']" style="width: 14px; height: 14px; color: {{ $sg['value'] ? 'var(--burgundy)' : 'var(--ink-4)' }};" />
                                <div>
                                    <div style="font-size: 12px; color: var(--ink-3);">{{ $sg['label'] }}</div>
                                    <div style="font-size: 13px; font-weight: 600; color: {{ $sg['value'] ? 'var(--burgundy)' : 'var(--ink-3)' }};">{{ $sg['value'] ? 'Yes' : 'No' }}</div>
                                </div>
                            </div>
                            @endforeach
                            <div style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: var(--parchment); border: 1px solid var(--rule-2);">
                                <x-lucide-alert-triangle style="width: 14px; height: 14px; color: {{ $case->risk->color() }};" />
                                <div>
                                    <div style="font-size: 12px; color: var(--ink-3);">{{ __('cases.immediate_risk_level') }}</div>
                                    <div style="font-size: 13px; font-weight: 600; color: {{ $case->risk->color() }};">{{ $case->risk->value }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- LAS CMS Litigation Data --}}
                    @if($cmsData)
                    @php
                        $fmtDate = function(?string $d): ?string {
                            if (!$d || str_starts_with($d, '0000')) return null;
                            try { return \Carbon\Carbon::parse($d)->format('d M Y'); } catch (\Exception $e) { return $d; }
                        };
                        $cmsApproval = $cmsData->caseApprovalStatus ?? 'Pending';
                        $isApproved  = $cmsApproval === 'Approved';
                        $isRejected  = $cmsApproval === 'Rejected';
                    @endphp
                    <div class="card jh-anim-section" style="padding: 22px 26px; animation-delay: 0.24s; border-left: 3px solid {{ $isApproved ? 'var(--moss)' : ($isRejected ? 'var(--burgundy)' : 'var(--ochre)') }};">

                        {{-- Header --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <x-lucide-gavel style="width: 15px; height: 15px; color: var(--burgundy);" />
                                <div class="label-cap" style="font-size: 10px;">LAS CMS · Litigation Record</div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                {{-- Approval status badge --}}
                                <span style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; letter-spacing: 0.04em;
                                    background: {{ $isApproved ? 'color-mix(in srgb, var(--moss) 15%, transparent)' : ($isRejected ? 'color-mix(in srgb, var(--burgundy) 15%, transparent)' : 'color-mix(in srgb, var(--ochre) 15%, transparent)') }};
                                    color: {{ $isApproved ? 'var(--moss)' : ($isRejected ? 'var(--burgundy)' : 'var(--ochre)') }};">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor; display: inline-block;"></span>
                                    {{ strtoupper($cmsApproval) }}
                                </span>
                                <span style="font-size: 10px; color: var(--ink-4); font-family: monospace;">ID #{{ $case->external_case_id }}</span>
                            </div>
                        </div>

                        @if($isRejected)
                        {{-- Rejection notice --}}
                        <div style="display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px; background: color-mix(in srgb, var(--burgundy) 8%, transparent); border: 1px solid color-mix(in srgb, var(--burgundy) 25%, transparent); border-radius: 4px;">
                            <x-lucide-circle-x style="width: 18px; height: 18px; color: var(--burgundy); flex-shrink: 0; margin-top: 1px;" />
                            <div>
                                <div style="font-size: 13px; font-weight: 700; color: var(--burgundy); margin-bottom: 4px;">Case Rejected in LAS CMS</div>
                                <div style="font-size: 12px; color: var(--ink-2); line-height: 1.5;">
                                    This case was reviewed by LAS CMS and marked as <strong>Rejected</strong>.
                                    The case will not proceed to litigation in the CMS system.
                                    @if($cmsData->currentCaseStatus) Current status: <strong>{{ $cmsData->currentCaseStatus }}</strong>. @endif
                                </div>
                            </div>
                        </div>

                        @else
                        {{-- Approved or Pending: show full litigation data --}}

                        @if(!$isApproved)
                        <div style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; background: color-mix(in srgb, var(--ochre) 10%, transparent); border: 1px solid color-mix(in srgb, var(--ochre) 30%, transparent); border-radius: 4px; margin-bottom: 14px; font-size: 12px; color: var(--ochre);">
                            <x-lucide-clock style="width: 14px; height: 14px; flex-shrink: 0;" />
                            Awaiting approval in LAS CMS — litigation details will appear once approved.
                        </div>
                        @endif

                        {{-- Highlight strip: Next Hearing + Status + Stage --}}
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 16px;">
                            @foreach([
                                ['label' => 'Next Hearing',   'value' => $fmtDate($cmsData->nextHearing) ?? '—', 'color' => 'var(--ochre)'],
                                ['label' => 'Current Status', 'value' => $cmsData->currentCaseStatus ?: '—',     'color' => 'var(--forest)'],
                                ['label' => 'Case Stage',     'value' => $cmsData->caseStage ?: '—',             'color' => 'var(--burgundy)'],
                            ] as $h)
                            <div style="padding: 10px 14px; background: var(--parchment); border: 1px solid var(--rule-2); border-top: 2px solid {{ $h['color'] }};">
                                <div style="font-size: 10px; color: var(--ink-4); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px;">{{ $h['label'] }}</div>
                                <div style="font-size: 13px; font-weight: 600; color: {{ $h['color'] }};">{{ $h['value'] }}</div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Full field grid --}}
                        @php
                        $cmsRows = [
                            ['label' => 'Case Number',      'value' => $cmsData->caseNumber,                         'icon' => 'hash'],
                            ['label' => 'FIR Number',       'value' => $cmsData->firNumber,                          'icon' => 'file-badge'],
                            ['label' => 'Police Station',   'value' => $cmsData->policeStation,                      'icon' => 'building-2'],
                            ['label' => 'Court Name',       'value' => $cmsData->courtName,                          'icon' => 'landmark'],
                            ['label' => 'Level of Court',   'value' => $cmsData->levelOfCourt,                       'icon' => 'layers'],
                            ['label' => 'Nature of Case',   'value' => $cmsData->natureOfCase,                       'icon' => 'scale'],
                            ['label' => 'Type of Case',     'value' => $cmsData->typeOfCase,                         'icon' => 'tag'],
                            ['label' => 'Main Category',    'value' => $cmsData->mainCaseCategory,                   'icon' => 'folder'],
                            ['label' => 'Filed Under Act',  'value' => $cmsData->caseFiledUnderAct,                  'icon' => 'book-open'],
                            ['label' => 'Assigned Lawyer',  'value' => $cmsData->lawyer1,                            'icon' => 'user'],
                            ['label' => 'Approval Date',    'value' => $fmtDate($cmsData->approvalDate),             'icon' => 'calendar-check'],
                            ['label' => 'Vakalatnama Date', 'value' => $fmtDate($cmsData->vakalatnamaSubmissionDate),'icon' => 'calendar'],
                            ['label' => 'Case File Date',   'value' => $fmtDate($cmsData->caseFileDate),             'icon' => 'calendar'],
                            ['label' => 'Case Decision',    'value' => $cmsData->caseDecision,                       'icon' => 'gavel'],
                            ['label' => 'Disposal Date',    'value' => $fmtDate($cmsData->caseDisposalDate),         'icon' => 'calendar-x'],
                            ['label' => 'CMS Unique No.',   'value' => $cmsData->UniqueNumber2,                      'icon' => 'fingerprint'],
                        ];
                        @endphp
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0; border: 1px solid var(--rule-2);">
                            @foreach($cmsRows as $row)
                            @if($row['value'])
                            <div style="display: flex; align-items: flex-start; gap: 10px; padding: 9px 14px; border-bottom: 1px solid var(--rule-2); {{ $loop->iteration % 2 === 0 ? 'border-left: 1px solid var(--rule-2);' : '' }}">
                                <x-dynamic-component :component="'lucide-' . $row['icon']" style="width: 13px; height: 13px; color: var(--ink-4); margin-top: 2px; flex-shrink: 0;" />
                                <div>
                                    <div style="font-size: 10px; color: var(--ink-4); text-transform: uppercase; letter-spacing: 0.05em;">{{ $row['label'] }}</div>
                                    <div style="font-size: 12.5px; font-weight: 500; color: var(--ink); margin-top: 1px; word-break: break-word;">{{ $row['value'] }}</div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                        @endif {{-- end rejected/approved --}}

                        <div style="margin-top: 10px; font-size: 10.5px; color: var(--ink-4); display: flex; align-items: center; gap: 5px;">
                            <x-lucide-refresh-cw style="width: 10px; height: 10px;" />
                            {{ __('cases.synced_from_cms') }}
                            @if($case->external_synced_at) · {{ \Carbon\Carbon::parse($case->external_synced_at)->format('d M Y, H:i') }} @endif
                        </div>
                    </div>
                    @endif

                    {{-- Activity Timeline --}}
                    <div class="card jh-anim-section" style="padding: 22px 26px; animation-delay: 0.26s;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <x-lucide-activity style="width: 15px; height: 15px; color: var(--forest);" />
                                <div class="label-cap" style="font-size: 10px;">{{ __('cases.activity_timeline') }}</div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span class="mono" style="font-size: 10px; color: var(--ink-4);">{{ __('cases.events_count', ['count' => $timeline->count()]) }}</span>
                                @if($canWrite && !$isResolved)
                                <button class="btn-ghost" style="font-size: 11px; padding: 4px 10px; display: inline-flex; align-items: center; gap: 5px;" onclick="jhOpenModal('log-encounter')">
                                    <x-lucide-plus style="width: 11px; height: 11px;" /> Log Encounter
                                </button>
                                @endif
                            </div>
                        </div>

                        @forelse($timeline as $event)
                        @php
                            $iconMap = [
                                'clipboard'       => 'clipboard',
                                'message-square'  => 'message-square',
                                'gavel'           => 'gavel',
                                'heart-handshake' => 'heart-handshake',
                                'file-text'       => 'file-text',
                                'alert-triangle'  => 'alert-triangle',
                                'star'            => 'star',
                                'send'            => 'send',
                                'check-circle-2'  => 'check-circle-2',
                                'x-circle'        => 'x-circle',
                                'arrow-right-left'=> 'arrow-right-left',
                            ];
                            $ico = $event['icon'];
                            $col = $event['color'];
                        @endphp
                        <div style="display: flex; align-items: flex-start; gap: 12px; padding: 11px 0; {{ !$loop->last ? 'border-bottom: 1px solid var(--rule-2);' : '' }}">

                            {{-- Icon dot --}}
                            <div style="display: flex; flex-direction: column; align-items: center; flex-shrink: 0;">
                                <div style="width: 30px; height: 30px; background: var(--parchment); border: 1px solid var(--rule); display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                    @if($ico === 'clipboard')         <x-lucide-clipboard       style="width:13px;height:13px;color:{{ $col }};" />
                                    @elseif($ico === 'message-square')<x-lucide-message-square  style="width:13px;height:13px;color:{{ $col }};" />
                                    @elseif($ico === 'gavel')         <x-lucide-gavel           style="width:13px;height:13px;color:{{ $col }};" />
                                    @elseif($ico === 'heart-handshake')<x-lucide-heart-handshake style="width:13px;height:13px;color:{{ $col }};" />
                                    @elseif($ico === 'file-text')     <x-lucide-file-text       style="width:13px;height:13px;color:{{ $col }};" />
                                    @elseif($ico === 'alert-triangle')<x-lucide-alert-triangle  style="width:13px;height:13px;color:{{ $col }};" />
                                    @elseif($ico === 'star')          <x-lucide-star            style="width:13px;height:13px;color:{{ $col }};" />
                                    @elseif($ico === 'send')          <x-lucide-send            style="width:13px;height:13px;color:{{ $col }};" />
                                    @elseif($ico === 'check-circle-2')<x-lucide-check-circle-2  style="width:13px;height:13px;color:{{ $col }};" />
                                    @elseif($ico === 'x-circle')      <x-lucide-x-circle        style="width:13px;height:13px;color:{{ $col }};" />
                                    @elseif($ico === 'database')      <x-lucide-database        style="width:13px;height:13px;color:{{ $col }};" />
                                    @elseif($ico === 'refresh-cw')    <x-lucide-refresh-cw      style="width:13px;height:13px;color:{{ $col }};" />
                                    @elseif($ico === 'scale')         <x-lucide-scale           style="width:13px;height:13px;color:{{ $col }};" />
                                    @else                             <x-lucide-arrow-right-left style="width:13px;height:13px;color:{{ $col }};" />
                                    @endif
                                </div>
                                @if(!$loop->last)
                                <div style="width: 1px; flex: 1; min-height: 12px; background: var(--rule-2); margin-top: 4px;"></div>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div style="flex: 1; min-width: 0; padding-bottom: 4px;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 3px; flex-wrap: wrap;">
                                    <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: {{ $col }};">{{ $event['label'] }}</span>
                                    <span class="mono" style="font-size: 10px; color: var(--ink-4);">{{ $event['at']->format('d M Y') }}@if($event['at']->format('H:i') !== '09:00') · {{ $event['at']->format('H:i') }}@endif</span>
                                </div>
                                @if($event['text'])
                                <div style="font-size: 12.5px; color: var(--ink-2); line-height: 1.5; word-break: break-word;">{{ $event['text'] }}</div>
                                @endif
                                @if($event['by'])
                                <div style="font-size: 11px; color: var(--ink-4); margin-top: 3px;">by {{ $event['by'] }}</div>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div style="font-size: 13px; color: var(--ink-4); font-style: italic;">{{ __('cases.no_activity_yet') }}</div>
                        @endforelse
                    </div>

                    {{-- Approval History (moved here from separate tab) --}}
                    @if($hasApprovalHistory)
                    <div class="card jh-anim-section" style="padding: 22px 26px; animation-delay: 0.34s;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                            <x-lucide-check-circle-2 style="width: 15px; height: 15px; color: var(--ochre);" />
                            <div class="label-cap" style="font-size: 10px;">{{ __('cases.approval_history') }}</div>
                        </div>
                        <div style="display: grid; grid-template-columns: 160px 1fr; gap: 8px; font-size: 13px;">
                            <div style="color: var(--ink-3);">{{ __('cases.pathway_manager') }}</div>
                            <div style="color: var(--ink-2); font-weight: 500;">{{ $case->pathway_manager ?? '---' }}</div>
                            <div style="color: var(--ink-3);">{{ __('cases.decision') }}</div>
                            <div style="color: var(--ink-2); font-weight: 500;">{{ ucfirst($case->approval_decision ?? 'N/A') }}</div>
                            @if($case->requested_at)
                            <div style="color: var(--ink-3);">{{ __('cases.requested') }}</div>
                            <div style="color: var(--ink-2);">{{ $case->requested_at->format('M d, Y H:i') }}</div>
                            @endif
                            @if($case->rejected_at)
                            <div style="color: var(--ink-3);">{{ __('cases.rejected_label') }}</div>
                            <div style="color: var(--ink-2);">{{ $case->rejected_at->format('M d, Y H:i') }} by {{ $case->rejected_by }}</div>
                            @endif
                            @if($case->rejection_reason)
                            <div style="color: var(--ink-3);">{{ __('cases.reason') }}</div>
                            <div style="color: var(--ink-2);">{{ $case->rejection_reason }}</div>
                            @endif
                        </div>
                    </div>
                    @endif

                </div>

                {{-- ══ RIGHT COLUMN ══ --}}
                <div style="display: flex; flex-direction: column; gap: 18px;">

                    {{-- Assigned Team --}}
                    <div class="card jh-anim-section" style="padding: 22px 26px; animation-delay: 0.12s;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                            <x-lucide-user-check style="width: 15px; height: 15px; color: var(--forest);" />
                            <div class="label-cap" style="font-size: 10px;">{{ __('cases.assigned_team') }}</div>
                        </div>
                        @if($case->assigned_to)
                        <div style="display: flex; align-items: center; gap: 14px; padding: 14px 16px; background: var(--parchment); border: 1px solid var(--rule);">
                            @php
                                $staffInitials = collect(explode(' ', $case->assigned_to))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('');
                                $assignedUser  = \App\Models\User::where('name', $case->assigned_to)->first();
                                $assignedTitle = $assignedUser ? ($assignedUser->designation ?: $assignedUser->role->label()) : 'Staff';
                            @endphp
                            <div style="width: 40px; height: 40px; background: var(--forest); color: var(--ochre-2); display: flex; align-items: center; justify-content: center; flex-shrink: 0; border-radius: 50;">
                                <span style="font-size: 14px; font-weight: 600;">{{ $staffInitials }}</span>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-size: 14px; font-weight: 600; color: var(--ink);">{{ $case->assigned_to }}</div>
                                <div style="font-size: 11px; color: var(--ink-3);">{{ __('cases.primary_staff') }} &mdash; {{ $assignedTitle }}</div>
                            </div>
                            <x-lucide-mail style="width: 15px; height: 15px; color: var(--ink-4); cursor: pointer;" />
                        </div>
                        @else
                        <div style="font-size: 13px; color: var(--ink-4); font-style: italic;">{{ __('cases.not_yet_assigned') }}</div>
                        @endif
                    </div>

                    {{-- Transfer History --}}
                    @if($case->transfers->isNotEmpty())
                    <div class="card jh-anim-section" style="padding: 22px 26px; animation-delay: 0.18s;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:14px;">
                            <x-lucide-arrow-right-left style="width:15px;height:15px;color:var(--forest);" />
                            <div class="label-cap" style="font-size:10px;">{{ __('cases.transfer_history') }}</div>
                        </div>
                        @foreach($case->transfers as $tr)
                        @php
                            $trColor = $tr->status === 'approved' ? 'var(--moss)' : ($tr->status === 'rejected' ? 'var(--burgundy)' : 'var(--ochre)');
                        @endphp
                        <div style="padding:10px 0; {{ !$loop->last ? 'border-bottom:1px solid var(--rule-2);' : '' }}">
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                                <span style="font-size:10px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;color:{{ $trColor }};background:{{ $trColor }}18;padding:2px 7px;border-radius:4px;">
                                    {{ ucfirst($tr->status) }}
                                </span>
                                <span style="font-size:11px;color:var(--ink-4);">{{ $tr->transfer_date->format('M d, Y') }}</span>
                            </div>
                            <div style="font-size:12px;color:var(--ink-2);">
                                <strong>{{ $tr->from_assignee }}</strong> → <strong>{{ $tr->to_assignee }}</strong>
                            </div>
                            <div style="font-size:11px;color:var(--ink-3);margin-top:3px;">{{ $tr->reason }}</div>
                            @if($tr->approval_note)
                            <div style="font-size:11px;color:var(--ink-4);margin-top:2px;font-style:italic;">{{ $tr->approval_note }}</div>
                            @endif
                            <div style="font-size:10px;color:var(--ink-4);margin-top:3px;">
                                Requested by {{ $tr->transferredBy->name }}
                                @if($tr->approvedBy) · {{ $tr->status === 'approved' ? 'Approved' : 'Rejected' }} by {{ $tr->approvedBy->name }} @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- LAS Core Services --}}
                    <div class="card jh-anim-section" style="padding: 22px 26px; animation-delay: 0.2s;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                            <x-lucide-layers style="width: 15px; height: 15px; color: var(--forest);" />
                            <div class="label-cap" style="font-size: 10px;">{{ __('cases.las_core_services') }}</div>
                        </div>
                        <div style="font-size: 11px; color: var(--ink-4); margin-bottom: 14px;">{{ __('cases.pillars_description') }}</div>
                        @php
                            $pillars = [
                                ['name' => 'Free Legal Advice',       'icon' => 'scale',          'active' => $pillarLegal],
                                ['name' => 'Mediation & ADR',         'icon' => 'handshake',      'active' => $pillarMediation],
                                ['name' => 'NADRA & Documentation',   'icon' => 'file-badge',     'active' => $pillarNadra],
                                ['name' => 'Representation in Court', 'icon' => 'landmark',       'active' => $pillarCourt],
                            ];
                        @endphp
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @foreach($pillars as $pillar)
                            <div style="display: flex; align-items: center; gap: 12px; padding: 10px 14px; background: {{ $pillar['active'] ? 'rgba(22,48,41,0.04)' : 'var(--paper)' }}; border: 1px solid {{ $pillar['active'] ? 'rgba(22,48,41,0.15)' : 'var(--rule-2)' }};">
                                <x-dynamic-component :component="'lucide-' . $pillar['icon']" style="width: 15px; height: 15px; color: {{ $pillar['active'] ? 'var(--forest)' : 'var(--ink-4)' }};" />
                                <div style="flex: 1; font-size: 12.5px; color: {{ $pillar['active'] ? 'var(--ink)' : 'var(--ink-4)' }}; font-weight: {{ $pillar['active'] ? '500' : '400' }};">{{ $pillar['name'] }}</div>
                                @if($pillar['active'])
                                    <span style="font-size: 9px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--moss); background: rgba(56,102,65,0.08); padding: 2px 8px;">{{ __('cases.delivered') }}</span>
                                @else
                                    <span style="font-size: 12px; color: var(--ink-4);">&mdash;</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Related Indicators --}}
                    <div class="card jh-anim-section" style="padding: 22px 26px; animation-delay: 0.28s;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                            <x-lucide-bar-chart-3 style="width: 15px; height: 15px; color: var(--forest);" />
                            <div class="label-cap" style="font-size: 10px;">{{ __('cases.related_indicators') }}</div>
                        </div>
                        @php
                            $indicators = [
                                ['code' => 'O1.1', 'label' => 'Pathway within 48hr',  'met' => $case->sla_met],
                                ['code' => 'O1.3', 'label' => 'Individual served',     'met' => true],
                                ['code' => 'OP1.2','label' => 'CMS data complete',     'met' => true],
                                ['code' => 'O1.5', 'label' => 'Mediation uptake',      'met' => $pillarMediation],
                            ];
                        @endphp
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            @foreach($indicators as $ind)
                            <div style="display: flex; align-items: center; gap: 10px; padding: 8px 0; {{ !$loop->last ? 'border-bottom: 1px solid var(--rule-2);' : '' }}">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $ind['met'] ? 'var(--moss)' : 'var(--rule)' }}; flex-shrink: 0;"></span>
                                <span class="mono" style="font-size: 10px; color: var(--ink-3); min-width: 44px;">{{ $ind['code'] }}</span>
                                <span style="font-size: 12.5px; color: {{ $ind['met'] ? 'var(--ink-2)' : 'var(--ink-4)' }};">{{ $ind['label'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Consent & Privacy --}}
                    <div class="card jh-anim-section" style="padding: 22px 26px; animation-delay: 0.36s;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                            <x-lucide-lock style="width: 15px; height: 15px; color: var(--forest);" />
                            <div class="label-cap" style="font-size: 10px;">{{ __('cases.consent_privacy') }}</div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                            @if($case->consent)
                                <x-lucide-check-circle-2 style="width: 16px; height: 16px; color: var(--moss);" />
                                <span style="font-size: 13px; font-weight: 500; color: var(--moss);">{{ __('cases.consent_recorded') }}</span>
                                <span class="mono" style="font-size: 10px; color: var(--ink-4);">{{ $case->intake_date->format('M d, Y') }}</span>
                            @else
                                <x-lucide-x-circle style="width: 16px; height: 16px; color: var(--burgundy);" />
                                <span style="font-size: 13px; font-weight: 500; color: var(--burgundy);">{{ __('cases.consent_not_obtained') }}</span>
                            @endif
                        </div>
                        <div style="font-size: 11.5px; color: var(--ink-3); line-height: 1.55; padding: 10px 14px; background: var(--parchment); border: 1px solid var(--rule-2);">
                            {{ __('cases.consent_description') }}
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────────
             TAB 2: PATHWAY
             ───────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ $activeTab === 'referrals' ? 'show active' : '' }}" id="tab-referrals" role="tabpanel">

            @php
                $isMediationCase = in_array($case->assigned_pathway, ['Mediation']);
                $parties         = $case->mediationParties;
                $diary           = $case->mediationDiary;
                $anyAgreed       = $parties->where('consent_status', 'agreed')->count() > 0;
                $activeMstep     = $mstep ?? 1;
            @endphp

            @if($isMediationCase)
            {{-- ── Mediation Stepper ── --}}
            <div style="margin-bottom: 28px;">

                {{-- Step progress bar --}}
                <div style="display: flex; align-items: center; gap: 0; margin-bottom: 24px;">
                    @php
                        $steps = [1 => 'Parties', 2 => 'Consent to mediate', 3 => 'Mediation diary'];
                    @endphp
                    @foreach($steps as $num => $label)
                    <div style="display: flex; align-items: center; flex: 1;">
                        <button onclick="jhMstep({{ $num }})"
                            id="mstep-btn-{{ $num }}"
                            style="display: flex; align-items: center; gap: 10px; background: none; border: none; cursor: pointer; padding: 0; font-family: inherit;"
                            @if($num === 3 && !$anyAgreed) disabled title="Locked until a party agrees" @endif>
                            <div id="mstep-circle-{{ $num }}"
                                style="width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; transition: all 0.2s;
                                {{ $num < $activeMstep ? 'background: var(--moss); color: var(--cream);' : ($num == $activeMstep ? 'background: var(--forest); color: var(--cream);' : ($num === 3 && !$anyAgreed ? 'background: var(--rule-2); color: var(--ink-4);' : 'background: var(--rule-2); color: var(--ink-3);')) }}">
                                @if($num < $activeMstep)
                                <x-lucide-check style="width: 14px; height: 14px;" />
                                @elseif($num === 3 && !$anyAgreed)
                                <x-lucide-lock style="width: 12px; height: 12px;" />
                                @else
                                {{ $num }}
                                @endif
                            </div>
                            <span style="font-size: 13px; font-weight: {{ $num == $activeMstep ? '600' : '400' }}; color: {{ $num == $activeMstep ? 'var(--ink)' : 'var(--ink-3)' }};">{{ $label }}</span>
                        </button>
                        @if($num < 3)
                        <div style="flex: 1; height: 1px; background: {{ $num < $activeMstep ? 'var(--moss)' : 'var(--rule)' }}; margin: 0 12px;"></div>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- ── STEP 1: Parties ── --}}
                <div id="mstep-panel-1" style="{{ $activeMstep == 1 ? '' : 'display:none;' }}">
                    <div class="card" style="padding: 26px 30px; max-width: 640px;">
                        <h3 class="serif" style="font-size: 20px; font-weight: 500; margin: 0 0 6px;">Who are the two parties in this mediation?</h3>
                        <p style="font-size: 13px; color: var(--ink-3); margin: 0 0 22px; line-height: 1.5;">Record both parties you'll invite to mediation.</p>

                        {{-- Mediation Type --}}
                        @php $mediationType = $case->meta['mediation_type'] ?? null; @endphp
                        <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--rule-2);">
                            <label style="font-size: 11px; font-weight: 700; color: var(--ink-3); display: block; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.06em;">
                                Mediation Type <span style="color: var(--burgundy);">*</span>
                            </label>
                            @if($mediationType)
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="font-size:13px; font-weight:600; color:var(--forest); padding:5px 14px; background:rgba(47,122,77,0.08); border:1px solid rgba(47,122,77,0.25);">
                                    {{ $mediationType }}
                                </span>
                                <form method="POST" action="{{ route('mediation.type.update', $case) }}" style="display:inline;">
                                    @csrf
                                    <select name="mediation_type" onchange="this.form.submit()" class="inp" style="font-size:12px; padding:4px 8px;">
                                        <option value="">— Change —</option>
                                        <option value="Court Annexed Mediation">Court Annexed Mediation</option>
                                        <option value="Private Mediation">Private Mediation</option>
                                    </select>
                                </form>
                            </div>
                            @else
                            <form method="POST" action="{{ route('mediation.type.update', $case) }}">
                                @csrf
                                <div style="display:flex; gap:10px; align-items:center;">
                                    <select name="mediation_type" required class="inp" style="font-size:13px; min-width:260px;">
                                        <option value="">— Select mediation type —</option>
                                        <option value="Court Annexed Mediation">Court Annexed Mediation</option>
                                        <option value="Private Mediation">Private Mediation</option>
                                    </select>
                                    <button type="submit" style="padding:8px 18px; background:var(--forest); color:var(--cream); border:none; font-size:13px; font-family:inherit; font-weight:600; cursor:pointer; white-space:nowrap;">
                                        Set Type
                                    </button>
                                </div>
                            </form>
                            @endif
                        </div>

                        @if($parties->count())
                        {{-- Saved parties list --}}
                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 18px;">
                            @foreach($parties as $i => $party)
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: var(--paper); border: 1px solid var(--rule); border-radius: 4px;">
                                <div>
                                    <div style="font-size: 11px; font-weight: 700; color: var(--ink-4); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">Party {{ $i + 1 }}</div>
                                    <div style="font-size: 13px; font-weight: 600; color: var(--ink);">{{ $party->name }}</div>
                                    <div style="font-size: 12px; color: var(--ink-3); margin-top: 2px;">{{ $party->role }}{{ $party->phone ? ' · ' . $party->phone : '' }}</div>
                                </div>
                                <form method="POST" action="{{ route('mediation.party.destroy', [$case, $party]) }}" onsubmit="return confirm('Remove this party?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: var(--ink-4); cursor: pointer; padding: 4px 8px; font-size: 18px; line-height: 1;">×</button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                        @else
                        {{-- Two-party entry form --}}
                        <form method="POST" action="{{ route('mediation.party.store', $case) }}">
                            @csrf
                            @php $partyLabels = ['Party 1', 'Party 2']; @endphp
                            @foreach($partyLabels as $i => $partyLabel)
                            <div style="{{ $i === 0 ? 'margin-bottom: 22px; padding-bottom: 22px; border-bottom: 1px solid var(--rule-2);' : '' }}">
                                <div style="font-size: 11px; font-weight: 700; color: var(--ink-3); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 12px;">{{ $partyLabel }}</div>
                                <input type="hidden" name="parties[{{ $i }}][role]" value="{{ $i === 0 ? 'Applicant' : 'Respondent' }}">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                                    <div>
                                        <label style="font-size: 11px; font-weight: 600; color: var(--ink-3); display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Full name</label>
                                        <input type="text" name="parties[{{ $i }}][name]" placeholder="Party name" required class="inp"
                                            style="width:100%; font-size:13px; box-sizing:border-box;{{ $i === 0 ? ' background:#f0efe9; cursor:default;' : '' }}"
                                            value="{{ $i === 0 ? $case->name : '' }}" {{ $i === 0 ? 'readonly' : '' }} />
                                    </div>
                                    <div>
                                        <label style="font-size: 11px; font-weight: 600; color: var(--ink-3); display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Phone</label>
                                        <input type="tel" name="parties[{{ $i }}][phone]" placeholder="+92 ..." class="inp"
                                            style="width:100%; font-size:13px; box-sizing:border-box;{{ $i === 0 ? ' background:#f0efe9; cursor:default;' : '' }}"
                                            value="{{ $i === 0 ? $case->primary_contact : '' }}" {{ $i === 0 ? 'readonly' : '' }}
                                            oninput="this.value=this.value.replace(/[^0-9+\-\s()]/g,'')" />
                                    </div>
                                    <div style="grid-column: span 2;">
                                        <label style="font-size: 11px; font-weight: 600; color: var(--ink-3); display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Note (optional)</label>
                                        <input type="text" name="parties[{{ $i }}][note]" placeholder="How to reach them..." class="inp" style="width:100%; font-size:13px; box-sizing:border-box;" />
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            <div style="margin-top: 20px;">
                                <button type="submit" style="padding: 10px 24px; background: var(--forest); color: var(--cream); border: none; font-size: 13px; font-family: inherit; font-weight: 600; cursor: pointer;">Save Both Parties</button>
                            </div>
                        </form>
                        @endif

                        <div style="margin-top: 22px; padding-top: 16px; border-top: 1px solid var(--rule-2);">
                            <button onclick="jhMstep(2)" {{ $parties->count() ? '' : 'disabled' }}
                                style="padding: 10px 28px; background: var(--forest); color: var(--cream); border: none; font-size: 13px; font-family: inherit; font-weight: 600; cursor: pointer; {{ !$parties->count() ? 'opacity: 0.4;' : '' }}">
                                Continue
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ── STEP 2: Consent ── --}}
                <div id="mstep-panel-2" style="{{ $activeMstep == 2 ? '' : 'display:none;' }}">
                    <div class="card" style="padding: 26px 30px; max-width: 640px;">
                        <h3 class="serif" style="font-size: 20px; font-weight: 500; margin: 0 0 6px;">Have the parties agreed to mediation?</h3>
                        <p style="font-size: 13px; color: var(--ink-3); margin: 0 0 22px; line-height: 1.5;">Contact each party and record their answer. The mediation diary opens once at least one party agrees.</p>

                        <form method="POST" action="{{ route('mediation.consent.update', $case) }}">
                            @csrf
                            <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 18px;">
                                @foreach($parties as $pIdx => $party)
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; background: var(--paper); border: 1px solid var(--rule); border-radius: 4px;">
                                    <div>
                                        <div style="font-size: 13px; font-weight: 600; color: var(--ink);">{{ $party->name }}</div>
                                        <div style="font-size: 12px; color: var(--ink-3); margin-top: 1px;">{{ $party->role }}{{ $party->phone ? ' · ' . $party->phone : '' }}</div>
                                    </div>
                                    @if($pIdx === 0)
                                    {{-- Party 1 (Applicant) — auto-agreed, read-only --}}
                                    <input type="hidden" name="consent[{{ $party->id }}]" value="agreed">
                                    <span style="display:inline-block; padding:6px 14px; font-size:12px; font-weight:600; background:var(--forest); color:var(--cream); border:1.5px solid var(--forest); user-select:none;">Agreed</span>
                                    @else
                                    <div style="display: flex; gap: 6px;">
                                        @foreach(['agreed' => 'Agreed', 'declined' => 'Declined', 'awaiting' => 'Awaiting'] as $val => $lbl)
                                        <label style="cursor: pointer;">
                                            <input type="radio" name="consent[{{ $party->id }}]" value="{{ $val }}"
                                                {{ $party->consent_status === $val ? 'checked' : '' }}
                                                style="display:none;" onchange="jhConsentStyle(this)">
                                            <span class="consent-btn consent-{{ $val }} {{ $party->consent_status === $val ? 'consent-active-'.$val : '' }}"
                                                style="display: inline-block; padding: 6px 14px; font-size: 12px; font-weight: 500; border: 1.5px solid var(--rule); cursor: pointer; user-select: none;
                                                {{ $party->consent_status === $val ? ($val === 'agreed' ? 'background: var(--forest); color: var(--cream); border-color: var(--forest);' : ($val === 'declined' ? 'background: var(--burgundy); color: #fff; border-color: var(--burgundy);' : 'background: var(--ochre); color: #fff; border-color: var(--ochre);')) : '' }}">
                                                {{ $lbl }}
                                            </span>
                                        </label>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>

                            <div id="jh-consent-lock-notice" style="padding: 12px 16px; background: var(--ochre-tint); border: 1px solid rgba(196,130,57,0.25); color: var(--ochre); font-size: 12.5px; margin-bottom: 16px; display: {{ $anyAgreed ? 'none' : 'flex' }}; align-items: center; gap: 8px;">
                                <x-lucide-lock style="width: 13px; height: 13px; flex-shrink: 0;" />
                                Mediation stays locked until a party agrees.
                            </div>

                            <div style="display: flex; gap: 10px; align-items: center;">
                                <button type="button" onclick="jhMstep(1)" style="padding: 9px 20px; background: none; border: 1.5px solid var(--rule); color: var(--ink-2); font-size: 13px; font-family: inherit; cursor: pointer;">Back</button>
                                <button id="jh-open-diary-btn" type="submit" {{ $anyAgreed ? '' : 'disabled' }} style="padding: 9px 20px; background: {{ $anyAgreed ? 'var(--forest)' : 'var(--rule-2)' }}; color: {{ $anyAgreed ? 'var(--cream)' : 'var(--ink-4)' }}; border: none; font-size: 13px; font-family: inherit; font-weight: 600; cursor: {{ $anyAgreed ? 'pointer' : 'default' }};">
                                    Open mediation diary
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ── STEP 3: Mediation Diary ── --}}
                <div id="mstep-panel-3" style="{{ $activeMstep == 3 ? '' : 'display:none;' }}">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start;">

                        {{-- Add diary entry form --}}
                        <div class="card" style="padding: 26px 30px;">
                            <h3 class="serif" style="font-size: 20px; font-weight: 500; margin: 0 0 6px;">Mediation diary</h3>
                            <p style="font-size: 13px; color: var(--ink-3); margin: 0 0 20px; line-height: 1.5;">Log each session and set the next date.</p>

                            <form method="POST" action="{{ route('mediation.diary.store', $case) }}">
                                @csrf
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                                    <div>
                                        <label style="font-size: 11px; font-weight: 600; color: var(--ink-3); display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Date</label>
                                        <input type="date" name="session_date" value="{{ date('Y-m-d') }}" required class="inp" style="width: 100%; font-size: 13px; box-sizing: border-box;" />
                                    </div>
                                    <div>
                                        <label style="font-size: 11px; font-weight: 600; color: var(--ink-3); display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Next session (optional)</label>
                                        <input type="date" name="next_session_date" class="inp" style="width: 100%; font-size: 13px; box-sizing: border-box;" />
                                    </div>
                                </div>
                                <div style="margin-bottom: 14px;">
                                    <label style="font-size: 11px; font-weight: 600; color: var(--ink-3); display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">What happened</label>
                                    <textarea name="what_happened" rows="4" required placeholder="Outcome of the session, points agreed, follow-ups." class="inp" style="width: 100%; font-size: 13px; resize: vertical; box-sizing: border-box; line-height: 1.5;"></textarea>
                                </div>
                                <div style="margin-bottom: 20px;">
                                    <label style="font-size: 11px; font-weight: 600; color: var(--ink-3); display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Note for next session (optional)</label>
                                    <input type="text" name="note_for_next_session" placeholder="What to prepare or confirm." class="inp" style="width: 100%; font-size: 13px; box-sizing: border-box;" />
                                </div>
                                <div style="display: flex; gap: 10px;">
                                    <button type="submit" style="padding: 10px 24px; background: var(--forest); color: var(--cream); border: none; font-size: 13px; font-family: inherit; font-weight: 600; cursor: pointer;">Add diary entry</button>
                                    <button type="button" onclick="jhMstep(2)" style="padding: 10px 20px; background: none; border: 1.5px solid var(--rule); color: var(--ink-2); font-size: 13px; font-family: inherit; cursor: pointer;">Back</button>
                                </div>
                            </form>
                        </div>

                        {{-- Diary history --}}
                        <div>
                            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 12px;">Session history ({{ $diary->count() }})</div>
                            @forelse($diary as $entry)
                            <div class="card" style="padding: 16px 20px; margin-bottom: 10px; border-left: 3px solid var(--moss);">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                    <span class="mono" style="font-size: 12px; font-weight: 600; color: var(--forest);">{{ $entry->session_date->format('d M Y') }}</span>
                                    @if($entry->next_session_date)
                                    <span style="font-size: 11px; color: var(--ochre);">Next: {{ $entry->next_session_date->format('d M Y') }}</span>
                                    @endif
                                </div>
                                <div style="font-size: 13px; color: var(--ink-2); line-height: 1.55; margin-bottom: 6px;">{{ $entry->what_happened }}</div>
                                @if($entry->note_for_next_session)
                                <div style="font-size: 11.5px; color: var(--ink-3); font-style: italic; padding: 6px 10px; background: var(--rule-2); margin-top: 8px;">{{ $entry->note_for_next_session }}</div>
                                @endif
                                <div style="font-size: 10.5px; color: var(--ink-4); margin-top: 8px;">Logged by {{ $entry->logged_by ?? 'unknown' }}</div>
                            </div>
                            @empty
                            <div style="padding: 24px; text-align: center; color: var(--ink-4); font-size: 13px; border: 1px dashed var(--rule); border-radius: 4px;">No diary entries yet. Add the first session above.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>{{-- end mediation stepper --}}

            <hr style="border: none; border-top: 1px solid var(--rule); margin: 8px 0 22px;">
            @endif

            @php
                $isReferralCase = in_array($case->assigned_pathway, [
                    'Government Department / Public Institution',
                    'Civil Society / NGO / CSO / NPO',
                    'ADR / Dispute Resolution Support',
                    'Referred',
                    'Referral',
                    'Other',
                ]);
            @endphp

            @if($isReferralCase)
            {{-- ── Referral Tracking ── --}}
            <div style="margin-bottom: 20px;">

                {{-- Section header + "New Referral" button --}}
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <x-lucide-share-2 style="width: 15px; height: 15px; color: var(--forest);" />
                        <span class="label-cap" style="font-size: 10px;">Referral Tracking</span>
                        <span class="mono" style="font-size: 11px; color: var(--ink-4);">{{ $caseReferrals->count() }} {{ Str::plural('referral', $caseReferrals->count()) }}</span>
                    </div>
                    @if($canWrite && !$isResolved)
                    <button onclick="jhRefToggleCreate(this)"
                        style="display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; background: var(--forest); color: var(--cream); border: none; font-size: 12px; font-family: inherit; font-weight: 600; cursor: pointer;">
                        <x-lucide-plus style="width: 12px; height: 12px;" /> New Referral
                    </button>
                    @endif
                </div>

                {{-- Create Referral Form (collapsed by default if referrals exist) --}}
                @if($canWrite && !$isResolved)
                <div id="jh-ref-create-form" style="{{ $caseReferrals->isEmpty() ? '' : 'display:none;' }} margin-bottom: 20px; padding: 20px 22px; background: var(--parchment); border: 1px solid var(--rule-2);">
                    <div class="label-cap" style="font-size: 10px; margin-bottom: 14px;">Create New Referral</div>
                    <form method="POST" action="{{ route('cases.referral.store', $case) }}">
                        @csrf
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                            <div>
                                <label class="jh-field-label">Referred To <span style="color:var(--burgundy)">*</span></label>
                                @php
                                    // Only auto-fill with actual partner/org names, not pathway categories
                                    $autoReferredTo = $case->pathway_govt_dept
                                        ?? $case->pathway_ngo_name
                                        ?? (! in_array($case->pathway_specific, [
                                               null, '', 'Justice Hub Lawyer',
                                               'Justice Hub Accredited Mediator', 'Other',
                                           ]) ? $case->pathway_specific : null)
                                        ?? '';
                                @endphp
                                <input type="text" name="referred_to" required placeholder="Organisation / partner name"
                                    value="{{ old('referred_to', $autoReferredTo) }}"
                                    {{ $autoReferredTo ? 'readonly' : '' }}
                                    class="inp" style="width:100%; font-size:13px; box-sizing:border-box; {{ $autoReferredTo ? 'background:var(--paper); color:var(--ink-3); cursor:default;' : '' }}" />
                            </div>
                            <div>
                                <label class="jh-field-label">Date Referred <span style="color:var(--burgundy)">*</span></label>
                                <input type="date" name="referral_date" required value="{{ now()->toDateString() }}"
                                    class="inp" style="width:100%; font-size:13px; box-sizing:border-box;" />
                            </div>
                            <div>
                                <label class="jh-field-label">Referred By</label>
                                <input type="text" name="referred_by" value="{{ auth()->user()->name }}"
                                    class="inp" style="width:100%; font-size:13px; box-sizing:border-box;" />
                            </div>
                            <div>
                                <label class="jh-field-label">Filing Status</label>
                                <select name="filing_status" id="ref-filing-status" onchange="jhRefFilingToggle(this.value)"
                                    class="inp" style="width:100%; font-size:13px; box-sizing:border-box;">
                                    <option value="">— Select —</option>
                                    <option value="Filed">Filed</option>
                                    <option value="Not Filed">Not Filed</option>
                                </select>
                            </div>
                            {{-- Filed: tracking number --}}
                            <div id="ref-tracking-box" style="display:none; grid-column: 1 / -1;">
                                <label class="jh-field-label">Tracking Number</label>
                                <input type="text" name="tracking_number" placeholder="Enter tracking / reference number"
                                    class="inp" style="width:100%; font-size:13px; box-sizing:border-box;" />
                            </div>
                            {{-- Not Filed: justification --}}
                            <div id="ref-justification-box" style="display:none; grid-column: 1 / -1;">
                                <label class="jh-field-label">Justification for Not Filing</label>
                                <textarea name="filing_justification" rows="2" placeholder="Explain why this referral was not filed…"
                                    class="inp" style="width:100%; font-size:13px; box-sizing:border-box; resize:vertical;"></textarea>
                            </div>
                            <div style="grid-column: 1 / -1;">
                                <label class="jh-field-label">Reason for Referral</label>
                                <textarea name="reason" rows="2" placeholder="Briefly describe why this referral is being made."
                                    class="inp" style="width:100%; font-size:13px; box-sizing:border-box; resize:vertical;">{{ old('reason', $case->issue_description) }}</textarea>
                            </div>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button type="submit" style="padding:8px 18px; background:var(--forest); color:var(--cream); border:none; font-size:13px; font-family:inherit; font-weight:600; cursor:pointer;">Create Referral</button>
                            <button type="button" onclick="jhRefToggleCreate(null)"
                                style="padding:8px 14px; background:none; border:1px solid var(--rule); color:var(--ink-3); font-size:13px; font-family:inherit; cursor:pointer;">Cancel</button>
                        </div>
                    </form>
                </div>
                @endif

                {{-- No referrals state --}}
                @if($caseReferrals->isEmpty())
                <x-empty-state icon="share-2" message="No referrals logged yet. Use the button above to create one." />
                @endif

                {{-- Each referral card --}}
                @foreach($caseReferrals as $refIdx => $ref)
                @php
                    $refId    = $ref->id;
                    $isClosed = $ref->isClosed();
                    $statusColor = match($ref->status) {
                        'Closed'  => 'var(--ink-3)',
                        'Active'  => 'var(--forest)',
                        default   => 'var(--ochre)',
                    };
                    $statusBg = match($ref->status) {
                        'Closed'  => 'rgba(0,0,0,0.05)',
                        'Active'  => 'rgba(22,48,41,0.08)',
                        default   => 'rgba(184,115,25,0.08)',
                    };
                @endphp
                <div class="card" style="margin-bottom: 16px; padding: 0; overflow: hidden; {{ $isClosed ? 'opacity:0.82;' : '' }}">

                    {{-- Card header --}}
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 20px; background:var(--parchment); border-bottom:1px solid var(--rule-2);">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <x-lucide-building-2 style="width:14px; height:14px; color:var(--forest);" />
                            <span style="font-size:14px; font-weight:600; color:var(--ink);">{{ $ref->referred_to }}</span>
                            <span class="mono" style="font-size:11px; color:var(--ink-4);">{{ $ref->referral_date->format('d M Y') }}</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="padding:3px 10px; font-size:11px; font-weight:700; color:{{ $statusColor }}; background:{{ $statusBg }};">{{ $ref->status }}</span>
                            @if($canWrite && !$isResolved)
                            <form method="POST" action="{{ route('cases.referral.destroy', [$case, $ref]) }}" style="display:inline;"
                                onsubmit="return confirm('Delete this referral and all its data?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Delete referral"
                                    style="padding:3px 8px; background:none; border:1px solid var(--rule); color:var(--ink-4); cursor:pointer; font-size:11px; line-height:1.4; font-family:inherit;">×</button>
                            </form>
                            @endif
                        </div>
                    </div>

                    <div style="padding: 0 20px 20px;">

                        {{-- ── SECTION 1: Referred ── --}}
                        <div style="padding-top: 18px; padding-bottom: 16px; border-bottom: 1px solid var(--rule-2);">
                            <div class="label-cap" style="font-size: 9.5px; color: var(--ink-4); margin-bottom: 10px; display:flex; align-items:center; gap:6px;">
                                <x-lucide-arrow-up-right style="width:11px;height:11px;" /> Referred
                            </div>
                            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:14px;">
                                <div>
                                    <div class="jh-intake-label">Date Referred</div>
                                    <div class="jh-intake-value">{{ $ref->referral_date->format('d M Y') }}</div>
                                </div>
                                <div>
                                    <div class="jh-intake-label">Pathway</div>
                                    <div class="jh-intake-value">{{ $case->assigned_pathway ?? '---' }}</div>
                                </div>
                                <div>
                                    <div class="jh-intake-label">Referred By</div>
                                    <div class="jh-intake-value">{{ $ref->referred_by ?? '---' }}</div>
                                </div>
                                @if($ref->filing_status)
                                <div>
                                    <div class="jh-intake-label">Filing Status</div>
                                    <div class="jh-intake-value">
                                        <span style="padding:2px 8px; font-size:11px; font-weight:600;
                                            background:{{ $ref->filing_status === 'Filed' ? 'rgba(22,48,41,0.08)' : 'rgba(138,46,29,0.08)' }};
                                            color:{{ $ref->filing_status === 'Filed' ? 'var(--forest)' : 'var(--burgundy)' }};">
                                            {{ $ref->filing_status }}
                                        </span>
                                    </div>
                                </div>
                                @endif
                                @if($ref->tracking_number)
                                <div>
                                    <div class="jh-intake-label">Tracking Number</div>
                                    <div class="jh-intake-value mono">{{ $ref->tracking_number }}</div>
                                </div>
                                @endif
                                @if($ref->reason)
                                <div style="grid-column: 1 / -1;">
                                    <div class="jh-intake-label">Reason</div>
                                    <div style="font-size:13px; color:var(--ink-2); line-height:1.55;">{{ $ref->reason }}</div>
                                </div>
                                @endif
                                @if($ref->filing_justification)
                                <div style="grid-column: 1 / -1;">
                                    <div class="jh-intake-label">Justification for Not Filing</div>
                                    <div style="font-size:13px; color:var(--ink-2); line-height:1.55;">{{ $ref->filing_justification }}</div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- ── SECTION 2: Focal Person ── --}}
                        <div style="padding-top: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--rule-2);">
                            <div class="label-cap" style="font-size: 9.5px; color: var(--ink-4); margin-bottom: 10px; display:flex; align-items:center; gap:6px;">
                                <x-lucide-user style="width:11px;height:11px;" /> Focal Person
                            </div>
                            @if($ref->focal_person_name || ($canWrite && !$isResolved && !$isClosed))
                            <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:10px; margin-bottom:{{ ($canWrite && !$isResolved && !$isClosed) ? '12px' : '0' }};">
                                @if($ref->focal_person_name || $ref->focal_person_designation || $ref->focal_person_phone || $ref->focal_person_email)
                                <div>
                                    <div class="jh-intake-label">Name</div>
                                    <div class="jh-intake-value">{{ $ref->focal_person_name ?: '---' }}</div>
                                </div>
                                <div>
                                    <div class="jh-intake-label">Designation</div>
                                    <div class="jh-intake-value">{{ $ref->focal_person_designation ?: '---' }}</div>
                                </div>
                                <div>
                                    <div class="jh-intake-label">Phone</div>
                                    <div class="jh-intake-value">{{ $ref->focal_person_phone ?: '---' }}</div>
                                </div>
                                <div>
                                    <div class="jh-intake-label">Email</div>
                                    <div class="jh-intake-value" style="word-break:break-all;">{{ $ref->focal_person_email ?: '---' }}</div>
                                </div>
                                @endif
                            </div>
                            @endif
                            @if($canWrite && !$isResolved && !$isClosed)
                            <details style="margin-top: {{ $ref->focal_person_name ? '4px' : '0' }};">
                                <summary style="font-size:12px; color:var(--forest); cursor:pointer; list-style:none; display:inline-flex; align-items:center; gap:5px;">
                                    <x-lucide-pencil style="width:11px;height:11px;" />
                                    {{ $ref->focal_person_name ? 'Edit focal person' : 'Add focal person' }}
                                </summary>
                                <form method="POST" action="{{ route('cases.referral.focal', [$case, $ref]) }}" style="margin-top:10px;">
                                    @csrf @method('PATCH')
                                    <div style="display:grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap:10px; margin-bottom:10px;">
                                        <div>
                                            <label class="jh-field-label">Name</label>
                                            <input type="text" name="focal_person_name" value="{{ $ref->focal_person_name }}"
                                                placeholder="Full name" class="inp" style="width:100%; font-size:13px; box-sizing:border-box;" />
                                        </div>
                                        <div>
                                            <label class="jh-field-label">Designation</label>
                                            <input type="text" name="focal_person_designation" value="{{ $ref->focal_person_designation }}"
                                                placeholder="Title / role" class="inp" style="width:100%; font-size:13px; box-sizing:border-box;" />
                                        </div>
                                        <div>
                                            <label class="jh-field-label">Phone</label>
                                            <input type="tel" name="focal_person_phone" value="{{ $ref->focal_person_phone }}"
                                                placeholder="+92..." pattern="[0-9+\-\s()]+" title="Numbers only"
                                                class="inp" style="width:100%; font-size:13px; box-sizing:border-box;"
                                                oninput="this.value=this.value.replace(/[^0-9+\-\s()]/g,'')" />
                                        </div>
                                        <div>
                                            <label class="jh-field-label">Email</label>
                                            <input type="email" name="focal_person_email" value="{{ $ref->focal_person_email }}"
                                                placeholder="email@org.pk" class="inp" style="width:100%; font-size:13px; box-sizing:border-box;" />
                                        </div>
                                    </div>
                                    <button type="submit"
                                        style="padding:7px 16px; background:var(--forest); color:var(--cream); border:none; font-size:12px; font-family:inherit; font-weight:600; cursor:pointer;">Save Focal Person</button>
                                </form>
                            </details>
                            @elseif(!$ref->focal_person_name)
                            <span style="font-size:12px; color:var(--ink-4); font-style:italic;">No focal person on record.</span>
                            @endif
                        </div>

                        {{-- ── SECTION 3: Letter Sent ── --}}
                        <div style="padding-top: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--rule-2);">
                            <div class="label-cap" style="font-size: 9.5px; color: var(--ink-4); margin-bottom: 10px; display:flex; align-items:center; gap:6px;">
                                <x-lucide-mail style="width:11px;height:11px;" /> Letter Sent
                                <span class="mono" style="font-size:10px; color:var(--ink-4); font-weight:400; text-transform:none; letter-spacing:0;">{{ $ref->letters->count() }} {{ Str::plural('letter', $ref->letters->count()) }}</span>
                            </div>

                            {{-- Letters list --}}
                            @if($ref->letters->count())
                            <div style="display:flex; flex-direction:column; gap:6px; margin-bottom:12px;">
                                @foreach($ref->letters as $letter)
                                <div style="display:flex; align-items:flex-start; gap:12px; padding:10px 12px; background:var(--paper); border:1px solid var(--rule-2);">
                                    <x-lucide-file-text style="width:13px;height:13px; color:var(--ink-4); flex-shrink:0; margin-top:2px;" />
                                    <div style="flex:1; min-width:0;">
                                        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                            @if($letter->our_ref)
                                            <span style="font-size:12px; font-weight:600; color:var(--ink);">{{ $letter->our_ref }}</span>
                                            @endif
                                            <span class="mono" style="font-size:11px; color:var(--ink-4);">{{ $letter->letter_date->format('d M Y') }}</span>
                                            @if($letter->logged_by)
                                            <span style="font-size:11px; color:var(--ink-4);">by {{ $letter->logged_by }}</span>
                                            @endif
                                            @if($letter->file_path)
                                            <a href="{{ Storage::url($letter->file_path) }}" target="_blank"
                                                style="display:inline-flex; align-items:center; gap:4px; font-size:11px; color:var(--forest); text-decoration:none; padding:2px 7px; border:1px solid var(--rule); background:var(--parchment);">
                                                <x-lucide-paperclip style="width:10px;height:10px;" />
                                                {{ $letter->file_name ?? 'attachment' }}
                                            </a>
                                            @endif
                                        </div>
                                        @if($letter->note)
                                        <div style="font-size:12px; color:var(--ink-3); margin-top:4px; line-height:1.45;">{{ $letter->note }}</div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            @if($canWrite && !$isResolved && !$isClosed)
                            <details>
                                <summary style="font-size:12px; color:var(--forest); cursor:pointer; list-style:none; display:inline-flex; align-items:center; gap:5px;">
                                    <x-lucide-plus style="width:11px;height:11px;" /> Log a letter
                                </summary>
                                <div style="margin-top:12px; padding:16px 18px; background:var(--paper); border:1px solid var(--rule-2);">
                                    <div style="font-size:14px; font-weight:600; color:var(--ink); margin-bottom:3px;">Letter sent</div>
                                    <div style="font-size:12px; color:var(--ink-3); margin-bottom:14px;">Record the reference letter dispatched to {{ $ref->referred_to }}.</div>
                                    <form method="POST" action="{{ route('cases.referral.letter', [$case, $ref]) }}"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
                                            <div>
                                                <label class="jh-field-label">Our Reference / Dispatch No.</label>
                                                <input type="text" name="our_ref" placeholder="e.g. LAS-HYD/Disp/0231"
                                                    class="inp" style="width:100%; font-size:13px; box-sizing:border-box;" />
                                            </div>
                                            <div>
                                                <label class="jh-field-label">Date Sent <span style="color:var(--burgundy)">*</span></label>
                                                <input type="date" name="letter_date" required value="{{ now()->toDateString() }}"
                                                    class="inp" style="width:100%; font-size:13px; box-sizing:border-box;" />
                                            </div>
                                            <div style="grid-column: 1 / -1;">
                                                <label class="jh-field-label">Reference Letter (PDF / Scan)</label>
                                                <div id="jh-letter-drop-{{ $refId }}"
                                                    style="border:1.5px dashed var(--rule); padding:12px 14px; cursor:pointer; background:var(--parchment); display:flex; align-items:center; gap:10px;"
                                                    onclick="document.getElementById('jh-letter-file-{{ $refId }}').click()">
                                                    <x-lucide-paperclip style="width:14px;height:14px; color:var(--ink-4); flex-shrink:0;" />
                                                    <span id="jh-letter-name-{{ $refId }}" style="font-size:13px; color:var(--ink-4);">Click to attach PDF or scan…</span>
                                                    <button type="button" id="jh-letter-clear-{{ $refId }}"
                                                        onclick="event.stopPropagation(); jhLetterClear('{{ $refId }}')"
                                                        style="display:none; margin-left:auto; background:none; border:none; color:var(--ink-4); cursor:pointer; font-size:13px; line-height:1; padding:0 2px;">×</button>
                                                </div>
                                                <input type="file" id="jh-letter-file-{{ $refId }}" name="letter_file"
                                                    accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                                    style="display:none;"
                                                    onchange="jhLetterPicked('{{ $refId }}', this)" />
                                            </div>
                                            <div style="grid-column: 1 / -1;">
                                                <label class="jh-field-label">Note</label>
                                                <textarea name="note" rows="3" placeholder="Purpose of the letter, any context..."
                                                    class="inp" style="width:100%; font-size:13px; box-sizing:border-box; resize:vertical;"></textarea>
                                            </div>
                                        </div>
                                        <div style="display:flex; gap:8px;">
                                            <button type="submit"
                                                style="padding:8px 18px; background:var(--forest); color:var(--cream); border:none; font-size:13px; font-family:inherit; font-weight:600; cursor:pointer;">Record letter sent</button>
                                            <button type="button" onclick="this.closest('details').removeAttribute('open')"
                                                style="padding:8px 14px; background:none; border:1px solid var(--rule); color:var(--ink-3); font-size:13px; font-family:inherit; cursor:pointer;">× Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </details>
                            @elseif($ref->letters->isEmpty())
                            <span style="font-size:12px; color:var(--ink-4); font-style:italic;">No letters logged.</span>
                            @endif
                        </div>

                        {{-- ── SECTION 4: Follow-up ── --}}
                        <div style="padding-top: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--rule-2);">
                            <div class="label-cap" style="font-size: 9.5px; color: var(--ink-4); margin-bottom: 10px; display:flex; align-items:center; gap:6px;">
                                <x-lucide-repeat-2 style="width:11px;height:11px;" /> Follow-up
                                <span class="mono" style="font-size:10px; color:var(--ink-4); font-weight:400; text-transform:none; letter-spacing:0;">{{ $ref->threads->count() }} {{ Str::plural('entry', $ref->threads->count()) }}</span>
                            </div>

                            {{-- Follow-up meta (due date, partner ref) --}}
                            @if($ref->follow_up_date || $ref->partner_tracking_ref)
                            <div style="display:flex; gap:18px; flex-wrap:wrap; margin-bottom:12px;">
                                @if($ref->follow_up_date)
                                <span style="font-size:12px; color:var(--ink-3);">
                                    <span style="font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:0.04em;">Due:</span>
                                    {{ $ref->follow_up_date->format('d M Y') }}
                                    @if($ref->follow_up_date->isPast() && !$isClosed)
                                    <span style="color:var(--burgundy); font-size:10px; font-weight:700; margin-left:4px;">OVERDUE</span>
                                    @endif
                                </span>
                                @endif
                                @if($ref->partner_tracking_ref)
                                <span style="font-size:12px; color:var(--ink-3);">
                                    <span style="font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:0.04em;">Partner Ref:</span>
                                    {{ $ref->partner_tracking_ref }}
                                </span>
                                @endif
                            </div>
                            @endif

                            {{-- Thread timeline --}}
                            @if($ref->threads->count())
                            <div style="display:flex; flex-direction:column; gap:0; margin-bottom:14px; border-left:2px solid var(--rule); padding-left:14px;">
                                @foreach($ref->threads as $thread)
                                @php
                                    $isPartner = $thread->isFromPartner();
                                    $threadIcon = match($thread->type) {
                                        'Email'   => 'mail',
                                        'Phone'   => 'phone',
                                        'Meeting' => 'users',
                                        'Letter'  => 'file-text',
                                        default   => 'message-circle',
                                    };
                                @endphp
                                <div style="position:relative; padding:0 0 14px 0;">
                                    <div style="position:absolute; left:-19px; top:3px; width:8px; height:8px; border-radius:50%; background:{{ $isPartner ? 'var(--ochre)' : 'var(--forest)' }}; border:2px solid var(--paper);"></div>
                                    <div style="display:flex; align-items:flex-start; gap:10px;">
                                        <div style="flex:1; min-width:0;">
                                            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                                <span style="font-size:11px; font-weight:700; color:{{ $isPartner ? 'var(--ochre)' : 'var(--forest)' }}; text-transform:uppercase; letter-spacing:0.04em;">
                                                    {{ $isPartner ? 'From Partner' : 'From Us' }}
                                                </span>
                                                <span style="font-size:11px; color:var(--ink-4);">{{ $thread->type }}</span>
                                                <span class="mono" style="font-size:11px; color:var(--ink-4);">{{ $thread->thread_date->format('d M Y') }}</span>
                                                @if($thread->logged_by)
                                                <span style="font-size:11px; color:var(--ink-4);">· {{ $thread->logged_by }}</span>
                                                @endif
                                            </div>
                                            @if($thread->note)
                                            <div style="font-size:12.5px; color:var(--ink-2); line-height:1.5; margin-top:4px;">{{ $thread->note }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            @if($canWrite && !$isResolved && !$isClosed)
                            {{-- Always-visible add-thread form --}}
                            <div style="margin-top:10px; padding:14px 16px; background:var(--parchment); border:1px solid var(--rule-2);">
                                <div class="label-cap" style="font-size:9.5px; margin-bottom:10px; color:var(--ink-4);">Add to Thread</div>
                                <form method="POST" action="{{ route('cases.referral.thread', [$case, $ref]) }}">
                                    @csrf
                                    <div style="display:grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap:10px; margin-bottom:10px;">
                                        <div>
                                            <label class="jh-field-label">Direction <span style="color:var(--burgundy)">*</span></label>
                                            <select name="direction" required class="inp" style="width:100%; font-size:13px; box-sizing:border-box;">
                                                <option value="from_us">From Us</option>
                                                <option value="from_partner">From Partner</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="jh-field-label">Type <span style="color:var(--burgundy)">*</span></label>
                                            <select name="type" required class="inp" style="width:100%; font-size:13px; box-sizing:border-box;">
                                                <option>Email</option>
                                                <option>Phone</option>
                                                <option>Meeting</option>
                                                <option>Letter</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="jh-field-label">Date <span style="color:var(--burgundy)">*</span></label>
                                            <input type="date" name="thread_date" required value="{{ now()->toDateString() }}"
                                                class="inp" style="width:100%; font-size:13px; box-sizing:border-box;" />
                                        </div>
                                        <div>
                                            <label class="jh-field-label">Follow-up Due</label>
                                            <input type="date" name="follow_up_date" value="{{ $ref->follow_up_date?->toDateString() }}"
                                                class="inp" style="width:100%; font-size:13px; box-sizing:border-box;" />
                                        </div>
                                        <div style="grid-column:1 / span 3;">
                                            <label class="jh-field-label">Note</label>
                                            <textarea name="note" rows="2" placeholder="Summary of the communication..."
                                                class="inp" style="width:100%; font-size:13px; box-sizing:border-box; resize:vertical;"></textarea>
                                        </div>
                                        <div>
                                            <label class="jh-field-label">Partner Ref</label>
                                            <input type="text" name="partner_tracking_ref" value="{{ $ref->partner_tracking_ref }}"
                                                placeholder="Their reference no."
                                                class="inp" style="width:100%; font-size:13px; box-sizing:border-box;" />
                                        </div>
                                    </div>
                                    <button type="submit"
                                        style="padding:8px 18px; background:var(--forest); color:var(--cream); border:none; font-size:12px; font-family:inherit; font-weight:600; cursor:pointer;">Add to Thread</button>
                                </form>
                            </div>
                            @elseif($ref->threads->isEmpty())
                            <span style="font-size:12px; color:var(--ink-4); font-style:italic;">No follow-up entries yet.</span>
                            @endif
                        </div>

                        {{-- ── SECTION 5: Closed ── --}}
                        <div style="padding-top: 16px;">
                            <div class="label-cap" style="font-size: 9.5px; color: var(--ink-4); margin-bottom: 10px; display:flex; align-items:center; gap:6px;">
                                <x-lucide-check-circle style="width:11px;height:11px;" /> Closed
                            </div>

                            @if($isClosed)
                            {{-- Closed record --}}
                            <div style="padding:12px 14px; background:rgba(0,0,0,0.025); border:1px solid var(--rule-2); display:flex; flex-direction:column; gap:6px;">
                                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                                    <span style="font-size:12px; color:var(--ink-3);">
                                        <span style="font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:0.04em;">Closed:</span>
                                        {{ $ref->closed_at->format('d M Y') }}
                                    </span>
                                    <span style="font-size:12px; color:var(--ink-3);">
                                        <span style="font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:0.04em;">Outcome:</span>
                                        {{ $ref->closed_outcome }}
                                    </span>
                                </div>
                                @if($ref->closed_note)
                                <div style="font-size:12.5px; color:var(--ink-2); line-height:1.5;">{{ $ref->closed_note }}</div>
                                @endif
                            </div>
                            @elseif($canWrite && !$isResolved)
                            <details>
                                <summary style="font-size:12px; color:var(--ink-3); cursor:pointer; list-style:none; display:inline-flex; align-items:center; gap:5px;">
                                    <x-lucide-x-circle style="width:11px;height:11px;" /> Close this referral
                                </summary>
                                <form method="POST" action="{{ route('cases.referral.close', [$case, $ref]) }}" style="margin-top:10px;">
                                    @csrf
                                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px;">
                                        <div>
                                            <label class="jh-field-label">Date Closed <span style="color:var(--burgundy)">*</span></label>
                                            <input type="date" name="closed_at" required value="{{ now()->toDateString() }}"
                                                class="inp" style="width:100%; font-size:13px; box-sizing:border-box;" />
                                        </div>
                                        <div>
                                            <label class="jh-field-label">Outcome <span style="color:var(--burgundy)">*</span></label>
                                            <select name="closed_outcome" required class="inp" style="width:100%; font-size:13px; box-sizing:border-box;">
                                                <option value="">— Select outcome —</option>
                                                <option>Resolved — Partner assisted</option>
                                                <option>Resolved — Self-resolved</option>
                                                <option>No Response from Partner</option>
                                                <option>Client withdrew</option>
                                                <option>Referred onwards</option>
                                                <option>Other</option>
                                            </select>
                                        </div>
                                        <div style="grid-column: 1 / -1;">
                                            <label class="jh-field-label">Closing Note</label>
                                            <textarea name="closed_note" rows="2" placeholder="Any final notes on this referral outcome..."
                                                class="inp" style="width:100%; font-size:13px; box-sizing:border-box; resize:vertical;"></textarea>
                                        </div>
                                    </div>
                                    <button type="submit"
                                        onclick="return confirm('Close this referral? This cannot be undone.')"
                                        style="padding:7px 16px; background:var(--burgundy); color:#fff; border:none; font-size:12px; font-family:inherit; font-weight:600; cursor:pointer;">Close Referral</button>
                                </form>
                            </details>
                            @else
                            <span style="font-size:12px; color:var(--ink-4); font-style:italic;">Referral is still active.</span>
                            @endif
                        </div>

                    </div>{{-- end card body --}}

                    {{-- ── ACTIVITY HISTORY ── --}}
                    @php
                        // Build unified activity feed from all referral events
                        $refActivity = collect();

                        // 1. Referral created
                        $refActivity->push([
                            'icon'  => 'git-branch',
                            'color' => 'var(--forest)',
                            'label' => 'Referral created',
                            'meta'  => 'Referred to ' . $ref->referred_to,
                            'by'    => $ref->referred_by,
                            'at'    => $ref->created_at,
                        ]);

                        // 2. Letters
                        foreach ($ref->letters as $l) {
                            $refActivity->push([
                                'icon'  => 'mail',
                                'color' => 'var(--ink-3)',
                                'label' => 'Letter logged' . ($l->our_ref ? ' · ' . $l->our_ref : ''),
                                'meta'  => $l->note ? \Str::limit($l->note, 80) : 'Letter sent on ' . $l->letter_date->format('d M Y'),
                                'by'    => $l->logged_by,
                                'at'    => $l->created_at,
                            ]);
                        }

                        // 3. Thread entries
                        foreach ($ref->threads as $t) {
                            $dir = $t->isFromPartner() ? 'From Partner' : 'From Us';
                            $refActivity->push([
                                'icon'  => match($t->type) { 'Email' => 'mail', 'Phone' => 'phone', 'Meeting' => 'users', default => 'file-text' },
                                'color' => $t->isFromPartner() ? 'var(--ochre)' : 'var(--forest)',
                                'label' => $dir . ' · ' . $t->type,
                                'meta'  => $t->note ? \Str::limit($t->note, 80) : $t->type . ' on ' . $t->thread_date->format('d M Y'),
                                'by'    => $t->logged_by,
                                'at'    => $t->created_at,
                            ]);
                        }

                        // 4. Closure
                        if ($ref->isClosed()) {
                            $refActivity->push([
                                'icon'  => 'check-circle',
                                'color' => 'var(--moss)',
                                'label' => 'Referral closed',
                                'meta'  => 'Outcome: ' . $ref->closed_outcome,
                                'by'    => null,
                                'at'    => $ref->closed_at,
                            ]);
                        }

                        $refActivity = $refActivity->sortByDesc('at')->values();
                    @endphp
                    <div style="border-top: 1px solid var(--rule-2); padding: 14px 20px; background: var(--parchment);">
                        <div style="display:flex; align-items:center; gap:6px; margin-bottom:12px;">
                            <x-lucide-clock style="width:12px;height:12px; color:var(--ink-4);" />
                            <span class="label-cap" style="font-size:9.5px; color:var(--ink-4);">Activity History</span>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:0; border-left:2px solid var(--rule); padding-left:14px;">
                            @foreach($refActivity as $evt)
                            <div style="position:relative; padding:0 0 10px;">
                                <div style="position:absolute; left:-19px; top:3px; width:8px; height:8px; border-radius:50%; background:{{ $evt['color'] }}; border:2px solid var(--parchment);"></div>
                                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:8px; flex-wrap:wrap;">
                                    <div>
                                        <span style="font-size:12px; font-weight:600; color:var(--ink);">{{ $evt['label'] }}</span>
                                        @if($evt['by'])
                                        <span style="font-size:11px; color:var(--ink-4);"> · {{ $evt['by'] }}</span>
                                        @endif
                                        @if($evt['meta'])
                                        <div style="font-size:11.5px; color:var(--ink-3); margin-top:2px; line-height:1.4;">{{ $evt['meta'] }}</div>
                                        @endif
                                    </div>
                                    <span class="mono" style="font-size:10px; color:var(--ink-4); white-space:nowrap; flex-shrink:0;">
                                        {{ $evt['at']->format('d M Y, H:i') }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>{{-- end referral card --}}
                @endforeach

            </div>{{-- end referral tracking --}}
            @endif

            <style>
                .jh-field-label {
                    font-size: 11px;
                    font-weight: 600;
                    color: var(--ink-3);
                    display: block;
                    margin-bottom: 5px;
                    text-transform: uppercase;
                    letter-spacing: 0.04em;
                }
                details > summary { user-select: none; }
                details > summary::-webkit-details-marker { display: none; }
            </style>

            <script>
            function jhRefToggleCreate(btn) {
                var form = document.getElementById('jh-ref-create-form');
                if (!form) return;
                var showing = form.style.display !== 'none';
                form.style.display = showing ? 'none' : '';
            }

            function jhRefFilingToggle(val) {
                var trackBox = document.getElementById('ref-tracking-box');
                var justBox  = document.getElementById('ref-justification-box');
                if (!trackBox || !justBox) return;
                trackBox.style.display = val === 'Filed'     ? '' : 'none';
                justBox.style.display  = val === 'Not Filed' ? '' : 'none';
            }

            function jhLetterPicked(refId, input) {
                var name = document.getElementById('jh-letter-name-' + refId);
                var clear = document.getElementById('jh-letter-clear-' + refId);
                var drop  = document.getElementById('jh-letter-drop-' + refId);
                if (input.files && input.files[0]) {
                    name.textContent  = input.files[0].name;
                    name.style.color  = 'var(--ink)';
                    clear.style.display = 'inline-block';
                    drop.style.borderColor = 'var(--forest)';
                    drop.style.borderStyle = 'solid';
                }
            }

            function jhLetterClear(refId) {
                var input = document.getElementById('jh-letter-file-' + refId);
                var name  = document.getElementById('jh-letter-name-' + refId);
                var clear = document.getElementById('jh-letter-clear-' + refId);
                var drop  = document.getElementById('jh-letter-drop-' + refId);
                input.value = '';
                name.textContent = 'Click to attach PDF or scan…';
                name.style.color = 'var(--ink-4)';
                clear.style.display = 'none';
                drop.style.borderColor = 'var(--rule)';
                drop.style.borderStyle = 'dashed';
            }
            </script>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

                {{-- Pathway Assignment --}}
                <div class="card" style="padding: 22px 26px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                        <x-lucide-git-branch style="width: 15px; height: 15px; color: var(--forest);" />
                        <div class="label-cap" style="font-size: 10px;">Pathway Assignment</div>
                    </div>
                    <div>
                        <div class="jh-intake-row">
                            <div class="jh-intake-label">Assigned Pathway</div>
                            <div class="jh-intake-value">{{ $case->assigned_pathway ?? '---' }}</div>
                        </div>
                        <div class="jh-intake-row">
                            <div class="jh-intake-label">Pathway Specific</div>
                            <div class="jh-intake-value">{{ $case->pathway_specific ?? '---' }}</div>
                        </div>
                        <div class="jh-intake-row">
                            <div class="jh-intake-label">Govt Department</div>
                            <div class="jh-intake-value">{{ $case->pathway_govt_dept ?? '---' }}</div>
                        </div>
                        <div class="jh-intake-row">
                            <div class="jh-intake-label">NGO Name</div>
                            <div class="jh-intake-value">{{ $case->pathway_ngo_name ?? '---' }}</div>
                        </div>
                        <div class="jh-intake-row">
                            <div class="jh-intake-label">Other Details</div>
                            <div class="jh-intake-value">{{ $case->pathway_other_details ?? '---' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Active Pathways --}}
                <div class="card" style="padding: 22px 26px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                        <x-lucide-arrow-right-left style="width: 15px; height: 15px; color: var(--forest);" />
                        <div class="label-cap" style="font-size: 10px;">Active Pathways</div>
                    </div>
                    @forelse($pathways as $pw)
                    <div style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; margin-bottom: 6px; background: rgba(22,48,41,0.03); border: 1px solid var(--rule);">
                        <x-lucide-check-circle-2 style="width: 13px; height: 13px; color: var(--moss);" />
                        <span style="font-size: 13px; font-weight: 500; color: var(--ink);">{{ $pw }}</span>
                    </div>
                    @empty
                    <x-empty-state icon="arrow-right-left" message="No referral pathways assigned to this case." />
                    @endforelse
                </div>

                {{-- LAS CMS Integration Status --}}
                @if($case->external_case_id)
                <div class="card" style="padding: 22px 26px; border-left: 3px solid var(--forest);">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 14px;">
                        <x-lucide-link style="width: 15px; height: 15px; color: var(--forest);" />
                        <div class="label-cap" style="font-size: 10px;">LAS CMS Integration</div>
                        <span style="font-size: 9px; padding: 2px 6px; background: var(--moss-tint); color: var(--moss); font-weight: 700; letter-spacing: 0.04em; margin-left: auto;">LINKED</span>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <div style="font-size: 10px; color: var(--ink-4); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 3px;">External ID</div>
                            <div class="mono" style="font-size: 13px; font-weight: 500; color: var(--forest);">{{ $case->external_case_id }}</div>
                        </div>
                        <div>
                            <div style="font-size: 10px; color: var(--ink-4); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 3px;">Last synced</div>
                            <div style="font-size: 12px; color: var(--ink-2);">{{ $case->external_synced_at ? \Carbon\Carbon::parse($case->external_synced_at)->diffForHumans() : 'Never' }}</div>
                        </div>
                        @if($case->meta && ($case->meta['court_name'] ?? null))
                        <div>
                            <div style="font-size: 10px; color: var(--ink-4); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 3px;">Court</div>
                            <div style="font-size: 12px; color: var(--ink-2);">{{ $case->meta['court_name'] }}</div>
                        </div>
                        @endif
                        @if($case->meta && ($case->meta['case_number'] ?? null))
                        <div>
                            <div style="font-size: 10px; color: var(--ink-4); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 3px;">Case Number</div>
                            <div class="mono" style="font-size: 12px; color: var(--ink-2);">{{ $case->meta['case_number'] }}</div>
                        </div>
                        @endif
                        @if($case->meta && ($case->meta['next_hearing'] ?? null))
                        <div>
                            <div style="font-size: 10px; color: var(--ink-4); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 3px;">Next Hearing</div>
                            <div style="font-size: 12px; font-weight: 500; color: var(--ochre);">{{ $case->meta['next_hearing'] }}</div>
                        </div>
                        @endif
                        @if($case->meta && ($case->meta['external_status'] ?? null))
                        <div>
                            <div style="font-size: 10px; color: var(--ink-4); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 3px;">LAS CMS Status</div>
                            <div style="font-size: 12px; font-weight: 500; color: var(--ink);">{{ $case->meta['external_status'] }}</div>
                        </div>
                        @endif
                        @if($case->meta && ($case->meta['cms_approval_status'] ?? null))
                        @php
                            $approvalVal = $case->meta['cms_approval_status'];
                            $approvalColor = match($approvalVal) {
                                'Approved' => 'var(--moss)',
                                'Rejected' => 'var(--burgundy)',
                                default    => 'var(--ochre)',
                            };
                        @endphp
                        <div style="grid-column: 1 / -1;">
                            <div style="font-size: 10px; color: var(--ink-4); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 3px;">Case Approval Status</div>
                            <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: color-mix(in srgb, {{ $approvalColor }} 12%, transparent); border-radius: 20px;">
                                <span style="width: 7px; height: 7px; border-radius: 50%; background: {{ $approvalColor }}; display: inline-block;"></span>
                                <span style="font-size: 12px; font-weight: 700; color: {{ $approvalColor }};">{{ $approvalVal }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @php
                        $courtHearings = $case->serviceEncounters->where('type', 'Court Hearing')->sortByDesc('date');
                    @endphp
                    @if($courtHearings->count())
                    <div style="margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--rule-2);">
                        <div style="font-size: 10px; color: var(--ink-4); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 10px;">Court Hearings ({{ $courtHearings->count() }})</div>
                        @foreach($courtHearings->take(5) as $h)
                        <div style="padding: 10px 12px; margin-bottom: 6px; background: var(--paper); border: 1px solid var(--rule-2);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                <span class="mono" style="font-size: 11px; color: var(--forest); font-weight: 500;">{{ \Carbon\Carbon::parse($h->date)->format('d M Y') }}</span>
                                @if($h->meta['next_hearing'] ?? null)
                                <span style="font-size: 10px; color: var(--ochre);">Next: {{ $h->meta['next_hearing'] }}</span>
                                @endif
                            </div>
                            <div style="font-size: 12px; color: var(--ink-2); line-height: 1.5;">{{ Str::limit($h->note, 200) }}</div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────────
             TAB 4: DOCUMENTS (kept)
             ───────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ $activeTab === 'documents' ? 'show active' : '' }}" id="tab-documents" role="tabpanel">

            {{-- Summary header --}}
            <div class="card-accent" style="padding: 18px 22px; border-left-color: var(--forest); margin-bottom: 18px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div class="label-cap" style="font-size: 9.5px;">Documents on file for {{ $case->case_ref }}</div>
                    <div style="display: flex; align-items: baseline; gap: 6px; margin-top: 6px;">
                        <span class="serif" style="font-size: 28px; font-weight: 400;">{{ $case->documents->count() }}</span>
                        <span style="font-size: 13px; color: var(--ink-3);">total</span>
                    </div>
                </div>
                @if($canWrite && !$isResolved)
                <button class="btn-primary" onclick="jhOpenModal('add-document')" style="display: inline-flex; align-items: center; gap: 6px;">
                    <x-lucide-plus style="width: 13px; height: 13px;" /> Add document
                </button>
                @endif
            </div>

            {{-- Document table --}}
            @if($case->documents->count() > 0)
            <div class="card" style="padding: 0; overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--rule);">
                            <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Document</th>
                            <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Type</th>
                            <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Date Added</th>
                            <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Added By</th>
                            <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Status</th>
                            <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Confidentiality</th>
                            <th style="text-align: right; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($case->documents as $doc)
                        <tr style="border-bottom: 1px solid var(--rule-2);" class="tr-hover">
                            <td style="padding: 12px 14px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <x-lucide-file-text style="width: 16px; height: 16px; color: var(--forest); flex-shrink: 0;" />
                                    <div>
                                        <div style="font-weight: 500; color: var(--ink);">{{ $doc->name }}</div>
                                        <div class="mono" style="font-size: 10px; color: var(--ink-4);">{{ $doc->document_uid }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 12px 14px; color: var(--ink-2);">{{ $doc->type }}</td>
                            <td style="padding: 12px 14px; color: var(--ink-3);">{{ $doc->added_date->format('M d, Y') }}</td>
                            <td style="padding: 12px 14px; color: var(--ink-2);">{{ $doc->added_by }}</td>
                            <td style="padding: 12px 14px;">
                                <x-pill :color="$doc->status === 'Verified' ? 'var(--moss)' : 'var(--ochre)'" :bg="$doc->status === 'Verified' ? 'rgba(74,122,92,0.08)' : 'rgba(184,115,25,0.08)'">{{ $doc->status }}</x-pill>
                            </td>
                            <td style="padding: 12px 14px;">
                                <x-pill :color="$doc->confidentiality === 'Restricted' ? 'var(--burgundy)' : 'var(--ink-3)'" :bg="$doc->confidentiality === 'Restricted' ? 'rgba(138,46,29,0.08)' : 'var(--rule-2)'">{{ $doc->confidentiality }}</x-pill>
                            </td>
                            <td style="padding: 12px 14px; text-align: right;">
                                <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                    @if($doc->file_path)
                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" title="Download" style="padding: 5px 8px; background: var(--paper); border: 1px solid var(--rule); color: var(--ink-2); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-size: 11px;">
                                        <x-lucide-download style="width: 12px; height: 12px;" /> View
                                    </a>
                                    @endif
                                    @if($doc->status !== 'Verified' && $canWrite)
                                    <form method="POST" action="{{ route('documents.verify', $doc) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" title="Mark as Verified" style="padding: 5px 8px; background: rgba(74,122,92,0.08); border: 1px solid var(--moss); color: var(--moss); cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 500;">
                                            <x-lucide-check-circle-2 style="width: 12px; height: 12px;" /> Verify
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <x-empty-state icon="file-text" message="No documents attached to this case." />
            @endif
        </div>

        {{-- ─────────────────────────────────────────────────────
             TAB 5: FEEDBACK (kept)
             ───────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ $activeTab === 'feedback' ? 'show active' : '' }}" id="tab-feedback" role="tabpanel">
            <div class="card" style="padding: 24px 28px;">
                @forelse($case->feedback as $fb)
                <div style="padding: 16px 0; {{ !$loop->last ? 'border-bottom: 1px solid var(--rule-2);' : '' }}">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 32px; height: 32px; background: var(--ochre-tint); color: var(--ochre); display: flex; align-items: center; justify-content: center; border-radius: 50; font-size: 12px; font-weight: 600;">
                                {{ $fb->is_anonymous ? '?' : strtoupper(substr($fb->client_name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-size: 13px; font-weight: 500; color: var(--ink);">{{ $fb->is_anonymous ? 'Anonymous' : $fb->client_name }}</div>
                                <div style="font-size: 11px; color: var(--ink-4);">{{ $fb->date->format('M d, Y') }} · via {{ $fb->channel }}</div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 4px;">
                            @for($i = 1; $i <= 5; $i++)
                            <span style="font-size: 16px; color: {{ $i <= $fb->score_overall ? 'var(--ochre)' : 'var(--rule)' }};">★</span>
                            @endfor
                        </div>
                    </div>
                    <div style="display: flex; gap: 20px; margin-bottom: 8px; font-size: 12px;">
                        <div><span class="label-cap" style="font-size: 9px;">Overall</span><div style="color: var(--ochre); letter-spacing: 2px;">{{ str_repeat('★', $fb->score_overall) }}{{ str_repeat('☆', 5 - $fb->score_overall) }}</div></div>
                        <div><span class="label-cap" style="font-size: 9px;">Helpfulness</span><div style="color: var(--ochre); letter-spacing: 2px;">{{ str_repeat('★', $fb->score_helpfulness) }}{{ str_repeat('☆', 5 - $fb->score_helpfulness) }}</div></div>
                        <div><span class="label-cap" style="font-size: 9px;">Respect</span><div style="color: var(--ochre); letter-spacing: 2px;">{{ str_repeat('★', $fb->score_respect) }}{{ str_repeat('☆', 5 - $fb->score_respect) }}</div></div>
                    </div>
                    @if($fb->comment)
                    <div style="font-size: 13px; color: var(--ink-2); line-height: 1.55; font-style: italic; padding: 10px 14px; background: var(--parchment); border-left: 3px solid var(--ochre); margin-top: 8px;">"{{ $fb->comment }}"</div>
                    @endif
                </div>
                @empty
                <div style="text-align: center; padding: 40px 20px;">
                    <x-lucide-heart-handshake style="width: 40px; height: 40px; color: var(--ochre); margin: 0 auto 14px; opacity: 0.5;" />
                    <div class="serif" style="font-size: 18px; font-weight: 500; color: var(--ink); margin-bottom: 8px;">No feedback captured yet</div>
                    <div style="font-size: 13px; color: var(--ink-3); max-width: 420px; margin: 0 auto 18px; line-height: 1.55;">
                        Feedback can be captured after every service encounter — and again at case closure. Use the Timeline tab to record encounter-specific feedback, or capture an overall response below.
                    </div>
                    @if($canWrite)
                    <div style="display:flex; gap:10px; justify-content:center;">
                        <a href="{{ route('feedback.index') }}" onclick="event.preventDefault(); jhOpenModal('case-detailed-feedback');" class="btn-ghost" style="display:inline-flex; align-items:center; gap:6px;">
                            <x-lucide-clipboard-list style="width:13px; height:13px;" /> Detailed Feedback
                        </a>
                        <button class="btn-primary" onclick="jhOpenModal('capture-feedback')" style="display: inline-flex; align-items: center; gap: 6px;">
                            <x-lucide-plus style="width: 13px; height: 13px;" /> Capture feedback
                        </button>
                    </div>
                    @endif
                </div>
                @endforelse

                @if($case->feedback->count() > 0 && $canWrite)
                <div style="padding-top: 16px; border-top: 1px solid var(--rule-2); margin-top: 8px; display:flex; gap:10px;">
                    <a href="#" onclick="event.preventDefault(); jhOpenModal('case-detailed-feedback');" class="btn-ghost" style="display:inline-flex; align-items:center; gap:6px;">
                        <x-lucide-clipboard-list style="width:13px; height:13px;" /> Detailed Feedback
                    </a>
                    <button class="btn-primary" onclick="jhOpenModal('capture-feedback')" style="display: inline-flex; align-items: center; gap: 6px;">
                        <x-lucide-plus style="width: 13px; height: 13px;" /> Capture feedback
                    </button>
                </div>
                @endif

                <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--rule-2); text-align: center;">
                    <div class="mono" style="font-size: 9.5px; color: var(--ink-4); letter-spacing: 0.06em; text-transform: uppercase;">
                        Indicator O14 · Client Satisfaction · Overall, Helpfulness, Respect & Dignity
                    </div>
                </div>
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────────
             TAB 7: COMPLAINTS (kept)
             ───────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ $activeTab === 'complaints' ? 'show active' : '' }}" id="tab-complaints" role="tabpanel">
            <div class="card" style="padding: 24px 28px;">
                @forelse($case->complaints as $complaint)
                <div style="padding: 16px 0; {{ !$loop->last ? 'border-bottom: 1px solid var(--rule-2);' : '' }}">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span class="mono" style="font-size: 11px; color: var(--ink-3);">{{ $complaint->complaint_uid }}</span>
                            <x-pill :color="$complaint->severity->color()" :bg="$complaint->severity->tint()">{{ $complaint->severity->label() }}</x-pill>
                            <x-pill :color="$complaint->status->color()">{{ $complaint->status->label() }}</x-pill>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px; font-size: 11px; color: var(--ink-4);">
                            <span>{{ $complaint->category }}</span>
                            <span>·</span>
                            <span>{{ $complaint->channel }}</span>
                            <span>·</span>
                            <span>{{ $complaint->submitted_date->format('M d, Y') }}</span>
                        </div>
                    </div>
                    <div style="font-size: 13px; color: var(--ink-2); line-height: 1.55;">{{ $complaint->description }}</div>
                    @if($complaint->submitted_by)
                    <div style="font-size: 11px; color: var(--ink-4); margin-top: 6px;">Submitted by: {{ $complaint->is_anonymous ? 'Anonymous' : $complaint->submitted_by }} · SLA: {{ $complaint->sla_days }} days</div>
                    @endif
                    @if($complaint->resolution)
                    <div style="margin-top: 10px; padding: 12px 14px; background: rgba(74,122,92,0.05); border-left: 3px solid var(--moss); font-size: 12.5px; color: var(--ink-2); line-height: 1.55;">
                        <div style="font-weight: 600; color: var(--moss); margin-bottom: 4px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em;">Resolution</div>
                        {{ $complaint->resolution }}
                        @if($complaint->resolved_date)
                        <div style="font-size: 11px; color: var(--ink-4); margin-top: 4px;">Resolved: {{ $complaint->resolved_date->format('M d, Y') }}</div>
                        @endif
                    </div>
                    @endif
                </div>
                @empty
                <div style="text-align: center; padding: 40px 20px;">
                    <div style="width: 52px; height: 52px; background: rgba(74,122,92,0.08); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px;">
                        <x-lucide-check-circle-2 style="width: 24px; height: 24px; color: var(--moss);" />
                    </div>
                    <div class="serif" style="font-size: 18px; font-weight: 500; color: var(--ink); margin-bottom: 8px;">No complaints linked to this case</div>
                    <div style="font-size: 13px; color: var(--ink-3); max-width: 480px; margin: 0 auto 18px; line-height: 1.55;">
                        Service users can log complaints via in-person, phone, written submission, or through a paralegal. Linked complaints would appear here, with full SLA tracking and resolution timeline.
                    </div>
                    @if($canWrite)
                    <button class="btn-ghost" onclick="jhOpenModal('log-complaint')" style="display: inline-flex; align-items: center; gap: 6px;">
                        <x-lucide-plus style="width: 13px; height: 13px;" /> Log a complaint for this case
                    </button>
                    @endif
                </div>
                @endforelse

                @if($case->complaints->count() > 0 && $canWrite)
                <div style="padding-top: 16px; border-top: 1px solid var(--rule-2); margin-top: 8px;">
                    <button class="btn-ghost" onclick="jhOpenModal('log-complaint')" style="display: inline-flex; align-items: center; gap: 6px;">
                        <x-lucide-plus style="width: 13px; height: 13px;" /> Log Complaint
                    </button>
                </div>
                @endif

                <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--rule-2); text-align: center;">
                    <div class="mono" style="font-size: 9.5px; color: var(--ink-4); letter-spacing: 0.06em; text-transform: uppercase;">
                        Indicator OP4.3 · % of Complaints Resolved Within Agreed Timelines
                    </div>
                </div>
            </div>
        </div>

    {{-- ════════════════════════════════════════════════════════
         TAB: CASE NOTES (Coordinator ↔ Lawyer/Mediator)
         Confidential thread — only visible to Coordinator,
         assigned Lawyer/Mediator, and Head.
    ════════════════════════════════════════════════════════ --}}
    @php
        $user           = auth()->user();
        $canSeeMessages = $user->isHead()
                       || $user->isHubCoordinator()
                       || ($case->assigned_to && $user->name === $case->assigned_to);
    @endphp

    @if($canSeeMessages)
    <div class="tab-pane fade {{ $activeTab === 'messages' ? 'show active' : '' }}"
         id="tab-messages" role="tabpanel">

        <div style="max-width: 680px; margin: 0 auto;">

            {{-- Header --}}
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px;">
                <div>
                    <div style="font-size:15px; font-weight:700; color:var(--ink);">{{ __('cases.case_notes') }}</div>
                    <div style="font-size:11px; color:var(--ink-3); margin-top:2px;">
                        {{ __('cases.confidential_thread', ['staff' => $case->assigned_to ?? 'assigned staff']) }}
                        {{ __('cases.not_visible_other_roles') }}
                    </div>
                </div>
                <span style="margin-left:auto; font-size:10px; padding:3px 10px; background:var(--burgundy-soft,#fdf2f2); color:var(--burgundy); border:1px solid var(--burgundy); font-weight:600; letter-spacing:0.05em; text-transform:uppercase;">
                    {{ __('cases.confidential') }}
                </span>
            </div>

            {{-- Message thread --}}
            <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:24px;">
                @forelse($case->messages as $msg)
                @php
                    $isMine    = $msg->sender_id === $user->id;
                    $senderRole = $msg->sender?->role?->label() ?? 'Staff';
                @endphp
                <div style="display:flex; flex-direction:column; align-items:{{ $isMine ? 'flex-end' : 'flex-start' }};">
                    {{-- Sender label --}}
                    <div style="font-size:9.5px; color:var(--ink-3); margin-bottom:3px; padding:0 4px;">
                        <span style="font-weight:600; color:var(--ink-2);">{{ $msg->sender?->name ?? 'Unknown' }}</span>
                        &middot; {{ $senderRole }}
                        &middot; <span class="mono">{{ $msg->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    {{-- Bubble --}}
                    <div style="
                        max-width: 80%;
                        padding: 10px 14px;
                        font-size: 13px;
                        line-height: 1.55;
                        color: {{ $isMine ? '#fff' : 'var(--ink)' }};
                        background: {{ $isMine ? 'var(--forest)' : 'var(--surface-2, #f4f4f2)' }};
                        border: 1px solid {{ $isMine ? 'var(--forest)' : 'var(--rule)' }};
                        border-radius: {{ $isMine ? '12px 12px 2px 12px' : '12px 12px 12px 2px' }};
                        white-space: pre-wrap;
                        word-break: break-word;
                    ">{{ $msg->body }}</div>
                </div>
                @empty
                <div style="text-align:center; padding:48px 0; color:var(--ink-4);">
                    <x-lucide-message-square style="width:32px; height:32px; margin:0 auto 10px; display:block; opacity:0.3;" />
                    <div style="font-size:13px;">{{ __('cases.no_messages') }}</div>
                    <div style="font-size:11px; margin-top:4px;">{{ __('cases.start_conversation') }}</div>
                </div>
                @endforelse
            </div>

            {{-- Reply form --}}
            <div style="border-top:1px solid var(--rule); padding-top:18px;">
                <form method="POST" action="{{ route('cases.message.store', $case) }}">
                    @csrf
                    <div style="margin-bottom:10px;">
                        <label style="font-size:10px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3); display:block; margin-bottom:6px;">
                            {{ $user->isHubCoordinator() ? __('cases.message_to', ['name' => $case->assigned_to ?? 'Assigned Staff']) : __('cases.reply_to_coordinator') }}
                        </label>
                        <textarea
                            name="body"
                            rows="3"
                            required
                            placeholder="{{ __('cases.write_message') }}"
                            style="width:100%; padding:10px 12px; border:1px solid var(--rule); font-family:inherit; font-size:13px; color:var(--ink); background:var(--surface); resize:vertical; outline:none;"
                            onkeydown="if(event.ctrlKey && event.key==='Enter') this.closest('form').submit();"
                        >{{ old('body') }}</textarea>
                        <div style="font-size:10px; color:var(--ink-4); margin-top:4px;">{{ __('cases.ctrl_enter_send') }}</div>
                    </div>
                    <button type="submit" class="jh-btn jh-btn-primary" style="display:inline-flex; align-items:center; gap:6px;">
                        <x-lucide-send style="width:13px; height:13px;" />
                        {{ __('cases.send_message') }}
                    </button>
                </form>
            </div>

        </div>
    </div>{{-- tab-messages --}}
    @endif

    {{-- ═══ Outcome Tab ═══ --}}
    <div class="tab-pane fade {{ $activeTab === 'outcome' ? 'show active' : '' }}"
         id="tab-outcome" role="tabpanel">

        @php
            $meta         = $case->meta ?? [];
            $caseOutcome  = $meta['outcome']         ?? null;
            $disposedDate = $meta['disposed_date']   ?? null;
            $resNote      = $meta['resolution_note'] ?? null;
            $resolvedAt   = $meta['resolved_at']     ?? null;
            $resolvedBy   = $meta['resolved_by']     ?? null;
            $resType      = $case->status; // 'Closed' or 'Settlement'

            $outcomeColors = [
                'In Favour'  => ['bg' => 'rgba(47,122,77,0.08)',  'border' => '#2f7a4d',         'text' => '#2f7a4d'],
                'Won'        => ['bg' => 'rgba(47,122,77,0.08)',  'border' => 'var(--moss)',      'text' => 'var(--moss)'],
                'Partial'    => ['bg' => 'rgba(184,115,25,0.08)', 'border' => 'var(--ochre)',     'text' => 'var(--ochre)'],
                'Settlement' => ['bg' => 'rgba(22,48,41,0.06)',   'border' => 'var(--forest)',    'text' => 'var(--forest)'],
                'Withdrawn'  => ['bg' => 'rgba(107,106,101,0.08)','border' => 'var(--ink-3)',     'text' => 'var(--ink-3)'],
                'Lost'       => ['bg' => 'rgba(138,46,29,0.08)',  'border' => 'var(--burgundy)',  'text' => 'var(--burgundy)'],
                'Against'    => ['bg' => 'rgba(138,46,29,0.08)',  'border' => 'var(--burgundy)',  'text' => 'var(--burgundy)'],
            ];
            $oc = $outcomeColors[$caseOutcome] ?? ['bg' => 'var(--surface)', 'border' => 'var(--rule)', 'text' => 'var(--ink-3)'];
        @endphp

        <div style="max-width: 640px; margin: 0 auto;">

            @if($caseOutcome)
            {{-- Resolved state --}}

            {{-- Outcome hero card --}}
            <div style="background:{{ $oc['bg'] }}; border:2px solid {{ $oc['border'] }}; border-radius:4px; padding:28px 28px 24px; margin-bottom:24px; text-align:center;">
                <div class="label-cap" style="font-size:9.5px; color:var(--ink-3); margin-bottom:10px;">{{ __('cases.case_outcome_label') }}</div>
                <div class="serif" style="font-size:40px; font-weight:400; color:{{ $oc['text'] }}; line-height:1; margin-bottom:12px;">
                    {{ $caseOutcome }}
                </div>
                <div style="display:inline-flex; align-items:center; gap:8px; font-size:12px; color:var(--ink-3);">
                    <span style="background:var(--surface); border:1px solid var(--rule); padding:3px 10px; font-weight:600; font-size:11px; letter-spacing:0.04em;">
                        {{ $resType }}
                    </span>
                    @if($resolvedAt)
                    <span>&middot; {{ \Carbon\Carbon::parse($resolvedAt)->format('d M Y, H:i') }}</span>
                    @endif
                    @if($resolvedBy)
                    <span>&middot; by <strong style="color:var(--ink-2);">{{ $resolvedBy }}</strong></span>
                    @endif
                </div>
            </div>

            {{-- Resolution Notes --}}
            @if($resNote)
            <div style="background:var(--surface); border:1px solid var(--rule); border-radius:3px; padding:20px 22px; margin-bottom:20px;">
                <div class="label-cap" style="font-size:9.5px; color:var(--ink-3); margin-bottom:10px;">{{ __('cases.resolution_notes_label') }}</div>
                <div style="font-size:14px; color:var(--ink); line-height:1.65; white-space:pre-wrap;">{{ $resNote }}</div>
            </div>
            @else
            <div style="background:var(--surface); border:1px solid var(--rule); border-radius:3px; padding:16px 22px; margin-bottom:20px; font-size:12px; color:var(--ink-4); font-style:italic;">
                {{ __('cases.no_resolution_notes') }}
            </div>
            @endif

            {{-- Meta row --}}
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                <div style="background:var(--surface); border:1px solid var(--rule); border-radius:3px; padding:16px 18px;">
                    <div class="label-cap" style="font-size:9px; color:var(--ink-4); margin-bottom:6px;">{{ __('cases.disposed_off_date') }}</div>
                    <div style="font-size:13px; font-weight:600; color:var(--ink);">
                        {{ $disposedDate ? \Carbon\Carbon::parse($disposedDate)->format('d M Y') : '—' }}
                    </div>
                </div>
                <div style="background:var(--surface); border:1px solid var(--rule); border-radius:3px; padding:16px 18px;">
                    <div class="label-cap" style="font-size:9px; color:var(--ink-4); margin-bottom:6px;">{{ __('cases.resolved_by_label') }}</div>
                    <div style="font-size:13px; font-weight:600; color:var(--ink);">{{ $resolvedBy ?? '—' }}</div>
                </div>
                <div style="background:var(--surface); border:1px solid var(--rule); border-radius:3px; padding:16px 18px;">
                    <div class="label-cap" style="font-size:9px; color:var(--ink-4); margin-bottom:6px;">{{ __('cases.resolved_at_label') }}</div>
                    <div style="font-size:13px; font-weight:600; color:var(--ink);">
                        {{ $resolvedAt ? \Carbon\Carbon::parse($resolvedAt)->format('d M Y, H:i') : '—' }}
                    </div>
                </div>
            </div>

            @else
            {{-- Not yet resolved --}}
            <div style="text-align:center; padding:72px 0; color:var(--ink-4);">
                <x-lucide-circle-dashed style="width:40px; height:40px; margin:0 auto 14px; display:block; opacity:0.25;" />
                <div style="font-size:14px; font-weight:600; color:var(--ink-3); margin-bottom:6px;">{{ __('cases.no_outcome_yet') }}</div>
                <div style="font-size:12px; color:var(--ink-4);">
                    {{ __('cases.outcome_pending') }}
                </div>
            </div>
            @endif

        </div>
    </div>{{-- tab-outcome --}}

    </div>{{-- end tab-content --}}
</div>

{{-- ═══ Edit Intake Modal ═══ --}}
@if($canEdit)
<x-jh-modal name="edit-intake" title="Edit Intake Information" max-width="680px">
    <form method="POST" action="{{ route('cases.update-intake', $case) }}">
        @csrf @method('PATCH')

        {{-- Client Details --}}
        <div style="font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:12px; padding-bottom:6px; border-bottom:1px solid var(--rule);">Client Details</div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
            <x-form-input name="name" label="Full Name" required :value="$case->name" />
            <x-form-input name="father_husband_name" label="Father / Husband Name" :value="$case->father_husband_name" />
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
            <x-form-select name="gender" label="Gender" required lookup-group="intake.gender" :selected="$case->gender" />
            <x-form-input name="age" label="Age" type="number" min="0" max="120" :value="$case->age" />
            <x-form-input name="cnic" label="CNIC" :value="$case->cnic" maxlength="15" />
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
            <x-form-input name="primary_contact" label="Primary Contact" :value="$case->primary_contact" />
            <x-form-input name="alternative_contact" label="Alternative Contact" :value="$case->alternative_contact" />
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
            <x-form-select name="marital_status" label="Marital Status" lookup-group="intake.marital_status" :selected="$case->marital_status" />
            <x-form-select name="religion" label="Religion" lookup-group="intake.religion" :selected="$case->religion" />
            <x-form-select name="education_level" label="Education Level" lookup-group="intake.education_level" :selected="$case->education_level" />
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
            <x-form-input name="occupation" label="Occupation" :value="$case->occupation" />
            <x-form-select name="income_bracket" label="Income Bracket" lookup-group="intake.income_bracket" :selected="$case->income_bracket" />
            <x-form-select name="disability_status" label="Disability Status" lookup-group="intake.disability_status" :selected="$case->disability_status" />
        </div>

        {{-- Address --}}
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
            <x-form-input name="district" label="District" :value="$case->district" />
            <x-form-input name="tehsil" label="Tehsil / Taluka" :value="$case->tehsil" />
            <x-form-input name="union_council" label="Union Council" :value="$case->union_council" />
        </div>

        {{-- Intake --}}
        <div style="font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin:16px 0 12px; padding-bottom:6px; border-bottom:1px solid var(--rule);">{{ __('cases.intake_assessment') }}</div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
            <x-form-select name="language" label="Preferred Language" lookup-group="intake.preferred_language" :selected="$case->language" />
            <x-form-select name="referral_source" label="Referral Source" lookup-group="intake.referral_source" :selected="$case->referral_source" />
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:16px;">
            <x-form-select name="primary_issue" label="Primary Legal Issue" lookup-group="case.primary_issue" :selected="$case->primary_issue" />
            <x-form-input name="secondary_issue" label="Secondary Issue" :value="$case->secondary_issue" />
            <x-form-select name="urgency" label="Urgency" lookup-group="case.urgency" :selected="$case->urgency->value" />
        </div>

        <div style="margin-bottom:16px;">
            <x-form-input name="issue_description" label="Issue Description" type="textarea" :value="$case->issue_description" />
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
            <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn-primary" style="display:inline-flex; align-items:center; gap:6px;">
                <x-lucide-save style="width:12px;height:12px;" /> Save Changes
            </button>
        </div>
    </form>
</x-jh-modal>
@endif

{{-- ═══ Reassign / Transfer Case Modal ═══ --}}
@if($canEdit && !$isResolved && !$pendingTransfer)
<x-jh-modal name="reassign-case" title="Transfer Case" max-width="560px">
    <form method="POST" action="{{ route('cases.reassign', $case) }}" id="jhTransferForm">
        @csrf
        <p style="font-size:13px;color:var(--ink-2);margin:0 0 16px 0;">
            <strong>{{ $case->case_uid }}</strong> · Currently:
            <strong>{{ $case->assigned_pathway ?? 'No pathway' }}</strong> → <strong>{{ $case->assigned_to ?? 'Unassigned' }}</strong>
        </p>

        {{-- Transfer Type --}}
        <div style="margin-bottom:16px;">
            <label style="display:block;margin-bottom:6px;font-size:10px;font-weight:500;letter-spacing:0.06em;text-transform:uppercase;color:var(--ink-3);">
                Transfer Type <span style="color:var(--burgundy);">*</span>
            </label>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                <label style="display:flex; align-items:center; gap:10px; padding:12px 14px; border:2px solid var(--forest); cursor:pointer; background:rgba(22,48,41,0.04);"
                       id="jh-tt-staff-label">
                    <input type="radio" name="transfer_type" value="staff" checked onchange="jhTransferTypeChange(this.value)"
                           style="accent-color:var(--forest); width:15px; height:15px;">
                    <div>
                        <div style="font-size:13px; font-weight:600; color:var(--ink);">Reassign Staff</div>
                        <div style="font-size:11px; color:var(--ink-3);">Same pathway, different person</div>
                    </div>
                </label>
                <label style="display:flex; align-items:center; gap:10px; padding:12px 14px; border:2px solid var(--rule); cursor:pointer;"
                       id="jh-tt-pathway-label">
                    <input type="radio" name="transfer_type" value="pathway" onchange="jhTransferTypeChange(this.value)"
                           style="accent-color:var(--forest); width:15px; height:15px;">
                    <div>
                        <div style="font-size:13px; font-weight:600; color:var(--ink);">Change Pathway</div>
                        <div style="font-size:11px; color:var(--ink-3);">Move to different service track</div>
                    </div>
                </label>
            </div>
        </div>

        {{-- Pathway section (hidden by default) --}}
        <div id="jh-transfer-pathway-section" style="display:none; margin-bottom:16px; padding:14px; border:1px solid var(--rule); background:var(--surface);">
            <div style="font-size:9.5px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--ink-3); margin-bottom:10px;">
                Current: <span style="color:var(--forest);">{{ $case->assigned_pathway ?? '—' }}</span>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div>
                    <label style="display:block;margin-bottom:4px;font-size:10px;font-weight:500;letter-spacing:0.06em;text-transform:uppercase;color:var(--ink-3);">
                        New Pathway <span style="color:var(--burgundy);">*</span>
                    </label>
                    <select name="to_pathway" id="jh-transfer-pathway" class="inp" style="width:100%; font-size:12px;" onchange="jhTransferPathwayChange(this.value)">
                        <option value="">— Select pathway —</option>
                        @foreach(['Legal Advice / Consultation', 'Court Representation', 'Mediation', 'ADR / Dispute Resolution Support', 'Government Department / Public Institution', 'Civil Society / NGO / CSO / NPO'] as $pw)
                            @if($pw !== $case->assigned_pathway)
                            <option value="{{ $pw }}">{{ $pw }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;margin-bottom:4px;font-size:10px;font-weight:500;letter-spacing:0.06em;text-transform:uppercase;color:var(--ink-3);">
                        Specific <span style="color:var(--burgundy);">*</span>
                    </label>
                    <select name="to_pathway_specific" id="jh-transfer-specific" class="inp" style="width:100%; font-size:12px;">
                        <option value="">— Select —</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Assign To --}}
        <div style="margin-bottom:14px;">
            <label style="display:block;margin-bottom:6px;font-size:10px;font-weight:500;letter-spacing:0.06em;text-transform:uppercase;color:var(--ink-3);">
                Assign To <span style="color:var(--burgundy);">*</span>
            </label>
            <select name="to_assignee" id="jh-transfer-assignee" class="inp" required>
                <option value="">— Select staff member —</option>
                @foreach($allStaff as $u)
                    @if($u->name !== $case->assigned_to)
                    <option value="{{ $u->name }}" data-hub="{{ $u->hub_id }}" data-role="{{ $u->role->value }}">
                        {{ $u->name }} ({{ $u->designation ?: $u->role->label() }}{{ $u->hub_id !== $case->hub_id ? ' · ' . $u->hub_id : '' }})
                    </option>
                    @endif
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:14px;">
            <label style="display:block;margin-bottom:6px;font-size:10px;font-weight:500;letter-spacing:0.06em;text-transform:uppercase;color:var(--ink-3);">
                Effective Date <span style="color:var(--burgundy);">*</span>
            </label>
            <input type="date" name="transfer_date" class="inp" required value="{{ now()->format('Y-m-d') }}">
        </div>
        <div style="margin-bottom:20px;">
            <label style="display:block;margin-bottom:6px;font-size:10px;font-weight:500;letter-spacing:0.06em;text-transform:uppercase;color:var(--ink-3);">
                Reason for Transfer <span style="color:var(--burgundy);">*</span>
            </label>
            <textarea name="reason" class="inp" rows="3" required minlength="10" placeholder="Explain why the case is being transferred…" style="resize:vertical;"></textarea>
        </div>
        <div style="padding:10px 14px;background:var(--parchment);border:1px solid var(--rule);font-size:11.5px;color:var(--ink-3);margin-bottom:20px;">
            <x-lucide-info style="width:12px;height:12px;display:inline;vertical-align:-1px;margin-right:5px;" />
            This request will be sent for approval. The case will not be transferred until approved.
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn-primary">
                <x-lucide-arrow-right-left style="width:12px;height:12px;" /> Submit Transfer Request
            </button>
        </div>
    </form>
</x-jh-modal>
<script>
function jhTransferTypeChange(type) {
    var pwSection = document.getElementById('jh-transfer-pathway-section');
    var staffLabel = document.getElementById('jh-tt-staff-label');
    var pathwayLabel = document.getElementById('jh-tt-pathway-label');
    var pwSelect = document.getElementById('jh-transfer-pathway');

    if (type === 'pathway') {
        pwSection.style.display = '';
        staffLabel.style.borderColor = 'var(--rule)';
        staffLabel.style.background = '';
        pathwayLabel.style.borderColor = 'var(--forest)';
        pathwayLabel.style.background = 'rgba(22,48,41,0.04)';
        if (pwSelect) pwSelect.required = true;
    } else {
        pwSection.style.display = 'none';
        staffLabel.style.borderColor = 'var(--forest)';
        staffLabel.style.background = 'rgba(22,48,41,0.04)';
        pathwayLabel.style.borderColor = 'var(--rule)';
        pathwayLabel.style.background = '';
        if (pwSelect) { pwSelect.required = false; pwSelect.value = ''; }
        // Show all staff when in staff mode
        jhFilterStaff('');
    }
}
function jhTransferPathwayChange(pw) {
    jhFilterStaff(pw);
    jhUpdateTransferSpecific(pw);
}
function jhUpdateTransferSpecific(pw) {
    var sel = document.getElementById('jh-transfer-specific');
    if (!sel) return;
    var map = {
        'Legal Advice / Consultation': ['SLACC', 'Justice Hub Lawyer', 'NAZ Assist', 'Other'],
        'Court Representation': ['Justice Hub Lawyer', 'Other'],
        'Mediation': ['Justice Hub Accredited Mediator', 'MICADR', 'Other'],
        'ADR / Dispute Resolution Support': ['Provincial Ombudsman / Mohtasib', 'Federal Ombudsman', 'Other'],
        'Government Department / Public Institution': {!! json_encode(
            \App\Models\Partner::where('category', 'Government')->pluck('name')->push('Other')->unique()->values()
        ) !!},
        'Civil Society / NGO / CSO / NPO': {!! json_encode(
            \App\Models\Partner::where('category', 'NGO')->pluck('name')->push('Other')->unique()->values()
        ) !!},
    };
    var opts = map[pw] || [];
    sel.innerHTML = '<option value="">— Select —</option>' + opts.map(function(o) {
        return '<option value="' + o + '">' + o + '</option>';
    }).join('');
}
function jhFilterStaff(pw) {
    var sel = document.getElementById('jh-transfer-assignee');
    if (!sel) return;
    var roleMap = {
        'Court Representation': ['lawyer'],
        'Legal Advice / Consultation': ['lawyer', 'hub-coordinator'],
        'Mediation': ['hub-coordinator'],
        'ADR / Dispute Resolution Support': ['hub-coordinator'],
    };
    var allowedRoles = pw && roleMap[pw] ? roleMap[pw] : null;
    Array.from(sel.options).forEach(function(opt) {
        if (!opt.value) return;
        if (!allowedRoles) { opt.style.display = ''; return; }
        var role = opt.dataset.role || '';
        opt.style.display = allowedRoles.includes(role) || role === 'head' ? '' : 'none';
        if (opt.style.display === 'none' && opt.selected) { opt.selected = false; sel.value = ''; }
    });
}
</script>
@endif

{{-- ═══ Reject Transfer Modal ═══ --}}
@if($pendingTransfer && $canApprove)
<x-jh-modal name="reject-transfer" title="Reject Transfer Request" max-width="460px">
    <form method="POST" action="{{ route('cases.transfer.reject', [$case, $pendingTransfer]) }}">
        @csrf
        <p style="font-size:13px;color:var(--ink-2);margin:0 0 16px 0;">
            Rejecting transfer of <strong>{{ $case->case_uid }}</strong> from
            <strong>{{ $pendingTransfer->from_assignee }}</strong> to <strong>{{ $pendingTransfer->to_assignee }}</strong>.
        </p>
        <div style="margin-bottom:20px;">
            <label style="display:block;margin-bottom:6px;font-size:10px;font-weight:500;letter-spacing:0.06em;text-transform:uppercase;color:var(--ink-3);">
                Reason for Rejection <span style="color:var(--burgundy);">*</span>
            </label>
            <textarea name="approval_note" class="inp" rows="3" required minlength="5" placeholder="Explain why the transfer is rejected…" style="resize:vertical;"></textarea>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn-primary" style="background:var(--burgundy);border-color:var(--burgundy);">
                <x-lucide-x-circle style="width:12px;height:12px;" /> Reject Transfer
            </button>
        </div>
    </form>
</x-jh-modal>
@endif

{{-- ═══ Reject Pathway Modal ═══ --}}
@if($isPending && $canApprove)
<x-jh-modal name="reject-pathway" title="Reject Pathway" max-width="480px">
    <form method="POST" action="{{ route('cases.reject', $case) }}">
        @csrf
        <p style="font-size: 13px; color: var(--ink-2); margin: 0 0 16px 0;">
            Provide a reason for rejecting the <strong>{{ $pathways->first() }}</strong> pathway for {{ $case->name }}.
        </p>
        <x-form-input name="rejection_reason" label="Rejection Reason" type="textarea" required placeholder="Explain why and suggest alternative pathway…" />
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
            <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn-primary" style="background: var(--burgundy); border-color: var(--burgundy);">
                <x-lucide-x-circle style="width:12px;height:12px;" /> Reject Pathway
            </button>
        </div>
    </form>
</x-jh-modal>
@endif

{{-- ═══ Log Service Encounter Modal ═══ --}}
@if($canWrite && !$isResolved)
<x-jh-modal name="log-encounter" title="Log Service Encounter" max-width="560px">
    <form method="POST" action="{{ route('encounters.store', $case) }}">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
            <x-form-input name="date" label="Date" type="date" required :value="now()->format('Y-m-d')" />
            <x-form-select name="type" label="Service Type" required lookup-group="service.type" />
        </div>
        <x-form-input name="performed_by" label="Performed By" required :value="auth()->user()->name" />
        <div style="margin-top: 14px;">
            <x-form-input name="note" label="Encounter Notes" type="textarea" required placeholder="Describe what was done, outcome, next steps…" />
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
            <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn-primary"><x-lucide-plus style="width:12px;height:12px;" /> Log Encounter</button>
        </div>
    </form>
</x-jh-modal>
@endif

{{-- ═══ Resolve Case Modal ═══ --}}
@if($canEdit && !$isResolved && !$isPending)
<div class="modal fade" id="modal-resolve-case" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 500px; margin: 1.75rem auto;">
        <div class="modal-content" style="border: 1px solid var(--rule); border-radius: 4px; background: var(--parchment); box-shadow: 0 16px 48px rgba(0,0,0,.18);">

            <div style="padding: 22px 24px 18px; border-bottom: 1px solid var(--rule);">
                <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                    <div>
                        <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 6px;">{{ __('cases.case_resolution') }}</div>
                        <h2 class="serif" style="font-size: 26px; font-weight: 400; margin: 0;">{{ __('cases.resolve_uid', ['uid' => $case->case_uid]) }}</h2>
                    </div>
                    <button type="button" data-bs-dismiss="modal" style="background:none; border:1px solid var(--rule); cursor:pointer; padding:6px 8px; color:var(--ink-3); border-radius:3px;">
                        <x-lucide-x style="width:15px;height:15px;" />
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('cases.resolve', $case) }}" id="resolveForm">
                @csrf
                <div style="padding: 22px 24px;">

                    <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:8px;">
                        {{ __('cases.case_outcome') }} <span style="color:var(--burgundy);">*</span>
                    </label>

                    @if($case->assigned_pathway === 'ADR / Dispute Resolution Support')
                    {{-- ADR outcomes: 3 options --}}
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin-bottom: 20px;">
                        @foreach(['In Favour' => '#2f7a4d', 'Against' => 'var(--burgundy)', 'Withdrawn' => 'var(--ink-3)'] as $outcome => $color)
                        <label style="display:flex; flex-direction:column; align-items:center; gap:5px; padding:14px 6px; border:2px solid var(--rule); cursor:pointer; transition:all 120ms; text-align:center;"
                               onclick="this.querySelector('input').checked=true; document.querySelectorAll('#resolveForm [name=outcome]').forEach(r => r.closest('label').style.borderColor='var(--rule)'); this.style.borderColor='{{ $color }}';">
                            <input type="radio" name="outcome" value="{{ $outcome }}" required style="display:none;">
                            <span style="font-size:13px; font-weight:600; color:{{ $color }};">{{ $outcome }}</span>
                        </label>
                        @endforeach
                    </div>
                    <input type="hidden" name="resolution_type" value="Closed">
                    @else
                    {{-- Standard outcomes: 5 options --}}
                    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; margin-bottom: 20px;">
                        @foreach(['Won' => 'var(--moss)', 'Partial' => 'var(--ochre)', 'Lost' => 'var(--burgundy)', 'Withdrawn' => 'var(--ink-3)', 'Settlement' => 'var(--forest)'] as $outcome => $color)
                        <label style="display:flex; flex-direction:column; align-items:center; gap:5px; padding:12px 6px; border:2px solid var(--rule); cursor:pointer; transition:all 120ms; text-align:center;"
                               onclick="this.querySelector('input').checked=true; document.querySelectorAll('#resolveForm [name=outcome]').forEach(r => r.closest('label').style.borderColor='var(--rule)'); this.style.borderColor='{{ $color }}';">
                            <input type="radio" name="outcome" value="{{ $outcome }}" required style="display:none;">
                            <span style="font-size:13px; font-weight:600; color:{{ $color }};">{{ $outcome }}</span>
                        </label>
                        @endforeach
                    </div>

                    <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:8px;">
                        {{ __('cases.resolution_type') }} <span style="color:var(--burgundy);">*</span>
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 20px;">
                        <label style="display:flex; align-items:center; gap:10px; padding:12px 14px; border:1px solid var(--rule); cursor:pointer; transition:border-color 120ms;"
                               onmouseenter="this.style.borderColor='var(--ink-2)'" onmouseleave="if(!this.querySelector('input').checked)this.style.borderColor='var(--rule)'">
                            <input type="radio" name="resolution_type" value="Closed" required style="accent-color:var(--forest); width:15px; height:15px;">
                            <div>
                                <div style="font-size:13px; font-weight:600; color:var(--ink);">{{ __('common.closed') }}</div>
                                <div style="font-size:11px; color:var(--ink-3);">{{ __('cases.case_fully_concluded') }}</div>
                            </div>
                        </label>
                        <label style="display:flex; align-items:center; gap:10px; padding:12px 14px; border:1px solid var(--rule); cursor:pointer; transition:border-color 120ms;"
                               onmouseenter="this.style.borderColor='var(--ink-2)'" onmouseleave="if(!this.querySelector('input').checked)this.style.borderColor='var(--rule)'">
                            <input type="radio" name="resolution_type" value="Settlement" style="accent-color:var(--forest); width:15px; height:15px;">
                            <div>
                                <div style="font-size:13px; font-weight:600; color:var(--ink);">{{ __('common.settlement') }}</div>
                                <div style="font-size:11px; color:var(--ink-3);">{{ __('cases.resolved_via_agreement') }}</div>
                            </div>
                        </label>
                    </div>
                    @endif

                    <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">{{ __('cases.disposed_off_date_field') }} <span style="color:var(--burgundy);">*</span></label>
                    <input type="date" name="disposed_date" required value="{{ now()->format('Y-m-d') }}"
                           style="width:100%; padding:9px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px; margin-bottom:16px;" />

                    <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">{{ __('cases.resolution_notes') }}</label>
                    <textarea name="resolution_note" rows="3" placeholder="Court order details, settlement terms, reason for withdrawal…"
                              style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; resize:vertical; box-sizing:border-box; border-radius:2px; line-height:1.5;"></textarea>
                </div>

                <div style="padding:14px 24px; border-top:1px solid var(--rule); display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" data-bs-dismiss="modal" class="btn-ghost">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn-primary" style="background:var(--moss); border-color:var(--moss); display:inline-flex; align-items:center; gap:7px;">
                        <x-lucide-check-circle-2 style="width:13px;height:13px;" /> {{ __('cases.resolve_case') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ═══ Add Document Modal ═══ --}}
@if($canWrite && !$isResolved)
<x-jh-modal name="add-document" title="Upload Document" max-width="560px">
    <form method="POST" action="{{ route('documents.store', $case) }}" enctype="multipart/form-data">
        @csrf
        <div style="margin-bottom: 14px;">
            <x-form-input name="name" label="Document Title" required placeholder="e.g. CNIC Copy, Court Order, Medical Certificate" />
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
            <x-form-select name="type" label="Document Type" required lookup-group="document.type" />
            <x-form-select name="confidentiality" label="Confidentiality" required lookup-group="document.confidentiality" />
        </div>
        <div style="margin-bottom: 14px;">
            <x-form-input name="added_by" label="Uploaded By" required :value="auth()->user()->name" />
        </div>
        <div style="margin-bottom: 14px;">
            <x-form-input name="description" label="Description / Notes" type="textarea" placeholder="Brief description of the document..." />
        </div>
        <div style="margin-bottom: 18px;">
            <label style="display: block; margin-bottom: 6px; font-size: 10px; font-weight: 500; letter-spacing: 0.06em; text-transform: uppercase; color: var(--ink-3);">
                File <span style="color: var(--burgundy);"> *</span>
            </label>
            <div id="jh-doc-dropzone" style="border: 2px dashed var(--rule); padding: 28px 20px; text-align: center; cursor: pointer; transition: border-color 0.2s, background 0.2s;"
                 onclick="document.getElementById('jh-doc-file').click()"
                 ondragover="event.preventDefault(); this.style.borderColor='var(--forest)'; this.style.background='rgba(22,48,41,0.03)';"
                 ondragleave="this.style.borderColor='var(--rule)'; this.style.background='transparent';"
                 ondrop="event.preventDefault(); this.style.borderColor='var(--rule)'; this.style.background='transparent'; document.getElementById('jh-doc-file').files = event.dataTransfer.files; document.getElementById('jh-doc-filename').textContent = event.dataTransfer.files[0].name; document.getElementById('jh-doc-hint').style.display='none'; document.getElementById('jh-doc-selected').style.display='flex';">
                <div id="jh-doc-hint">
                    <x-lucide-upload-cloud style="width: 28px; height: 28px; color: var(--ink-4); margin: 0 auto 8px;" />
                    <div style="font-size: 13px; color: var(--ink-2); font-weight: 500;">Click to upload or drag & drop</div>
                    <div style="font-size: 11px; color: var(--ink-4); margin-top: 4px;">PDF, DOC, JPG, PNG up to 10MB</div>
                </div>
                <div id="jh-doc-selected" style="display: none; align-items: center; justify-content: center; gap: 8px;">
                    <x-lucide-file-check style="width: 18px; height: 18px; color: var(--moss);" />
                    <span id="jh-doc-filename" style="font-size: 13px; color: var(--ink); font-weight: 500;"></span>
                </div>
            </div>
            <input type="file" name="file" id="jh-doc-file" required style="display: none;" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx"
                   onchange="if(this.files[0]) { document.getElementById('jh-doc-filename').textContent = this.files[0].name; document.getElementById('jh-doc-hint').style.display='none'; document.getElementById('jh-doc-selected').style.display='flex'; }">
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn-primary">
                <x-lucide-upload style="width: 12px; height: 12px;" /> Upload Document
            </button>
        </div>
    </form>
</x-jh-modal>
@endif

{{-- ═══ Capture Feedback Modal ═══ --}}
@if($canWrite)
<x-jh-modal name="capture-feedback" title="" max-width="620px">
    <form method="POST" action="{{ route('feedback.store') }}">
        @csrf
        <input type="hidden" name="case_id" value="{{ $case->id }}">
        <input type="hidden" name="service" value="{{ $case->primary_issue }}">
        <input type="hidden" name="lawyer" value="{{ $case->assigned_to }}">

        {{-- Header --}}
        <div style="margin-bottom: 20px;">
            <div class="label-cap" style="font-size: 9.5px; color: var(--ink-4); margin-bottom: 6px;">Capture Client Feedback</div>
            <div class="serif" style="font-size: 22px; font-weight: 500; color: var(--ink);">Exit survey · 3 questions</div>
        </div>

        {{-- Case context --}}
        <div style="margin-bottom: 22px;">
            <div class="label-cap" style="font-size: 9px; margin-bottom: 6px;">Case · locked to current case</div>
            <div style="padding: 12px 16px; background: var(--parchment); border: 1px solid var(--rule);">
                <span class="mono" style="font-size: 12px; color: var(--forest); font-weight: 500;">{{ $case->case_ref }}</span>
                <span style="margin: 0 6px; color: var(--ink-4);">·</span>
                <span style="font-size: 13px; color: var(--ink);">{{ $case->name }}</span>
                <span style="margin: 0 6px; color: var(--ink-4);">·</span>
                <span style="font-size: 13px; color: var(--ink-3);">{{ $case->primary_issue }}</span>
            </div>
            <div style="font-size: 11px; color: var(--ink-4); margin-top: 4px;">{{ $case->hub?->name ?? $case->hub_id }} Hub · handled by {{ $case->assigned_to }}</div>
        </div>

        {{-- Q1: Overall --}}
        <div style="padding: 16px 18px; background: var(--ochre-tint); border: 1px solid rgba(184,115,25,0.15); margin-bottom: 10px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 14px; font-weight: 500; color: var(--ink);">1. Overall, how was your experience with the Hub?</div>
                    <div style="font-size: 11px; color: var(--ink-4); margin-top: 2px;">This is the headline indicator (O1.4)</div>
                </div>
                <div class="jh-star-group" data-target="score_overall" style="display: flex; gap: 4px; cursor: pointer;">
                    @for($i = 1; $i <= 5; $i++)
                    <span data-value="{{ $i }}" style="font-size: 24px; color: var(--rule); transition: color 0.15s;" onmouseenter="jhStarHover(this)" onmouseleave="jhStarLeave(this)" onclick="jhStarClick(this)">★</span>
                    @endfor
                </div>
            </div>
            <input type="hidden" name="score_overall" value="" required>
        </div>

        {{-- Q2: Helpfulness --}}
        <div style="padding: 16px 18px; background: var(--ochre-tint); border: 1px solid rgba(184,115,25,0.15); margin-bottom: 10px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 14px; font-weight: 500; color: var(--ink);">2. How helpful was the help you received?</div>
                    <div style="font-size: 11px; color: var(--ink-4); margin-top: 2px;">The substantive value of the service</div>
                </div>
                <div class="jh-star-group" data-target="score_helpfulness" style="display: flex; gap: 4px; cursor: pointer;">
                    @for($i = 1; $i <= 5; $i++)
                    <span data-value="{{ $i }}" style="font-size: 24px; color: var(--rule); transition: color 0.15s;" onmouseenter="jhStarHover(this)" onmouseleave="jhStarLeave(this)" onclick="jhStarClick(this)">★</span>
                    @endfor
                </div>
            </div>
            <input type="hidden" name="score_helpfulness" value="" required>
        </div>

        {{-- Q3: Respect --}}
        <div style="padding: 16px 18px; background: var(--ochre-tint); border: 1px solid rgba(184,115,25,0.15); margin-bottom: 18px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 14px; font-weight: 500; color: var(--ink);">3. Were you treated with respect and dignity?</div>
                    <div style="font-size: 11px; color: var(--ink-4); margin-top: 2px;">Safeguarding signal</div>
                </div>
                <div class="jh-star-group" data-target="score_respect" style="display: flex; gap: 4px; cursor: pointer;">
                    @for($i = 1; $i <= 5; $i++)
                    <span data-value="{{ $i }}" style="font-size: 24px; color: var(--rule); transition: color 0.15s;" onmouseenter="jhStarHover(this)" onmouseleave="jhStarLeave(this)" onclick="jhStarClick(this)">★</span>
                    @endfor
                </div>
            </div>
            <input type="hidden" name="score_respect" value="" required>
        </div>

        {{-- Rights + Recommend --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px;">
            <div>
                <div class="label-cap" style="font-size: 9.5px; margin-bottom: 8px;">Understood your rights?</div>
                <div style="display: flex; gap: 1px;">
                    @foreach(['Yes', 'Partial', 'No'] as $opt)
                    <label style="flex: 1; text-align: center; padding: 8px 0; border: 1px solid var(--rule); cursor: pointer; font-size: 12.5px; font-weight: 500; transition: all 0.15s;">
                        <input type="radio" name="understood_rights" value="{{ strtolower($opt) }}" style="display: none;"
                            onchange="this.closest('div').querySelectorAll('label').forEach(l => { l.style.background='var(--paper)'; l.style.color='var(--ink-2)'; }); this.parentElement.style.background='var(--forest)'; this.parentElement.style.color='var(--cream)';">
                        {{ $opt }}
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <div class="label-cap" style="font-size: 9.5px; margin-bottom: 8px;">Would you recommend the Hub?</div>
                <div style="display: flex; gap: 1px;">
                    @foreach(['Yes', 'Maybe', 'No'] as $opt)
                    <label style="flex: 1; text-align: center; padding: 8px 0; border: 1px solid var(--rule); cursor: pointer; font-size: 12.5px; font-weight: 500; transition: all 0.15s;">
                        <input type="radio" name="would_recommend" value="{{ strtolower($opt) }}" style="display: none;"
                            onchange="this.closest('div').querySelectorAll('label').forEach(l => { l.style.background='var(--paper)'; l.style.color='var(--ink-2)'; }); this.parentElement.style.background='var(--forest)'; this.parentElement.style.color='var(--cream)';">
                        {{ $opt }}
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Comment --}}
        <div style="margin-bottom: 18px;">
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 6px;">Comment (optional)</div>
            <textarea name="comment" class="inp" rows="3" style="width: 100%; font-size: 13px;" placeholder="Anything else you'd like to share — what worked, what didn't, what could be better."></textarea>
        </div>

        {{-- Channel --}}
        <div style="margin-bottom: 18px;">
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 8px;">Capture Channel</div>
            <div style="display: flex; gap: 1px;">
                @foreach([['in-person', 'In-person'], ['sms', 'SMS link'], ['phone', 'Phone follow-up']] as $ch)
                <label style="flex: 1; text-align: center; padding: 10px 0; border: 1px solid var(--rule); cursor: pointer; font-size: 12.5px; font-weight: 500; transition: all 0.15s;">
                    <input type="radio" name="channel" value="{{ $ch[0] }}" {{ $ch[0] === 'in-person' ? 'checked' : '' }} style="display: none;"
                        onchange="this.closest('div').querySelectorAll('label').forEach(l => { l.style.background='var(--paper)'; l.style.color='var(--ink-2)'; }); this.parentElement.style.background='var(--forest)'; this.parentElement.style.color='var(--cream)';">
                    {{ $ch[1] }}
                </label>
                @endforeach
            </div>
        </div>

        {{-- Checkboxes --}}
        <div style="padding: 14px 16px; background: var(--parchment); border: 1px solid var(--rule); margin-bottom: 20px;">
            <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; margin-bottom: 10px;">
                <input type="checkbox" name="is_anonymous" value="1" style="margin-top: 3px; accent-color: var(--forest);">
                <div>
                    <div style="font-size: 13px; font-weight: 500; color: var(--ink);">Submit anonymously</div>
                    <div style="font-size: 11px; color: var(--ink-4);">The case stays linked, but the client name is replaced with "Anonymous".</div>
                </div>
            </label>
            <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="consent_to_share" value="1" checked style="margin-top: 3px; accent-color: var(--forest);">
                <div>
                    <div style="font-size: 13px; font-weight: 500; color: var(--ink);">Consent to share comment in reports</div>
                    <div style="font-size: 11px; color: var(--ink-4);">Without this, the score still counts toward indicators but the comment text is kept internal.</div>
                </div>
            </label>
        </div>

        {{-- Footer --}}
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <div style="font-size: 12px; color: var(--moss); font-weight: 500;">Complete all 5 questions to save</div>
            <div style="display: flex; gap: 10px;">
                <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <x-lucide-check-circle-2 style="width: 13px; height: 13px;" /> Save feedback
                </button>
            </div>
        </div>
    </form>
</x-jh-modal>

<script>
// Star rating interaction
window.jhStarHover = function(star) {
    const group = star.closest('.jh-star-group');
    const val = parseInt(star.dataset.value);
    group.querySelectorAll('span').forEach(s => {
        s.style.color = parseInt(s.dataset.value) <= val ? 'var(--ochre)' : 'var(--rule)';
    });
};
window.jhStarLeave = function(star) {
    const group = star.closest('.jh-star-group');
    const target = group.dataset.target;
    const current = parseInt(document.querySelector('input[name="' + target + '"]').value) || 0;
    group.querySelectorAll('span').forEach(s => {
        s.style.color = parseInt(s.dataset.value) <= current ? 'var(--ochre)' : 'var(--rule)';
    });
};
window.jhStarClick = function(star) {
    const group = star.closest('.jh-star-group');
    const target = group.dataset.target;
    const val = parseInt(star.dataset.value);
    document.querySelector('input[name="' + target + '"]').value = val;
    group.querySelectorAll('span').forEach(s => {
        s.style.color = parseInt(s.dataset.value) <= val ? 'var(--ochre)' : 'var(--rule)';
    });
};

// Auto-select first channel on load
document.addEventListener('DOMContentLoaded', function() {
    const firstChannel = document.querySelector('input[name="channel"][value="in-person"]');
    if (firstChannel) {
        firstChannel.closest('label').style.background = 'var(--forest)';
        firstChannel.closest('label').style.color = 'var(--cream)';
    }
});
</script>
@endif

{{-- ═══ Log Complaint Modal ═══ --}}
@if($canWrite)
<x-jh-modal name="log-complaint" title="" max-width="560px">
    <form method="POST" action="{{ route('complaints.store') }}">
        @csrf
        <input type="hidden" name="case_id" value="{{ $case->id }}">
        <input type="hidden" name="hub_id" value="{{ $case->hub_id }}">

        {{-- Header --}}
        <div style="margin-bottom: 22px;">
            <div class="label-cap" style="font-size: 9.5px; color: var(--ink-4); margin-bottom: 6px;">New Complaint</div>
            <div class="serif" style="font-size: 22px; font-weight: 500; color: var(--ink);">Log a <em style="color: var(--ochre);">complaint</em></div>
        </div>

        {{-- Severity --}}
        <div style="margin-bottom: 18px;">
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 8px;">Severity (sets SLA)</div>
            <div style="display: flex; gap: 1px;" id="jh-severity-group">
                @foreach([['critical', 'Critical', '3d SLA'], ['high', 'High', '7d SLA'], ['medium', 'Medium', '14d SLA'], ['low', 'Low', '30d SLA']] as $sev)
                <label style="flex: 1; text-align: center; padding: 10px 0 8px; border: 1px solid var(--rule); cursor: pointer; transition: all 0.15s;">
                    <input type="radio" name="severity" value="{{ $sev[0] }}" {{ $sev[0] === 'medium' ? 'checked' : '' }} style="display: none;"
                        onchange="
                            this.closest('#jh-severity-group').querySelectorAll('label').forEach(l => { l.style.background='var(--paper)'; l.style.color='var(--ink-2)'; });
                            this.parentElement.style.background='var(--forest)'; this.parentElement.style.color='var(--cream)';
                            var slaMap = {critical:'3',high:'7',medium:'14',low:'30'};
                            document.getElementById('jh-sla-text').textContent = 'Must be resolved within ' + slaMap[this.value] + ' days from submission to count toward OP4.3.';
                            document.querySelector('input[name=sla_days]').value = slaMap[this.value];
                        ">
                    <div style="font-size: 13px; font-weight: 600;">{{ $sev[1] }}</div>
                    <div style="font-size: 10px; opacity: 0.7; margin-top: 2px;">{{ $sev[2] }}</div>
                </label>
                @endforeach
            </div>
            <input type="hidden" name="sla_days" value="14">
            <div style="margin-top: 8px; padding: 8px 12px; background: var(--ochre-tint); border: 1px solid rgba(184,115,25,0.15); font-size: 12px; color: var(--ink-2); display: flex; align-items: center; gap: 6px;">
                <x-lucide-info style="width: 13px; height: 13px; color: var(--ochre); flex-shrink: 0;" />
                <span id="jh-sla-text">Must be resolved within 14 days from submission to count toward OP4.3.</span>
            </div>
        </div>

        {{-- Category + Channel --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
            <x-form-select name="category" label="Category" required lookup-group="complaint.category" />
            <x-form-select name="channel" label="Channel" required lookup-group="complaint.channel" />
        </div>

        {{-- Submitted by --}}
        <div style="margin-bottom: 8px;">
            <x-form-input name="submitted_by" label="Submitted By" required placeholder="Name of complainant (or organisation)" />
        </div>
        <label style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--ink-3); cursor: pointer; margin-bottom: 16px;">
            <input type="checkbox" name="is_anonymous" value="1" style="accent-color: var(--forest);">
            Submit anonymously (no name recorded)
        </label>

        {{-- Hub + Case --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
            <div>
                <div class="label-cap" style="font-size: 10px; margin-bottom: 6px;">Hub where it occurred</div>
                <div class="inp" style="background: var(--parchment); color: var(--ink-2);">{{ $case->hub?->name ?? $case->hub_id }}</div>
            </div>
            <div>
                <div class="label-cap" style="font-size: 10px; margin-bottom: 6px;">Case ID · linked to this case</div>
                <div class="inp" style="background: var(--parchment); color: var(--ink-2);">{{ $case->case_ref }}</div>
            </div>
        </div>

        {{-- Description --}}
        <div style="margin-bottom: 18px;">
            <x-form-input name="description" label="What happened" type="textarea" required placeholder="Describe the issue in the complainant's own words where possible." />
        </div>

        {{-- Info note --}}
        <div style="padding: 10px 14px; background: var(--ochre-tint); border: 1px solid rgba(184,115,25,0.15); font-size: 12px; color: var(--ink-2); margin-bottom: 20px; display: flex; align-items: flex-start; gap: 8px;">
            <x-lucide-info style="width: 14px; height: 14px; color: var(--ochre); flex-shrink: 0; margin-top: 1px;" />
            <span>Complaints are logged with <strong>open</strong> status and assigned to the hub manager. Resolution must be recorded within <span id="jh-sla-note-days">14</span> days to count as on-time.</span>
        </div>

        {{-- Footer --}}
        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                <x-lucide-plus style="width: 13px; height: 13px;" /> Log complaint
            </button>
        </div>
    </form>
</x-jh-modal>

<script>
// Auto-select default severity on load
document.addEventListener('DOMContentLoaded', function() {
    var defSev = document.querySelector('#jh-severity-group input[value="medium"]');
    if (defSev) {
        defSev.closest('label').style.background = 'var(--forest)';
        defSev.closest('label').style.color = 'var(--cream)';
    }
});
</script>
@endif

{{-- ═══ Detailed Feedback Survey Modal (Case Context) ═══ --}}
@if($canWrite)
@php
    $svcMap = [
        'Legal Advice / Consultation' => 'Legal advice',
        'Court Representation' => 'Legal advice',
        'Mediation' => 'Mediation / ADR',
        'ADR / Dispute Resolution Support' => 'Mediation / ADR',
        'Government Department / Public Institution' => 'Referral to another service',
        'Civil Society / NGO / CSO / NPO' => 'Referral to another service',
    ];
    $autoService = $svcMap[$case->assigned_pathway] ?? '';
    $autoFirstVisit = $case->returning_client ? 'no' : 'yes';
@endphp
<div class="modal fade" id="modal-case-detailed-feedback" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width:680px; margin:1.75rem auto;">
        <div class="modal-content" style="border:1px solid var(--rule); border-radius:4px; background:var(--parchment); box-shadow:0 16px 48px rgba(0,0,0,.18); display:flex; flex-direction:column; max-height:88vh;">

            <div style="padding:18px 24px 14px; border-bottom:1px solid var(--rule); flex-shrink:0;">
                <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                    <div>
                        <div class="label-cap" style="font-size:9px; color:var(--ink-4); margin-bottom:4px;">Beneficiary Feedback · {{ $case->case_uid }}</div>
                        <h2 class="serif" style="font-size:22px; font-weight:400; margin:0;">
                            Section <span id="cds-section-num">A</span> · <span id="cds-section-title">Basic Information</span>
                        </h2>
                    </div>
                    <button type="button" data-bs-dismiss="modal" style="background:none; border:1px solid var(--rule); cursor:pointer; padding:5px 7px; color:var(--ink-3); border-radius:3px;">
                        <x-lucide-x style="width:14px;height:14px;" />
                    </button>
                </div>
                <div style="display:flex; gap:3px; margin-top:12px;">
                    @foreach(['A','B','C','D','E','F','G'] as $sec)
                    <div id="cds-prog-{{ $sec }}" style="flex:1; height:3px; background:var(--rule-2); border-radius:2px; transition:background 200ms;"></div>
                    @endforeach
                </div>
            </div>

            <form method="POST" action="{{ route('feedback.survey.store') }}" id="cdsFeedbackForm">
                @csrf
                <input type="hidden" name="case_id" value="{{ $case->id }}">
                <div style="padding:20px 24px; overflow-y:auto; flex:1;">

                    {{-- SECTION A --}}
                    <div class="cds-section" data-section="A">
                        <div style="padding:10px 14px; background:var(--surface); border:1px solid var(--rule); margin-bottom:16px;">
                            <div style="font-size:12px; color:var(--ink-2);"><strong>{{ $case->case_uid }}</strong> — {{ $case->name }} · {{ $case->hub_id }}</div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                            <div>
                                <label class="jh-field-label">4. Date of Visit</label>
                                <input type="date" name="visit_date" class="inp" value="{{ $case->intake_date?->format('Y-m-d') ?? now()->format('Y-m-d') }}">
                            </div>
                            <div>
                                <label class="jh-field-label">5. Date Service Received</label>
                                <input type="date" name="service_date" class="inp" value="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div style="margin-bottom:14px;">
                            <label class="jh-field-label">6. Type of Service Received *</label>
                            <select name="service_type" class="inp" required>
                                <option value="">— Select —</option>
                                @foreach(['Legal advice','Mediation / ADR','Documentation support','Referral to another service','Multiple services'] as $svc)
                                <option value="{{ $svc }}" @selected($svc === $autoService)>{{ $svc }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="margin-bottom:14px;">
                            <label class="jh-field-label">7. First visit?</label>
                            <div style="display:flex; gap:8px;">
                                <label class="cds-toggle-btn {{ $autoFirstVisit === 'yes' ? 'cds-active' : '' }}" onclick="cdsToggle('first_visit','yes',this)">Yes</label>
                                <label class="cds-toggle-btn {{ $autoFirstVisit === 'no' ? 'cds-active' : '' }}" onclick="cdsToggle('first_visit','no',this)">No</label>
                            </div>
                            <input type="hidden" name="first_visit" id="cds-first_visit" value="{{ $autoFirstVisit }}">
                        </div>
                        <div style="margin-bottom:14px;">
                            <label class="jh-field-label">Consent *</label>
                            <div style="display:flex; gap:8px;">
                                <label class="cds-toggle-btn cds-active" onclick="cdsToggle('consent','yes',this)">Yes</label>
                                <label class="cds-toggle-btn" onclick="cdsToggle('consent','no',this)">No</label>
                            </div>
                            <input type="hidden" name="consent" id="cds-consent" value="yes">
                        </div>
                    </div>

                    {{-- SECTION B --}}
                    <div class="cds-section" data-section="B" style="display:none;">
                        <div style="font-size:11px; color:var(--ink-4); margin-bottom:16px;">Scale: 1 = Very Poor, 2 = Poor, 3 = Fair, 4 = Good, 5 = Very Good</div>
                        @foreach([['q11_access','11. How easy was it to access the Justice Hub?'],['q12_reception','12. How respectfully were you received?'],['q13_explanation','13. Did staff explain their role clearly?'],['q14_waiting','14. Satisfied with waiting time?']] as [$f,$l])
                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">{{ $l }}</label>
                            <div style="display:flex; gap:6px;">@for($r=1;$r<=5;$r++)<label class="cds-scale-btn" onclick="cdsScale('{{ $f }}',{{ $r }},this)">{{ $r }}</label>@endfor</div>
                            <input type="hidden" name="{{ $f }}" id="cds-{{ $f }}" value="">
                        </div>
                        @endforeach
                        <div style="margin-bottom:14px;">
                            <label class="jh-field-label">15. Any difficulty accessing services?</label>
                            <select name="q15_difficulty" class="inp"><option value="">— Select —</option><option>No</option><option>Distance / transport</option><option>Timing</option><option>Disability / accessibility</option><option>Language barrier</option><option>Other</option></select>
                        </div>
                    </div>

                    {{-- SECTION C --}}
                    <div class="cds-section" data-section="C" style="display:none;">
                        @foreach([['q16_listened','16. Was your problem properly listened to?',['Yes, completely','To some extent','No']],['q17_comfortable','17. Comfortable sharing your issue?',['Yes, completely','Somewhat','No']]] as [$f,$l,$opts])
                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">{{ $l }}</label>
                            <div style="display:flex; gap:6px; flex-wrap:wrap;">@foreach($opts as $o)<label class="cds-toggle-btn" onclick="cdsToggle('{{ $f }}','{{ $o }}',this)">{{ $o }}</label>@endforeach</div>
                            <input type="hidden" name="{{ $f }}" id="cds-{{ $f }}" value="">
                        </div>
                        @endforeach
                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">18. How well did staff understand your problem? (1-5)</label>
                            <div style="display:flex; gap:6px;">@for($r=1;$r<=5;$r++)<label class="cds-scale-btn" onclick="cdsScale('q18_understood',{{ $r }},this)">{{ $r }}</label>@endfor</div>
                            <input type="hidden" name="q18_understood" id="cds-q18_understood" value="">
                        </div>
                        <div style="margin-bottom:14px;">
                            <label class="jh-field-label">19. Treated fairly regardless of background?</label>
                            <div style="display:flex; gap:6px;">@foreach(['Yes','Somewhat','No'] as $o)<label class="cds-toggle-btn" onclick="cdsToggle('q19_fair_treatment','{{ $o }}',this)">{{ $o }}</label>@endforeach</div>
                            <input type="hidden" name="q19_fair_treatment" id="cds-q19_fair_treatment" value="">
                        </div>
                    </div>

                    {{-- SECTION D --}}
                    <div class="cds-section" data-section="D" style="display:none;">
                        @foreach([['q20_info_safety','20. Comfortable that info won\'t be shared?',['Yes','Somewhat','No']],['q21_data_explained','21. Did staff explain data usage?',['Yes, clearly','Somewhat','No']]] as [$f,$l,$opts])
                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">{{ $l }}</label>
                            <div style="display:flex; gap:6px; flex-wrap:wrap;">@foreach($opts as $o)<label class="cds-toggle-btn" onclick="cdsToggle('{{ $f }}','{{ $o }}',this)">{{ $o }}</label>@endforeach</div>
                            <input type="hidden" name="{{ $f }}" id="cds-{{ $f }}" value="">
                        </div>
                        @endforeach
                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">22. Confidence info is handled safely? (1-5)</label>
                            <div style="display:flex; gap:6px;">@for($r=1;$r<=5;$r++)<label class="cds-scale-btn" onclick="cdsScale('q22_confidence',{{ $r }},this)">{{ $r }}</label>@endfor</div>
                            <input type="hidden" name="q22_confidence" id="cds-q22_confidence" value="">
                        </div>
                        <div style="margin-bottom:14px;">
                            <label class="jh-field-label">23. Informed about how to raise a complaint?</label>
                            <div style="display:flex; gap:6px;">@foreach(["Yes","No","Don't remember"] as $o)<label class="cds-toggle-btn" onclick="cdsToggle('q23_complaint_info','{{ $o }}',this)">{{ $o }}</label>@endforeach</div>
                            <input type="hidden" name="q23_complaint_info" id="cds-q23_complaint_info" value="">
                        </div>
                    </div>

                    {{-- SECTION E --}}
                    <div class="cds-section" data-section="E" style="display:none;">
                        @foreach([['q24_advice_useful','24. Was the advice useful?',['Yes, very useful','Somewhat useful','Not useful']],['q25_referral_clarity','25. Was the referral clear?',['Clear and explained properly','Somewhat clear','Not clear']],['q26_next_steps','26. Did staff explain next steps?',['Yes, clearly','Somewhat','No']],['q27_clarity','27. How clear about what to do next?',['Very clear','Clear','Neutral','Unclear','Very unclear']]] as [$f,$l,$opts])
                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">{{ $l }}</label>
                            <div style="display:flex; gap:6px; flex-wrap:wrap;">@foreach($opts as $o)<label class="cds-toggle-btn" onclick="cdsToggle('{{ $f }}','{{ $o }}',this)">{{ $o }}</label>@endforeach</div>
                            <input type="hidden" name="{{ $f }}" id="cds-{{ $f }}" value="">
                        </div>
                        @endforeach
                    </div>

                    {{-- SECTION F --}}
                    <div class="cds-section" data-section="F" style="display:none;">
                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">28. Overall satisfaction? (1-5)</label>
                            <div style="display:flex; gap:6px;">@for($r=1;$r<=5;$r++)<label class="cds-scale-btn" onclick="cdsScale('q28_satisfaction',{{ $r }},this)">{{ $r }}</label>@endfor</div>
                            <input type="hidden" name="q28_satisfaction" id="cds-q28_satisfaction" value="">
                        </div>
                        @foreach([['q29_resolution_help','29. Did visiting help resolve your problem?',['Yes','Somewhat','No']],['q30_recommend','30. Would you recommend the Justice Hub?',['Yes','Maybe','No']]] as [$f,$l,$opts])
                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">{{ $l }}</label>
                            <div style="display:flex; gap:6px;">@foreach($opts as $o)<label class="cds-toggle-btn" onclick="cdsToggle('{{ $f }}','{{ $o }}',this)">{{ $o }}</label>@endforeach</div>
                            <input type="hidden" name="{{ $f }}" id="cds-{{ $f }}" value="">
                        </div>
                        @endforeach
                        <div style="margin-bottom:14px;">
                            <label class="jh-field-label">31. How much do you trust the Justice Hub? (1-5)</label>
                            <div style="display:flex; gap:6px;">@for($r=1;$r<=5;$r++)<label class="cds-scale-btn" onclick="cdsScale('q31_trust',{{ $r }},this)">{{ $r }}</label>@endfor</div>
                            <input type="hidden" name="q31_trust" id="cds-q31_trust" value="">
                        </div>
                    </div>

                    {{-- SECTION G --}}
                    <div class="cds-section" data-section="G" style="display:none;">
                        <div style="font-size:11px; color:var(--ink-4); margin-bottom:16px;">Optional — capture the beneficiary's own words.</div>
                        @foreach([['q32_helpful_part','32. Most helpful part of your visit?'],['q33_improvement','33. What can be improved?'],['q34_additional','34. Additional comments?']] as [$f,$l])
                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">{{ $l }}</label>
                            <textarea name="{{ $f }}" rows="3" class="inp" style="width:100%; resize:vertical; box-sizing:border-box;"></textarea>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div style="padding:12px 24px; border-top:1px solid var(--rule); display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
                    <button type="button" id="cds-back-btn" class="btn-ghost" onclick="cdsNav(-1)" style="display:none;">← Back</button>
                    <div id="cds-section-hint" style="font-size:11px; color:var(--ink-4);">Section 1 of 7</div>
                    <div style="display:flex; gap:8px;">
                        <button type="button" data-bs-dismiss="modal" class="btn-ghost">Cancel</button>
                        <button type="button" id="cds-next-btn" class="btn-primary" onclick="cdsNav(1)" style="display:inline-flex; align-items:center; gap:6px;">
                            Continue <x-lucide-chevron-right style="width:12px;height:12px;" />
                        </button>
                        <button type="submit" id="cds-submit-btn" class="btn-primary" style="background:var(--moss); display:none;">
                            <x-lucide-check-circle-2 style="width:13px;height:13px;" /> Submit Survey
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .cds-toggle-btn { display:inline-flex; align-items:center; padding:8px 14px; border:2px solid var(--rule); cursor:pointer; font-size:12px; font-weight:500; color:var(--ink-2); transition:all 120ms; user-select:none; }
    .cds-toggle-btn:hover { border-color:var(--ink-3); }
    .cds-toggle-btn.cds-active { border-color:var(--forest); color:var(--forest); background:rgba(22,48,41,0.04); font-weight:600; }
    .cds-scale-btn { display:inline-flex; align-items:center; justify-content:center; width:44px; height:40px; border:2px solid var(--rule); cursor:pointer; font-size:14px; font-weight:600; color:var(--ink-3); transition:all 120ms; user-select:none; }
    .cds-scale-btn:hover { border-color:var(--ink-3); }
    .cds-scale-btn.cds-active { border-color:var(--forest); color:#fff; background:var(--forest); }
</style>
<script>
(function() {
    var secs = ['A','B','C','D','E','F','G'];
    var titles = ['Basic Information','Access & Welcome','Listening & Dignity','Confidentiality','Service Quality','Satisfaction & Trust','Open Feedback'];
    var step = 0;
    window.cdsNav = function(d) {
        step = Math.max(0, Math.min(6, step + d));
        document.querySelectorAll('.cds-section').forEach(function(el) { el.style.display = el.dataset.section === secs[step] ? '' : 'none'; });
        document.getElementById('cds-section-num').textContent = secs[step];
        document.getElementById('cds-section-title').textContent = titles[step];
        document.getElementById('cds-section-hint').textContent = 'Section ' + (step+1) + ' of 7';
        document.getElementById('cds-back-btn').style.display = step === 0 ? 'none' : '';
        document.getElementById('cds-next-btn').style.display = step === 6 ? 'none' : '';
        document.getElementById('cds-submit-btn').style.display = step === 6 ? '' : 'none';
        secs.forEach(function(s,i) { document.getElementById('cds-prog-'+s).style.background = i<=step ? 'var(--forest)' : 'var(--rule-2)'; });
    };
    cdsNav(0);
    window.cdsToggle = function(f,v,btn) {
        btn.parentElement.querySelectorAll('.cds-toggle-btn').forEach(function(b){b.classList.remove('cds-active');});
        btn.classList.add('cds-active');
        document.getElementById('cds-'+f).value = v;
    };
    window.cdsScale = function(f,r,btn) {
        btn.parentElement.querySelectorAll('.cds-scale-btn').forEach(function(b){b.classList.remove('cds-active');});
        btn.classList.add('cds-active');
        document.getElementById('cds-'+f).value = r;
    };
})();
</script>
@endif
</x-layouts.app>
