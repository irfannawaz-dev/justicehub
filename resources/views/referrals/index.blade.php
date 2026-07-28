<x-layouts.app>

@php
$sections = [
    [
        'label'     => 'Government Department / Public Institution',
        'icon'      => 'building-2',
        'color'     => 'var(--forest)',
        'breakdown' => $govtBreakdown,
        'cases'     => $govtCases,
        'key'       => 'govt',
    ],
    [
        'label'     => 'Civil Society / NGO / CSO / NPO',
        'icon'      => 'users',
        'color'     => 'var(--ochre)',
        'breakdown' => $ngoBreakdown,
        'cases'     => $ngoCases,
        'key'       => 'ngo',
    ],
];
@endphp

<div style="padding: 28px 36px 60px; max-width: 1640px; margin: 0 auto;">

    {{-- ═══ Header ═══ --}}
    <div style="margin-bottom: 32px; padding-bottom: 22px; border-bottom: 1px solid var(--rule);">
        <div class="label-cap" style="margin-bottom:8px;">Service Delivery · External Partnerships</div>
        <h1 class="serif" style="font-size:42px; font-weight:400; letter-spacing:-0.018em; line-height:1.02; margin:0;">
            Referral <em style="color:var(--forest);">Network</em>
        </h1>
        <div style="font-size:13.5px; color:var(--ink-2); margin-top:14px; line-height:1.6; max-width:680px;">
            Track every referral — incoming cases sent to us by partners, and outgoing cases we route to external services.
        </div>
    </div>

    {{-- ═══ KPI Summary ═══ --}}
    @php
        $total    = $referralKpi->total    ?? 0;
        $resolved = $referralKpi->resolved ?? 0;
        $active   = $referralKpi->active   ?? 0;
        $incoming = $referralKpi->incoming ?? 0;
        $outgoing = $referralKpi->outgoing ?? 0;
        $resolvedPct = $total > 0 ? round(($resolved / $total) * 100) : 0;
    @endphp
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:12px; margin-bottom:36px;">

        {{-- Total --}}
        <div style="background:var(--paper); border:1px solid var(--rule); border-radius:10px; padding:20px 22px; position:relative; overflow:hidden;">
            <div style="position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--forest);border-radius:10px 0 0 10px;"></div>
            <div style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:8px;">Total Cases</div>
            <div style="font-size:36px;font-weight:700;color:var(--ink-1);line-height:1;margin-bottom:6px;">{{ $total }}</div>
            <div style="font-size:11px;color:var(--ink-3);">Govt · NGO/CSO · Other pathways</div>
        </div>

        {{-- Resolved --}}
        <div style="background:var(--paper); border:1px solid var(--rule); border-radius:10px; padding:20px 22px; position:relative; overflow:hidden;">
            <div style="position:absolute;left:0;top:0;bottom:0;width:3px;background:#2f7a4d;border-radius:10px 0 0 10px;"></div>
            <div style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:8px;">Resolved</div>
            <div style="font-size:36px;font-weight:700;color:#2f7a4d;line-height:1;margin-bottom:6px;">{{ $resolved }}</div>
            <div style="height:4px;background:var(--rule-2);border-radius:2px;margin-bottom:6px;">
                <div style="height:4px;width:{{ $resolvedPct }}%;background:#2f7a4d;border-radius:2px;transition:width .4s;"></div>
            </div>
            <div style="font-size:11px;color:var(--ink-3);">{{ $resolvedPct }}% resolution rate</div>
        </div>

        {{-- Active --}}
        <div style="background:var(--paper); border:1px solid var(--rule); border-radius:10px; padding:20px 22px; position:relative; overflow:hidden;">
            <div style="position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--ochre);border-radius:10px 0 0 10px;"></div>
            <div style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:8px;">Active</div>
            <div style="font-size:36px;font-weight:700;color:var(--ochre);line-height:1;margin-bottom:6px;">{{ $active }}</div>
            <div style="font-size:11px;color:var(--ink-3);">Pending resolution</div>
        </div>

        {{-- Incoming --}}
        <div style="background:var(--paper); border:1px solid var(--rule); border-radius:10px; padding:20px 22px; position:relative; overflow:hidden;">
            <div style="position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--forest);border-radius:10px 0 0 10px;"></div>
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
                <x-lucide-arrow-down-left style="width:11px;height:11px;color:var(--forest);" />
                <span style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--ink-3);">Incoming</span>
            </div>
            <div style="font-size:36px;font-weight:700;color:var(--ink-1);line-height:1;margin-bottom:6px;">{{ $incoming }}</div>
            <div style="font-size:11px;color:var(--ink-3);">Referred to us by partners</div>
        </div>

        {{-- Outgoing --}}
        <div style="background:var(--paper); border:1px solid var(--rule); border-radius:10px; padding:20px 22px; position:relative; overflow:hidden;">
            <div style="position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--ochre);border-radius:10px 0 0 10px;"></div>
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
                <x-lucide-arrow-up-right style="width:11px;height:11px;color:var(--ochre);" />
                <span style="font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--ink-3);">Outgoing</span>
            </div>
            <div style="font-size:36px;font-weight:700;color:var(--ink-1);line-height:1;margin-bottom:6px;">{{ $outgoing }}</div>
            <div style="font-size:11px;color:var(--ink-3);">We route to external services</div>
        </div>

    </div>

    {{-- ═══ Govt & NGO Sections ═══ --}}
    @foreach($sections as $section)
    @if($section['breakdown']->isNotEmpty())
    <div style="margin-bottom: 40px;">

        {{-- Section heading --}}
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
            <div style="width:32px; height:32px; background:{{ $section['color'] }}; border-radius:6px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <x-dynamic-component :component="'lucide-' . $section['icon']" style="width:15px;height:15px;color:#fff;" />
            </div>
            <div>
                <div style="font-size:14px; font-weight:600; color:var(--ink-1);">{{ $section['label'] }}</div>
                <div style="font-size:11px; color:var(--ink-3);">
                    {{ $section['breakdown']->sum('total') }} cases · {{ $section['breakdown']->count() }} {{ $section['breakdown']->count() === 1 ? 'organisation' : 'organisations' }}
                </div>
            </div>
        </div>

        {{-- Cards grid --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:10px;">
            @foreach($section['breakdown'] as $item)
            @php
                $cardId   = 'cases-' . $section['key'] . '-' . Str::slug($item->name);
                $caselist = $section['cases'][$item->name] ?? collect();
                $pct      = $section['breakdown']->sum('total') > 0
                            ? round(($item->total / $section['breakdown']->sum('total')) * 100) : 0;
            @endphp

            <div>
                {{-- Card --}}
                <div onclick="jhToggleReferralPanel('{{ $cardId }}')"
                     id="card-{{ $cardId }}"
                     style="background:var(--paper); border:1px solid var(--rule); border-radius:8px; padding:16px 18px;
                            position:relative; overflow:hidden; cursor:pointer; transition:box-shadow .15s;"
                     onmouseenter="this.style.boxShadow='0 2px 10px rgba(0,0,0,0.07)'"
                     onmouseleave="this.style.boxShadow='none'">
                    <div style="position:absolute; left:0; top:0; bottom:0; width:3px; background:{{ $section['color'] }}; border-radius:8px 0 0 8px;"></div>

                    <div style="font-size:12px; font-weight:600; color:var(--ink-2); margin-bottom:10px; line-height:1.35;">
                        {{ $item->name }}
                    </div>
                    <div style="font-size:28px; font-weight:700; color:var(--ink-1); line-height:1; margin-bottom:8px;">
                        {{ $item->total }}
                    </div>
                    <div style="height:3px; background:var(--rule-2); border-radius:2px; margin-bottom:10px;">
                        <div style="height:3px; width:{{ $pct }}%; background:{{ $section['color'] }}; border-radius:2px;"></div>
                    </div>
                    <div style="display:flex; gap:10px; align-items:center; justify-content:space-between;">
                        <div style="display:flex; gap:10px;">
                            @if($item->incoming > 0)
                            <div style="display:flex; align-items:center; gap:3px;">
                                <x-lucide-arrow-down-left style="width:10px;height:10px;color:var(--forest);" />
                                <span style="font-size:11px; color:var(--ink-3);">{{ $item->incoming }} in</span>
                            </div>
                            @endif
                            @if($item->outgoing > 0)
                            <div style="display:flex; align-items:center; gap:3px;">
                                <x-lucide-arrow-up-right style="width:10px;height:10px;color:var(--ochre);" />
                                <span style="font-size:11px; color:var(--ink-3);">{{ $item->outgoing }} out</span>
                            </div>
                            @endif
                        </div>
                        <x-lucide-chevron-down id="chev-{{ $cardId }}"
                            style="width:12px;height:12px;color:var(--ink-4);transition:transform .2s;flex-shrink:0;" />
                    </div>
                </div>

                {{-- Expandable case list --}}
                <div id="{{ $cardId }}" style="display:none; margin-top:6px; border:1px solid var(--rule); border-radius:8px; overflow:hidden; background:var(--paper);">
                    @if($caselist->isEmpty())
                    <div style="padding:16px 18px; font-size:12px; color:var(--ink-4);">No cases found.</div>
                    @else
                    @foreach($caselist as $case)
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 16px; border-bottom:1px solid var(--rule-2); gap:12px;"
                         onmouseenter="this.style.background='var(--parchment)'"
                         onmouseleave="this.style.background='transparent'">
                        <div style="min-width:0; flex:1;">
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:3px; flex-wrap:wrap;">
                                <span style="font-size:11px; font-weight:600; color:var(--forest); font-family:monospace;">{{ $case->case_uid }}</span>
                                @if($case->referral_type)
                                <span style="font-size:10px; padding:1px 7px; border-radius:20px; font-weight:600;
                                    background:{{ $case->referral_type === 'Incoming' ? 'rgba(22,48,41,0.1)' : 'rgba(196,148,56,0.12)' }};
                                    color:{{ $case->referral_type === 'Incoming' ? 'var(--forest)' : 'var(--ochre)' }};">
                                    {{ $case->referral_type }}
                                </span>
                                @endif
                                @php $u = is_object($case->urgency) ? $case->urgency->value : $case->urgency; @endphp
                                @if($u === 'High' || $u === 'Immediate')
                                <span style="font-size:10px; padding:1px 7px; border-radius:20px; font-weight:600; background:rgba(139,0,0,0.09); color:var(--burgundy);">{{ $u }}</span>
                                @endif
                            </div>
                            <div style="font-size:12.5px; font-weight:500; color:var(--ink-1); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $case->name }}</div>
                            @if($case->referral_contact_person)
                            <div style="font-size:11px; color:var(--ink-3); margin-top:2px;">
                                <x-lucide-user style="width:9px;height:9px;display:inline;vertical-align:middle;" /> {{ $case->referral_contact_person }}
                            </div>
                            @endif
                            <div style="font-size:11px; color:var(--ink-4); margin-top:1px;">{{ $case->primary_issue }}</div>
                        </div>
                        <a href="{{ route('cases.show', $case->id) }}"
                           style="flex-shrink:0; padding:5px 12px; background:var(--forest); color:#fff; font-size:11px; font-weight:600;
                                  border-radius:5px; text-decoration:none; white-space:nowrap; transition:opacity .15s;"
                           onmouseenter="this.style.opacity='.85'" onmouseleave="this.style.opacity='1'">
                            Open Case
                        </a>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endforeach

    {{-- ═══ Other ═══ --}}
    @if($otherBreakdown && $otherBreakdown->total > 0)
    <div style="margin-bottom: 36px;">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
            <div style="width:32px; height:32px; background:var(--ink-3); border-radius:6px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <x-lucide-circle-dot style="width:15px;height:15px;color:#fff;" />
            </div>
            <div>
                <div style="font-size:14px; font-weight:600; color:var(--ink-1);">Other</div>
                <div style="font-size:11px; color:var(--ink-3);">Community, bar associations, schools, village councils, etc.</div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:10px; max-width:640px; margin-bottom:16px;">
            @foreach([['Total','circle-dot','var(--ink-3)',$otherBreakdown->total],['Incoming','arrow-down-left','var(--forest)',$otherBreakdown->incoming],['Outgoing','arrow-up-right','var(--ochre)',$otherBreakdown->outgoing]] as [$lbl,$ico,$clr,$val])
            <div style="background:var(--paper); border:1px solid var(--rule); border-radius:8px; padding:16px 18px; position:relative; overflow:hidden;">
                <div style="position:absolute; left:0; top:0; bottom:0; width:3px; background:{{ $clr }}; border-radius:8px 0 0 8px;"></div>
                <div style="display:flex; align-items:center; gap:5px; margin-bottom:6px;">
                    <x-dynamic-component :component="'lucide-' . $ico" style="width:10px;height:10px;color:{{ $clr }};" />
                    <span style="font-size:11px; font-weight:600; color:var(--ink-3); text-transform:uppercase; letter-spacing:0.05em;">{{ $lbl }}</span>
                </div>
                <div style="font-size:28px; font-weight:700; color:var(--ink-1); line-height:1;">{{ $val }}</div>
            </div>
            @endforeach
        </div>

        {{-- Other cases list --}}
        <div style="border:1px solid var(--rule); border-radius:8px; overflow:hidden; background:var(--paper);">
            @foreach($otherCases as $case)
            <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 16px; border-bottom:1px solid var(--rule-2); gap:12px;"
                 onmouseenter="this.style.background='var(--parchment)'"
                 onmouseleave="this.style.background='transparent'">
                <div style="min-width:0; flex:1;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:3px; flex-wrap:wrap;">
                        <span style="font-size:11px; font-weight:600; color:var(--forest); font-family:monospace;">{{ $case->case_uid }}</span>
                        @if($case->referral_type)
                        <span style="font-size:10px; padding:1px 7px; border-radius:20px; font-weight:600;
                            background:{{ $case->referral_type === 'Incoming' ? 'rgba(22,48,41,0.1)' : 'rgba(196,148,56,0.12)' }};
                            color:{{ $case->referral_type === 'Incoming' ? 'var(--forest)' : 'var(--ochre)' }};">
                            {{ $case->referral_type }}
                        </span>
                        @endif
                        @php $u = is_object($case->urgency) ? $case->urgency->value : $case->urgency; @endphp
                        @if($u === 'High' || $u === 'Immediate')
                        <span style="font-size:10px; padding:1px 7px; border-radius:20px; font-weight:600; background:rgba(139,0,0,0.09); color:var(--burgundy);">{{ $u }}</span>
                        @endif
                    </div>
                    <div style="font-size:12.5px; font-weight:500; color:var(--ink-1);">{{ $case->name }}</div>
                    @if($case->referral_contact_person)
                    <div style="font-size:11px; color:var(--ink-3); margin-top:2px;">
                        <x-lucide-user style="width:9px;height:9px;display:inline;vertical-align:middle;" /> {{ $case->referral_contact_person }}
                    </div>
                    @endif
                    @if($case->pathway_other_details)
                    <div style="font-size:11px; color:var(--ink-4); margin-top:1px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:400px;">{{ $case->pathway_other_details }}</div>
                    @endif
                </div>
                <a href="{{ route('cases.show', $case->id) }}"
                   style="flex-shrink:0; padding:5px 12px; background:var(--forest); color:#fff; font-size:11px; font-weight:600;
                          border-radius:5px; text-decoration:none; white-space:nowrap;"
                   onmouseenter="this.style.opacity='.85'" onmouseleave="this.style.opacity='1'">
                    Open Case
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ═══ Referral Records Table ═══ --}}
    @php
        $allRefRecords = $referralTracker;
        $totalRef      = $allRefRecords->count();
        $resolvedRef   = $allRefRecords->whereIn('stage', ['Completed', 'Failed'])->count();
        $pendingRef    = $allRefRecords->whereIn('stage', ['Sent', 'Acknowledged', 'In progress'])->count();
        $completedRef  = $allRefRecords->where('stage', 'Completed')->count();
        $failedRef     = $allRefRecords->where('stage', 'Failed')->count();
        $resolvedPctRef = $totalRef > 0 ? round(($resolvedRef / $totalRef) * 100, 1) : 0;
    @endphp
    <div style="margin-top:40px; border-top:2px solid var(--rule); padding-top:32px;">

        {{-- Section header --}}
        <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px;">
            <div>
                <div class="label-cap" style="font-size:9.5px; margin-bottom:6px;">Referral Tracking</div>
                <h2 class="serif" style="font-size:26px; font-weight:400; margin:0;">
                    Referral <em style="color:var(--forest); font-style:italic;">Records</em>
                </h2>
                <div style="font-size:12px; color:var(--ink-3); margin-top:6px;">
                    All outgoing referrals logged across cases — track status, filing, and outcomes.
                </div>
            </div>
        </div>

        {{-- KPI cards --}}
        <div style="display:grid; grid-template-columns:repeat(6,1fr); gap:10px; margin-bottom:20px;">
            <div class="card" style="padding:14px 16px; border-top:3px solid var(--forest);">
                <div class="label-cap" style="font-size:8px; margin-bottom:4px;">Total</div>
                <div class="serif" style="font-size:26px; font-weight:400; line-height:1;">{{ $totalRef }}</div>
                <div style="font-size:10px; color:var(--ink-4); margin-top:3px;">Referrals</div>
            </div>
            <div class="card" style="padding:14px 16px; border-top:3px solid #2f7a4d;">
                <div class="label-cap" style="font-size:8px; margin-bottom:4px;">Completed</div>
                <div class="serif" style="font-size:26px; font-weight:400; line-height:1; color:#2f7a4d;">{{ $completedRef }}</div>
                <div style="font-size:10px; color:var(--ink-4); margin-top:3px;">{{ $resolvedPctRef }}% resolved</div>
            </div>
            <div class="card" style="padding:14px 16px; border-top:3px solid var(--ochre);">
                <div class="label-cap" style="font-size:8px; margin-bottom:4px;">Pending</div>
                <div class="serif" style="font-size:26px; font-weight:400; line-height:1; color:var(--ochre);">{{ $pendingRef }}</div>
                <div style="font-size:10px; color:var(--ink-4); margin-top:3px;">In progress</div>
            </div>
            <div class="card" style="padding:14px 16px; border-top:3px solid var(--burgundy);">
                <div class="label-cap" style="font-size:8px; margin-bottom:4px;">Failed</div>
                <div class="serif" style="font-size:26px; font-weight:400; line-height:1; color:var(--burgundy);">{{ $failedRef }}</div>
                <div style="font-size:10px; color:var(--ink-4); margin-top:3px;">Unsuccessful</div>
            </div>
            <div class="card" style="padding:14px 16px; border-top:3px solid var(--ink-3);">
                <div class="label-cap" style="font-size:8px; margin-bottom:4px;">Incoming</div>
                <div class="serif" style="font-size:26px; font-weight:400; line-height:1;">{{ $incomingCount }}</div>
                <div style="font-size:10px; color:var(--ink-4); margin-top:3px;">From partners</div>
            </div>
            <div class="card" style="padding:14px 16px; border-top:3px solid var(--ochre);">
                <div class="label-cap" style="font-size:8px; margin-bottom:4px;">Outgoing</div>
                <div class="serif" style="font-size:26px; font-weight:400; line-height:1;">{{ $outgoingCount }}</div>
                <div style="font-size:10px; color:var(--ink-4); margin-top:3px;">To external services</div>
            </div>
        </div>

        {{-- Filter row --}}
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr 2fr auto auto; gap:10px; margin-bottom:14px; align-items:center;">
            <select id="refFilterStage" onchange="jhFilterRefTable()" class="inp" style="font-size:11px; padding:6px 10px;">
                <option value="">All Statuses</option>
                <option value="Sent">Sent</option>
                <option value="Acknowledged">Acknowledged</option>
                <option value="In progress">In Progress</option>
                <option value="Completed">Completed</option>
                <option value="Failed">Failed</option>
            </select>
            <select id="refFilterHub" onchange="jhFilterRefTable()" class="inp" style="font-size:11px; padding:6px 10px;">
                <option value="">All Hubs</option>
                @foreach($allRefRecords->pluck('hub_id')->unique()->sort() as $hub)
                <option value="{{ $hub }}">{{ $hub }}</option>
                @endforeach
            </select>
            <input type="date" id="refFilterFrom" onchange="jhFilterRefTable()" class="inp" style="font-size:11px; padding:6px 10px;" title="From date">
            <input type="date" id="refFilterTo" onchange="jhFilterRefTable()" class="inp" style="font-size:11px; padding:6px 10px;" title="To date">
            <input type="text" id="refFilterSearch" onkeyup="jhFilterRefTable()" placeholder="Search client or partner..."
                   class="inp" style="font-size:11px; padding:6px 10px;">
            <button onclick="jhExportRefTable()" class="btn-ghost" style="font-size:11px; padding:6px 12px; display:inline-flex; align-items:center; gap:5px; white-space:nowrap;">
                <x-lucide-download style="width:12px; height:12px;" /> Export CSV
            </button>
            <div style="font-size:11px; color:var(--ink-4); white-space:nowrap;">
                Showing <span id="refFilterCount">{{ $totalRef }}</span> of {{ $totalRef }}
            </div>
        </div>

        {{-- Data table --}}
        <div class="card" style="padding:0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse; font-size:12px;" id="refRecordsTable">
                <thead>
                    <tr style="border-bottom:2px solid var(--rule); background:var(--parchment);">
                        <th style="padding:10px 14px; text-align:left; font-size:9px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">Ref ID</th>
                        <th style="padding:10px 14px; text-align:left; font-size:9px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">Date</th>
                        <th style="padding:10px 14px; text-align:left; font-size:9px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">Client</th>
                        <th style="padding:10px 14px; text-align:left; font-size:9px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">Referred To</th>
                        <th style="padding:10px 14px; text-align:center; font-size:9px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">Stage</th>
                        <th style="padding:10px 14px; text-align:center; font-size:9px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">Days</th>
                        <th style="padding:10px 14px; text-align:left; font-size:9px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">Hub</th>
                        <th style="padding:10px 14px; text-align:center; font-size:9px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allRefRecords as $r)
                    @php
                        $stageColors = [
                            'Sent'          => ['bg' => 'rgba(107,106,101,0.1)', 'text' => 'var(--ink-3)'],
                            'Acknowledged'  => ['bg' => 'rgba(184,115,25,0.1)',  'text' => 'var(--ochre)'],
                            'In progress'   => ['bg' => 'rgba(22,48,41,0.1)',    'text' => 'var(--forest)'],
                            'Completed'     => ['bg' => 'rgba(47,122,77,0.1)',   'text' => '#2f7a4d'],
                            'Failed'        => ['bg' => 'rgba(138,46,29,0.1)',   'text' => 'var(--burgundy)'],
                        ];
                        $sc = $stageColors[$r['stage']] ?? $stageColors['Sent'];
                    @endphp
                    <tr class="ref-record-row" data-stage="{{ $r['stage'] }}" data-hub="{{ $r['hub_id'] }}" data-date="{{ $r['date'] ? \Carbon\Carbon::parse($r['date'])->format('Y-m-d') : '' }}" data-search="{{ strtolower($r['client_name'] . ' ' . $r['partner_name'] . ' ' . $r['case_uid']) }}"
                        style="border-bottom:1px solid var(--rule-2); transition:background 100ms;"
                        onmouseenter="this.style.background='var(--parchment)'" onmouseleave="this.style.background=''">
                        <td style="padding:10px 14px;">
                            <span class="mono" style="font-size:11px; font-weight:600; color:var(--forest);">{{ $r['ref'] }}</span>
                        </td>
                        <td style="padding:10px 14px;">
                            <span style="font-size:11px; color:var(--ink-2);">{{ $r['date'] ? \Carbon\Carbon::parse($r['date'])->format('d M Y') : '—' }}</span>
                        </td>
                        <td style="padding:10px 14px;">
                            <div style="font-size:12px; font-weight:600; color:var(--ink);">{{ $r['client_name'] }}</div>
                            <div class="mono" style="font-size:10px; color:var(--ink-4);">{{ $r['case_uid'] }}</div>
                        </td>
                        <td style="padding:10px 14px;">
                            <div style="font-size:11px; color:var(--ink-2); max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $r['partner_name'] }}</div>
                        </td>
                        <td style="padding:10px 14px; text-align:center;">
                            <span style="font-size:10px; padding:2px 8px; font-weight:600; background:{{ $sc['bg'] }}; color:{{ $sc['text'] }};">
                                {{ $r['stage'] }}
                            </span>
                        </td>
                        <td style="padding:10px 14px; text-align:center;">
                            <span style="font-size:11px; font-weight:600; color:{{ $r['days_open'] > 30 ? 'var(--burgundy)' : ($r['days_open'] > 14 ? 'var(--ochre)' : 'var(--ink-3)') }};">
                                {{ $r['days_open'] }}d
                            </span>
                        </td>
                        <td style="padding:10px 14px;">
                            <span class="mono" style="font-size:10px; color:var(--ink-3);">{{ $r['hub_id'] }}</span>
                        </td>
                        <td style="padding:10px 14px; text-align:center;">
                            <a href="{{ route('cases.show', ['case' => \App\Models\CaseReferral::where('id', (int) ltrim(str_replace('R-', '', $r['ref']), '0'))->value('case_id') ?? 0]) }}#referrals"
                               style="color:var(--forest); text-decoration:none; font-size:11px; font-weight:600;">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="padding:40px; text-align:center; color:var(--ink-4); font-size:13px;">
                            No referral records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function jhFilterRefTable() {
    var stage  = document.getElementById('refFilterStage').value.toLowerCase();
    var hub    = document.getElementById('refFilterHub').value;
    var from   = document.getElementById('refFilterFrom').value;
    var to     = document.getElementById('refFilterTo').value;
    var search = document.getElementById('refFilterSearch').value.toLowerCase();
    var rows   = document.querySelectorAll('.ref-record-row');
    var count  = 0;
    rows.forEach(function(row) {
        var s = row.dataset.stage.toLowerCase();
        var h = row.dataset.hub;
        var d = row.dataset.date;
        var t = row.dataset.search;
        var dateOk = true;
        if (from && d) dateOk = dateOk && (d >= from);
        if (to   && d) dateOk = dateOk && (d <= to);
        var show = (!stage || s === stage)
                && (!hub || h === hub)
                && dateOk
                && (!search || t.indexOf(search) !== -1);
        row.style.display = show ? '' : 'none';
        if (show) count++;
    });
    document.getElementById('refFilterCount').textContent = count;
}

function jhExportRefTable() {
    var rows = document.querySelectorAll('.ref-record-row');
    var csv = 'Ref ID,Date,Client,Case UID,Referred To,Stage,Days Open,Hub\n';
    rows.forEach(function(row) {
        if (row.style.display === 'none') return;
        var cells = row.querySelectorAll('td');
        var ref   = cells[0].textContent.trim();
        var date  = cells[1].textContent.trim();
        var name  = cells[2].querySelector('div').textContent.trim();
        var uid   = cells[2].querySelectorAll('div')[1].textContent.trim();
        var to    = cells[3].textContent.trim();
        var stage = cells[4].textContent.trim();
        var days  = cells[5].textContent.trim();
        var hub   = cells[6].textContent.trim();
        csv += '"' + ref + '","' + date + '","' + name + '","' + uid + '","' + to + '","' + stage + '","' + days + '","' + hub + '"\n';
    });
    var blob = new Blob([csv], { type: 'text/csv' });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'Referral_Records_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
}

function jhToggleReferralPanel(id) {
    const panel = document.getElementById(id);
    const chev  = document.getElementById('chev-' + id);
    if (!panel) return;
    const open = panel.style.display === 'none' || panel.style.display === '';
    panel.style.display = open ? 'block' : 'none';
    if (chev) chev.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
}
</script>

</x-layouts.app>
