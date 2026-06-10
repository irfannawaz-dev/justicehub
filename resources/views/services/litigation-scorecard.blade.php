<x-layouts.app>
@php
    // Avatar color palette — cycles by loop index inside kanban
    $avColors = ['#8a2e1d','#163029','#b87319','#4a5e2d','#2d3a5e','#5e2d4a','#1d4a5e'];

    // Stage header colors
    $stageColors = [
        'Filed'              => '#3d3d38',
        'In Hearings'        => 'var(--burgundy)',
        'Awaiting Judgment'  => 'var(--ochre)',
        'Resolved'           => 'var(--forest)',
    ];

    // Resolution outcome colours
    $outColors = [
        'won'       => '#4a7a5c',
        'partial'   => '#b87319',
        'pending'   => '#6b6a65',
        'lost'      => '#8a2e1d',
        'withdrawn' => '#bcb5a2',
    ];

    // Court type colours
    $ctColors = [
        'Sessions' => '#8a2e1d',
        'Civil'    => '#163029',
        'Family'   => '#b87319',
        'Juvenile' => '#4a7a5c',
        'Consumer' => '#2d3a5e',
    ];

    $ctMax = max(1, max(array_values($courtTypes)));
    $resTotal = max(1, array_sum(array_values($resolutionOutcomes)));

    $chartOutcomeData = [
        'labels' => ['Won', 'Partial', 'Pending', 'Lost', 'Withdrawn'],
        'values' => [$resolutionOutcomes['won'], $resolutionOutcomes['partial'],
                     $resolutionOutcomes['pending'], $resolutionOutcomes['lost'],
                     $resolutionOutcomes['withdrawn']],
        'colors' => array_values($outColors),
    ];
@endphp

<style>
.lit-pill-filter {
    padding: 5px 14px; font-size: 11.5px; font-weight: 600; letter-spacing: 0.03em;
    border: 1px solid var(--rule); background: var(--paper); color: var(--ink-3);
    cursor: pointer; transition: all 120ms; border-radius: 20px; white-space: nowrap;
}
.lit-pill-filter:hover { background: var(--parchment); color: var(--ink); }
.lit-pill-filter.active { background: var(--burgundy); color: var(--cream); border-color: var(--burgundy); }

.lit-kanban-card {
    display: block; padding: 12px 14px; text-decoration: none; color: inherit;
    border: 1px solid var(--rule); background: var(--paper);
    transition: border-color 120ms, box-shadow 100ms;
}
.lit-kanban-card:hover { border-color: var(--burgundy); box-shadow: 0 2px 8px rgba(0,0,0,.06); color: inherit; }

.staff-pill-filter {
    padding: 5px 14px; font-size: 11.5px; font-weight: 600; letter-spacing: 0.03em;
    border: 1px solid var(--rule); background: var(--paper); color: var(--ink-3);
    cursor: pointer; transition: all 120ms; border-radius: 20px;
}
.staff-pill-filter:hover { background: var(--parchment); color: var(--ink); }
.staff-pill-filter.active { background: var(--ink); color: var(--cream); border-color: var(--ink); }

.jh-anim-bar {
    transition: width 0s;
    animation: jh-bar-grow 0.7s cubic-bezier(.22,.68,0,1.2) both;
}
@keyframes jh-bar-grow {
    from { width: 0 !important; }
}
@keyframes jh-fade-up {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

<div style="padding: 24px 34px 64px; max-width: 1600px; margin: 0 auto;">

    {{-- ════════════════════════════════════════════════════════════
         1. HEADER
         ════════════════════════════════════════════════════════════ --}}
    <div style="margin-bottom: 26px;">
        <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.1em; color: var(--ink-3); margin-bottom: 4px;">
            O2.4 &middot; COURT REPRESENTATION
        </div>
        <h1 class="serif" style="font-size: 34px; font-weight: 400; letter-spacing: -0.02em; margin: 0; line-height: 1.1;">
            Litigation <em style="color: var(--burgundy);">Scorecard</em>
        </h1>
        <p style="margin: 7px 0 0 0; font-size: 13px; color: var(--ink-3);">
            Court pathway performance &middot; {{ $total }} cases on litigation track &middot; {{ $activeCount }} active
        </p>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         2. KPI STRIP
         ════════════════════════════════════════════════════════════ --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 30px; animation: jh-fade-up 0.5s ease both;">

        <div class="card" style="padding: 18px 20px; border-top: 3px solid var(--burgundy);">
            <div class="label-cap" style="font-size: 9px; color: var(--ink-3);">Active Litigation</div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: 8px;">
                <span class="serif" style="font-size: 42px; font-weight: 400; line-height: 1; color: var(--ink);">{{ $activeCount }}</span>
            </div>
            <div style="font-size: 11px; color: var(--ink-3); margin-top: 5px;">
                {{ $criminal }} criminal &middot; {{ $civil }} civil{{ $juvenile ? " &middot; {$juvenile} juvenile" : '' }}
            </div>
        </div>

        <div class="card" style="padding: 18px 20px; border-top: 3px solid var(--moss);">
            <div class="label-cap" style="font-size: 9px; color: var(--ink-3);">Favourable Rate</div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: 8px;">
                <span class="serif" style="font-size: 42px; font-weight: 400; line-height: 1; color: var(--forest);">{{ $favRate }}</span>
                <span class="serif" style="font-size: 22px; color: var(--ink-3);">%</span>
            </div>
            <div style="font-size: 11px; color: var(--ink-3); margin-top: 5px;">
                {{ $favourable }} of {{ $total }} resolved favourably &middot; target 60%
            </div>
        </div>

        <div class="card" style="padding: 18px 20px; border-top: 3px solid var(--ochre);">
            <div class="label-cap" style="font-size: 9px; color: var(--ink-3);">Avg Days to Disposal</div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: 8px;">
                <span class="serif" style="font-size: 42px; font-weight: 400; line-height: 1; color: var(--ink);">{{ $avgDays }}</span>
                <span style="font-size: 13px; color: var(--ink-3);">days</span>
            </div>
            <div style="font-size: 11px; color: var(--ink-3); margin-top: 5px;">
                intake to judgment for resolved cases
            </div>
        </div>

        <div class="card" style="padding: 18px 20px; border-top: 3px solid var(--forest);">
            <div class="label-cap" style="font-size: 9px; color: var(--ink-3);">Hearings This Quarter</div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: 8px;">
                <span class="serif" style="font-size: 42px; font-weight: 400; line-height: 1; color: var(--ink);">{{ $hearingsThisQuarter }}</span>
            </div>
            <div style="font-size: 11px; color: var(--ink-3); margin-top: 5px;">
                court appearances since {{ now()->startOfQuarter()->format('d M') }}
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         3. LITIGATION PIPELINE (KANBAN)
         ════════════════════════════════════════════════════════════ --}}
    <div style="margin-bottom: 32px; animation: jh-fade-up 0.55s ease both; animation-delay: 0.1s;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h2 class="serif" style="font-size: 26px; font-weight: 400; letter-spacing: -0.015em; margin: 0; line-height: 1.1;">
                    Litigation <em style="font-style: italic;">pipeline</em>
                </h2>
                <div style="font-size: 12.5px; color: var(--ink-3); margin-top: 4px;">
                    Filed &rarr; In Hearings &rarr; Awaiting Judgment &rarr; Resolved &middot; {{ $total }} cases total
                </div>
            </div>
            {{-- Filter pills --}}
            <div id="litFilters" style="display: flex; gap: 6px; flex-wrap: wrap;">
                <button class="lit-pill-filter active" onclick="litFilter('all', this)">All</button>
                <button class="lit-pill-filter" onclick="litFilter('gbv', this)">GBV-flagged</button>
                <button class="lit-pill-filter" onclick="litFilter('child', this)">Child</button>
                <button class="lit-pill-filter" onclick="litFilter('underserved', this)">Underserved</button>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; align-items: start;">
            @foreach($pipeline as $stage => $stageCases)
            @php $stageColor = $stageColors[$stage] ?? 'var(--ink)'; @endphp
            <div class="kanban-col" data-stage="{{ $stage }}">
                {{-- Column header --}}
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: {{ $stageColor }}; color: var(--cream); margin-bottom: 8px;">
                    <span style="font-size: 11.5px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase;">{{ $stage }}</span>
                    <span class="mono kanban-count" style="font-size: 12px; font-weight: 600;">{{ count($stageCases) }}</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 7px; min-height: 80px;">
                    @forelse($stageCases as $idx => $case)
                    @php
                        $avBg  = $avColors[$idx % count($avColors)];
                        $flags = intval($case->is_gbv) . intval($case->is_child) . intval($case->is_minority || $case->is_disability || $case->is_underserved);
                    @endphp
                    <div class="lit-kanban-card"
                       data-case-id="{{ $case->id }}"
                       data-gbv="{{ $case->is_gbv ? '1' : '0' }}"
                       data-child="{{ $case->is_child ? '1' : '0' }}"
                       data-underserved="{{ ($case->is_minority || $case->is_disability || $case->is_underserved) ? '1' : '0' }}">

                        {{-- Row 1: UID + days badge + link --}}
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 7px;">
                            <a href="{{ route('cases.show', $case) }}" style="text-decoration:none;">
                                <span class="mono" style="font-size: 10px; color: var(--ink-3); letter-spacing: 0.04em;">{{ $case->case_uid }}</span>
                            </a>
                            @if($case->days_in_stage > 30)
                            <span style="font-size: 9px; padding: 2px 6px; background: var(--ochre-tint); color: var(--ochre); font-weight: 700; border-radius: 2px;">{{ $case->days_in_stage }}d</span>
                            @endif
                        </div>

                        {{-- Row 2: Avatar + name --}}
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: {{ $avBg }}; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0;">
                                {{ strtoupper(substr($case->name ?? '?', 0, 1)) }}
                            </div>
                            <div style="font-size: 13px; font-weight: 600; color: var(--ink); line-height: 1.2; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $case->name }}</div>
                        </div>

                        {{-- Row 3: Issue + court type --}}
                        <div style="font-size: 11px; color: var(--ink-3); margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ Str::limit($case->primary_issue ?? 'Unspecified', 28) }} &middot; {{ $case->court_type }}
                        </div>

                        {{-- Row 4: Hub + hearings --}}
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 10.5px; color: var(--ink-4); margin-bottom: 4px;">
                            <span class="mono">{{ $case->hub_id }}</span>
                            <span>&middot;</span>
                            <span>{{ $case->hearing_count }} {{ Str::plural('hearing', $case->hearing_count) }}</span>
                        </div>

                        {{-- Row 5: Next hearing (if any) --}}
                        @if($case->next_hearing)
                        <div style="font-size: 10.5px; color: var(--forest); font-weight: 600; margin-bottom: 4px;">
                            Next: {{ $case->next_hearing->date->format('d M') }}
                        </div>
                        @endif

                        {{-- Row 6: CMS case stage badge --}}
                        @if(!empty($case->cms_case_stage))
                        <div style="margin-bottom: 4px;">
                            <span style="font-size: 9px; padding: 2px 6px; background: rgba(22,48,41,0.08); color: var(--forest); font-weight: 600; letter-spacing: 0.04em; border: 1px solid rgba(22,48,41,0.15); border-radius: 2px;">
                                CMS: {{ $case->cms_case_stage }}
                            </span>
                        </div>
                        @endif

                        {{-- Row 7: Lawyer --}}
                        @if($case->assigned_to)
                        <div style="font-size: 10.5px; color: var(--ink-3);">{{ $case->assigned_to }}</div>
                        @endif

                        {{-- Row 7: Vulnerability flags --}}
                        @if($case->is_gbv || $case->is_child || $case->is_minority || $case->is_disability)
                        <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px;">
                            @if($case->is_gbv)
                            <span style="font-size: 9px; padding: 1px 5px; background: var(--burgundy-tint); color: var(--burgundy); font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; border-radius: 2px;">GBV</span>
                            @endif
                            @if($case->is_child)
                            <span style="font-size: 9px; padding: 1px 5px; background: var(--ochre-tint); color: var(--ochre); font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; border-radius: 2px;">CHILD</span>
                            @endif
                            @if($case->is_minority)
                            <span style="font-size: 9px; padding: 1px 5px; background: var(--parchment); color: var(--ink-3); font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; border-radius: 2px;">MINORITY</span>
                            @endif
                            @if($case->is_disability)
                            <span style="font-size: 9px; padding: 1px 5px; background: var(--parchment); color: var(--ink-3); font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; border-radius: 2px;">PWD</span>
                            @endif
                        </div>
                        @endif

                        {{-- Row 8: Outcome badge (Resolved only) --}}
                        @if($stage === 'Resolved')
                        @php
                            $caseOutcome = $case->meta['outcome'] ?? null;
                            $lastEnc     = $case->serviceEncounters->last();
                            $encOutcome  = $lastEnc?->meta['outcome'] ?? '';
                            $outcome     = strtolower($caseOutcome ?: $encOutcome);
                            $needsOutcome = !$caseOutcome && !$encOutcome;

                            if (str_contains($outcome, 'won')) {
                                $obLabel = 'WON'; $obBg = 'var(--forest)';
                            } elseif (str_contains($outcome, 'partial')) {
                                $obLabel = 'PARTIAL'; $obBg = 'var(--ochre)';
                            } elseif (str_contains($outcome, 'lost')) {
                                $obLabel = 'LOST'; $obBg = 'var(--burgundy)';
                            } elseif (str_contains($outcome, 'settlement') || $case->status->value === 'Settlement') {
                                $obLabel = 'SETTLEMENT'; $obBg = 'var(--forest)';
                            } elseif (str_contains($outcome, 'withdrawn')) {
                                $obLabel = 'WITHDRAWN'; $obBg = 'var(--ink-3)';
                            } else {
                                $obLabel = 'CLOSED'; $obBg = 'var(--ink-3)';
                            }
                        @endphp
                        <div style="margin-top: 7px; display: flex; align-items: center; gap: 5px;">
                            <span style="font-size: 9px; padding: 2px 7px; background: {{ $obBg }}; color: #fff; font-weight: 700; letter-spacing: 0.07em; text-transform: uppercase;">{{ $obLabel }}</span>
                            @if($needsOutcome)
                            <button type="button"
                                    onclick="event.preventDefault(); event.stopPropagation(); litSetOutcomeOpen({{ $case->id }}, '{{ $case->case_uid }}', '{{ addslashes($case->name) }}')"
                                    style="font-size: 9px; padding: 2px 7px; background: var(--ochre); color: #fff; font-weight: 700; letter-spacing: 0.05em; border: none; cursor: pointer; text-transform: uppercase;">
                                Set Outcome
                            </button>
                            @endif
                        </div>
                        @endif

                        {{-- Stage change dropdown --}}
                        @php
                            $isSystemChange = $case->litigation_stage_changed_by === null && $case->litigation_stage_changed_at;
                            $changer   = $case->litigation_stage_changed_by
                                ? \App\Models\User::find($case->litigation_stage_changed_by)?->name
                                : ($isSystemChange ? 'System · CMS Sync' : null);
                            $changedAt = $case->litigation_stage_changed_at
                                ? \Carbon\Carbon::parse($case->litigation_stage_changed_at)->format('d M Y, H:i')
                                : null;
                        @endphp
                        <div style="margin-top:8px; border-top:1px solid var(--rule-2); padding-top:8px;" onclick="event.stopPropagation()">
                            <label style="font-size:9px; font-weight:700; letter-spacing:0.07em; text-transform:uppercase; color:var(--ink-3); display:block; margin-bottom:3px;">Move Stage</label>
                            <select onchange="litUpdateStage(this, {{ $case->id }})"
                                    style="width:100%; font-size:11px; padding:5px 6px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-family:inherit; border-radius:2px; cursor:pointer;">
                                @foreach(['Filed','In Hearings','Awaiting Judgment','Resolved'] as $s)
                                    <option value="{{ $s }}" {{ ($case->litigation_stage ?? 'Filed') === $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                            @if($changer)
                            <div id="lit-stage-meta-{{ $case->id }}" style="font-size:9px; color:var(--ink-4); margin-top:4px;">
                                <strong>{{ $changer }}</strong> · {{ $changedAt }}
                            </div>
                            @else
                            <div id="lit-stage-meta-{{ $case->id }}" style="font-size:9px; color:var(--ink-4); margin-top:4px; display:none;"></div>
                            @endif
                        </div>

                    </div>
                    @empty
                    <div style="padding: 20px 14px; text-align: center; color: var(--ink-4); font-size: 11px; border: 1px dashed var(--rule);">
                        No cases
                    </div>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         4. RESOLUTION OUTCOMES + COURT TYPE BREAKDOWN
         ════════════════════════════════════════════════════════════ --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 32px; animation: jh-fade-up 0.6s ease both; animation-delay: 0.2s;">

        {{-- Resolution outcomes --}}
        <div class="card" style="padding: 22px 24px;">
            <h3 class="serif" style="font-size: 22px; font-weight: 400; letter-spacing: -0.015em; margin: 0 0 4px 0;">
                Resolution <em style="font-style: italic;">outcomes</em>
            </h3>
            <p style="font-size: 12px; color: var(--ink-3); margin: 0 0 20px 0;">
                {{ $favourable }} resolved cases &middot; outcome breakdown
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: center;">
                {{-- Doughnut chart --}}
                <div style="position: relative;">
                    <div data-chart="serviceMixPie"
                         data-chart-config='{{ json_encode($chartOutcomeData) }}'
                         style="height: 220px; position: relative;">
                        <canvas></canvas>
                    </div>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); text-align: center; pointer-events: none;">
                        <div class="serif" style="font-size: 30px; font-weight: 500; line-height: 1;">{{ $favourable }}</div>
                        <div class="label-cap" style="font-size: 9px; color: var(--ink-3); margin-top: 2px;">RESOLVED</div>
                    </div>
                </div>

                {{-- Outcome bars --}}
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    @foreach(['won' => 'Won', 'partial' => 'Partial', 'pending' => 'Pending', 'lost' => 'Lost', 'withdrawn' => 'Withdrawn'] as $key => $label)
                    @php
                        $count  = $resolutionOutcomes[$key];
                        $pct    = $resTotal > 0 ? round(($count / $resTotal) * 100) : 0;
                        $barClr = $outColors[$key];
                    @endphp
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                            <div style="display: flex; align-items: center; gap: 7px;">
                                <span style="width: 9px; height: 9px; border-radius: 2px; background: {{ $barClr }}; display: inline-block; flex-shrink: 0;"></span>
                                <span style="font-size: 12.5px; font-weight: 500; color: var(--ink);">{{ $label }}</span>
                            </div>
                            <div style="display: flex; align-items: baseline; gap: 5px;">
                                <span class="mono" style="font-size: 13px; font-weight: 600;">{{ $count }}</span>
                                <span class="mono" style="font-size: 10px; color: var(--ink-4);">{{ $pct }}%</span>
                            </div>
                        </div>
                        <div style="height: 7px; background: var(--rule-2); border-radius: 4px; overflow: hidden;">
                            <div class="jh-anim-bar" style="height: 100%; width: {{ $pct }}%; background: {{ $barClr }}; border-radius: 4px; animation-delay: {{ 0.3 + $loop->index * 0.08 }}s;"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- By court type --}}
        <div class="card" style="padding: 22px 24px;">
            <h3 class="serif" style="font-size: 22px; font-weight: 400; letter-spacing: -0.015em; margin: 0 0 4px 0;">
                By court <em style="font-style: italic;">type</em>
            </h3>
            <p style="font-size: 12px; color: var(--ink-3); margin: 0 0 20px 0;">
                Case distribution by jurisdiction &middot; {{ $totalAppearances }} total appearances
            </p>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                @foreach($courtTypes as $ctName => $ctCount)
                @php
                    $ctPct = $ctMax > 0 ? round(($ctCount / $ctMax) * 100) : 0;
                    $ctClr = $ctColors[$ctName] ?? 'var(--ink-3)';
                @endphp
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="width: 10px; height: 10px; border-radius: 2px; background: {{ $ctClr }}; display: inline-block;"></span>
                            <span style="font-size: 13px; font-weight: 500; color: var(--ink);">{{ $ctName }}</span>
                        </div>
                        <div style="display: flex; align-items: baseline; gap: 6px;">
                            <span class="mono" style="font-size: 14px; font-weight: 600;">{{ $ctCount }}</span>
                            <span style="font-size: 11px; color: var(--ink-4);">cases</span>
                        </div>
                    </div>
                    <div style="height: 8px; background: var(--rule-2); border-radius: 4px; overflow: hidden;">
                        <div class="jh-anim-bar" style="height: 100%; width: {{ $ctPct }}%; background: {{ $ctClr }}; border-radius: 4px; animation-delay: {{ 0.35 + $loop->index * 0.1 }}s;"></div>
                    </div>
                </div>
                @endforeach
            </div>

            <div style="margin-top: 20px; padding-top: 14px; border-top: 1px solid var(--rule-2); display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 11.5px; color: var(--ink-3);">Total court appearances logged</span>
                <span class="mono serif" style="font-size: 20px; font-weight: 500; color: var(--ink);">{{ $totalAppearances }}</span>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         5. LAWYER & PARALEGAL WORKLOAD
         ════════════════════════════════════════════════════════════ --}}
    <div style="margin-bottom: 32px; animation: jh-fade-up 0.6s ease both; animation-delay: 0.3s;">
        <div style="margin-bottom: 14px;">
            <h2 class="serif" style="font-size: 26px; font-weight: 400; letter-spacing: -0.015em; margin: 0; line-height: 1.1;">
                Lawyer & paralegal <em style="font-style: italic;">workload</em>
            </h2>
            <div style="font-size: 12.5px; color: var(--ink-3); margin-top: 5px;">
                Active caseload vs. capacity &middot; SLA-aware &middot; {{ $staffCount }} staff across {{ $uniqueHubs }} hubs
            </div>
        </div>

        <div id="staffFilters" style="display: flex; gap: 6px; margin-bottom: 14px;">
            <button class="staff-pill-filter active" onclick="litStaffFilter('all', this)">All</button>
            <button class="staff-pill-filter" onclick="litStaffFilter('Lawyer', this)">Lawyers</button>
            <button class="staff-pill-filter" onclick="litStaffFilter('Paralegal', this)">Paralegals</button>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--rule); background: var(--parchment);">
                        <th style="padding: 10px 16px; text-align: left; font-size: 10px; letter-spacing: 0.06em; font-weight: 700; color: var(--ink-3); text-transform: uppercase;">Staff</th>
                        <th style="padding: 10px 14px; text-align: center; font-size: 10px; letter-spacing: 0.06em; font-weight: 700; color: var(--ink-3); text-transform: uppercase;">Hub</th>
                        <th style="padding: 10px 14px; text-align: center; font-size: 10px; letter-spacing: 0.06em; font-weight: 700; color: var(--ink-3); text-transform: uppercase;">ADR</th>
                        <th style="padding: 10px 14px; text-align: center; font-size: 10px; letter-spacing: 0.06em; font-weight: 700; color: var(--ink-3); text-transform: uppercase;">Court</th>
                        <th style="padding: 10px 14px; text-align: center; font-size: 10px; letter-spacing: 0.06em; font-weight: 700; color: var(--ink-3); text-transform: uppercase;">Active</th>
                        <th style="padding: 10px 14px; text-align: center; font-size: 10px; letter-spacing: 0.06em; font-weight: 700; color: var(--ink-3); text-transform: uppercase;">SLA Breach</th>
                        <th style="padding: 10px 14px; text-align: left; font-size: 10px; letter-spacing: 0.06em; font-weight: 700; color: var(--ink-3); text-transform: uppercase; min-width: 140px;">Utilization</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $s)
                    @php
                        $util = $s['utilization'];
                        $utilColor = $util >= 90 ? 'var(--burgundy)' : ($util >= 70 ? 'var(--ochre)' : 'var(--forest)');
                    @endphp
                    <tr class="staff-row" data-role="{{ $s['role'] }}"
                        style="border-bottom: 1px solid var(--rule); transition: background 100ms;"
                        onmouseenter="this.style.background='var(--parchment)'"
                        onmouseleave="this.style.background=''">
                        <td style="padding: 12px 16px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--burgundy); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0;">
                                    {{ $s['initials'] ?? strtoupper(substr($s['name'], 0, 2)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 600; font-size: 13px; color: var(--ink);">{{ $s['name'] }}</div>
                                    <div style="font-size: 11px; color: var(--ink-3); margin-top: 1px;">{{ $s['role'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 12px 14px; text-align: center;">
                            <span class="mono" style="font-size: 11px; color: var(--ink-3);">{{ $s['hub_id'] ?? '—' }}</span>
                        </td>
                        <td style="padding: 12px 14px; text-align: center;">
                            <span class="mono" style="font-size: 13px; font-weight: 600; color: var(--moss);">{{ $s['adr'] }}</span>
                        </td>
                        <td style="padding: 12px 14px; text-align: center;">
                            <span class="mono" style="font-size: 13px; font-weight: 600; color: var(--burgundy);">{{ $s['court'] }}</span>
                        </td>
                        <td style="padding: 12px 14px; text-align: center;">
                            <span class="mono" style="font-size: 14px; font-weight: 700; color: var(--ink);">{{ $s['active'] }}</span>
                            <span style="font-size: 10px; color: var(--ink-4);">/{{ $s['capacity'] }}</span>
                        </td>
                        <td style="padding: 12px 14px; text-align: center;">
                            @if($s['sla_breach'] > 0)
                            <span style="font-size: 12px; font-weight: 700; color: var(--burgundy);">{{ $s['sla_breach'] }}</span>
                            @else
                            <span style="font-size: 12px; color: var(--ink-4);">—</span>
                            @endif
                        </td>
                        <td style="padding: 12px 14px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="flex: 1; height: 7px; background: var(--rule-2); border-radius: 4px; overflow: hidden; min-width: 60px;">
                                    <div style="height: 100%; width: {{ $util }}%; background: {{ $utilColor }}; border-radius: 4px; transition: width 0.6s ease;"></div>
                                </div>
                                <span class="mono" style="font-size: 12px; font-weight: 700; color: {{ $utilColor }}; min-width: 34px; text-align: right;">{{ $util }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 32px; text-align: center; color: var(--ink-4); font-size: 13px;">
                            No active staff found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Legend --}}
        <div style="display: flex; gap: 20px; margin-top: 10px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--ink-3);">
                <span style="width: 12px; height: 6px; border-radius: 3px; background: var(--forest); display: inline-block;"></span> &lt;70% capacity
            </div>
            <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--ink-3);">
                <span style="width: 12px; height: 6px; border-radius: 3px; background: var(--ochre); display: inline-block;"></span> 70-89% capacity
            </div>
            <div style="display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--ink-3);">
                <span style="width: 12px; height: 6px; border-radius: 3px; background: var(--burgundy); display: inline-block;"></span> &ge;90% — near capacity
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         6. RECENT COURT ACTIVITY
         ════════════════════════════════════════════════════════════ --}}
    <div style="animation: jh-fade-up 0.6s ease both; animation-delay: 0.4s;">
        <div style="margin-bottom: 14px;">
            <h2 class="serif" style="font-size: 26px; font-weight: 400; letter-spacing: -0.015em; margin: 0; line-height: 1.1;">
                Recent court <em style="font-style: italic;">activity</em>
            </h2>
            <div style="font-size: 12.5px; color: var(--ink-3); margin-top: 5px;">
                Latest court appearances and hearings logged
            </div>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            @forelse($recentActivity as $enc)
            @php
                $encCase = $enc->caseRecord;
                $avBg2   = $avColors[$loop->index % count($avColors)];
                $typeBadgeColor = str_contains(strtolower($enc->type), 'court') ? 'var(--burgundy)' : 'var(--ochre)';
            @endphp
            <div style="display: grid; grid-template-columns: 80px 1fr auto; gap: 16px; padding: 16px 20px; border-bottom: 1px solid var(--rule); align-items: start; transition: background 100ms;"
                 onmouseenter="this.style.background='var(--parchment)'"
                 onmouseleave="this.style.background=''">

                {{-- Date column --}}
                <div style="text-align: center; padding-top: 2px;">
                    <div class="mono" style="font-size: 18px; font-weight: 700; color: var(--ink); line-height: 1;">{{ $enc->date->format('d') }}</div>
                    <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-top: 1px; letter-spacing: 0.08em;">{{ strtoupper($enc->date->format('M Y')) }}</div>
                </div>

                {{-- Content column --}}
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px; flex-wrap: wrap;">
                        {{-- Avatar + name --}}
                        <div style="width: 22px; height: 22px; border-radius: 50%; background: {{ $avBg2 }}; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 700; flex-shrink: 0;">
                            {{ strtoupper(substr($encCase?->name ?? '?', 0, 1)) }}
                        </div>
                        <span style="font-size: 13px; font-weight: 600; color: var(--ink);">{{ $encCase?->name ?? 'Unknown' }}</span>
                        <span class="mono" style="font-size: 10px; color: var(--ink-4); padding: 1px 6px; border: 1px solid var(--rule); border-radius: 2px;">{{ $encCase?->case_uid ?? '—' }}</span>
                        {{-- Type badge --}}
                        <span style="font-size: 9px; padding: 2px 7px; background: {{ $typeBadgeColor }}; color: #fff; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; border-radius: 2px;">{{ $enc->type }}</span>
                    </div>
                    @if($enc->note)
                    <div style="font-size: 12.5px; color: var(--ink-2); line-height: 1.5; margin-bottom: 4px;">
                        {{ Str::limit($enc->note, 140) }}
                    </div>
                    @endif
                    <div style="font-size: 11px; color: var(--ink-4);">
                        <span class="label-cap" style="font-size: 9px; letter-spacing: 0.06em; color: var(--ink-3);">DELIVERED BY</span>
                        <span style="margin-left: 4px; font-weight: 500; color: var(--ink-3);">{{ $enc->performed_by }}</span>
                        @if($encCase?->hub_id)
                        <span style="margin-left: 8px;">&middot;</span>
                        <span class="mono" style="margin-left: 8px; font-size: 10.5px;">{{ $encCase->hub_id }}</span>
                        @endif
                    </div>
                </div>

                {{-- Right: hub name --}}
                <div style="text-align: right; padding-top: 2px;">
                    @if($encCase?->hub)
                    <div style="font-size: 11px; color: var(--ink-3); white-space: nowrap;">{{ $encCase->hub->name }}</div>
                    @endif
                    @if($encCase)
                    <a href="{{ route('cases.show', $encCase) }}" style="font-size: 11px; color: var(--burgundy); font-weight: 600; text-decoration: none; margin-top: 4px; display: block;" onmouseenter="this.style.textDecoration='underline'" onmouseleave="this.style.textDecoration='none'">View case &rarr;</a>
                    @endif
                </div>
            </div>
            @empty
            <div style="padding: 40px; text-align: center; color: var(--ink-4); font-size: 13px;">
                No court activity recorded yet
            </div>
            @endforelse
        </div>
    </div>

</div>

<script>
// ── Stage update ──────────────────────────────────────────────
function litUpdateStage(selectEl, caseId) {
    var newStage = selectEl.value;
    selectEl.disabled = true;

    fetch('/cases/' + caseId + '/litigation-stage', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ stage: newStage })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Update the "changed by" text below the dropdown
            var info = selectEl.nextElementSibling;
            if (info && info.style.fontSize === '9px') {
                info.innerHTML = '<strong>' + data.changed_by + '</strong> · ' + data.changed_at;
            } else {
                var div = document.createElement('div');
                div.style.cssText = 'font-size:9px; color:var(--ink-4); margin-top:4px;';
                div.innerHTML = '<strong>' + data.changed_by + '</strong> · ' + data.changed_at;
                selectEl.parentNode.appendChild(div);
            }
            // Reload page to move card to new column
            setTimeout(() => location.reload(), 600);
        }
        selectEl.disabled = false;
    })
    .catch(() => { selectEl.disabled = false; });
}

// ── Kanban filter ──────────────────────────────────────────────
function litFilter(type, btn) {
    document.querySelectorAll('.lit-pill-filter').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.lit-kanban-card').forEach(card => {
        let show = false;
        if (type === 'all')        show = true;
        else if (type === 'gbv')       show = card.dataset.gbv === '1';
        else if (type === 'child')     show = card.dataset.child === '1';
        else if (type === 'underserved') show = card.dataset.underserved === '1';
        card.style.display = show ? '' : 'none';
    });

    // Refresh count badges
    document.querySelectorAll('.kanban-col').forEach(col => {
        const visible = [...col.querySelectorAll('.lit-kanban-card')].filter(c => c.style.display !== 'none').length;
        const badge = col.querySelector('.kanban-count');
        if (badge) badge.textContent = visible;
    });
}

// ── Staff filter ───────────────────────────────────────────────
function litStaffFilter(role, btn) {
    document.querySelectorAll('.staff-pill-filter').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.staff-row').forEach(row => {
        row.style.display = (role === 'all' || row.dataset.role === role) ? '' : 'none';
    });
}

// ── Set Outcome popup ──
function litSetOutcomeOpen(caseId, caseUid, clientName) {
    document.getElementById('litOutcomeCaseId').value = caseId;
    document.getElementById('litOutcomeTitle').textContent = caseUid + ' · ' + clientName;
    document.getElementById('litOutcomeForm').action = '/cases/' + caseId + '/set-outcome';
    // Reset selection
    document.querySelectorAll('#litOutcomeForm [name=outcome]').forEach(function(r) {
        r.checked = false;
        r.closest('label').style.borderColor = 'var(--rule)';
    });
    jhOpenModal('set-outcome');
}
</script>

{{-- ═══ Set Outcome Modal ═══ --}}
<div class="modal fade" id="modal-set-outcome" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 420px; margin: 1.75rem auto;">
        <div class="modal-content" style="border: 1px solid var(--rule); border-radius: 4px; background: var(--parchment); box-shadow: 0 16px 48px rgba(0,0,0,.18);">
            <div style="padding: 20px 24px 16px; border-bottom: 1px solid var(--rule);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 5px;">Set Case Outcome</div>
                        <h3 class="serif" style="font-size: 22px; font-weight: 400; margin: 0;" id="litOutcomeTitle"></h3>
                    </div>
                    <button type="button" data-bs-dismiss="modal" style="background:none; border:1px solid var(--rule); cursor:pointer; padding:5px 7px; color:var(--ink-3); border-radius:3px;">
                        <x-lucide-x style="width:14px;height:14px;" />
                    </button>
                </div>
            </div>
            <form method="POST" id="litOutcomeForm" action="">
                @csrf
                <input type="hidden" id="litOutcomeCaseId" value="">
                <div style="padding: 20px 24px;">
                    <div style="font-size: 12px; color: var(--ink-3); margin-bottom: 14px;">What was the result of this case?</div>
                    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px;">
                        @foreach(['Won' => 'var(--forest)', 'Partial' => 'var(--ochre)', 'Lost' => 'var(--burgundy)', 'Withdrawn' => 'var(--ink-3)', 'Settlement' => 'var(--moss)'] as $oc => $col)
                        <label style="display:flex; flex-direction:column; align-items:center; gap:4px; padding:14px 4px; border:2px solid var(--rule); cursor:pointer; transition:all 120ms; text-align:center;"
                               onclick="this.querySelector('input').checked=true; document.querySelectorAll('#litOutcomeForm [name=outcome]').forEach(function(r){r.closest('label').style.borderColor='var(--rule)'}); this.style.borderColor='{{ $col }}';">
                            <input type="radio" name="outcome" value="{{ $oc }}" required style="display:none;">
                            <span style="font-size: 13px; font-weight: 700; color: {{ $col }};">{{ $oc }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div style="padding: 14px 24px; border-top: 1px solid var(--rule); display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" data-bs-dismiss="modal" class="btn-ghost">Cancel</button>
                    <button type="submit" class="btn-primary" style="display:inline-flex; align-items:center; gap:6px;">
                        <x-lucide-check style="width:12px;height:12px;" /> Save Outcome
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-layouts.app>
