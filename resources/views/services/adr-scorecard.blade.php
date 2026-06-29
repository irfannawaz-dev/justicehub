<x-layouts.app>
@php
    $awaitingFirst = collect($pipeline['Mediation Intake'] ?? [])->count();
    $gbvActive = collect($cases)->where('is_gbv', true)->filter(fn($c) => in_array($c->status->value, ['Active','Pending Approval']))->count();
    $uniqueHubs = $staff->pluck('hub_id')->unique()->count();
    $staffCount = $staff->count();

    // Avatar colors pool
    $avatarColors = ['#163029','#8a2e1d','#b87319','#4a7a5c','#6b6a65','#3d5a47','#7a4a2e','#2e5a7a','#5a2e6b','#6b5a2e'];

    // Stage config
    $stageConfig = [
        'Mediation Intake' => ['color' => 'var(--ink-3)',    'dot' => '#8a8a84', 'subtitle' => 'Accepted · awaiting 1st session'],
        'Joint Session'    => ['color' => 'var(--ochre)',    'dot' => '#b87319', 'subtitle' => 'Sessions in progress'],
        'Agreement Draft'  => ['color' => 'var(--moss)',     'dot' => '#4a7a5c', 'subtitle' => 'Agreement being finalised'],
        'Resolved'         => ['color' => '#2f7a4d',         'dot' => '#2f7a4d', 'subtitle' => 'Settled & compliance confirmed'],
        'Escalated'        => ['color' => 'var(--burgundy)', 'dot' => '#8a2e1d', 'subtitle' => 'Moved to court · failed mediation'],
    ];

    // Outcome colors for chart and bars
    $outcomeColors = [
        'Settled via Mediation' => '#4a7a5c',
        'Ongoing Mediation'     => '#b87319',
        'Escalated'             => '#8a2e1d',
        'Withdrawn'             => '#8a8a84',
    ];
    $outcomeTotal = array_sum($outcomes);
@endphp

{{-- ═══ Animations ═══ --}}
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
    .jh-kanban-card:hover { border-color: var(--ochre) !important; box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
    .jh-kanban-card { transition: border-color 150ms ease, box-shadow 150ms ease; }
    .adr-kanban-wrap::-webkit-scrollbar { width: 4px; }
    .adr-kanban-wrap::-webkit-scrollbar-track { background: transparent; }
    .adr-kanban-wrap::-webkit-scrollbar-thumb { background: var(--rule); border-radius: 2px; }
    .adr-kanban-wrap::-webkit-scrollbar-thumb:hover { background: var(--ink-4); }
    .jh-staff-row:hover { background: var(--parchment); }
    .jh-staff-row { transition: background 120ms ease; }
    .jh-pill-filter { cursor: pointer; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink-2); padding: 5px 14px; font-size: 12px; font-weight: 500; transition: all 150ms ease; }
    .jh-pill-filter:hover { border-color: var(--forest); color: var(--forest); }
    .jh-pill-filter.active { background: var(--forest); color: var(--cream); border-color: var(--forest); }
</style>

<div style="padding: 24px 34px 64px; max-width: 1600px; margin: 0 auto;">

    {{-- ════════════════════════════════════════════════════════════
         1. HEADER
         ════════════════════════════════════════════════════════════ --}}
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 28px; animation: jh-fade-up 0.5s ease both;">
        <div>
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px; letter-spacing: 0.12em;">
                SERVICE DELIVERY &middot; REPRESENTATION & MEDIATION
            </div>
            <h1 class="serif" style="font-size: 34px; font-weight: 400; letter-spacing: -0.02em; margin: 0; line-height: 1.15;">
                Services & <em style="color: var(--ochre); font-style: italic;">Mediation</em>
            </h1>
            <p style="margin: 6px 0 0 0; font-size: 13px; color: var(--ink-3); max-width: 520px; line-height: 1.45;">
                Mediation pathway performance, service delivery tracking, and staff workload for {{ $total }} mediation cases across the programme.
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px; flex-shrink: 0;">
            <button onclick="jhOpenModal('log-service')" style="display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; font-size: 12px; font-weight: 500; font-family: inherit; border: 1px solid var(--rule); background: transparent; color: var(--ink-2); cursor: pointer;">
                <x-lucide-plus style="width: 13px; height: 13px;" /> Log service
            </button>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         2. KPI STRIP
         ════════════════════════════════════════════════════════════ --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 32px;">

        {{-- O2.1 ADR Resolution Rate --}}
        <div class="card jh-scorecard jh-anim-card" style="padding: 20px 22px; border-top: 3px solid var(--ochre); display: flex; flex-direction: column; gap: 6px; min-height: 140px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div class="label-cap" style="font-size: 9.5px; line-height: 1.4;">O2.1 Mediation Resolution Rate</div>
                <x-lucide-heart-handshake style="width: 13px; height: 13px; color: var(--ink-4);" />
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: 4px;">
                <span class="serif jh-anim-num" style="font-size: 44px; font-weight: 400; line-height: 1; letter-spacing: -0.02em;">{{ $rate }}</span>
                <span class="serif" style="font-size: 22px; color: var(--ink-2);">%</span>
            </div>
            <div style="margin-top: auto;">
                <div style="display: flex; align-items: center; gap: 4px; font-size: 11px; color: var(--moss); font-weight: 600;">
                    <x-lucide-trending-up style="width: 12px; height: 12px;" />
                    <span>+3 pp vs last quarter</span>
                </div>
                <div style="font-size: 11px; color: var(--ink-3); margin-top: 2px;">{{ $settled }} of {{ $total }} resolved &middot; target 70%</div>
            </div>
        </div>

        {{-- Active Mediations --}}
        <div class="card jh-scorecard jh-anim-card" style="padding: 20px 22px; border-top: 3px solid var(--ochre); display: flex; flex-direction: column; gap: 6px; min-height: 140px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div class="label-cap" style="font-size: 9.5px; line-height: 1.4;">Active Mediations</div>
                <x-lucide-gavel style="width: 13px; height: 13px; color: var(--ink-4);" />
            </div>
            <div class="serif jh-anim-num" style="font-size: 44px; font-weight: 400; line-height: 1; letter-spacing: -0.02em; margin-top: 4px;">{{ $active }}</div>
            <div style="margin-top: auto;">
                <div style="font-size: 11px; color: var(--ink-3);">{{ $awaitingFirst }} awaiting first session</div>
                <div style="display: inline-flex; align-items: center; gap: 3px; margin-top: 3px; font-size: 11px;">
                    <span style="display: inline-flex; align-items: center; gap: 3px; padding: 2px 7px; background: rgba(184,115,25,0.1); color: var(--ochre); font-weight: 600; border-radius: 10px;">
                        <x-lucide-shield style="width: 10px; height: 10px;" /> {{ $gbvActive }} are GBV-sensitive
                    </span>
                </div>
            </div>
        </div>

        {{-- Avg Days to Resolution --}}
        <div class="card jh-scorecard jh-anim-card" style="padding: 20px 22px; border-top: 3px solid var(--moss); display: flex; flex-direction: column; gap: 6px; min-height: 140px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div class="label-cap" style="font-size: 9.5px; line-height: 1.4;">Avg Days to Resolution</div>
                <x-lucide-clock style="width: 13px; height: 13px; color: var(--ink-4);" />
            </div>
            <div class="serif jh-anim-num" style="font-size: 44px; font-weight: 400; line-height: 1; letter-spacing: -0.02em; margin-top: 4px;">{{ $avgDays }}</div>
            <div style="margin-top: auto;">
                <div style="height: 6px; background: var(--rule-2); border-radius: 3px; overflow: hidden; margin-bottom: 6px;">
                    <div class="jh-anim-bar" style="height: 100%; width: {{ $avgDays > 0 ? min(100, round($avgDays/45*100)) : 0 }}%; background: {{ $avgDays <= 45 ? 'var(--moss)' : 'var(--burgundy)' }}; border-radius: 3px;"></div>
                </div>
                @if($avgDays > 0 && $avgDays <= 45)
                <span style="font-size: 11px; font-weight: 600; color: var(--moss);">{{ 45 - $avgDays }} days under target</span>
                @elseif($avgDays > 45)
                <span style="font-size: 11px; font-weight: 600; color: var(--burgundy);">{{ $avgDays - 45 }} days over target</span>
                @else
                <span style="font-size: 11px; color: var(--ink-3);">No resolved cases yet</span>
                @endif
                <span style="font-size: 11px; color: var(--ink-3);"> &middot; target &le; 45</span>
            </div>
        </div>

        {{-- Services Delivered --}}
        <div class="card jh-scorecard jh-anim-card" style="padding: 20px 22px; border-top: 3px solid var(--moss); display: flex; flex-direction: column; gap: 6px; min-height: 140px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div class="label-cap" style="font-size: 9.5px; line-height: 1.4;">Services Delivered &middot; Q</div>
                <x-lucide-activity style="width: 13px; height: 13px; color: var(--ink-4);" />
            </div>
            <div class="serif jh-anim-num" style="font-size: 44px; font-weight: 400; line-height: 1; letter-spacing: -0.02em; margin-top: 4px;">{{ $servicesDelivered }}</div>
            <div style="margin-top: auto;">
                <div style="display: flex; align-items: center; gap: 4px; font-size: 11px; color: var(--moss); font-weight: 600;">
                    <x-lucide-trending-up style="width: 12px; height: 12px;" />
                    <span>+12% vs last quarter</span>
                </div>
                <div style="font-size: 11px; color: var(--ink-3); margin-top: 2px;">total service encounters this quarter</div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         3. MEDIATION CASELOAD (KANBAN)
         ════════════════════════════════════════════════════════════ --}}
    <div style="margin-bottom: 36px; animation: jh-fade-up 0.6s ease both; animation-delay: 0.15s;">
        <div style="display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 6px;">
            <div>
                <h2 class="serif" style="font-size: 26px; font-weight: 400; letter-spacing: -0.015em; margin: 0; line-height: 1.1;">
                    Mediation caseload
                </h2>
                <div style="font-size: 12.5px; color: var(--ink-3); margin-top: 5px;">
                    {{ $active }} active cases across five stages &middot; cards sorted by days-in-stage
                </div>
            </div>
        </div>

        {{-- Filter pills --}}
        <div id="kanbanFilters" style="display: flex; gap: 6px; margin-bottom: 16px; flex-wrap: wrap;">
            <button class="jh-pill-filter active" data-filter="all" onclick="filterKanban('all', this)">All cases</button>
            <button class="jh-pill-filter" data-filter="gbv" onclick="filterKanban('gbv', this)">
                <span style="display: inline-flex; align-items: center; gap: 3px;"><x-lucide-shield style="width: 11px; height: 11px;" /> GBV-flagged</span>
            </button>
            <button class="jh-pill-filter" data-filter="child" onclick="filterKanban('child', this)">
                <span style="display: inline-flex; align-items: center; gap: 3px;"><x-lucide-baby style="width: 11px; height: 11px;" /> Child protection</span>
            </button>
            <button class="jh-pill-filter" data-filter="minority" onclick="filterKanban('minority', this)">
                <span style="display: inline-flex; align-items: center; gap: 3px;"><x-lucide-users style="width: 11px; height: 11px;" /> Minority</span>
            </button>
            <button class="jh-pill-filter" data-filter="disability" onclick="filterKanban('disability', this)">
                <span style="display: inline-flex; align-items: center; gap: 3px;"><x-lucide-accessibility style="width: 11px; height: 11px;" /> PwD</span>
            </button>
        </div>

        {{-- Pipeline search --}}
        <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
            <div style="position: relative; max-width: 280px; flex: 1;">
                <x-lucide-search style="width:13px;height:13px; position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--ink-4); pointer-events:none;" />
                <input type="text" id="adr-search" placeholder="Search name or UID…"
                    oninput="adrSearchCards(this.value)"
                    class="inp" style="padding-left:30px; font-size:12.5px; height:34px; width:100%; box-sizing:border-box;" />
            </div>
            <span id="adr-search-info" style="font-size:11px; color:var(--ink-4);"></span>
        </div>

        {{-- 5-Column Kanban Board --}}
        <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; align-items: start;">
            @foreach($pipeline as $stage => $stageCases)
            @php
                $cfg = $stageConfig[$stage] ?? ['color' => 'var(--ink-3)', 'dot' => '#8a8a84', 'subtitle' => ''];
                $sortedCases = collect($stageCases)->sortByDesc('days_in_stage')->values();
                $showNextSession = in_array($stage, ['Mediation Intake', 'Joint Session']);
            @endphp
            <div class="adr-kanban-col">
                {{-- Column header (sticky) --}}
                <div style="padding: 10px 12px; margin-bottom: 8px; border-bottom: 2px solid {{ $cfg['color'] }}; background: var(--parchment); position: sticky; top: 0; z-index: 2;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 3px;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $cfg['dot'] }}; display: inline-block;"></span>
                            <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink);">{{ $stage }}</span>
                        </div>
                        <span class="mono adr-kanban-count" style="font-size: 12px; font-weight: 600; color: var(--ink-2);">{{ count($stageCases) }}</span>
                    </div>
                    <div style="font-size: 10px; color: var(--ink-4); line-height: 1.3;">{{ $cfg['subtitle'] }}</div>
                </div>

                {{-- Cards --}}
                <div class="adr-kanban-wrap" style="display: flex; flex-direction: column; gap: 6px; min-height: 80px; max-height: calc(100vh - 340px); overflow-y: auto; padding-right: 2px; padding-bottom: 6px;">
                    @forelse($sortedCases as $case)
                    @php
                        $cInitials = collect(explode(' ', $case->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('');
                        $avatarBg = $avatarColors[abs(crc32($case->name ?? '')) % count($avatarColors)];
                        $nextSession = $case->serviceEncounters->where('date', '>=', now()->toDateString())->first();
                        $adrStages = ['Mediation Intake', 'Joint Session', 'Agreement Draft', 'Resolved', 'Escalated'];
                        $currentAdrStage = $case->adr_stage ?? 'Mediation Intake';
                    @endphp
                    <div class="card jh-kanban-card"
                         style="padding: 12px 13px; display: block; cursor: pointer;"
                         onclick="window.location='{{ route('cases.show', $case) }}'"
                         data-name="{{ strtolower($case->name ?? '') }}"
                         data-uid="{{ strtolower($case->case_uid ?? '') }}"
                         data-gbv="{{ $case->is_gbv ? '1' : '0' }}"
                         data-child="{{ $case->is_child ? '1' : '0' }}"
                         data-minority="{{ $case->is_minority ? '1' : '0' }}"
                         data-disability="{{ $case->is_disability ? '1' : '0' }}">

                        {{-- Row 1: Case UID (link) + days badge --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                            <a href="{{ route('cases.show', $case) }}" class="mono" style="font-size: 10px; color: var(--ink-4); text-decoration: none;" onclick="event.stopPropagation()">{{ $case->case_uid }}</a>
                            <span class="mono" style="font-size: 10px; font-weight: 600; padding: 1px 6px; background: {{ $case->days_in_stage > 14 ? 'var(--burgundy-tint)' : 'var(--ochre-tint)' }}; color: {{ $case->days_in_stage > 14 ? 'var(--burgundy)' : 'var(--ochre)' }};">{{ $case->days_in_stage }}d</span>
                        </div>

                        {{-- Row 2: Avatar + client name --}}
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                            <div style="width: 24px; height: 24px; border-radius: 50%; background: {{ $avatarBg }}; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 600; flex-shrink: 0;">{{ $cInitials }}</div>
                            <span style="font-size: 12.5px; font-weight: 600; color: var(--ink); line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $case->name }}</span>
                        </div>

                        {{-- Row 3: Primary issue + secondary issue --}}
                        <div style="font-size: 11px; color: var(--ink-3); margin-bottom: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $case->primary_issue }}@if($case->secondary_issue) &middot; {{ $case->secondary_issue }}@endif
                        </div>

                        {{-- Row 4: Hub ID + session count --}}
                        <div style="font-size: 10px; color: var(--ink-4); margin-bottom: 4px;">
                            {{ $case->hub_id }} &middot; {{ $case->session_count > 0 ? $case->session_count . ' session' . ($case->session_count > 1 ? 's' : '') : 'No sessions' }}
                        </div>

                        {{-- Row 5: Next session (only for ADR Intake and In Mediation) --}}
                        @if($showNextSession && $nextSession)
                        <div style="display: flex; align-items: center; gap: 4px; font-size: 10px; color: var(--ink-3); padding: 3px 0; margin-bottom: 3px;">
                            <x-lucide-calendar style="width: 10px; height: 10px; color: var(--ink-4);" />
                            <span>Next</span>
                            <span style="font-weight: 600;">{{ \Carbon\Carbon::parse($nextSession->date)->format('d M Y') }}</span>
                        </div>
                        @endif

                        {{-- Row 6: Assigned lawyer --}}
                        @if($case->assigned_to)
                        <div style="font-size: 10px; color: var(--ink-3); margin-bottom: 6px;">
                            <x-lucide-user style="width: 9px; height: 9px; display: inline; vertical-align: -1px; color: var(--ink-4);" />
                            {{ $case->assigned_to }}
                        </div>
                        @endif

                        {{-- Stage dropdown --}}
                        <div style="margin-bottom: 4px;" onclick="event.stopPropagation()">
                            <select onchange="adrUpdateStage(this, {{ $case->id }})"
                                style="width: 100%; padding: 4px 7px; font-size: 11px; font-family: inherit; border: 1px solid var(--rule); background: var(--parchment); color: var(--ink); cursor: pointer; border-radius: 2px;">
                                @foreach($adrStages as $s)
                                <option value="{{ $s }}" {{ $currentAdrStage === $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Changed by / at --}}
                        @if($case->adr_stage_changed_by)
                        @php
                            $changer = \App\Models\User::find($case->adr_stage_changed_by);
                            $changedAt = $case->adr_stage_changed_at ? \Carbon\Carbon::parse($case->adr_stage_changed_at)->format('d M, H:i') : '';
                        @endphp
                        <div id="adr-stage-meta-{{ $case->id }}" style="font-size: 9.5px; color: var(--ink-4); margin-bottom: 4px;">
                            {{ $changer?->name ?? '—' }} &middot; {{ $changedAt }}
                        </div>
                        @else
                        <div id="adr-stage-meta-{{ $case->id }}" style="font-size: 9.5px; color: var(--ink-4); margin-bottom: 4px; display: none;"></div>
                        @endif

                        {{-- Vulnerability flags --}}
                        @if($case->is_gbv || $case->is_child || $case->is_minority || $case->is_underserved || $case->is_disability)
                        <div style="display: flex; flex-wrap: wrap; gap: 3px; margin-top: 4px;">
                            @if($case->is_gbv)
                            <span style="font-size: 8.5px; padding: 1px 5px; background: var(--burgundy-tint); color: var(--burgundy); font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase;">GBV</span>
                            @endif
                            @if($case->is_child)
                            <span style="font-size: 8.5px; padding: 1px 5px; background: var(--ochre-tint); color: var(--ochre); font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase;">CHILD</span>
                            @endif
                            @if($case->is_minority)
                            <span style="font-size: 8.5px; padding: 1px 5px; background: rgba(74,122,92,0.12); color: var(--moss); font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase;">MIN</span>
                            @endif
                            @if($case->is_underserved)
                            <span style="font-size: 8.5px; padding: 1px 5px; background: var(--rule-2); color: var(--ink-3); font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase;">UNDSVD</span>
                            @endif
                            @if($case->is_disability)
                            <span style="font-size: 8.5px; padding: 1px 5px; background: rgba(46,90,122,0.12); color: #2e5a7a; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase;">PwD</span>
                            @endif
                        </div>
                        @endif
                    </div>
                    @empty
                    <div style="padding: 24px 14px; text-align: center; color: var(--ink-4); font-size: 11px; border: 1px dashed var(--rule);">
                        No cases in this stage
                    </div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         4. RESOLUTION OUTCOMES
         ════════════════════════════════════════════════════════════ --}}
    <div class="card" style="padding: 0; margin-bottom: 36px; overflow: hidden; animation: jh-fade-up 0.6s ease both; animation-delay: 0.25s;">

        {{-- Card header bar --}}
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 22px; border-bottom: 1px solid var(--rule); background: var(--parchment);">
            <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.1em;">COMPLETED + ONGOING &middot; ALL TIME</div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="label-cap" style="font-size: 9.5px; letter-spacing: 0.1em;">INDICATOR O2.1</span>
                <span class="mono" style="font-size: 18px; font-weight: 700; color: var(--ochre);">{{ $rate }}%</span>
                <span style="font-size: 11px; color: var(--ink-3);">resolved via mediation</span>
            </div>
        </div>

        <div style="padding: 28px 22px;">
            <h3 class="serif" style="font-size: 24px; font-weight: 400; letter-spacing: -0.015em; margin: 0 0 4px 0;">
                Resolution outcomes
            </h3>
            <p style="font-size: 12.5px; color: var(--ink-3); margin: 0 0 24px 0;">
                How {{ $total }} cases were resolved across the programme
            </p>

            {{-- Two-column: doughnut left, bars right --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: center;">

                {{-- Left: Doughnut chart --}}
                <div style="position: relative;">
                    @php
                        $chartOutcomes = [
                            'labels' => array_keys($outcomes),
                            'values' => array_values($outcomes),
                            'colors' => array_values($outcomeColors),
                        ];
                    @endphp
                    <div data-chart="serviceMixPie"
                         data-chart-config='{{ json_encode($chartOutcomes) }}'
                         style="height: 240px; position: relative;">
                        <canvas></canvas>
                    </div>
                    {{-- Center label --}}
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; pointer-events: none;">
                        <div class="serif" style="font-size: 32px; font-weight: 500; line-height: 1;">{{ $total }}</div>
                        <div class="label-cap" style="font-size: 9px; color: var(--ink-3); margin-top: 2px;">TOTAL CASES</div>
                    </div>
                </div>

                {{-- Right: Horizontal bars --}}
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    @foreach($outcomes as $label => $count)
                    @php
                        $barColor = $outcomeColors[$label] ?? '#8a8a84';
                        $pct = $total > 0 ? round(($count / $total) * 100) : 0;
                    @endphp
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="width: 10px; height: 10px; border-radius: 2px; background: {{ $barColor }}; display: inline-block;"></span>
                                <span style="font-size: 13px; font-weight: 500; color: var(--ink);">{{ $label }}</span>
                            </div>
                            <div style="display: flex; align-items: baseline; gap: 6px;">
                                <span class="mono" style="font-size: 14px; font-weight: 600;">{{ $count }}</span>
                                <span class="mono" style="font-size: 11px; color: var(--ink-4);">{{ $pct }}%</span>
                            </div>
                        </div>
                        <div style="height: 8px; background: var(--rule-2); border-radius: 4px; overflow: hidden;">
                            <div class="jh-anim-bar" style="height: 100%; width: {{ $pct }}%; background: {{ $barColor }}; border-radius: 4px; animation-delay: {{ 0.4 + $loop->index * 0.1 }}s;"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <p style="font-size: 11px; color: var(--ink-4); margin: 24px 0 0 0; line-height: 1.5; border-top: 1px solid var(--rule-2); padding-top: 14px;">
                Mediation resolution rate counts cases settled via mediation over the sum of completed routes (settled + escalated + withdrawn). Ongoing cases are excluded from the denominator until they reach a terminal state.
            </p>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         5. LAWYER & PARALEGAL WORKLOAD
         ════════════════════════════════════════════════════════════ --}}
    <div style="margin-bottom: 32px; animation: jh-fade-up 0.6s ease both; animation-delay: 0.35s;">
        <div style="margin-bottom: 14px;">
            <h2 class="serif" style="font-size: 26px; font-weight: 400; letter-spacing: -0.015em; margin: 0; line-height: 1.1;">
                Lawyer & paralegal <em style="font-style: italic;">workload</em>
            </h2>
            <div style="font-size: 12.5px; color: var(--ink-3); margin-top: 5px;">
                Active caseload vs. capacity &middot; SLA-aware &middot; {{ $staffCount }} staff across {{ $uniqueHubs }} hubs
            </div>
        </div>

        {{-- Staff filter pills --}}
        <div id="staffFilters" style="display: flex; gap: 6px; margin-bottom: 14px;">
            <button class="jh-pill-filter active" data-staff-filter="all" onclick="filterStaff('all', this)">All</button>
            <button class="jh-pill-filter" data-staff-filter="Lawyer" onclick="filterStaff('Lawyer', this)">Lawyers</button>
            <button class="jh-pill-filter" data-staff-filter="Paralegal" onclick="filterStaff('Paralegal', this)">Paralegals</button>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--rule);">
                        <th style="text-align: left; padding: 11px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Staff Member</th>
                        <th style="text-align: left; padding: 11px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Role &middot; Hub</th>
                        <th style="text-align: left; padding: 11px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Active Caseload</th>
                        <th style="text-align: center; padding: 11px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Mediation</th>
                        <th style="text-align: center; padding: 11px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Court</th>
                        <th style="text-align: center; padding: 11px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">SLA</th>
                        <th style="text-align: right; padding: 11px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Utilisation</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $s)
                    @php
                        $sAvatarBg = $avatarColors[abs(crc32($s['name'] ?? '')) % count($avatarColors)];
                        $barColor = $s['utilization'] >= 90 ? 'var(--burgundy)' : ($s['utilization'] >= 70 ? 'var(--ochre)' : 'var(--moss)');
                        $utilColor = $s['utilization'] >= 90 ? 'var(--burgundy)' : ($s['utilization'] >= 70 ? 'var(--ochre)' : 'var(--ink-2)');
                        $slaClean = $s['sla_breach'] === 0;
                    @endphp
                    <tr class="jh-staff-row jh-anim-row"
                        data-staff-role="{{ $s['role'] }}"
                        style="border-bottom: 1px solid var(--rule-2); animation-delay: {{ 0.4 + $loop->index * 0.04 }}s;">

                        {{-- Staff Member --}}
                        <td style="padding: 12px 14px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 30px; height: 30px; border-radius: 50%; background: {{ $sAvatarBg }}; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 600; flex-shrink: 0;">{{ $s['initials'] }}</div>
                                <span style="font-weight: 500; color: var(--ink);">{{ $s['name'] }}</span>
                            </div>
                        </td>

                        {{-- Role + Hub --}}
                        <td style="padding: 12px 14px;">
                            <span style="font-size: 12px; color: var(--ink-2);">{{ $s['designation'] ?: $s['role'] }}</span>
                            <span style="font-size: 12px; color: var(--ink-4);"> &middot; {{ $s['hub'] }}</span>
                        </td>

                        {{-- Active Caseload with bar --}}
                        <td style="padding: 12px 14px; width: 200px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="flex: 1; height: 6px; background: var(--rule-2); border-radius: 3px; overflow: hidden;">
                                    <div class="jh-anim-bar" style="height: 100%; width: {{ $s['utilization'] }}%; background: {{ $barColor }}; border-radius: 3px; animation-delay: {{ 0.5 + $loop->index * 0.04 }}s;"></div>
                                </div>
                                <span class="mono" style="font-size: 11px; font-weight: 500; white-space: nowrap;">{{ $s['active'] }} / {{ $s['capacity'] }}</span>
                            </div>
                        </td>

                        {{-- ADR --}}
                        <td style="padding: 12px 14px; text-align: center;">
                            <span class="mono" style="font-size: 13px; font-weight: 600; color: var(--ochre);">{{ $s['adr'] }}</span>
                        </td>

                        {{-- Court --}}
                        <td style="padding: 12px 14px; text-align: center;">
                            <span class="mono" style="font-size: 13px; color: var(--ink-2);">{{ $s['court'] }}</span>
                        </td>

                        {{-- SLA --}}
                        <td style="padding: 12px 14px; text-align: center;">
                            @if($slaClean)
                            <span style="display: inline-flex; align-items: center; gap: 3px; font-size: 11px; font-weight: 600; color: var(--moss);">
                                <x-lucide-check-circle style="width: 12px; height: 12px;" /> Clean
                            </span>
                            @else
                            <span style="display: inline-flex; align-items: center; gap: 3px; font-size: 11px; font-weight: 600; color: var(--burgundy);">
                                <x-lucide-alert-triangle style="width: 12px; height: 12px;" /> {{ $s['sla_breach'] }} breach{{ $s['sla_breach'] > 1 ? 'es' : '' }}
                            </span>
                            @endif
                        </td>

                        {{-- Utilisation --}}
                        <td style="padding: 12px 14px; text-align: right;">
                            <span class="mono" style="font-size: 13px; font-weight: 600; color: {{ $utilColor }};">{{ $s['utilization'] }}%</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <x-empty-state icon="user-check" message="No staff records found." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════════
     NEW ADR REFERRAL MODAL
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-new-adr-referral" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 640px; margin: 1.75rem auto;">
        <div class="modal-content" style="border-radius: 4px; background: var(--paper); box-shadow: 0 16px 48px rgba(0,0,0,0.16); max-height: 92vh; overflow-y: auto; border-top: 3px solid var(--ochre);">

            {{-- Header --}}
            <div style="padding: 18px 24px 14px; border-bottom: 1px solid var(--rule);">
                <div class="label-cap" style="font-size: 9px; letter-spacing: 0.14em; color: var(--ochre); margin-bottom: 5px;">
                    SERVICE DELIVERY &middot; MEDIATION PATHWAY
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <h3 class="serif" style="font-size: 22px; font-weight: 400; margin: 0; color: var(--forest); letter-spacing: -0.01em;">
                        New Mediation Referral
                    </h3>
                    <button type="button" data-bs-dismiss="modal"
                        style="background: none; border: none; cursor: pointer; color: var(--ink-3); padding: 4px; line-height: 1;">
                        <x-lucide-x style="width: 16px; height: 16px;" />
                    </button>
                </div>
            </div>

            {{-- Form --}}
            <form action="{{ route('services.adr.referral') }}" method="POST" style="padding: 0;">
                @csrf

                {{-- ── Section 1: Case to Refer ── --}}
                <div style="padding: 20px 24px 16px; border-bottom: 1px solid var(--rule-2);">
                    <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.1em; color: var(--ink-3); margin-bottom: 12px;">
                        CASE TO REFER
                    </div>
                    <p style="font-size: 12px; color: var(--ink-3); margin: 0 0 10px 0; line-height: 1.4;">
                        Select the active case being routed to mediation
                    </p>

                    {{-- Searchable case picker --}}
                    <input type="hidden" name="case_id" id="casePickerValue" required>
                    <div style="position: relative;" id="casePicker">
                        <div style="position: relative;">
                            <x-lucide-search style="width: 13px; height: 13px; position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--ink-4); pointer-events: none;" />
                            <input type="text" id="casePickerInput" autocomplete="off"
                                placeholder="Type case ID or client name..."
                                style="width: 100%; padding: 9px 36px 9px 32px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); box-sizing: border-box; outline: none;"
                                onfocus="openCasePicker()" oninput="filterCasePicker(this.value)">
                            <x-lucide-chevron-down id="casePickerChevron" style="width: 13px; height: 13px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--ink-3); pointer-events: none; transition: transform 200ms ease;" />
                        </div>
                        <div id="casePickerDropdown"
                            style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: var(--paper); border: 1px solid var(--rule); border-top: none; max-height: 240px; overflow-y: auto; box-shadow: 0 8px 24px rgba(0,0,0,0.1);">
                            @forelse($activeCases as $ac)
                            @php
                                $dispLabel = $ac->disposition ? strtoupper($ac->disposition->value) : '';
                                $dispColor = match($dispLabel) {
                                    'ADR' => 'var(--ochre)',
                                    'LITIGATION' => 'var(--burgundy)',
                                    'ADVICE-ONLY' => 'var(--moss)',
                                    default => 'var(--ink-4)',
                                };
                            @endphp
                            <div class="case-picker-option"
                                data-id="{{ $ac->id }}"
                                data-label="{{ $ac->case_uid }} — {{ $ac->name }}"
                                data-search="{{ strtolower($ac->case_uid . ' ' . $ac->name . ' ' . $ac->primary_issue) }}"
                                onclick="selectCaseOption(this)"
                                style="padding: 9px 12px; cursor: pointer; border-bottom: 1px solid var(--rule-2); display: flex; align-items: center; justify-content: space-between; gap: 10px;"
                                onmouseover="this.style.background='var(--parchment)'" onmouseout="this.style.background='var(--paper)'">
                                <div>
                                    <span class="mono" style="font-size: 11px; font-weight: 600; color: var(--ink-3);">{{ $ac->case_uid }}</span>
                                    <span style="font-size: 13px; color: var(--ink); margin-left: 6px;">{{ $ac->name }}</span>
                                    @if($ac->primary_issue)
                                    <div style="font-size: 11px; color: var(--ink-4); margin-top: 1px;">{{ $ac->primary_issue }}</div>
                                    @endif
                                </div>
                                @if($dispLabel)
                                <span style="font-size: 9.5px; font-weight: 700; letter-spacing: 0.06em; padding: 2px 6px; background: rgba(0,0,0,0.05); color: {{ $dispColor }}; white-space: nowrap; flex-shrink: 0;">{{ $dispLabel }}</span>
                                @endif
                            </div>
                            @empty
                            <div style="padding: 14px 12px; font-size: 13px; color: var(--ink-4); text-align: center;">No open cases found in this hub</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- ── Section 2: Dispute Details ── --}}
                <div style="padding: 20px 24px 16px; border-bottom: 1px solid var(--rule-2);">
                    <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.1em; color: var(--ink-3); margin-bottom: 14px;">
                        DISPUTE DETAILS
                    </div>

                    {{-- Dispute Type --}}
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11px; font-weight: 600; color: var(--ink-2); margin-bottom: 5px; letter-spacing: 0.03em;">Dispute Type</label>
                        <div style="position: relative;">
                            <select name="dispute_type" required
                                style="width: 100%; padding: 9px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); appearance: none; cursor: pointer;">
                                <option value="" disabled selected>Select dispute category...</option>
                                <option>Family / Matrimonial</option>
                                <option>Property / Land</option>
                                <option>Labour / Employment</option>
                                <option>Commercial / Contract</option>
                                <option>Neighbourhood / Community</option>
                                <option>Debt / Financial</option>
                                <option>Inheritance / Succession</option>
                                <option>GBV-related</option>
                                <option>Other</option>
                            </select>
                            <x-lucide-chevron-down style="width: 14px; height: 14px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--ink-3); pointer-events: none;" />
                        </div>
                    </div>

                    {{-- Urgency Toggle --}}
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11px; font-weight: 600; color: var(--ink-2); margin-bottom: 8px; letter-spacing: 0.03em;">Urgency</label>
                        <div id="urgencyToggle" style="display: inline-flex; border: 1px solid var(--rule); overflow: hidden;">
                            @foreach(['Low', 'Medium', 'High'] as $u)
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="radio" name="urgency" value="{{ $u }}" {{ $u === 'Medium' ? 'checked' : '' }}
                                    style="position: absolute; opacity: 0; width: 0;"
                                    onchange="updateUrgencyToggle(this)">
                                <span class="urgency-opt"
                                    style="padding: 7px 18px; font-size: 12px; font-weight: 500; font-family: inherit; transition: all 150ms ease;
                                    {{ $u === 'Medium' ? 'background: var(--ochre); color: #fff;' : 'background: var(--paper); color: var(--ink-2);' }}">
                                    {{ $u }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 600; color: var(--ink-2); margin-bottom: 5px; letter-spacing: 0.03em;">Dispute Summary <span style="font-weight: 400; color: var(--ink-4);">(optional)</span></label>
                        <textarea name="summary" rows="3" placeholder="Brief description of the dispute and key parties involved..."
                            style="width: 100%; padding: 9px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); resize: vertical; box-sizing: border-box;"></textarea>
                    </div>
                </div>

                {{-- ── Section 3: Mediation Assignment ── --}}
                <div style="padding: 20px 24px 16px; border-bottom: 1px solid var(--rule-2);">
                    <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.1em; color: var(--ink-3); margin-bottom: 14px;">
                        MEDIATION ASSIGNMENT
                    </div>

                    {{-- Assigned Mediator --}}
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 11px; font-weight: 600; color: var(--ink-2); margin-bottom: 5px; letter-spacing: 0.03em;">Assigned Mediator</label>
                        <div style="position: relative;">
                            <select name="mediator_name"
                                style="width: 100%; padding: 9px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); appearance: none; cursor: pointer;">
                                <option value="">Select mediator (caseload shown)...</option>
                                @foreach($staff as $s)
                                <option value="{{ $s['name'] }}">
                                    {{ $s['name'] }} — {{ $s['designation'] ?: $s['role'] }} · {{ $s['adr'] }} mediation cases ({{ $s['utilization'] }}% capacity)
                                </option>
                                @endforeach
                            </select>
                            <x-lucide-chevron-down style="width: 14px; height: 14px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--ink-3); pointer-events: none;" />
                        </div>
                    </div>

                    {{-- Two columns: date + mode --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 600; color: var(--ink-2); margin-bottom: 5px; letter-spacing: 0.03em;">Proposed First Session</label>
                            <input type="date" name="proposed_date"
                                style="width: 100%; padding: 9px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 11px; font-weight: 600; color: var(--ink-2); margin-bottom: 8px; letter-spacing: 0.03em;">Session Mode</label>
                            <div id="sessionModeToggle" style="display: flex; border: 1px solid var(--rule); overflow: hidden;">
                                @foreach(['At Hub', 'Off-site', 'Joint'] as $i => $mode)
                                <label style="display: flex; align-items: center; cursor: pointer; flex: 1; justify-content: center;">
                                    <input type="radio" name="session_mode" value="{{ $mode }}" {{ $i === 0 ? 'checked' : '' }}
                                        style="position: absolute; opacity: 0; width: 0;"
                                        onchange="updateModeToggle(this)">
                                    <span class="mode-opt"
                                        style="padding: 7px 10px; font-size: 11px; font-weight: 500; font-family: inherit; transition: all 150ms ease; text-align: center; white-space: nowrap;
                                        {{ $i === 0 ? 'background: var(--forest); color: #fff;' : 'background: var(--paper); color: var(--ink-2);' }}">
                                        {{ $mode }}
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Section 4: Safeguarding & Suitability ── --}}
                <div style="padding: 20px 24px 16px; border-bottom: 1px solid var(--rule-2);">
                    <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.1em; color: var(--ink-3); margin-bottom: 14px;">
                        SAFEGUARDING &amp; SUITABILITY
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 14px;">
                        @php
                            $sgFlags = [
                                ['name' => 'is_gbv',       'label' => 'GBV-sensitive case',            'desc' => 'Involves gender-based violence — requires trained mediator & safety protocol'],
                                ['name' => 'is_child',     'label' => 'Child involved',                 'desc' => 'Minor is a party or witness — child-friendly procedures apply'],
                                ['name' => 'is_minority',  'label' => 'Minority / underserved community','desc' => 'Party belongs to a marginalised or underserved group'],
                                ['name' => 'is_disability','label' => 'Person with disability',          'desc' => 'Special accommodation or access needs required'],
                            ];
                        @endphp
                        @foreach($sgFlags as $flag)
                        <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; padding: 10px 12px; border: 1px solid var(--rule-2); background: var(--parchment); transition: border-color 120ms ease;"
                               onmouseover="this.style.borderColor='var(--ochre)'" onmouseout="this.style.borderColor='var(--rule-2)'">
                            <input type="checkbox" name="{{ $flag['name'] }}" value="1"
                                style="margin-top: 2px; flex-shrink: 0; accent-color: var(--ochre); width: 15px; height: 15px;">
                            <div>
                                <div style="font-size: 13px; font-weight: 600; color: var(--ink); margin-bottom: 2px;">{{ $flag['label'] }}</div>
                                <div style="font-size: 11px; color: var(--ink-3); line-height: 1.4;">{{ $flag['desc'] }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 600; color: var(--ink-2); margin-bottom: 5px; letter-spacing: 0.03em;">Special Accommodations <span style="font-weight: 400; color: var(--ink-4);">(optional)</span></label>
                        <textarea name="accommodations" rows="2" placeholder="Any physical, language, or procedural accommodations needed..."
                            style="width: 100%; padding: 9px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); resize: vertical; box-sizing: border-box;"></textarea>
                    </div>
                </div>

                {{-- ── Section 5: Notes for Mediator ── --}}
                <div style="padding: 20px 24px 16px;">
                    <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.1em; color: var(--ink-3); margin-bottom: 10px;">
                        NOTES FOR MEDIATOR
                    </div>
                    <textarea name="mediator_notes" rows="3" placeholder="Background context, previous attempts at resolution, power imbalances, or other notes the mediator should know before the first session..."
                        style="width: 100%; padding: 9px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); resize: vertical; box-sizing: border-box;"></textarea>
                </div>

                {{-- Footer --}}
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; padding: 14px 24px 20px; border-top: 1px solid var(--rule);">
                    <button type="button" data-bs-dismiss="modal"
                        style="padding: 8px 18px; font-size: 13px; font-weight: 500; font-family: inherit; border: 1px solid var(--rule); background: transparent; color: var(--ink-2); cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit"
                        style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; font-size: 13px; font-weight: 500; font-family: inherit; border: 1px solid var(--forest); background: var(--forest); color: var(--cream); cursor: pointer;">
                        <x-lucide-gavel style="width: 14px; height: 14px;" />
                        Refer to mediation
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     LOG SERVICE ENCOUNTER PANEL
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-log-service" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 680px; margin: 1.75rem auto;">
        <div class="modal-content" style="border-radius: 4px; background: var(--paper); box-shadow: 0 16px 48px rgba(0,0,0,0.18); max-height: 92vh; overflow-y: auto; border-top: 3px solid var(--forest);">

            {{-- Header --}}
            <div style="padding: 18px 24px 14px; border-bottom: 1px solid var(--rule); position: sticky; top: 0; background: var(--paper); z-index: 10;">
                <div class="label-cap" style="font-size: 9px; letter-spacing: 0.14em; color: var(--ink-4); margin-bottom: 6px;">
                    SERVICE DELIVERY &middot; INDICATOR O2.1 &middot; RESOLUTION RATE
                </div>
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <h3 class="serif" style="font-size: 26px; font-weight: 400; margin: 0; color: var(--ink); letter-spacing: -0.015em; line-height: 1.15;">
                        Log <em style="font-style: italic; color: var(--forest);">service</em> encounter
                    </h3>
                    <button type="button" data-bs-dismiss="modal"
                        style="background: none; border: 1px solid var(--rule); cursor: pointer; color: var(--ink-3); padding: 6px 8px; line-height: 1; flex-shrink: 0;">
                        <x-lucide-x style="width: 15px; height: 15px;" />
                    </button>
                </div>
            </div>

            @if(session('success'))
            <div style="padding: 10px 24px; background: rgba(74,122,92,0.1); border-bottom: 1px solid var(--moss); color: var(--moss); font-size: 12.5px; display: flex; align-items: center; gap: 7px;">
                <x-lucide-check-circle style="width: 13px; height: 13px; flex-shrink: 0;" />
                {{ session('success') }}
            </div>
            @endif

            <form action="{{ route('encounters.log') }}" method="POST" style="padding: 0;">
                @csrf

                {{-- ── CASE ──────────────────────────────────────────── --}}
                <div style="padding: 20px 24px 18px; border-bottom: 1px solid var(--rule-2);">
                    <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.12em; color: var(--ink-3); margin-bottom: 4px;">CASE</div>
                    <p style="font-size: 12px; color: var(--ink-4); margin: 0 0 12px 0; line-height: 1.4;">Select the case this encounter is for</p>

                    <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">
                        Case <span style="color: var(--burgundy);">*</span>
                    </label>

                    <input type="hidden" name="case_id" id="logSvcCaseId" required>
                    <div style="position: relative;" id="logSvcCasePicker">
                        <div style="position: relative;">
                            <x-lucide-search style="width: 13px; height: 13px; position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--ink-4); pointer-events: none;" />
                            <input type="text" id="logSvcCaseInput" autocomplete="off"
                                placeholder="Search by case ID, client name, or issue..."
                                style="width: 100%; padding: 10px 36px 10px 32px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); box-sizing: border-box; outline: none;"
                                onfocus="lsOpenPicker()" oninput="lsFilterPicker(this.value)">
                            <x-lucide-chevron-down id="logSvcChevron" style="width: 13px; height: 13px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--ink-3); pointer-events: none; transition: transform 200ms ease;" />
                        </div>
                        <div id="logSvcPickerDropdown"
                            style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: var(--paper); border: 1px solid var(--rule); border-top: none; max-height: 220px; overflow-y: auto; box-shadow: 0 8px 24px rgba(0,0,0,0.1);">
                            @forelse($activeCases as $ac)
                            @php
                                $dispLabel = $ac->disposition ? strtoupper($ac->disposition->value) : '';
                                $dispColor = match($dispLabel) { 'ADR' => 'var(--ochre)', 'LITIGATION' => 'var(--burgundy)', default => 'var(--ink-4)' };
                            @endphp
                            <div class="ls-case-opt"
                                data-id="{{ $ac->id }}"
                                data-label="{{ $ac->case_uid }} — {{ $ac->name }}"
                                data-search="{{ strtolower($ac->case_uid . ' ' . $ac->name . ' ' . $ac->primary_issue) }}"
                                onclick="lsSelectCase(this)"
                                style="padding: 9px 12px; cursor: pointer; border-bottom: 1px solid var(--rule-2); display: flex; align-items: center; justify-content: space-between; gap: 10px;"
                                onmouseover="this.style.background='var(--parchment)'" onmouseout="this.style.background='var(--paper)'">
                                <div>
                                    <span class="mono" style="font-size: 11px; font-weight: 600; color: var(--ink-3);">{{ $ac->case_uid }}</span>
                                    <span style="font-size: 13px; color: var(--ink); margin-left: 6px;">{{ $ac->name }}</span>
                                    @if($ac->primary_issue)
                                    <div style="font-size: 11px; color: var(--ink-4); margin-top: 1px;">{{ $ac->primary_issue }}</div>
                                    @endif
                                </div>
                                @if($dispLabel)
                                <span style="font-size: 9.5px; font-weight: 700; letter-spacing: 0.06em; padding: 2px 6px; background: rgba(0,0,0,0.05); color: {{ $dispColor }}; white-space: nowrap; flex-shrink: 0;">{{ $dispLabel }}</span>
                                @endif
                            </div>
                            @empty
                            <div style="padding: 14px 12px; font-size: 13px; color: var(--ink-4); text-align: center;">No open cases in this hub</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- ── SERVICE DETAILS ───────────────────────────────── --}}
                <div style="padding: 20px 24px 18px; border-bottom: 1px solid var(--rule-2);">
                    <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.12em; color: var(--ink-3); margin-bottom: 4px;">SERVICE DETAILS</div>
                    <p style="font-size: 12px; color: var(--ink-4); margin: 0 0 14px 0; line-height: 1.4;">What was delivered, when, and by whom</p>

                    {{-- Service Type --}}
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">
                            Service Type <span style="color: var(--burgundy);">*</span>
                        </label>
                        <div style="position: relative;">
                            <select name="type" required
                                style="width: 100%; padding: 10px 36px 10px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); appearance: none; cursor: pointer; outline: none;">
                                <option value="" disabled selected>Choose service type</option>
                                <optgroup label="Mediation">
                                    <option>Initial Assessment</option>
                                    <option>Mediation Session</option>
                                    <option>Joint Meeting</option>
                                    <option>Settlement Discussion</option>
                                    <option>Agreement Drafting</option>
                                    <option>Closure Meeting</option>
                                </optgroup>
                                <optgroup label="Legal Services">
                                    <option>Legal Advice</option>
                                    <option>Document Review</option>
                                    <option>Court Representation</option>
                                    <option>Legal Research</option>
                                </optgroup>
                                <optgroup label="Follow-up">
                                    <option>Follow-up Session</option>
                                    <option>Compliance Check</option>
                                    <option>Client Support Call</option>
                                </optgroup>
                                <option>Other</option>
                            </select>
                            <x-lucide-chevron-down style="width: 14px; height: 14px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--ink-3); pointer-events: none;" />
                        </div>
                    </div>

                    {{-- Date + Time --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                        <div>
                            <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">
                                Date <span style="color: var(--burgundy);">*</span>
                            </label>
                            <input type="date" name="date" required value="{{ now()->toDateString() }}"
                                style="width: 100%; padding: 10px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); box-sizing: border-box; outline: none;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">
                                Time
                            </label>
                            <input type="time" name="time" value="{{ now()->format('H:i') }}"
                                style="width: 100%; padding: 10px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); box-sizing: border-box; outline: none;">
                        </div>
                    </div>

                    {{-- Provider --}}
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">
                            Provider <span style="color: var(--burgundy);">*</span>
                        </label>
                        <div style="position: relative;">
                            <select name="performed_by" required
                                style="width: 100%; padding: 10px 36px 10px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); appearance: none; cursor: pointer; outline: none;">
                                <option value="" disabled selected>Select provider</option>
                                @foreach($providers as $p)
                                <option value="{{ $p->name }}">{{ $p->name }} ({{ $p->designation ?: $p->role->label() }})</option>
                                @endforeach
                                @if($providers->isEmpty())
                                <option value="" disabled>No staff found for this hub</option>
                                @endif
                            </select>
                            <x-lucide-chevron-down style="width: 14px; height: 14px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--ink-3); pointer-events: none;" />
                        </div>
                    </div>

                    {{-- Duration + Mode --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div>
                            <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">
                                Duration <span style="color: var(--burgundy);">*</span>
                            </label>
                            <div style="position: relative;">
                                <select name="duration" required
                                    style="width: 100%; padding: 10px 36px 10px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); appearance: none; cursor: pointer; outline: none;">
                                    <option value="" disabled selected>Time spent</option>
                                    <option>30 min</option>
                                    <option>1 hour</option>
                                    <option>1.5 hours</option>
                                    <option>2 hours</option>
                                    <option>3 hours</option>
                                    <option>4+ hours</option>
                                    <option>Half day</option>
                                    <option>Full day</option>
                                </select>
                                <x-lucide-chevron-down style="width: 14px; height: 14px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--ink-3); pointer-events: none;" />
                            </div>
                        </div>
                        <div>
                            <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.06em;">Mode</label>
                            <div id="lsModeToggle" style="display: flex; border: 1px solid var(--rule); overflow: hidden;">
                                @foreach(['In-person', 'Phone', 'Field'] as $i => $m)
                                <label style="display: flex; align-items: center; cursor: pointer; flex: 1; justify-content: center;">
                                    <input type="radio" name="mode" value="{{ $m }}" {{ $i === 0 ? 'checked' : '' }}
                                        style="position: absolute; opacity: 0; width: 0;"
                                        onchange="lsUpdateToggle(this,'lsModeToggle','ls-mode-opt','var(--forest)')">
                                    <span class="ls-mode-opt" style="padding: 8px 6px; font-size: 12px; font-weight: 500; font-family: inherit; transition: all 150ms ease; text-align: center; white-space: nowrap; width: 100%;
                                        {{ $i === 0 ? 'background: var(--forest); color: #fff;' : 'background: var(--paper); color: var(--ink-2);' }}">
                                        {{ $m }}
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── OUTCOME ───────────────────────────────────────── --}}
                <div style="padding: 20px 24px 18px; border-bottom: 1px solid var(--rule-2);">
                    <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.12em; color: var(--ink-3); margin-bottom: 4px;">OUTCOME</div>
                    <p style="font-size: 12px; color: var(--ink-4); margin: 0 0 14px 0; line-height: 1.4;">How did this encounter conclude — does the case advance, hold, or close?</p>

                    {{-- Encounter Outcome toggle --}}
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.06em;">
                            Encounter Outcome <span style="color: var(--burgundy);">*</span>
                        </label>
                        <div id="lsOutcomeToggle" style="display: flex; border: 1px solid var(--rule); overflow: hidden;">
                            @php
                                $lsOutcomes = [
                                    'Resolved'  => 'var(--moss)',
                                    'Ongoing'   => 'var(--ochre)',
                                    'Escalated' => 'var(--burgundy)',
                                    'Pending'   => 'var(--ink-3)',
                                ];
                            @endphp
                            @foreach($lsOutcomes as $outcomeVal => $outcomeColor)
                            <label style="display: flex; align-items: center; cursor: pointer; flex: 1; justify-content: center;">
                                <input type="radio" name="outcome" value="{{ $outcomeVal }}" {{ $outcomeVal === 'Ongoing' ? 'checked' : '' }}
                                    style="position: absolute; opacity: 0; width: 0;"
                                    onchange="lsUpdateOutcome(this)">
                                <span class="ls-outcome-opt"
                                    data-color="{{ $outcomeColor }}"
                                    style="padding: 8px 6px; font-size: 12.5px; font-weight: 500; font-family: inherit; transition: all 150ms ease; text-align: center; width: 100%;
                                    {{ $outcomeVal === 'Ongoing' ? 'background: var(--ochre); color: #fff;' : 'background: var(--paper); color: var(--ink-2);' }}">
                                    {{ $outcomeVal }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Encounter Notes</label>
                        <textarea name="note" rows="4"
                            placeholder="Brief narrative — actions taken, advice given, documents reviewed, next steps agreed..."
                            style="width: 100%; padding: 10px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); resize: vertical; box-sizing: border-box; outline: none; line-height: 1.5;"></textarea>
                        <div style="font-size: 11px; color: var(--ink-4); margin-top: 5px; line-height: 1.4; font-style: italic;">
                            What was discussed or done. Be specific enough that another provider could pick up the case.
                        </div>
                    </div>
                </div>

                {{-- ── NEXT STEP ─────────────────────────────────────── --}}
                <div style="padding: 20px 24px 18px;">
                    <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.12em; color: var(--ink-3); margin-bottom: 4px;">NEXT STEP</div>
                    <p style="font-size: 12px; color: var(--ink-4); margin: 0 0 14px 0; line-height: 1.4;">Optional follow-up if the encounter doesn't close the matter</p>

                    <div style="display: grid; grid-template-columns: 1fr 180px; gap: 14px; align-items: end;">
                        <div>
                            <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Next Step / Action</label>
                            <input type="text" name="next_step"
                                placeholder="e.g. 'Collect inheritance documents' or 'Schedule second mediation session'"
                                style="width: 100%; padding: 10px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); box-sizing: border-box; outline: none;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Target Date</label>
                            <input type="date" name="next_step_date"
                                style="width: 100%; padding: 10px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); box-sizing: border-box; outline: none;">
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; padding: 14px 24px 20px; border-top: 1px solid var(--rule); background: var(--parchment);">
                    <button type="button" data-bs-dismiss="modal"
                        style="padding: 9px 20px; font-size: 13px; font-weight: 500; font-family: inherit; border: 1px solid var(--rule); background: transparent; color: var(--ink-2); cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit"
                        style="display: inline-flex; align-items: center; gap: 7px; padding: 9px 20px; font-size: 13px; font-weight: 500; font-family: inherit; border: 1px solid var(--forest); background: var(--forest); color: var(--cream); cursor: pointer;">
                        <x-lucide-circle-check style="width: 14px; height: 14px;" />
                        Log encounter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══ Client-side filtering ═══ --}}
<script>
function adrUpdateStage(select, caseId) {
    var stage = select.value;
    var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    select.disabled = true;
    fetch('/cases/' + caseId + '/adr-stage', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
        body: JSON.stringify({ stage: stage })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        select.disabled = false;
        if (data.success) {
            var meta = document.getElementById('adr-stage-meta-' + caseId);
            if (meta) {
                meta.textContent = data.changed_by + ' · ' + data.changed_at;
                meta.style.display = '';
            }
            setTimeout(function() { location.reload(); }, 600);
        }
    })
    .catch(function() { select.disabled = false; });
}

function filterKanban(type, btn) {
    // Update pill active states
    document.querySelectorAll('#kanbanFilters .jh-pill-filter').forEach(function(p) {
        p.classList.remove('active');
    });
    btn.classList.add('active');

    // Filter cards
    document.querySelectorAll('.jh-kanban-card').forEach(function(card) {
        if (type === 'all') {
            card.style.display = '';
        } else {
            var match = card.getAttribute('data-' + type) === '1';
            card.style.display = match ? '' : 'none';
        }
    });
}

function adrSearchCards(q) {
    q = q.trim().toLowerCase();
    var total = 0, shown = 0;
    document.querySelectorAll('.jh-kanban-card').forEach(function(card) {
        total++;
        var text = (card.dataset.name || '') + ' ' + (card.dataset.uid || '');
        var match = !q || text.includes(q);
        // also respect active filter pill
        var activeFilter = document.querySelector('#kanbanFilters .jh-pill-filter.active')?.dataset.filter || 'all';
        var filterOk = activeFilter === 'all' || card.dataset[activeFilter] === '1';
        card.style.display = (match && filterOk) ? '' : 'none';
        if (match && filterOk) shown++;
    });
    // Update column count badges
    document.querySelectorAll('.adr-kanban-col').forEach(function(col) {
        var v = [...col.querySelectorAll('.jh-kanban-card')].filter(function(c) { return c.style.display !== 'none'; }).length;
        var badge = col.querySelector('.adr-kanban-count');
        if (badge) badge.textContent = v;
    });
    var info = document.getElementById('adr-search-info');
    if (info) info.textContent = q ? shown + ' of ' + total + ' cases match' : '';
}

function filterStaff(role, btn) {
    // Update pill active states
    document.querySelectorAll('#staffFilters .jh-pill-filter').forEach(function(p) {
        p.classList.remove('active');
    });
    btn.classList.add('active');

    // Filter rows
    document.querySelectorAll('.jh-staff-row').forEach(function(row) {
        if (role === 'all') {
            row.style.display = '';
        } else {
            var match = row.getAttribute('data-staff-role') === role;
            row.style.display = match ? '' : 'none';
        }
    });
}

function updateUrgencyToggle(input) {
    var urgencyColors = { 'Low': 'var(--moss)', 'Medium': 'var(--ochre)', 'High': 'var(--burgundy)' };
    document.querySelectorAll('#urgencyToggle .urgency-opt').forEach(function(span) {
        span.style.background = 'var(--paper)';
        span.style.color = 'var(--ink-2)';
    });
    var selected = input.parentElement.querySelector('.urgency-opt');
    selected.style.background = urgencyColors[input.value] || 'var(--ochre)';
    selected.style.color = '#fff';
}

function updateModeToggle(input) {
    document.querySelectorAll('#sessionModeToggle .mode-opt').forEach(function(span) {
        span.style.background = 'var(--paper)';
        span.style.color = 'var(--ink-2)';
    });
    var selected = input.parentElement.querySelector('.mode-opt');
    selected.style.background = 'var(--forest)';
    selected.style.color = '#fff';
}

// ── Case picker ──────────────────────────────────────────────────────────
function openCasePicker() {
    var dd = document.getElementById('casePickerDropdown');
    var chevron = document.getElementById('casePickerChevron');
    dd.style.display = 'block';
    chevron.style.transform = 'translateY(-50%) rotate(180deg)';
    // Show all options
    document.querySelectorAll('.case-picker-option').forEach(function(o) { o.style.display = ''; });
}

function filterCasePicker(val) {
    var q = val.toLowerCase().trim();
    var dd = document.getElementById('casePickerDropdown');
    dd.style.display = 'block';
    var opts = document.querySelectorAll('.case-picker-option');
    var anyVisible = false;
    opts.forEach(function(o) {
        var match = !q || o.getAttribute('data-search').includes(q);
        o.style.display = match ? '' : 'none';
        if (match) anyVisible = true;
    });
    // Clear hidden value if user is typing
    document.getElementById('casePickerValue').value = '';
}

function selectCaseOption(el) {
    document.getElementById('casePickerValue').value = el.getAttribute('data-id');
    document.getElementById('casePickerInput').value = el.getAttribute('data-label');
    closeCasePicker();
}

function closeCasePicker() {
    var dd = document.getElementById('casePickerDropdown');
    var chevron = document.getElementById('casePickerChevron');
    dd.style.display = 'none';
    chevron.style.transform = 'translateY(-50%) rotate(0deg)';
}

// Close picker when clicking outside
document.addEventListener('click', function(e) {
    var picker = document.getElementById('casePicker');
    if (picker && !picker.contains(e.target)) closeCasePicker();
    var lsPicker = document.getElementById('logSvcCasePicker');
    if (lsPicker && !lsPicker.contains(e.target)) lsClosePicker();
});

// ── Log service case picker ───────────────────────────────────────────────
function lsOpenPicker() {
    document.getElementById('logSvcPickerDropdown').style.display = 'block';
    document.getElementById('logSvcChevron').style.transform = 'translateY(-50%) rotate(180deg)';
    document.querySelectorAll('.ls-case-opt').forEach(function(o) { o.style.display = ''; });
}
function lsFilterPicker(val) {
    var q = val.toLowerCase().trim();
    document.getElementById('logSvcPickerDropdown').style.display = 'block';
    document.querySelectorAll('.ls-case-opt').forEach(function(o) {
        o.style.display = (!q || o.getAttribute('data-search').includes(q)) ? '' : 'none';
    });
    document.getElementById('logSvcCaseId').value = '';
}
function lsSelectCase(el) {
    document.getElementById('logSvcCaseId').value = el.getAttribute('data-id');
    document.getElementById('logSvcCaseInput').value = el.getAttribute('data-label');
    lsClosePicker();
}
function lsClosePicker() {
    document.getElementById('logSvcPickerDropdown').style.display = 'none';
    document.getElementById('logSvcChevron').style.transform = 'translateY(-50%) rotate(0deg)';
}

// ── Log service toggle buttons ────────────────────────────────────────────
function lsUpdateToggle(input, groupId, spanClass, activeColor) {
    document.querySelectorAll('#' + groupId + ' .' + spanClass).forEach(function(s) {
        s.style.background = 'var(--paper)';
        s.style.color = 'var(--ink-2)';
    });
    var span = input.parentElement.querySelector('.' + spanClass);
    span.style.background = activeColor;
    span.style.color = '#fff';
}
function lsUpdateOutcome(input) {
    var spans = document.querySelectorAll('#lsOutcomeToggle .ls-outcome-opt');
    spans.forEach(function(s) { s.style.background = 'var(--paper)'; s.style.color = 'var(--ink-2)'; });
    var selected = input.parentElement.querySelector('.ls-outcome-opt');
    selected.style.background = selected.getAttribute('data-color');
    selected.style.color = '#fff';
}
</script>
</x-layouts.app>
