<x-layouts.app>

{{-- ═══════════════════════════════════════════════════════════════
     DARK FOREST BANNER HEADER
     ═══════════════════════════════════════════════════════════════ --}}
<div style="background: var(--forest); color: var(--cream); padding: 18px 34px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
    <div style="display: flex; align-items: center; gap: 14px;">
        <div style="width: 38px; height: 38px; border-radius: 6px; background: rgba(255,255,255,.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <x-lucide-calendar style="width: 18px; height: 18px; color: rgba(255,255,255,.85);" />
        </div>
        <div>
            <div style="font-size: 17px; font-weight: 700; line-height: 1.2; letter-spacing: -0.01em;">Litigation Calendar &amp; Upcoming Hearings</div>
            <div style="font-size: 12px; color: rgba(255,255,255,.65); margin-top: 2px;">Court hearings, hearing dates and case allocations</div>
        </div>
    </div>
    <div style="font-size: 10px; font-weight: 700; letter-spacing: 0.1em; padding: 5px 12px; border: 1px solid rgba(255,255,255,.3); color: rgba(255,255,255,.75); white-space: nowrap; border-radius: 2px;">
        SERVICE &middot; O2.4
    </div>
</div>

<div style="padding: 24px 34px 64px; max-width: 1600px; margin: 0 auto;">

    {{-- ═══════════════════════════════════════════════════════════
         KPI CARDS
         ═══════════════════════════════════════════════════════════ --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px;">

        {{-- Total Cases --}}
        <div class="card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 44px; height: 44px; border-radius: 8px; background: var(--parchment); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <x-lucide-briefcase style="width: 20px; height: 20px; color: var(--ink-3);" />
            </div>
            <div>
                <div class="label-cap" style="font-size: 9px; color: var(--ink-3);">TOTAL CASES</div>
                <div class="serif" style="font-size: 38px; font-weight: 400; line-height: 1; margin-top: 3px; color: var(--ink);">{{ $totalCases }}</div>
                <div style="font-size: 11px; color: var(--ink-3); margin-top: 3px;">in litigation pathway</div>
            </div>
        </div>

        {{-- Today's Hearings --}}
        <div class="card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px; border-top: 3px solid var(--forest);">
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(74,122,92,.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <x-lucide-check-circle style="width: 20px; height: 20px; color: var(--forest);" />
            </div>
            <div>
                <div class="label-cap" style="font-size: 9px; color: var(--ink-3);">TODAY'S HEARINGS</div>
                <div class="serif" style="font-size: 38px; font-weight: 400; line-height: 1; margin-top: 3px; color: var(--forest);">{{ $todayHearings->count() }}</div>
                <div style="font-size: 11px; color: var(--ink-3); margin-top: 3px;">As of {{ today()->format('M d, Y') }}</div>
            </div>
        </div>

        {{-- Next 7 Days --}}
        <div class="card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px; border-top: 3px solid var(--ochre);">
            <div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(184,115,25,.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <x-lucide-calendar style="width: 20px; height: 20px; color: var(--ochre);" />
            </div>
            <div>
                <div class="label-cap" style="font-size: 9px; color: var(--ink-3);">NEXT 7 DAYS</div>
                <div class="serif" style="font-size: 38px; font-weight: 400; line-height: 1; margin-top: 3px; color: var(--ochre);">{{ $upcomingHearings->count() }}</div>
                <div style="font-size: 11px; color: var(--ink-3); margin-top: 3px;">Scheduled across hubs</div>
            </div>
        </div>

        {{-- Missing Next Hearing --}}
        <div class="card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px; border-top: 3px solid {{ $missingNextHearing > 0 ? 'var(--burgundy)' : 'var(--rule)' }};">
            <div style="width: 44px; height: 44px; border-radius: 8px; background: {{ $missingNextHearing > 0 ? 'rgba(138,46,29,.1)' : 'var(--parchment)' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <x-lucide-alert-triangle style="width: 20px; height: 20px; color: {{ $missingNextHearing > 0 ? 'var(--burgundy)' : 'var(--ink-4)' }};" />
            </div>
            <div>
                <div class="label-cap" style="font-size: 9px; color: var(--ink-3);">MISSING NEXT HEARING</div>
                <div class="serif" style="font-size: 38px; font-weight: 400; line-height: 1; margin-top: 3px; color: {{ $missingNextHearing > 0 ? 'var(--burgundy)' : 'var(--ink-3)' }};">{{ $missingNextHearing }}</div>
                <div style="font-size: 11px; color: var(--ink-3); margin-top: 3px;">Active cases without next date</div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         TWO-COLUMN: TODAY | UPCOMING 7 DAYS
         ═══════════════════════════════════════════════════════════ --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start;">

        {{-- ── TODAY'S HEARINGS ─────────────────────────────────── --}}
        <div>
            <div style="background: var(--forest); color: var(--cream); padding: 13px 18px; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 14px; font-weight: 700; letter-spacing: 0.01em;">Today's Hearings</span>
                <span class="mono" style="font-size: 13px; font-weight: 700; background: rgba(255,255,255,.2); padding: 2px 9px; border-radius: 12px;">{{ $todayHearings->count() }}</span>
            </div>

            <div class="card" style="padding: 0; min-height: 280px; border-top: none;">
                @forelse($todayHearings as $enc)
                @php
                    $c    = $enc->caseRecord;
                    $time = $enc->meta['time'] ?? null;
                    $court = $enc->meta['court'] ?? ($c->hub->name ?? $c->hub_id ?? '—');
                    $cRef  = $c->case_ref ?: $c->case_uid;
                @endphp
                <div style="padding: 16px 18px; border-bottom: 1px solid var(--rule); position: relative;">
                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 8px; align-items: start;">
                        {{-- Left content --}}
                        <div>
                            {{-- Case ref --}}
                            <div style="display: flex; align-items: center; gap: 7px; margin-bottom: 7px;">
                                <x-lucide-map-pin style="width: 12px; height: 12px; color: var(--burgundy); flex-shrink: 0;" />
                                <span class="mono" style="font-size: 12.5px; font-weight: 700; color: var(--ink); letter-spacing: 0.02em;">{{ $cRef }}</span>
                            </div>
                            {{-- Client --}}
                            <div style="font-size: 12.5px; color: var(--ink); margin-bottom: 3px;">
                                <span style="color: var(--ink-3); font-weight: 500;">Client:</span>
                                <span style="font-weight: 600; margin-left: 4px;">{{ Str::limit($c->name ?? '—', 24) }}</span>
                            </div>
                            {{-- Court --}}
                            <div style="font-size: 12.5px; color: var(--ink); margin-bottom: 3px;">
                                <span style="color: var(--ink-3); font-weight: 500;">Court:</span>
                                <span style="font-weight: 500; margin-left: 4px; color: var(--forest);">{{ $court }}</span>
                            </div>
                            {{-- Lawyer --}}
                            <div style="font-size: 12.5px; color: var(--ink);">
                                <span style="color: var(--ink-3); font-weight: 500;">Lawyer:</span>
                                <span style="margin-left: 4px; color: var(--ink-2);">{{ $enc->performed_by }}</span>
                            </div>
                        </div>

                        {{-- Right: date/time --}}
                        <div style="text-align: right; padding-top: 2px;">
                            <div style="font-size: 12px; font-weight: 600; color: var(--ink-2); white-space: nowrap;">
                                {{ $enc->date->format('M d') }}{{ $time ? ', '.$time : '' }}
                            </div>
                        </div>
                    </div>

                    {{-- Action buttons --}}
                    <div style="display: flex; gap: 8px; margin-top: 12px; padding-top: 10px; border-top: 1px solid var(--rule-2);">
                        @can('cases.write')
                        <button onclick="litCalPresetCase({{ $c->id }}, '{{ addslashes($cRef.' – '.($c->name ?? '')) }}')"
                                style="display: flex; align-items: center; gap: 5px; padding: 5px 12px; font-size: 11.5px; font-weight: 600; border: 1.5px solid var(--burgundy); color: var(--burgundy); background: transparent; cursor: pointer; border-radius: 3px; transition: all 120ms; letter-spacing: 0.02em;"
                                onmouseenter="this.style.background='var(--burgundy)';this.style.color='#fff'"
                                onmouseleave="this.style.background='transparent';this.style.color='var(--burgundy)'">
                            <x-lucide-gavel style="width: 11px; height: 11px;" />
                            Hearing
                        </button>
                        @endcan
                        @if($c)
                        <a href="{{ route('cases.show', $c) }}"
                           style="display: flex; align-items: center; gap: 5px; padding: 5px 12px; font-size: 11.5px; font-weight: 600; border: 1.5px solid var(--rule); color: var(--ink-3); background: transparent; text-decoration: none; border-radius: 3px; transition: all 120ms;"
                           onmouseenter="this.style.borderColor='var(--ink-3)';this.style.color='var(--ink)'"
                           onmouseleave="this.style.borderColor='var(--rule)';this.style.color='var(--ink-3)'">
                            <x-lucide-eye style="width: 11px; height: 11px;" />
                            View
                        </a>
                        @endif
                    </div>
                </div>
                @empty
                <div style="padding: 40px 20px; text-align: center; color: var(--ink-4);">
                    <x-lucide-calendar style="width: 32px; height: 32px; margin: 0 auto 10px; display: block; opacity: .35;" />
                    <div style="font-size: 13px;">No hearings scheduled for today</div>
                    <div style="font-size: 11px; margin-top: 4px; color: var(--ink-4);">{{ today()->format('l, F d, Y') }}</div>
                </div>
                @endforelse
            </div>
        </div>

        {{-- ── UPCOMING HEARINGS (NEXT 7 DAYS) ─────────────────── --}}
        <div>
            <div style="background: var(--forest); color: var(--cream); padding: 13px 18px; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 14px; font-weight: 700; letter-spacing: 0.01em;">Upcoming Hearings <span style="font-weight: 400; opacity: .75;">(Next 7 Days)</span></span>
                <span class="mono" style="font-size: 13px; font-weight: 700; background: rgba(255,255,255,.2); padding: 2px 9px; border-radius: 12px;">{{ $upcomingHearings->count() }}</span>
            </div>

            <div class="card" style="padding: 0; border-top: none;">
                @forelse($upcomingHearings as $enc)
                @php
                    $c     = $enc->caseRecord;
                    $time  = $enc->meta['time'] ?? null;
                    $court = $enc->meta['court'] ?? ($c->hub->name ?? $c->hub_id ?? '—');
                    $cRef  = $c->case_ref ?: $c->case_uid;
                    $daysUntil = (int) today()->diffInDays($enc->date);
                @endphp
                <div style="padding: 16px 18px; border-bottom: 1px solid var(--rule); position: relative;">
                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 8px; align-items: start;">
                        {{-- Left content --}}
                        <div>
                            {{-- Case ref + days badge --}}
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 7px;">
                                <x-lucide-map-pin style="width: 12px; height: 12px; color: var(--burgundy); flex-shrink: 0;" />
                                <span class="mono" style="font-size: 12.5px; font-weight: 700; color: var(--ink); letter-spacing: 0.02em;">{{ $cRef }}</span>
                                <span style="font-size: 10px; padding: 2px 6px; background: rgba(74,122,92,.1); color: var(--forest); font-weight: 700; border-radius: 10px; white-space: nowrap;">in {{ $daysUntil }}d</span>
                            </div>
                            {{-- Client --}}
                            <div style="font-size: 12.5px; color: var(--ink); margin-bottom: 3px;">
                                <span style="color: var(--ink-3); font-weight: 500;">Client:</span>
                                <span style="font-weight: 600; margin-left: 4px;">{{ Str::limit($c->name ?? '—', 24) }}</span>
                            </div>
                            {{-- Court --}}
                            <div style="font-size: 12.5px; color: var(--ink); margin-bottom: 3px;">
                                <span style="color: var(--ink-3); font-weight: 500;">Court:</span>
                                <span style="font-weight: 500; margin-left: 4px; color: var(--forest);">{{ $court }}</span>
                            </div>
                            {{-- Lawyer --}}
                            <div style="font-size: 12.5px; color: var(--ink);">
                                <span style="color: var(--ink-3); font-weight: 500;">Lawyer:</span>
                                <span style="margin-left: 4px; color: var(--ink-2);">{{ $enc->performed_by }}</span>
                            </div>
                        </div>

                        {{-- Right: date/time + View link --}}
                        <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                            <div style="font-size: 12px; font-weight: 600; color: var(--ink-2); white-space: nowrap;">
                                {{ $enc->date->format('M d') }}{{ $time ? ', '.$time : '' }}
                            </div>
                            @if($c)
                            <a href="{{ route('cases.show', $c) }}"
                               style="display: flex; align-items: center; gap: 5px; padding: 5px 12px; font-size: 11.5px; font-weight: 600; border: 1.5px solid var(--rule); color: var(--ink-3); background: transparent; text-decoration: none; border-radius: 3px; transition: all 120ms; white-space: nowrap;"
                               onmouseenter="this.style.borderColor='var(--ink-3)';this.style.color='var(--ink)'"
                               onmouseleave="this.style.borderColor='var(--rule)';this.style.color='var(--ink-3)'">
                                <x-lucide-eye style="width: 11px; height: 11px;" />
                                View
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div style="padding: 40px 20px; text-align: center; color: var(--ink-4);">
                    <x-lucide-calendar style="width: 32px; height: 32px; margin: 0 auto 10px; display: block; opacity: .35;" />
                    <div style="font-size: 13px;">No upcoming hearings in the next 7 days</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         MISSING HEARINGS ALERT
         ═══════════════════════════════════════════════════════════ --}}
    @if($missingNextHearing > 0)
    <div style="margin-top: 20px; padding: 14px 18px; background: rgba(138,46,29,.06); border: 1px solid rgba(138,46,29,.2); border-left: 4px solid var(--burgundy); display: flex; align-items: center; gap: 12px;">
        <x-lucide-alert-circle style="width: 18px; height: 18px; color: var(--burgundy); flex-shrink: 0;" />
        <div>
            <span style="font-size: 13px; font-weight: 600; color: var(--burgundy);">{{ $missingNextHearing }} active {{ Str::plural('case', $missingNextHearing) }} without a scheduled hearing.</span>
            <span style="font-size: 12.5px; color: var(--ink-3); margin-left: 6px;">Schedule the next court date to keep cases on track.</span>
        </div>
    </div>
    @endif

</div>

{{-- ═══════════════════════════════════════════════════════════════
     LOG HEARING MODAL
     ═══════════════════════════════════════════════════════════════ --}}
@can('cases.write')
<div id="litCalLogModal" style="display:none; position:fixed; inset:0; z-index:1100; background:rgba(0,0,0,.45);" onclick="if(event.target===this)litCalCloseModal()">
    <div style="position:absolute; top:0; right:0; bottom:0; width:min(520px,100vw); background:var(--paper); display:flex; flex-direction:column; box-shadow:-4px 0 24px rgba(0,0,0,.15); transform:translateX(100%); transition:transform .25s ease;" id="litCalPanel">

        {{-- Panel header --}}
        <div style="padding:18px 22px 16px; border-bottom:2px solid var(--forest); background:var(--forest); color:var(--cream); display:flex; align-items:center; justify-content:space-between;">
            <div>
                <div style="font-size:15px; font-weight:700;">Log Court Hearing</div>
                <div id="litCalCaseLabel" style="font-size:11.5px; opacity:.75; margin-top:2px;">Select a case below</div>
            </div>
            <button onclick="litCalCloseModal()" style="background:none; border:none; color:var(--cream); cursor:pointer; padding:4px; opacity:.8;" onmouseenter="this.style.opacity='1'" onmouseleave="this.style.opacity='.8'">
                <x-lucide-x style="width:20px; height:20px;" />
            </button>
        </div>

        {{-- Form --}}
        <div style="flex:1; overflow-y:auto; padding:20px 22px;">
            <form method="POST" action="{{ route('encounters.log') }}" id="litCalForm">
                @csrf
                <input type="hidden" name="case_id" id="litCalCaseId">

                {{-- Case picker (shown when no case pre-set) --}}
                <div id="litCalCasePicker" style="margin-bottom:16px;">
                    <label style="display:block; font-size:11px; font-weight:700; letter-spacing:.05em; color:var(--ink-3); text-transform:uppercase; margin-bottom:6px;">Case</label>
                    <div style="position:relative;">
                        <input type="text" id="litCalCaseSearch" placeholder="Search case by name or UID…"
                               style="width:100%; padding:9px 12px; border:1.5px solid var(--rule); background:var(--paper); color:var(--ink); font-size:13px; outline:none; box-sizing:border-box; border-radius:3px;"
                               oninput="litCalFilterCases(this.value)" onfocus="document.getElementById('litCalDropdown').style.display='block'"
                               onblur="setTimeout(()=>document.getElementById('litCalDropdown').style.display='none',200)">
                        <div id="litCalDropdown" style="display:none; position:absolute; top:100%; left:0; right:0; background:var(--paper); border:1.5px solid var(--rule); border-top:none; max-height:200px; overflow-y:auto; z-index:10; box-shadow:0 4px 16px rgba(0,0,0,.1);">
                            @foreach($activeCases as $ac)
                            <div class="litcal-case-opt" data-id="{{ $ac->id }}" data-label="{{ $ac->case_uid }} – {{ $ac->name }}"
                                 data-search="{{ strtolower($ac->name.' '.$ac->case_uid) }}"
                                 style="padding:9px 12px; cursor:pointer; font-size:13px; border-bottom:1px solid var(--rule-2);"
                                 onmousedown="litCalSelectCase({{ $ac->id }}, '{{ addslashes($ac->case_uid.' – '.$ac->name) }}')"
                                 onmouseenter="this.style.background='var(--parchment)'" onmouseleave="this.style.background=''">
                                <span class="mono" style="font-size:10.5px; color:var(--ink-3);">{{ $ac->case_uid }}</span>
                                <span style="margin-left:8px; color:var(--ink);">{{ $ac->name }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Two-col: type + date --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:11px; font-weight:700; letter-spacing:.05em; color:var(--ink-3); text-transform:uppercase; margin-bottom:6px;">Hearing type</label>
                        <select name="type" required style="width:100%; padding:9px 10px; border:1.5px solid var(--rule); background:var(--paper); color:var(--ink); font-size:13px; border-radius:3px; outline:none;">
                            <option value="Court">Court</option>
                            <option value="Court Representation">Court Representation</option>
                            <option value="Litigation">Litigation</option>
                            <option value="Bail Hearing">Bail Hearing</option>
                            <option value="Judgment">Judgment</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:700; letter-spacing:.05em; color:var(--ink-3); text-transform:uppercase; margin-bottom:6px;">Date</label>
                        <input type="date" name="date" required value="{{ today()->format('Y-m-d') }}"
                               style="width:100%; padding:9px 10px; border:1.5px solid var(--rule); background:var(--paper); color:var(--ink); font-size:13px; border-radius:3px; outline:none; box-sizing:border-box;">
                    </div>
                </div>

                {{-- Two-col: time + lawyer --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:11px; font-weight:700; letter-spacing:.05em; color:var(--ink-3); text-transform:uppercase; margin-bottom:6px;">Time</label>
                        <input type="time" name="time"
                               style="width:100%; padding:9px 10px; border:1.5px solid var(--rule); background:var(--paper); color:var(--ink); font-size:13px; border-radius:3px; outline:none; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:700; letter-spacing:.05em; color:var(--ink-3); text-transform:uppercase; margin-bottom:6px;">Lawyer / Paralegal</label>
                        <select name="performed_by" required style="width:100%; padding:9px 10px; border:1.5px solid var(--rule); background:var(--paper); color:var(--ink); font-size:13px; border-radius:3px; outline:none;">
                            <option value="">Select…</option>
                            @foreach($staff as $s)
                            <option value="{{ $s['name'] }}">{{ $s['name'] }} ({{ $s['role'] }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Court venue --}}
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:11px; font-weight:700; letter-spacing:.05em; color:var(--ink-3); text-transform:uppercase; margin-bottom:6px;">Court / Venue</label>
                    <input type="text" name="court" placeholder="e.g. Family Court Sukkur, Sessions Court…"
                           style="width:100%; padding:9px 10px; border:1.5px solid var(--rule); background:var(--paper); color:var(--ink); font-size:13px; border-radius:3px; outline:none; box-sizing:border-box;">
                </div>

                {{-- Outcome toggles --}}
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:11px; font-weight:700; letter-spacing:.05em; color:var(--ink-3); text-transform:uppercase; margin-bottom:8px;">Hearing Outcome</label>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;" id="litCalOutcomeGroup">
                        @foreach(['Ongoing','Adjourned','Judgment Reserved','Won','Lost','Partial'] as $out)
                        <button type="button" class="litcal-outcome-btn"
                                onclick="litCalSetOutcome('{{ $out }}', this)"
                                style="padding:5px 12px; font-size:11.5px; font-weight:600; border:1.5px solid var(--rule); background:var(--paper); color:var(--ink-3); cursor:pointer; border-radius:14px; transition:all 120ms;">
                            {{ $out }}
                        </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="outcome" id="litCalOutcomeVal">
                </div>

                {{-- Notes --}}
                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:11px; font-weight:700; letter-spacing:.05em; color:var(--ink-3); text-transform:uppercase; margin-bottom:6px;">Hearing Notes</label>
                    <textarea name="note" rows="3" placeholder="What happened at the hearing?"
                              style="width:100%; padding:9px 10px; border:1.5px solid var(--rule); background:var(--paper); color:var(--ink); font-size:13px; resize:vertical; border-radius:3px; outline:none; box-sizing:border-box; font-family:inherit;"></textarea>
                </div>

                {{-- Next hearing date --}}
                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:11px; font-weight:700; letter-spacing:.05em; color:var(--ink-3); text-transform:uppercase; margin-bottom:6px;">Next Hearing Date <span style="font-weight:400; text-transform:none;">(optional)</span></label>
                    <input type="date" name="next_step_date"
                           style="width:100%; padding:9px 10px; border:1.5px solid var(--rule); background:var(--paper); color:var(--ink); font-size:13px; border-radius:3px; outline:none; box-sizing:border-box;">
                </div>

                <button type="submit" style="width:100%; padding:11px; background:var(--forest); color:var(--cream); border:none; font-size:13.5px; font-weight:700; cursor:pointer; letter-spacing:.03em; border-radius:3px; transition:background 120ms;" onmouseenter="this.style.background='var(--ink)'" onmouseleave="this.style.background='var(--forest)'">
                    Log Court Hearing
                </button>
            </form>
        </div>
    </div>
</div>
@endcan

<script>
// ── Log modal ──────────────────────────────────────────────────
function litCalOpenModal() {
    document.getElementById('litCalLogModal').style.display = 'block';
    setTimeout(() => document.getElementById('litCalPanel').style.transform = 'translateX(0)', 10);
}
function litCalCloseModal() {
    document.getElementById('litCalPanel').style.transform = 'translateX(100%)';
    setTimeout(() => document.getElementById('litCalLogModal').style.display = 'none', 260);
}

// Pre-set case from "Hearing" button
function litCalPresetCase(caseId, label) {
    document.getElementById('litCalCaseId').value = caseId;
    document.getElementById('litCalCaseLabel').textContent = label;
    const picker = document.getElementById('litCalCasePicker');
    if (picker) picker.style.display = 'none';
    litCalOpenModal();
}

// Case picker search
function litCalFilterCases(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.litcal-case-opt').forEach(opt => {
        opt.style.display = opt.dataset.search.includes(q) ? '' : 'none';
    });
}

function litCalSelectCase(id, label) {
    document.getElementById('litCalCaseId').value = id;
    document.getElementById('litCalCaseLabel').textContent = label;
    document.getElementById('litCalCaseSearch').value = label;
    document.getElementById('litCalDropdown').style.display = 'none';
}

// Outcome toggle
function litCalSetOutcome(val, btn) {
    document.querySelectorAll('.litcal-outcome-btn').forEach(b => {
        b.style.background = 'var(--paper)';
        b.style.color = 'var(--ink-3)';
        b.style.borderColor = 'var(--rule)';
    });
    btn.style.background = 'var(--burgundy)';
    btn.style.color = '#fff';
    btn.style.borderColor = 'var(--burgundy)';
    document.getElementById('litCalOutcomeVal').value = val;
}
</script>

</x-layouts.app>
