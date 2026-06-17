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
    $firstEncounterDate = $case->serviceEncounters->first()?->date?->toDateString();
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
        <x-lucide-chevron-left style="width: 13px; height: 13px;" /> Back to cases
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
                    @if($case->is_gbv)<x-pill color="var(--burgundy)" bg="rgba(138,46,29,0.08)" border-color="var(--burgundy)"><x-lucide-shield style="width:10px;height:10px;" /> GBV safeguarding</x-pill>@endif
                    @if($case->is_child)<x-pill color="var(--ochre)" bg="rgba(184,115,25,0.08)" border-color="var(--ochre)"><x-lucide-shield style="width:10px;height:10px;" /> Child protection</x-pill>@endif
                    @if($case->is_minority)<x-pill><x-lucide-flag style="width:10px;height:10px;" /> Minority</x-pill>@endif
                    @if($case->is_disability)<x-pill><x-lucide-heart-handshake style="width:10px;height:10px;" /> PwD</x-pill>@endif
                    @if($case->is_underserved)<x-pill color="var(--forest-3)" border-color="var(--forest-3)">Underserved population</x-pill>@endif
                    @if($case->returning_client)<x-pill bg="var(--parchment-2)">Returning client</x-pill>@endif
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
                    <span><x-lucide-calendar style="width:11px;height:11px;display:inline;vertical-align:-1px;margin-right:5px;" />Intake {{ $case->intake_date->format('M d, Y') }}</span>
                    <span><x-lucide-briefcase style="width:11px;height:11px;display:inline;vertical-align:-1px;margin-right:5px;" />Assigned: {{ $case->assigned_to }}</span>
                </div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px; align-items: flex-end;">
                @if($canWrite && !$isResolved)
                <div style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end;">
                    <a href="{{ route('cases.slip', $case) }}" target="_blank" class="btn-ghost" style="display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
                        <x-lucide-printer style="width:12px;height:12px;" /> Print Slip
                    </a>
                    <button class="btn-ghost" onclick="jhOpenModal('log-encounter')"><x-lucide-plus style="width:12px;height:12px;" /> Log service</button>
                    @if($canEdit && !$pendingTransfer)
                    <button class="btn-ghost" onclick="jhOpenModal('reassign-case')">
                        <x-lucide-arrow-right-left style="width:12px;height:12px;" /> Reassign
                    </button>
                    @endif
                    @if($canResolve && !$isResolved && !$isPending)
                    <button class="btn-primary" style="background: var(--moss); border-color: var(--moss);" onclick="jhOpenModal('resolve-case')">
                        <x-lucide-check-circle-2 style="width:12px;height:12px;" /> Mark as Resolved
                    </button>
                    @endif
                </div>
                @endif
                <div style="text-align: right;">
                    <div class="label-cap" style="font-size: 9px; margin-bottom: 2px;">Case Status</div>
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
            <div style="font-size:12.5px; font-weight:600; color:var(--ink);">Transfer pending approval</div>
            <div style="font-size:12px; color:var(--ink-3); margin-top:2px;">
                From <strong>{{ $pendingTransfer->from_assignee }}</strong> → <strong>{{ $pendingTransfer->to_assignee }}</strong>
                · Requested by {{ $pendingTransfer->transferredBy->name }} on {{ $pendingTransfer->transfer_date->format('M d, Y') }}
                · <em>{{ $pendingTransfer->reason }}</em>
            </div>
        </div>
        @if($canApprove)
        <div style="display:flex;gap:8px;flex-shrink:0;">
            <form method="POST" action="{{ route('cases.transfer.approve', [$case, $pendingTransfer]) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-primary" style="background:var(--moss);border-color:var(--moss);font-size:11px;padding:5px 12px;">
                    <x-lucide-check style="width:11px;height:11px;" /> Approve
                </button>
            </form>
            <button class="btn-ghost" style="font-size:11px;padding:5px 12px;" onclick="jhOpenModal('reject-transfer')">
                <x-lucide-x style="width:11px;height:11px;" /> Reject
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
            <div class="label-cap" style="font-size: 9px;">{{ $slaHours }}-hour SLA · {{ $case->urgency->value }}</div>
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
                Deadline: {{ $sla['deadline']->format('M d, Y H:i') }}
            </div>
        </div>
        <div class="card jh-anim-card" style="padding: 16px 18px;">
            <div class="label-cap" style="font-size: 9px;">Service Encounters</div>
            <div class="serif jh-anim-num" style="font-size: 22px; margin-top: 6px;">{{ $encounterCount }}</div>
            <div style="font-size: 11px; color: var(--ink-3);">Logged on this case</div>
        </div>
        <div class="card jh-anim-card" style="padding: 16px 18px;">
            <div class="label-cap" style="font-size: 9px;">Pathways</div>
            <div class="serif jh-anim-num" style="font-size: 22px; margin-top: 6px;">{{ $pathways->count() }}</div>
            <div style="font-size: 11px; color: var(--ink-3);">{{ $pathways->join(' · ') }}</div>
        </div>
        <div class="card jh-anim-card" style="padding: 16px 18px;">
            <div class="label-cap" style="font-size: 9px;">Risk & Urgency</div>
            <div class="serif jh-anim-num" style="font-size: 22px; margin-top: 6px; color: {{ $case->urgency->color() }};">{{ $case->urgency->value }}</div>
            <div style="font-size: 11px; color: var(--ink-3);">{{ $case->risk->value }} risk</div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         3. END-TO-END USER JOURNEY PROGRESS BAR (NEW)
         ═══════════════════════════════════════════════════════════ --}}
    <div class="card jh-anim-section" style="padding: 22px 26px; margin-bottom: 20px; animation-delay: 0.2s;">
        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 16px;">
            <div>
                <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">End-to-End User Journey</div>
                <div class="serif" style="font-size: 18px; font-weight: 400;">
                    @if($currentStep)
                        Step {{ $currentStep['num'] }} of 9 <span style="color: var(--ink-3); font-weight: 300;">&middot; {{ $currentStep['name'] }}</span>
                    @else
                        Journey Complete
                    @endif
                </div>
            </div>
            <div style="text-align: right;">
                <div class="mono" style="font-size: 20px; font-weight: 600; color: var(--forest);">{{ $pctComplete }}%</div>
                <div style="font-size: 11px; color: var(--ink-3);">complete &middot; of {{ $applicableSteps }} applicable steps</div>
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
                        <div class="mono" style="font-size: 8px; color: var(--ink-4); text-transform: uppercase; letter-spacing: 0.06em;">SKIPPED</div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Legend --}}
        <div style="display: flex; gap: 20px; margin-top: 14px; font-size: 11px; color: var(--ink-3);">
            <span style="display: inline-flex; align-items: center; gap: 5px;">
                <span style="width: 10px; height: 10px; background: var(--forest); display: inline-block;"></span> Current
            </span>
            <span style="display: inline-flex; align-items: center; gap: 5px;">
                <span style="width: 10px; height: 10px; background: var(--ochre-tint); border: 1px solid var(--ochre); display: inline-block;"></span> Completed
            </span>
            <span style="display: inline-flex; align-items: center; gap: 5px;">
                <span style="width: 10px; height: 10px; background: var(--paper); border: 1px solid var(--rule); display: inline-block;"></span> Pending
            </span>
            <span style="display: inline-flex; align-items: center; gap: 5px;">
                <span style="width: 10px; height: 10px; background: var(--rule-2); border: 1px solid var(--rule); display: inline-block;"></span> Not applicable
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
                    <div class="label-cap" style="font-size: 10px; color: var(--ochre);">Approval pending</div>
                </div>
                <div class="serif" style="font-size: 18px; font-weight: 500; margin-bottom: 6px;">Awaiting sign-off from {{ $case->pathway_manager ?? 'Manager' }}</div>
                <div style="font-size: 12.5px; color: var(--ink-2); line-height: 1.55;">
                    This case is on the <strong>{{ $pathways->first() }}</strong> pathway and cannot proceed until reviewed.
                    @if($case->requested_at) Submitted {{ $case->requested_at->format('M d, H:i') }}.@endif
                </div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px; flex-shrink: 0;">
                <form method="POST" action="{{ route('cases.approve', $case) }}">
                    @csrf
                    <button type="submit" style="background:var(--moss);color:var(--cream);border:1px solid var(--moss);padding:10px 18px;font-size:12.5px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px;">
                        <x-lucide-check-circle-2 style="width:13px;height:13px;" /> Approve & start service
                    </button>
                </form>
                <button onclick="jhOpenModal('reject-pathway')" style="background:transparent;color:var(--burgundy);border:1px solid var(--burgundy);padding:10px 18px;font-size:12.5px;font-weight:500;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:7px;">
                    <x-lucide-x-circle style="width:13px;height:13px;" /> Reject with reason
                </button>
            </div>
        </div>
    </div>
    @endif

    @if($isRejected)
    <div class="jh-anim-section" style="margin-bottom: 22px; padding: 20px 24px; background: var(--burgundy-tint); border: 1px solid var(--burgundy); border-left: 4px solid var(--burgundy); animation-delay: 0.25s;">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
            <x-lucide-x-circle style="width:14px;height:14px;color:var(--burgundy);" />
            <div class="label-cap" style="font-size: 10px; color: var(--burgundy);">Pathway rejected</div>
        </div>
        <div class="serif" style="font-size: 18px; font-weight: 500; margin-bottom: 6px;">Rejected by {{ $case->rejected_by }}</div>
        <div style="font-size: 12.5px; color: var(--ink-2); line-height: 1.55;">{{ $case->rejection_reason }}</div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         4. ENHANCED TAB NAVIGATION (6 tabs)
         ═══════════════════════════════════════════════════════════ --}}
    <ul class="nav" role="tablist" style="display: flex; gap: 0; border-bottom: 1px solid var(--rule); margin-bottom: 22px; padding: 0; list-style: none;">
        @foreach([
            ['id' => 'overview',   'label' => 'Overview',    'icon' => 'layout-dashboard'],
            ['id' => 'referrals',  'label' => 'Pathway',     'icon' => 'git-branch',       'count' => $pathways->count()],
            ['id' => 'documents',  'label' => 'Documents',   'icon' => 'file-text',        'count' => $case->documents->count()],
            ['id' => 'notes',      'label' => 'Case Notes',  'icon' => 'notebook-pen'],
            ['id' => 'feedback',   'label' => 'Feedback',    'icon' => 'heart-handshake',  'count' => $case->feedback->count()],
            ['id' => 'complaints', 'label' => 'Complaints',  'icon' => 'alert-triangle',   'count' => $case->complaints->count()],
        ] as $i => $t)
        <li class="nav-item" role="presentation">
            <button
                class="jh-case-tab {{ $i === 0 ? 'active' : '' }}"
                data-bs-toggle="tab"
                data-bs-target="#tab-{{ $t['id'] }}"
                role="tab"
                style="padding:12px 20px; border:none; border-bottom: 2px solid {{ $i === 0 ? 'var(--forest)' : 'transparent' }}; margin-bottom:-1px; cursor:pointer; font-family:inherit; display:inline-flex; align-items:center; gap:8px; font-size:13px; font-weight:{{ $i === 0 ? '600' : '500' }}; color:{{ $i === 0 ? 'var(--ink)' : 'var(--ink-3)' }}; background:transparent;"
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
            });
        });
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
    }
    </script>

    {{-- ═══════════════════════════════════════════════════════════
         TAB CONTENT
         ═══════════════════════════════════════════════════════════ --}}
    <div class="tab-content">

        {{-- ─────────────────────────────────────────────────────
             TAB 1: OVERVIEW (NEW comprehensive two-column layout)
             ───────────────────────────────────────────────────── --}}
        <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">

                {{-- ══ LEFT COLUMN ══ --}}
                <div style="display: flex; flex-direction: column; gap: 18px;">

                    {{-- Intake & Assessment --}}
                    <div class="card jh-anim-section" style="padding: 22px 26px; animation-delay: 0.1s;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                            <x-lucide-clipboard-list style="width: 15px; height: 15px; color: var(--forest);" />
                            <div class="label-cap" style="font-size: 10px;">Intake & Assessment</div>
                        </div>
                        <div>
                            <div class="jh-intake-row">
                                <div class="jh-intake-label">Date & Time of Intake</div>
                                <div class="jh-intake-value">{{ $case->intake_date->format('M d, Y') }} {{ $case->intake_time ?? '' }}</div>
                            </div>
                            <div class="jh-intake-row">
                                <div class="jh-intake-label">Intake Mode</div>
                                <div class="jh-intake-value">{{ $case->mode ?? '---' }}</div>
                            </div>
                            <div class="jh-intake-row">
                                <div class="jh-intake-label">Referral Source</div>
                                <div class="jh-intake-value">{{ $case->referral_source ?? '---' }}</div>
                            </div>
                            <div class="jh-intake-row">
                                <div class="jh-intake-label">Preferred Language</div>
                                <div class="jh-intake-value">{{ $case->language ?? '---' }}</div>
                            </div>
                            <div class="jh-intake-row">
                                <div class="jh-intake-label">Primary Legal Issue</div>
                                <div class="jh-intake-value">{{ $case->primary_issue ?? '---' }}</div>
                            </div>
                            <div class="jh-intake-row">
                                <div class="jh-intake-label">Secondary Issue</div>
                                <div class="jh-intake-value">{{ $case->secondary_issue ?? '---' }}</div>
                            </div>
                            <div class="jh-intake-row">
                                <div class="jh-intake-label">Urgency</div>
                                <div class="jh-intake-value" style="color: {{ $case->urgency->color() }};">{{ $case->urgency->value }}</div>
                            </div>
                            <div class="jh-intake-row">
                                <div class="jh-intake-label">Consent Obtained</div>
                                <div class="jh-intake-value">
                                    @if($case->consent)
                                        <span style="color: var(--moss);">
                                            <x-lucide-check-circle-2 style="width:12px;height:12px;display:inline;vertical-align:-1px;margin-right:4px;" />
                                            Yes &middot; informed consent recorded
                                        </span>
                                    @else
                                        <span style="color: var(--burgundy);">
                                            <x-lucide-x-circle style="width:12px;height:12px;display:inline;vertical-align:-1px;margin-right:4px;" />
                                            No{{ $case->no_consent_reason ? ' — ' . $case->no_consent_reason : '' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Safeguarding & Vulnerability --}}
                    <div class="card jh-anim-section" style="padding: 22px 26px; animation-delay: 0.18s;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                            <x-lucide-shield-check style="width: 15px; height: 15px; color: var(--burgundy);" />
                            <div class="label-cap" style="font-size: 10px;">Safeguarding & Vulnerability</div>
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
                                    <div style="font-size: 12px; color: var(--ink-3);">Immediate Risk Level</div>
                                    <div style="font-size: 13px; font-weight: 600; color: {{ $case->risk->color() }};">{{ $case->risk->value }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- LAS CMS Litigation Data --}}
                    @if($cmsData)
                    @php
                        // Helper to format date strings cleanly
                        $fmtDate = function(?string $d): ?string {
                            if (!$d || str_starts_with($d, '0000')) return null;
                            try { return \Carbon\Carbon::parse($d)->format('d M Y'); } catch (\Exception $e) { return $d; }
                        };
                        $cmsRows = [
                            ['label' => 'Case Number',          'value' => $cmsData->caseNumber,                        'icon' => 'hash'],
                            ['label' => 'FIR Number',           'value' => $cmsData->firNumber,                         'icon' => 'file-badge'],
                            ['label' => 'Police Station',       'value' => $cmsData->policeStation,                     'icon' => 'building-2'],
                            ['label' => 'Court Name',           'value' => $cmsData->courtName,                         'icon' => 'landmark'],
                            ['label' => 'Level of Court',       'value' => $cmsData->levelOfCourt,                      'icon' => 'layers'],
                            ['label' => 'Nature of Case',       'value' => $cmsData->natureOfCase,                      'icon' => 'scale'],
                            ['label' => 'Type of Case',         'value' => $cmsData->typeOfCase,                        'icon' => 'tag'],
                            ['label' => 'Main Category',        'value' => $cmsData->mainCaseCategory,                  'icon' => 'folder'],
                            ['label' => 'Filed Under Act',      'value' => $cmsData->caseFiledUnderAct,                 'icon' => 'book-open'],
                            ['label' => 'Assigned Lawyer',      'value' => $cmsData->lawyer1,                           'icon' => 'user'],
                            ['label' => 'Approval Date',        'value' => $fmtDate($cmsData->approvalDate),            'icon' => 'calendar-check'],
                            ['label' => 'Vakalatnama Date',     'value' => $fmtDate($cmsData->vakalatnamaSubmissionDate),'icon' => 'calendar'],
                            ['label' => 'Case File Date',       'value' => $fmtDate($cmsData->caseFileDate),            'icon' => 'calendar'],
                            ['label' => 'Next Hearing',         'value' => $fmtDate($cmsData->nextHearing),             'icon' => 'calendar-clock'],
                            ['label' => 'Case Stage',           'value' => $cmsData->caseStage,                         'icon' => 'git-branch'],
                            ['label' => 'Current Status',       'value' => $cmsData->currentCaseStatus,                 'icon' => 'activity'],
                            ['label' => 'Case Decision',        'value' => $cmsData->caseDecision,                      'icon' => 'gavel'],
                            ['label' => 'Disposal Date',        'value' => $fmtDate($cmsData->caseDisposalDate),        'icon' => 'calendar-x'],
                            ['label' => 'CMS Unique No.',       'value' => $cmsData->UniqueNumber2,                     'icon' => 'fingerprint'],
                        ];
                    @endphp
                    <div class="card jh-anim-section" style="padding: 22px 26px; animation-delay: 0.24s;">

                        {{-- Header --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <x-lucide-gavel style="width: 15px; height: 15px; color: var(--burgundy);" />
                                <div class="label-cap" style="font-size: 10px;">LAS CMS · Litigation Record</div>
                            </div>
                            <span style="font-size: 10px; color: var(--ink-4); font-family: monospace;">ID #{{ $case->external_case_id }}</span>
                        </div>

                        {{-- Highlight strip: Next Hearing + Status + Stage --}}
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 16px;">
                            @foreach([
                                ['label' => 'Next Hearing',    'value' => $fmtDate($cmsData->nextHearing) ?? '—',  'color' => 'var(--ochre)'],
                                ['label' => 'Current Status',  'value' => $cmsData->currentCaseStatus  ?: '—',  'color' => 'var(--forest)'],
                                ['label' => 'Case Stage',      'value' => $cmsData->caseStage          ?: '—',  'color' => 'var(--burgundy)'],
                            ] as $h)
                            <div style="padding: 10px 14px; background: var(--parchment); border: 1px solid var(--rule-2); border-top: 2px solid {{ $h['color'] }};">
                                <div style="font-size: 10px; color: var(--ink-4); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px;">{{ $h['label'] }}</div>
                                <div style="font-size: 13px; font-weight: 600; color: {{ $h['color'] }};">{{ $h['value'] ?: '—' }}</div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Full field grid --}}
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

                        <div style="margin-top: 10px; font-size: 10.5px; color: var(--ink-4); display: flex; align-items: center; gap: 5px;">
                            <x-lucide-refresh-cw style="width: 10px; height: 10px;" />
                            Synced from LAS CMS
                            @if($case->external_synced_at) · {{ \Carbon\Carbon::parse($case->external_synced_at)->format('d M Y, H:i') }} @endif
                        </div>
                    </div>
                    @endif

                    {{-- Activity Timeline --}}
                    <div class="card jh-anim-section" style="padding: 22px 26px; animation-delay: 0.26s;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <x-lucide-activity style="width: 15px; height: 15px; color: var(--forest);" />
                                <div class="label-cap" style="font-size: 10px;">Activity Timeline</div>
                            </div>
                            <span class="mono" style="font-size: 10px; color: var(--ink-4);">{{ $timeline->count() }} events</span>
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
                        <div style="font-size: 13px; color: var(--ink-4); font-style: italic;">No activity recorded yet.</div>
                        @endforelse
                    </div>

                    {{-- Approval History (moved here from separate tab) --}}
                    @if($hasApprovalHistory)
                    <div class="card jh-anim-section" style="padding: 22px 26px; animation-delay: 0.34s;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                            <x-lucide-check-circle-2 style="width: 15px; height: 15px; color: var(--ochre);" />
                            <div class="label-cap" style="font-size: 10px;">Approval History</div>
                        </div>
                        <div style="display: grid; grid-template-columns: 160px 1fr; gap: 8px; font-size: 13px;">
                            <div style="color: var(--ink-3);">Pathway Manager</div>
                            <div style="color: var(--ink-2); font-weight: 500;">{{ $case->pathway_manager ?? '---' }}</div>
                            <div style="color: var(--ink-3);">Decision</div>
                            <div style="color: var(--ink-2); font-weight: 500;">{{ ucfirst($case->approval_decision ?? 'N/A') }}</div>
                            @if($case->requested_at)
                            <div style="color: var(--ink-3);">Requested</div>
                            <div style="color: var(--ink-2);">{{ $case->requested_at->format('M d, Y H:i') }}</div>
                            @endif
                            @if($case->rejected_at)
                            <div style="color: var(--ink-3);">Rejected</div>
                            <div style="color: var(--ink-2);">{{ $case->rejected_at->format('M d, Y H:i') }} by {{ $case->rejected_by }}</div>
                            @endif
                            @if($case->rejection_reason)
                            <div style="color: var(--ink-3);">Reason</div>
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
                            <div class="label-cap" style="font-size: 10px;">Assigned Team</div>
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
                                <div style="font-size: 11px; color: var(--ink-3);">Primary &mdash; {{ $assignedTitle }}</div>
                            </div>
                            <x-lucide-mail style="width: 15px; height: 15px; color: var(--ink-4); cursor: pointer;" />
                        </div>
                        @else
                        <div style="font-size: 13px; color: var(--ink-4); font-style: italic;">Not yet assigned.</div>
                        @endif
                    </div>

                    {{-- Transfer History --}}
                    @if($case->transfers->isNotEmpty())
                    <div class="card jh-anim-section" style="padding: 22px 26px; animation-delay: 0.18s;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:14px;">
                            <x-lucide-arrow-right-left style="width:15px;height:15px;color:var(--forest);" />
                            <div class="label-cap" style="font-size:10px;">Transfer History</div>
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
                            <div class="label-cap" style="font-size: 10px;">LAS Core Services</div>
                        </div>
                        <div style="font-size: 11px; color: var(--ink-4); margin-bottom: 14px;">Which of the four pillars this client has accessed</div>
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
                                    <span style="font-size: 9px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--moss); background: rgba(56,102,65,0.08); padding: 2px 8px;">DELIVERED</span>
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
                            <div class="label-cap" style="font-size: 10px;">Related Indicators</div>
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
                            <div class="label-cap" style="font-size: 10px;">Consent & Privacy</div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                            @if($case->consent)
                                <x-lucide-check-circle-2 style="width: 16px; height: 16px; color: var(--moss);" />
                                <span style="font-size: 13px; font-weight: 500; color: var(--moss);">Consent recorded</span>
                                <span class="mono" style="font-size: 10px; color: var(--ink-4);">{{ $case->intake_date->format('M d, Y') }}</span>
                            @else
                                <x-lucide-x-circle style="width: 16px; height: 16px; color: var(--burgundy);" />
                                <span style="font-size: 13px; font-weight: 500; color: var(--burgundy);">Consent not obtained</span>
                            @endif
                        </div>
                        <div style="font-size: 11.5px; color: var(--ink-3); line-height: 1.55; padding: 10px 14px; background: var(--parchment); border: 1px solid var(--rule-2);">
                            Client consented to anonymised data use for learning and aggregate reporting. Name masked in UI per data protection protocol.
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────────
             TAB 2: PATHWAY
             ───────────────────────────────────────────────────── --}}
        <div class="tab-pane fade" id="tab-referrals" role="tabpanel">

            @php
                $isMediationCase = in_array($case->assigned_pathway, ['Mediation', 'ADR / Dispute Resolution Support']);
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
                        $steps = [1 => 'Opposing party', 2 => 'Consent to mediate', 3 => 'Mediation diary'];
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

                {{-- ── STEP 1: Opposing Parties ── --}}
                <div id="mstep-panel-1" style="{{ $activeMstep == 1 ? '' : 'display:none;' }}">
                    <div class="card" style="padding: 26px 30px; max-width: 640px;">
                        <h3 class="serif" style="font-size: 20px; font-weight: 500; margin: 0 0 6px;">Who needs to be contacted for mediation?</h3>
                        <p style="font-size: 13px; color: var(--ink-3); margin: 0 0 22px; line-height: 1.5;">Record the opposing party (or parties) you'll invite to mediation.</p>

                        <form method="POST" action="{{ route('mediation.party.store', $case) }}">
                            @csrf
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                                <div>
                                    <label style="font-size: 11px; font-weight: 600; color: var(--ink-3); display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Full name</label>
                                    <input type="text" name="name" placeholder="Party name" required class="inp" style="width: 100%; font-size: 13px; box-sizing: border-box;" />
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 600; color: var(--ink-3); display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Role</label>
                                    <select name="role" class="inp" style="width: 100%; font-size: 13px; box-sizing: border-box;">
                                        <option>Respondent</option>
                                        <option>Applicant</option>
                                        <option>Witness</option>
                                        <option>Representative</option>
                                        <option>Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 600; color: var(--ink-3); display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Phone</label>
                                    <input type="text" name="phone" placeholder="+92 ..." class="inp" style="width: 100%; font-size: 13px; box-sizing: border-box;" />
                                </div>
                                <div>
                                    <label style="font-size: 11px; font-weight: 600; color: var(--ink-3); display: block; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.04em;">Note (optional)</label>
                                    <input type="text" name="note" placeholder="How to reach them, preferred time..." class="inp" style="width: 100%; font-size: 13px; box-sizing: border-box;" />
                                </div>
                            </div>
                            <button type="submit" style="padding: 8px 18px; background: none; border: 1.5px solid var(--rule); color: var(--ink-2); font-size: 13px; font-family: inherit; cursor: pointer;">+ Add party</button>
                        </form>

                        {{-- Parties list --}}
                        @if($parties->count())
                        <div style="margin-top: 18px; display: flex; flex-direction: column; gap: 8px;">
                            @foreach($parties as $party)
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: var(--paper); border: 1px solid var(--rule); border-radius: 4px;">
                                <div>
                                    <div style="font-size: 13px; font-weight: 600; color: var(--ink);">{{ $party->name }}</div>
                                    <div style="font-size: 12px; color: var(--ink-3); margin-top: 2px;">{{ $party->role }}{{ $party->phone ? ' · ' . $party->phone : '' }}</div>
                                </div>
                                <form method="POST" action="{{ route('mediation.party.destroy', [$case, $party]) }}" onsubmit="return confirm('Remove this party?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: var(--ink-4); cursor: pointer; padding: 4px;">×</button>
                                </form>
                            </div>
                            @endforeach
                        </div>
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
                                @foreach($parties as $party)
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; background: var(--paper); border: 1px solid var(--rule); border-radius: 4px;">
                                    <div>
                                        <div style="font-size: 13px; font-weight: 600; color: var(--ink);">{{ $party->name }}</div>
                                        <div style="font-size: 12px; color: var(--ink-3); margin-top: 1px;">{{ $party->role }}{{ $party->phone ? ' · ' . $party->phone : '' }}</div>
                                    </div>
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
                                </div>
                                @endforeach
                            </div>

                            @if(!$anyAgreed)
                            <div style="padding: 12px 16px; background: var(--ochre-tint); border: 1px solid rgba(196,130,57,0.25); color: var(--ochre); font-size: 12.5px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                                <x-lucide-lock style="width: 13px; height: 13px; flex-shrink: 0;" />
                                Mediation stays locked until a party agrees.
                            </div>
                            @endif

                            <div style="display: flex; gap: 10px; align-items: center;">
                                <button type="button" onclick="jhMstep(1)" style="padding: 9px 20px; background: none; border: 1.5px solid var(--rule); color: var(--ink-2); font-size: 13px; font-family: inherit; cursor: pointer;">Back</button>
                                <button type="submit" style="padding: 9px 20px; background: {{ $anyAgreed ? 'var(--forest)' : 'var(--rule-2)' }}; color: {{ $anyAgreed ? 'var(--cream)' : 'var(--ink-4)' }}; border: none; font-size: 13px; font-family: inherit; font-weight: 600; cursor: {{ $anyAgreed ? 'pointer' : 'default' }};">
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
        <div class="tab-pane fade" id="tab-documents" role="tabpanel">

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
             TAB 5: CASE NOTES (NEW)
             ───────────────────────────────────────────────────── --}}
        <div class="tab-pane fade" id="tab-notes" role="tabpanel">
            <div style="display: flex; flex-direction: column; gap: 18px;">
                {{-- Issue Description --}}
                <div class="card" style="padding: 22px 26px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 14px;">
                        <x-lucide-file-text style="width: 15px; height: 15px; color: var(--forest);" />
                        <div class="label-cap" style="font-size: 10px;">Issue Description</div>
                    </div>
                    @if($case->issue_description)
                    <div style="font-size: 13.5px; color: var(--ink-2); line-height: 1.65; white-space: pre-wrap;">{{ $case->issue_description }}</div>
                    @else
                    <div style="font-size: 13px; color: var(--ink-4); font-style: italic;">No issue description recorded.</div>
                    @endif
                </div>

                {{-- Case Summary --}}
                <div class="card" style="padding: 22px 26px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 14px;">
                        <x-lucide-notebook-pen style="width: 15px; height: 15px; color: var(--forest);" />
                        <div class="label-cap" style="font-size: 10px;">Case Summary</div>
                    </div>
                    @if($case->summary)
                    <div style="font-size: 13.5px; color: var(--ink-2); line-height: 1.65; white-space: pre-wrap;">{{ $case->summary }}</div>
                    @else
                    <div style="font-size: 13px; color: var(--ink-4); font-style: italic;">No case summary recorded.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ─────────────────────────────────────────────────────
             TAB 6: FEEDBACK (kept)
             ───────────────────────────────────────────────────── --}}
        <div class="tab-pane fade" id="tab-feedback" role="tabpanel">
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
                    <button class="btn-primary" onclick="jhOpenModal('capture-feedback')" style="display: inline-flex; align-items: center; gap: 6px;">
                        <x-lucide-plus style="width: 13px; height: 13px;" /> Capture feedback
                    </button>
                    @endif
                </div>
                @endforelse

                @if($case->feedback->count() > 0 && $canWrite)
                <div style="padding-top: 16px; border-top: 1px solid var(--rule-2); margin-top: 8px;">
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
        <div class="tab-pane fade" id="tab-complaints" role="tabpanel">
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

    </div>{{-- end tab-content --}}
</div>

{{-- ═══ Reassign Case Modal ═══ --}}
@if($canEdit && !$isResolved && !$pendingTransfer)
<x-jh-modal name="reassign-case" title="Reassign / Transfer Case" max-width="520px">
    <form method="POST" action="{{ route('cases.reassign', $case) }}">
        @csrf
        <p style="font-size:13px;color:var(--ink-2);margin:0 0 16px 0;">
            Reassigning <strong>{{ $case->case_uid }}</strong> currently assigned to <strong>{{ $case->assigned_to ?? 'Unassigned' }}</strong>.
            A reason and approval are required for accountability.
        </p>
        <div style="margin-bottom:14px;">
            <label style="display:block;margin-bottom:6px;font-size:10px;font-weight:500;letter-spacing:0.06em;text-transform:uppercase;color:var(--ink-3);">
                Assign To <span style="color:var(--burgundy);">*</span>
            </label>
            <select name="to_assignee" class="inp" required>
                <option value="">— Select staff member —</option>
                @foreach($assignableUsers as $u)
                    @if($u->name !== $case->assigned_to)
                    <option value="{{ $u->name }}">{{ $u->name }} ({{ $u->designation ?: $u->role->label() }})</option>
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
            This request will be sent for approval. The case will not be reassigned until approved.
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn-primary">
                <x-lucide-arrow-right-left style="width:12px;height:12px;" /> Submit Transfer Request
            </button>
        </div>
    </form>
</x-jh-modal>
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
                        <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 6px;">Case Resolution</div>
                        <h2 class="serif" style="font-size: 26px; font-weight: 400; margin: 0;">Resolve {{ $case->case_uid }}</h2>
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
                        Case Outcome <span style="color:var(--burgundy);">*</span>
                    </label>
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
                        Resolution Type <span style="color:var(--burgundy);">*</span>
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 20px;">
                        <label style="display:flex; align-items:center; gap:10px; padding:12px 14px; border:1px solid var(--rule); cursor:pointer; transition:border-color 120ms;"
                               onmouseenter="this.style.borderColor='var(--ink-2)'" onmouseleave="if(!this.querySelector('input').checked)this.style.borderColor='var(--rule)'">
                            <input type="radio" name="resolution_type" value="Closed" required style="accent-color:var(--forest); width:15px; height:15px;">
                            <div>
                                <div style="font-size:13px; font-weight:600; color:var(--ink);">Closed</div>
                                <div style="font-size:11px; color:var(--ink-3);">Case fully concluded</div>
                            </div>
                        </label>
                        <label style="display:flex; align-items:center; gap:10px; padding:12px 14px; border:1px solid var(--rule); cursor:pointer; transition:border-color 120ms;"
                               onmouseenter="this.style.borderColor='var(--ink-2)'" onmouseleave="if(!this.querySelector('input').checked)this.style.borderColor='var(--rule)'">
                            <input type="radio" name="resolution_type" value="Settlement" style="accent-color:var(--forest); width:15px; height:15px;">
                            <div>
                                <div style="font-size:13px; font-weight:600; color:var(--ink);">Settlement</div>
                                <div style="font-size:11px; color:var(--ink-3);">Resolved via agreement</div>
                            </div>
                        </label>
                    </div>

                    <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Resolution Notes</label>
                    <textarea name="resolution_note" rows="3" placeholder="Court order details, settlement terms, reason for withdrawal…"
                              style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; resize:vertical; box-sizing:border-box; border-radius:2px; line-height:1.5;"></textarea>
                </div>

                <div style="padding:14px 24px; border-top:1px solid var(--rule); display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" data-bs-dismiss="modal" class="btn-ghost">Cancel</button>
                    <button type="submit" class="btn-primary" style="background:var(--moss); border-color:var(--moss); display:inline-flex; align-items:center; gap:7px;">
                        <x-lucide-check-circle-2 style="width:13px;height:13px;" /> Resolve Case
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
</x-layouts.app>
