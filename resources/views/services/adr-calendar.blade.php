<x-layouts.app>
@php
    $today7 = $upcomingSessions->count();
    $todayCount = $todaySessions->count();

    // Avatar color pool (consistent with rest of app)
    $avatarColors = ['#163029','#8a2e1d','#b87319','#4a7a5c','#6b6a65','#3d5a47','#7a4a2e','#2e5a7a'];

    function jhCalVenue($session): string {
        $hub = $session->caseRecord?->hub;
        $room = $session->meta['venue'] ?? null;
        $hubLabel = $hub ? ($hub->id . ' — ' . $hub->name) : ($session->caseRecord?->hub_id ?? '—');
        return $room ? $hubLabel . ' · ' . $room : $hubLabel;
    }

    function jhCalTime($session): ?string {
        $t = $session->meta['time'] ?? null;
        if (!$t) return null;
        try { return \Carbon\Carbon::createFromFormat('H:i', substr($t, 0, 5))->format('H:i'); } catch (\Exception $e) { return $t; }
    }
@endphp

<style>
    @keyframes jh-fade-up { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
    .jh-cal-anim { animation: jh-fade-up 0.5s ease both; }
    .jh-session-card { transition: border-color 140ms ease, box-shadow 140ms ease; }
    .jh-session-card:hover { border-color: var(--ochre) !important; box-shadow: 0 4px 14px rgba(0,0,0,0.07); }
    .jh-btn-session { display: inline-flex; align-items: center; gap: 5px; padding: 5px 11px; font-size: 11.5px; font-weight: 500; font-family: inherit; background: var(--ochre); color: #fff; border: 1px solid var(--ochre); cursor: pointer; transition: opacity 140ms; white-space: nowrap; }
    .jh-btn-session:hover { opacity: 0.88; }
    .jh-btn-view { display: inline-flex; align-items: center; gap: 5px; padding: 5px 11px; font-size: 11.5px; font-weight: 500; font-family: inherit; background: transparent; color: var(--ink-2); border: 1px solid var(--rule); cursor: pointer; transition: border-color 140ms, color 140ms; text-decoration: none; white-space: nowrap; }
    .jh-btn-view:hover { border-color: var(--forest); color: var(--forest); }
    .jh-section-header { background: var(--forest); color: var(--cream); padding: 12px 18px; display: flex; align-items: center; justify-content: space-between; }
</style>

<div style="padding: 24px 34px 64px; max-width: 1600px; margin: 0 auto;">

    {{-- ═══ HEADER ════════════════════════════════════════════════ --}}
    <div class="jh-cal-anim" style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 26px; gap: 16px; flex-wrap: wrap;">
        <div>
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 6px; letter-spacing: 0.12em; color: var(--ink-4);">
                SERVICE DELIVERY &middot; ADR SESSIONS
            </div>
            <h1 class="serif" style="font-size: 32px; font-weight: 400; letter-spacing: -0.02em; margin: 0; line-height: 1.15; color: var(--ink);">
                ADR Calendar &amp; <em style="color: var(--ochre); font-style: italic;">Upcoming Sessions</em>
            </h1>
            <p style="margin: 7px 0 0 0; font-size: 13px; color: var(--ink-3); line-height: 1.45; max-width: 520px;">
                Mediation sessions, ADR appointments and pathway milestones
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; flex-shrink: 0; margin-top: 4px;">
            <span style="font-size: 10px; font-weight: 700; letter-spacing: 0.1em; padding: 5px 12px; background: rgba(184,115,25,0.12); color: var(--ochre); border: 1px solid rgba(184,115,25,0.25); text-transform: uppercase;">SERVICE &middot; O2.2</span>
            <button onclick="jhOpenModal('cal-log-service')" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-size: 12.5px; font-weight: 500; font-family: inherit; border: 1px solid var(--forest); background: var(--forest); color: var(--cream); cursor: pointer;">
                <x-lucide-plus style="width: 13px; height: 13px;" /> Log session
            </button>
        </div>
    </div>

    {{-- ═══ 4 KPI CARDS ════════════════════════════════════════════ --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 28px;">

        <div class="card jh-cal-anim" style="padding: 18px 20px; display: flex; align-items: center; gap: 14px; animation-delay: 0.05s;">
            <div style="width: 38px; height: 38px; background: var(--parchment); border: 1px solid var(--rule-2); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <x-lucide-archive style="width: 15px; height: 15px; color: var(--forest);" />
            </div>
            <div>
                <div class="serif" style="font-size: 30px; font-weight: 400; line-height: 1; letter-spacing: -0.02em; color: var(--ink);">{{ $totalCases }}</div>
                <div style="font-size: 11px; color: var(--ink-4); margin-top: 3px; line-height: 1.3;">TOTAL CASES<br>in mediation pathway</div>
            </div>
        </div>

        <div class="card jh-cal-anim" style="padding: 18px 20px; display: flex; align-items: center; gap: 14px; border-top: 3px solid var(--ochre); animation-delay: 0.1s;">
            <div style="width: 38px; height: 38px; background: rgba(184,115,25,0.08); border: 1px solid rgba(184,115,25,0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <x-lucide-circle-check style="width: 15px; height: 15px; color: var(--ochre);" />
            </div>
            <div>
                <div class="serif" style="font-size: 30px; font-weight: 400; line-height: 1; letter-spacing: -0.02em; color: var(--ink);">{{ $todayCount }}</div>
                <div style="font-size: 11px; color: var(--ink-4); margin-top: 3px; line-height: 1.3;">TODAY'S SESSIONS<br>as of {{ now()->format('M j, Y') }}</div>
            </div>
        </div>

        <div class="card jh-cal-anim" style="padding: 18px 20px; display: flex; align-items: center; gap: 14px; border-top: 3px solid var(--moss); animation-delay: 0.15s;">
            <div style="width: 38px; height: 38px; background: rgba(74,122,92,0.08); border: 1px solid rgba(74,122,92,0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <x-lucide-calendar style="width: 15px; height: 15px; color: var(--moss);" />
            </div>
            <div>
                <div class="serif" style="font-size: 30px; font-weight: 400; line-height: 1; letter-spacing: -0.02em; color: var(--ink);">{{ $today7 }}</div>
                <div style="font-size: 11px; color: var(--ink-4); margin-top: 3px; line-height: 1.3;">NEXT 7 DAYS<br>scheduled across hubs</div>
            </div>
        </div>

        <div class="card jh-cal-anim" style="padding: 18px 20px; display: flex; align-items: center; gap: 14px; border-top: 3px solid {{ $missingNextHearing > 0 ? 'var(--burgundy)' : 'var(--rule)' }}; animation-delay: 0.2s; {{ $missingNextHearing > 0 ? 'cursor:pointer;' : '' }}"
             {{ $missingNextHearing > 0 ? 'onclick=jhOpenModal(\'missing-hearing-modal\')' : '' }}>
            <div style="width: 38px; height: 38px; background: {{ $missingNextHearing > 0 ? 'rgba(138,46,29,0.08)' : 'var(--parchment)' }}; border: 1px solid {{ $missingNextHearing > 0 ? 'rgba(138,46,29,0.2)' : 'var(--rule-2)' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <x-lucide-alert-triangle style="width: 15px; height: 15px; color: {{ $missingNextHearing > 0 ? 'var(--burgundy)' : 'var(--ink-4)' }};" />
            </div>
            <div>
                <div class="serif" style="font-size: 30px; font-weight: 400; line-height: 1; letter-spacing: -0.02em; color: {{ $missingNextHearing > 0 ? 'var(--burgundy)' : 'var(--ink)' }};">{{ $missingNextHearing }}</div>
                <div style="font-size: 11px; color: var(--ink-4); margin-top: 3px; line-height: 1.3;">MISSING NEXT HEARING<br>active cases without next date</div>
            </div>
            @if($missingNextHearing > 0)
            <x-lucide-external-link style="width:12px;height:12px;color:var(--burgundy);margin-left:auto;flex-shrink:0;" />
            @endif
        </div>

    </div>

    {{-- ═══ TWO-COLUMN SESSION LISTS ═══════════════════════════════ --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: start;" class="jh-cal-anim" style="animation-delay: 0.25s;">

        {{-- ── LEFT: Today's Sessions ────────────────────────────── --}}
        <div>
            {{-- Section header --}}
            <div class="jh-section-header" style="margin-bottom: 0;">
                <span style="font-size: 14px; font-weight: 600; letter-spacing: 0.01em;">Today's Sessions</span>
                <span class="mono" style="font-size: 13px; font-weight: 700; background: rgba(255,255,255,0.15); padding: 2px 10px; border-radius: 2px;">{{ $todayCount }}</span>
            </div>

            {{-- Cards --}}
            <div style="border: 1px solid var(--rule); border-top: none; background: var(--paper);">
                @forelse($todaySessions as $s)
                @php
                    $case = $s->caseRecord;
                    $initials = $case ? collect(explode(' ', $case->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('') : '?';
                    $avatarBg = $avatarColors[abs(crc32($case?->name ?? '')) % count($avatarColors)];
                    $timeStr = jhCalTime($s);
                    $venue = jhCalVenue($s);
                @endphp
                <div class="jh-session-card" style="padding: 14px 16px; border-bottom: 1px solid var(--rule-2); border-left: 3px solid var(--ochre);">

                    {{-- Row 1: case UID + date/time --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 7px;">
                            <span style="width: 7px; height: 7px; border-radius: 50%; background: var(--ochre); flex-shrink: 0; display: inline-block;"></span>
                            <span class="mono" style="font-size: 13px; font-weight: 700; color: var(--ink);">{{ $case?->case_uid ?? '—' }}</span>
                        </div>
                        <span class="mono" style="font-size: 11.5px; color: var(--ink-3); white-space: nowrap;">
                            {{ $s->date->format('M j, Y') }}{{ $timeStr ? ', ' . $timeStr : '' }}
                        </span>
                    </div>

                    {{-- Row 2: avatar + client name --}}
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                        <div style="width: 26px; height: 26px; border-radius: 50%; background: {{ $avatarBg }}; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 700; flex-shrink: 0;">{{ $initials }}</div>
                        <span style="font-size: 13px; color: var(--ink-2);">Client: <strong style="color: var(--ink);">{{ $case?->name ?? 'Unknown' }}</strong></span>
                    </div>

                    {{-- Row 3: venue --}}
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 11.5px; color: var(--ink-3); margin-bottom: 4px;">
                        <x-lucide-map-pin style="width: 11px; height: 11px; flex-shrink: 0; color: var(--ink-4);" />
                        Venue: {{ $venue }}
                    </div>

                    {{-- Row 4: lawyer --}}
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 11.5px; color: var(--ink-3); margin-bottom: 10px;">
                        <x-lucide-user style="width: 11px; height: 11px; flex-shrink: 0; color: var(--ink-4);" />
                        Lawyer: {{ $s->performed_by }}
                    </div>

                    {{-- Row 5: actions --}}
                    <div style="display: flex; align-items: center; gap: 7px;">
                        <button type="button" class="jh-btn-session"
                            onclick="jhCalPresetCase('{{ $case?->id }}', '{{ addslashes($case?->case_uid . ' — ' . $case?->name) }}')">
                            <x-lucide-circle-check style="width: 12px; height: 12px;" />
                            Session
                        </button>
                        @if($case)
                        <a href="{{ route('cases.show', $case) }}" class="jh-btn-view">
                            <x-lucide-eye style="width: 12px; height: 12px;" />
                            View
                        </a>
                        @endif
                    </div>
                </div>
                @empty
                <div style="padding: 36px 20px; text-align: center; color: var(--ink-4);">
                    <x-lucide-sun style="width: 22px; height: 22px; margin: 0 auto 8px; display: block; opacity: 0.4;" />
                    <div style="font-size: 13px;">No sessions scheduled for today</div>
                </div>
                @endforelse
            </div>
        </div>

        {{-- ── RIGHT: Upcoming Sessions (Next 7 Days) ────────────── --}}
        <div>
            {{-- Section header --}}
            <div class="jh-section-header" style="margin-bottom: 0;">
                <span style="font-size: 14px; font-weight: 600; letter-spacing: 0.01em;">Upcoming Sessions (Next 7 Days)</span>
                <span class="mono" style="font-size: 13px; font-weight: 700; background: rgba(255,255,255,0.15); padding: 2px 10px; border-radius: 2px;">{{ $today7 }}</span>
            </div>

            {{-- Cards --}}
            <div style="border: 1px solid var(--rule); border-top: none; background: var(--paper); max-height: 680px; overflow-y: auto;" class="jh-scroll">
                @forelse($upcomingSessions as $s)
                @php
                    $case = $s->caseRecord;
                    $initials = $case ? collect(explode(' ', $case->name))->map(fn($n) => strtoupper(substr($n, 0, 1)))->take(2)->join('') : '?';
                    $avatarBg = $avatarColors[abs(crc32($case?->name ?? '')) % count($avatarColors)];
                    $timeStr = jhCalTime($s);
                    $venue = jhCalVenue($s);
                    $daysAway = (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($s->date)->startOfDay(), false);
                @endphp
                <div class="jh-session-card" style="padding: 14px 16px; border-bottom: 1px solid var(--rule-2); border-left: 3px solid var(--moss);">

                    {{-- Row 1: case UID + date/time + days-away badge --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 7px;">
                            <span style="width: 7px; height: 7px; border-radius: 50%; background: var(--ochre); flex-shrink: 0; display: inline-block;"></span>
                            <span class="mono" style="font-size: 13px; font-weight: 700; color: var(--ink);">{{ $case?->case_uid ?? '—' }}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span class="mono" style="font-size: 9.5px; font-weight: 600; padding: 1px 6px; background: rgba(74,122,92,0.10); color: var(--moss);">
                                in {{ $daysAway }}d
                            </span>
                            <span class="mono" style="font-size: 11.5px; color: var(--ink-3); white-space: nowrap;">
                                {{ $s->date->format('M j') }}{{ $timeStr ? ', ' . $timeStr : '' }}
                            </span>
                        </div>
                    </div>

                    {{-- Row 2: avatar + client name --}}
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                        <div style="width: 26px; height: 26px; border-radius: 50%; background: {{ $avatarBg }}; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 700; flex-shrink: 0;">{{ $initials }}</div>
                        <span style="font-size: 13px; color: var(--ink-2);">Client: <strong style="color: var(--ink);">{{ $case?->name ?? 'Unknown' }}</strong></span>
                    </div>

                    {{-- Row 3: venue --}}
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 11.5px; color: var(--ink-3); margin-bottom: 4px;">
                        <x-lucide-map-pin style="width: 11px; height: 11px; flex-shrink: 0; color: var(--ink-4);" />
                        Venue: {{ $venue }}
                    </div>

                    {{-- Row 4: lawyer --}}
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 11.5px; color: var(--ink-3); margin-bottom: 10px;">
                        <x-lucide-user style="width: 11px; height: 11px; flex-shrink: 0; color: var(--ink-4);" />
                        Lawyer: {{ $s->performed_by }}
                    </div>

                    {{-- Row 5: View action --}}
                    @if($case)
                    <div>
                        <a href="{{ route('cases.show', $case) }}" class="jh-btn-view">
                            <x-lucide-eye style="width: 12px; height: 12px;" />
                            View
                        </a>
                    </div>
                    @endif
                </div>
                @empty
                <div style="padding: 36px 20px; text-align: center; color: var(--ink-4);">
                    <x-lucide-calendar style="width: 22px; height: 22px; margin: 0 auto 8px; display: block; opacity: 0.4;" />
                    <div style="font-size: 13px;">No sessions in the next 7 days</div>
                </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- ═══ MISSING NEXT HEARING ALERT ════════════════════════════ --}}
    @if($missingNextHearing > 0)
    <div style="margin-top: 20px; padding: 14px 18px; background: rgba(138,46,29,0.06); border: 1px solid rgba(138,46,29,0.2); border-left: 4px solid var(--burgundy); display: flex; align-items: center; gap: 12px;" class="jh-cal-anim" style="animation-delay:0.3s;">
        <x-lucide-alert-triangle style="width: 16px; height: 16px; color: var(--burgundy); flex-shrink: 0;" />
        <div>
            <strong style="font-size: 13px; color: var(--burgundy);">{{ $missingNextHearing }} active ADR case{{ $missingNextHearing > 1 ? 's have' : ' has' }} no next session scheduled.</strong>
            <span style="font-size: 12.5px; color: var(--ink-3); margin-left: 6px;">Use "Log session" to schedule a follow-up for each.</span>
        </div>
    </div>
    @endif

</div>

{{-- ═══════════════════════════════════════════════════════════════════
     MISSING NEXT HEARING MODAL
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-missing-hearing-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 560px; margin: 1.75rem auto;">
        <div class="modal-content" style="border-radius: 4px; background: var(--paper); box-shadow: 0 16px 48px rgba(0,0,0,0.18); border-top: 3px solid var(--burgundy);">

            {{-- Header --}}
            <div style="padding: 16px 22px 12px; border-bottom: 1px solid var(--rule); display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <div class="label-cap" style="font-size:9px; color:var(--burgundy); margin-bottom:4px;">Action Required</div>
                    <div style="font-size:15px; font-weight:600; color:var(--ink);">Cases Missing Next Session</div>
                    <div style="font-size:11px; color:var(--ink-4); margin-top:2px;">{{ $missingNextHearing }} active ADR {{ $missingNextHearing === 1 ? 'case has' : 'cases have' }} no upcoming session scheduled</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Case grid --}}
            <div style="padding: 16px 22px; display:grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                @foreach($missingCases as $mc)
                <a href="{{ route('cases.show', $mc) }}"
                   style="display:block; padding:12px 14px; border:1px solid rgba(138,46,29,0.2); border-left:3px solid var(--burgundy); background:rgba(138,46,29,0.03); text-decoration:none; color:inherit; border-radius:3px; transition: box-shadow .15s;"
                   onmouseenter="this.style.boxShadow='0 2px 8px rgba(138,46,29,0.12)'" onmouseleave="this.style.boxShadow=''">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:5px;">
                        <span class="mono" style="font-size:10px; color:var(--burgundy); font-weight:700;">{{ $mc->case_uid }}</span>
                        <x-lucide-arrow-right style="width:11px;height:11px;color:var(--burgundy);" />
                    </div>
                    <div style="font-size:12.5px; font-weight:600; color:var(--ink); margin-bottom:3px;">{{ $mc->name }}</div>
                    <div style="font-size:10px; color:var(--ink-4);">{{ $mc->primary_issue }}</div>
                    <div style="font-size:10px; color:var(--ink-4);">{{ $mc->hub_id }}{{ $mc->assigned_to ? ' · ' . $mc->assigned_to : '' }}</div>
                </a>
                @endforeach
            </div>

            {{-- Footer --}}
            <div style="padding:12px 22px 16px; border-top:1px solid var(--rule); font-size:11px; color:var(--ink-4);">
                Click any case to open it and log the next session date.
            </div>

        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     LOG SERVICE ENCOUNTER MODAL (same as ADR scorecard)
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-cal-log-service" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 680px; margin: 1.75rem auto;">
        <div class="modal-content" style="border-radius: 4px; background: var(--paper); box-shadow: 0 16px 48px rgba(0,0,0,0.18); max-height: 92vh; overflow-y: auto; border-top: 3px solid var(--forest);">

            <div style="padding: 18px 24px 14px; border-bottom: 1px solid var(--rule); position: sticky; top: 0; background: var(--paper); z-index: 10;">
                <div class="label-cap" style="font-size: 9px; letter-spacing: 0.14em; color: var(--ink-4); margin-bottom: 6px;">
                    SERVICE DELIVERY &middot; INDICATOR O2.2 &middot; ADR CALENDAR
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

            <form action="{{ route('encounters.log') }}" method="POST" style="padding: 0;">
                @csrf

                {{-- CASE --}}
                <div style="padding: 20px 24px 18px; border-bottom: 1px solid var(--rule-2);">
                    <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.12em; color: var(--ink-3); margin-bottom: 4px;">CASE</div>
                    <p style="font-size: 12px; color: var(--ink-4); margin: 0 0 12px 0;">Select the case this encounter is for</p>
                    <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Case <span style="color: var(--burgundy);">*</span></label>
                    <input type="hidden" name="case_id" id="calSvcCaseId" required>
                    <div style="position: relative;" id="calSvcPicker">
                        <div style="position: relative;">
                            <x-lucide-search style="width: 13px; height: 13px; position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--ink-4); pointer-events: none;" />
                            <input type="text" id="calSvcInput" autocomplete="off"
                                placeholder="Search by case ID, client name, or issue..."
                                style="width: 100%; padding: 10px 36px 10px 32px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); box-sizing: border-box; outline: none;"
                                onfocus="calSvcOpen()" oninput="calSvcFilter(this.value)">
                            <x-lucide-chevron-down id="calSvcChevron" style="width: 13px; height: 13px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--ink-3); pointer-events: none; transition: transform 200ms ease;" />
                        </div>
                        <div id="calSvcDropdown"
                            style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: var(--paper); border: 1px solid var(--rule); border-top: none; max-height: 220px; overflow-y: auto; box-shadow: 0 8px 24px rgba(0,0,0,0.1);">
                            @forelse($activeCases as $ac)
                            @php $dl = $ac->disposition ? strtoupper($ac->disposition->value) : ''; @endphp
                            <div class="cal-case-opt"
                                data-id="{{ $ac->id }}"
                                data-label="{{ $ac->case_uid }} — {{ $ac->name }}"
                                data-search="{{ strtolower($ac->case_uid . ' ' . $ac->name . ' ' . $ac->primary_issue) }}"
                                onclick="calSvcSelect(this)"
                                style="padding: 9px 12px; cursor: pointer; border-bottom: 1px solid var(--rule-2); display: flex; align-items: center; justify-content: space-between; gap: 10px;"
                                onmouseover="this.style.background='var(--parchment)'" onmouseout="this.style.background='var(--paper)'">
                                <div>
                                    <span class="mono" style="font-size: 11px; font-weight: 600; color: var(--ink-3);">{{ $ac->case_uid }}</span>
                                    <span style="font-size: 13px; color: var(--ink); margin-left: 6px;">{{ $ac->name }}</span>
                                    @if($ac->primary_issue)<div style="font-size: 11px; color: var(--ink-4); margin-top: 1px;">{{ $ac->primary_issue }}</div>@endif
                                </div>
                                @if($dl)<span style="font-size: 9.5px; font-weight: 700; letter-spacing: 0.06em; padding: 2px 6px; background: rgba(0,0,0,0.05); color: var(--ink-3); white-space: nowrap; flex-shrink: 0;">{{ $dl }}</span>@endif
                            </div>
                            @empty
                            <div style="padding: 14px; font-size: 13px; color: var(--ink-4); text-align: center;">No open cases found</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- SERVICE DETAILS --}}
                <div style="padding: 20px 24px 18px; border-bottom: 1px solid var(--rule-2);">
                    <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.12em; color: var(--ink-3); margin-bottom: 4px;">SERVICE DETAILS</div>
                    <p style="font-size: 12px; color: var(--ink-4); margin: 0 0 14px 0;">What was delivered, when, and by whom</p>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Service Type <span style="color: var(--burgundy);">*</span></label>
                        <div style="position: relative;">
                            <select name="type" required style="width: 100%; padding: 10px 36px 10px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); appearance: none; cursor: pointer; outline: none;">
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

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                        <div>
                            <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Date <span style="color: var(--burgundy);">*</span></label>
                            <input type="date" name="date" required value="{{ now()->toDateString() }}" style="width: 100%; padding: 10px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); box-sizing: border-box; outline: none;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Time</label>
                            <input type="time" name="time" value="{{ now()->format('H:i') }}" style="width: 100%; padding: 10px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); box-sizing: border-box; outline: none;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div>
                            <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Provider <span style="color: var(--burgundy);">*</span></label>
                            <div style="position: relative;">
                                <select name="performed_by" required style="width: 100%; padding: 10px 36px 10px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); appearance: none; cursor: pointer; outline: none;">
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
                        <div>
                            <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Duration <span style="color: var(--burgundy);">*</span></label>
                            <div style="position: relative;">
                                <select name="duration" required style="width: 100%; padding: 10px 36px 10px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); appearance: none; cursor: pointer; outline: none;">
                                    <option value="" disabled selected>Time spent</option>
                                    <option>30 min</option><option>1 hour</option><option>1.5 hours</option>
                                    <option>2 hours</option><option>3 hours</option><option>4+ hours</option>
                                    <option>Half day</option><option>Full day</option>
                                </select>
                                <x-lucide-chevron-down style="width: 14px; height: 14px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: var(--ink-3); pointer-events: none;" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- OUTCOME --}}
                <div style="padding: 20px 24px 18px; border-bottom: 1px solid var(--rule-2);">
                    <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.12em; color: var(--ink-3); margin-bottom: 14px;">OUTCOME</div>
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.06em;">Encounter Outcome <span style="color: var(--burgundy);">*</span></label>
                        <div id="calOutcomeToggle" style="display: flex; border: 1px solid var(--rule); overflow: hidden;">
                            @foreach(['Resolved' => 'var(--moss)', 'Ongoing' => 'var(--ochre)', 'Escalated' => 'var(--burgundy)', 'Pending' => 'var(--ink-3)'] as $ov => $oc)
                            <label style="display: flex; align-items: center; cursor: pointer; flex: 1; justify-content: center;">
                                <input type="radio" name="outcome" value="{{ $ov }}" {{ $ov === 'Ongoing' ? 'checked' : '' }}
                                    style="position: absolute; opacity: 0; width: 0;" onchange="calOutcome(this)">
                                <span class="cal-outcome-opt" data-color="{{ $oc }}"
                                    style="padding: 8px 6px; font-size: 12.5px; font-weight: 500; font-family: inherit; transition: all 150ms; text-align: center; width: 100%;
                                    {{ $ov === 'Ongoing' ? 'background: var(--ochre); color: #fff;' : 'background: var(--paper); color: var(--ink-2);' }}">
                                    {{ $ov }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Encounter Notes</label>
                        <textarea name="note" rows="3" placeholder="Brief narrative — actions taken, advice given, next steps agreed..."
                            style="width: 100%; padding: 10px 12px; font-size: 13px; font-family: inherit; border: 1px solid var(--rule); background: var(--paper); color: var(--ink); resize: vertical; box-sizing: border-box; outline: none; line-height: 1.5;"></textarea>
                    </div>
                </div>

                {{-- NEXT STEP --}}
                <div style="padding: 20px 24px 18px;">
                    <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.12em; color: var(--ink-3); margin-bottom: 4px;">NEXT STEP</div>
                    <p style="font-size: 12px; color: var(--ink-4); margin: 0 0 12px 0;">Optional follow-up if the encounter doesn't close the matter</p>
                    <div style="display: grid; grid-template-columns: 1fr 180px; gap: 14px; align-items: end;">
                        <div>
                            <label style="display: block; font-size: 10.5px; font-weight: 600; color: var(--ink-2); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em;">Next Step / Action</label>
                            <input type="text" name="next_step" placeholder="e.g. 'Schedule second mediation session'"
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

<script>
// Pre-select case and open modal from "Session" button
function jhCalPresetCase(caseId, caseLabel) {
    document.getElementById('calSvcCaseId').value = caseId;
    document.getElementById('calSvcInput').value = caseLabel;
    jhOpenModal('cal-log-service');
}

// Case picker
function calSvcOpen() {
    document.getElementById('calSvcDropdown').style.display = 'block';
    document.getElementById('calSvcChevron').style.transform = 'translateY(-50%) rotate(180deg)';
    document.querySelectorAll('.cal-case-opt').forEach(function(o) { o.style.display = ''; });
}
function calSvcFilter(val) {
    var q = val.toLowerCase().trim();
    document.getElementById('calSvcDropdown').style.display = 'block';
    document.querySelectorAll('.cal-case-opt').forEach(function(o) {
        o.style.display = (!q || o.getAttribute('data-search').includes(q)) ? '' : 'none';
    });
    document.getElementById('calSvcCaseId').value = '';
}
function calSvcSelect(el) {
    document.getElementById('calSvcCaseId').value = el.getAttribute('data-id');
    document.getElementById('calSvcInput').value = el.getAttribute('data-label');
    calSvcClose();
}
function calSvcClose() {
    document.getElementById('calSvcDropdown').style.display = 'none';
    document.getElementById('calSvcChevron').style.transform = 'translateY(-50%) rotate(0deg)';
}
document.addEventListener('click', function(e) {
    var p = document.getElementById('calSvcPicker');
    if (p && !p.contains(e.target)) calSvcClose();
});

// Outcome toggle
function calOutcome(input) {
    document.querySelectorAll('#calOutcomeToggle .cal-outcome-opt').forEach(function(s) {
        s.style.background = 'var(--paper)'; s.style.color = 'var(--ink-2)';
    });
    var sel = input.parentElement.querySelector('.cal-outcome-opt');
    sel.style.background = sel.getAttribute('data-color');
    sel.style.color = '#fff';
}
</script>

</x-layouts.app>
