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

        {{-- ── Charts row ── --}}
        @php
            $maxPathway = $pathwayRanking->max('total') ?: 1;  // for bar width only
            $maxPartner = $namedPartners->max('total') ?: 1;   // for bar width only
            $partnerColors = ['#2f5c3a','#b87319','#4a7a5c','#6b6a65','#8a2e1d','#163029','#5a6e4a','#a07830','#3d5a52','#7a4a2a','#2a4a3a','#6a5a3a'];
        @endphp
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">

            {{-- Left: Pathway ranking --}}
            <div class="card" style="padding:20px 24px;">
                <div style="font-size:15px; font-weight:700; color:var(--ink); margin-bottom:4px;">Channel ranking</div>
                <div style="font-size:11px; color:var(--ink-4); margin-bottom:18px;">How the client first reached a hub. Click a channel to filter the page.</div>
                @foreach($pathwayRanking as $row)
                @if($row['total'] > 0)
                <div style="display:grid; grid-template-columns:180px 1fr 52px 32px; align-items:center; gap:10px; margin-bottom:10px;">
                    <div style="font-size:11px; color:var(--ink-2); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $row['label'] }}">{{ $row['label'] }}</div>
                    <div style="background:var(--rule-2); border-radius:2px; height:8px; overflow:hidden;">
                        <div style="width:{{ round(($row['total']/$maxPathway)*100) }}%; height:100%; background:{{ $row['color'] }}; border-radius:2px; transition:width 0.4s;"></div>
                    </div>
                    <div style="font-size:11px; font-weight:600; color:var(--ink-2); text-align:right;">{{ number_format($row['total']) }}</div>
                    <div style="font-size:10px; color:var(--ink-4);">{{ $row['pct'] }}%</div>
                </div>
                @endif
                @endforeach
            </div>

            {{-- Right: Named partners --}}
            @php $partnerTotal = $namedPartners->sum('total') ?: 1; @endphp
            <div class="card" style="padding:20px 24px;">
                <div style="font-size:15px; font-weight:700; color:var(--ink); margin-bottom:4px;">Named partners</div>
                <div style="font-size:11px; color:var(--ink-4); margin-bottom:18px;">Organisations recorded by name — the relationships you manage.</div>
                @foreach($namedPartners as $i => $row)
                @php $barColor = $partnerColors[$i % count($partnerColors)]; @endphp
                <div style="display:grid; grid-template-columns:160px 1fr 44px 32px; align-items:center; gap:10px; margin-bottom:9px;">
                    <div style="font-size:11px; color:var(--ink-2); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $row['label'] }}">{{ Str::limit($row['label'], 22) }}</div>
                    <div style="background:var(--rule-2); border-radius:2px; height:8px; overflow:hidden;">
                        <div style="width:{{ round(($row['total']/$maxPartner)*100) }}%; height:100%; background:{{ $barColor }}; border-radius:2px; transition:width 0.4s;"></div>
                    </div>
                    <div style="font-size:11px; font-weight:600; color:var(--ink-2); text-align:right;">{{ $row['total'] }}</div>
                    <div style="font-size:10px; color:var(--ink-4);">{{ round(($row['total']/$partnerTotal)*100) }}%</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── Filter bar ── --}}
        <form method="GET" action="{{ route('referrals.index') }}" id="refFilterForm"
              style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:16px; padding:12px 16px; background:var(--paper); border:1px solid var(--rule); border-radius:8px;">

            {{-- From date --}}
            <div style="position:relative; display:inline-flex; align-items:center;">
                <span style="position:absolute; left:10px; font-size:10px; color:var(--ink-4); pointer-events:none; font-weight:500;">From</span>
                <input type="date" name="from" value="{{ $filterFrom }}"
                    style="padding:7px 10px 7px 46px; border:1px solid var(--rule); border-radius:20px; font-size:12px; color:var(--ink); background:var(--parchment); font-family:inherit; cursor:pointer; outline:none; min-width:160px;"
                    onfocus="this.style.borderColor='var(--forest)'" onblur="this.style.borderColor='var(--rule)'; this.form.submit();"
                    onchange="this.form.submit()">
            </div>

            {{-- To date --}}
            <div style="position:relative; display:inline-flex; align-items:center;">
                <span style="position:absolute; left:10px; font-size:10px; color:var(--ink-4); pointer-events:none; font-weight:500;">To</span>
                <input type="date" name="to" value="{{ $filterTo }}"
                    style="padding:7px 10px 7px 34px; border:1px solid var(--rule); border-radius:20px; font-size:12px; color:var(--ink); background:var(--parchment); font-family:inherit; cursor:pointer; outline:none; min-width:150px;"
                    onfocus="this.style.borderColor='var(--forest)'" onblur="this.style.borderColor='var(--rule)'; this.form.submit();"
                    onchange="this.form.submit()">
            </div>

            {{-- Hub --}}
            <select name="hub" onchange="this.form.submit()"
                style="padding:7px 28px 7px 14px; border:1px solid var(--rule); border-radius:20px; font-size:12px; color:var(--ink); background:var(--parchment); font-family:inherit; cursor:pointer; outline:none; appearance:none; -webkit-appearance:none; background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%236b7280' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E\"); background-repeat:no-repeat; background-position:right 10px center;"
                onfocus="this.style.borderColor='var(--forest)'" onblur="this.style.borderColor='var(--rule)'">
                <option value="all" {{ $filterHub === 'all' ? 'selected' : '' }}>Hub</option>
                @foreach($hubs as $hub)
                <option value="{{ $hub->id }}" {{ $filterHub == $hub->id ? 'selected' : '' }}>{{ $hub->name }}</option>
                @endforeach
            </select>

            {{-- Channel (referral source group) --}}
            <select name="channel" onchange="this.form.submit()"
                style="padding:7px 28px 7px 14px; border:1px solid var(--rule); border-radius:20px; font-size:12px; color:var(--ink); background:var(--parchment); font-family:inherit; cursor:pointer; outline:none; appearance:none; -webkit-appearance:none; background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%236b7280' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E\"); background-repeat:no-repeat; background-position:right 10px center;"
                onfocus="this.style.borderColor='var(--forest)'" onblur="this.style.borderColor='var(--rule)'">
                <option value="all" {{ $filterChannel === 'all' ? 'selected' : '' }}>Channel</option>
                @foreach($channelGroups as $group)
                <option value="{{ $group }}" {{ $filterChannel === $group ? 'selected' : '' }}>{{ $group }}</option>
                @endforeach
            </select>

            {{-- Pathway --}}
            <select name="pathway" onchange="this.form.submit()"
                style="padding:7px 28px 7px 14px; border:1px solid var(--rule); border-radius:20px; font-size:12px; color:var(--ink); background:var(--parchment); font-family:inherit; cursor:pointer; outline:none; appearance:none; -webkit-appearance:none; background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%236b7280' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E\"); background-repeat:no-repeat; background-position:right 10px center;"
                onfocus="this.style.borderColor='var(--forest)'" onblur="this.style.borderColor='var(--rule)'">
                <option value="all"  {{ $filterPathway === 'all'   ? 'selected' : '' }}>Pathway</option>
                <option value="govt" {{ $filterPathway === 'govt'  ? 'selected' : '' }}>Government</option>
                <option value="ngo"  {{ $filterPathway === 'ngo'   ? 'selected' : '' }}>NGO / CSO</option>
                <option value="other"{{ $filterPathway === 'other' ? 'selected' : '' }}>Other</option>
            </select>

            {{-- Generate report button --}}
            <button type="button" onclick="jhOpenReport()"
                style="padding:7px 18px; background:var(--forest); color:var(--cream); border:none; border-radius:20px; font-size:12px; font-weight:600; cursor:pointer; font-family:inherit; transition:opacity .15s; white-space:nowrap;"
                onmouseenter="this.style.opacity='.85'" onmouseleave="this.style.opacity='1'">
                Generate report
            </button>

            @if($filterFrom || $filterTo || $filterHub !== 'all' || $filterChannel !== 'all' || $filterPathway !== 'all')
            <a href="{{ route('referrals.index') }}"
               style="font-size:11px; color:var(--ink-4); text-decoration:none; padding:7px 4px; white-space:nowrap;"
               onmouseenter="this.style.color='var(--ink)'" onmouseleave="this.style.color='var(--ink-4)'">
                Clear
            </a>
            @endif
        </form>

        {{-- All Sources Table --}}
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
            <div>
                <div style="font-size:15px; font-weight:700; color:var(--ink);">All sources</div>
                <div style="font-size:11px; color:var(--ink-4);">{{ $referralNetworkTotal }} referral network cases grouped by how clients heard about us.</div>
            </div>
            <button onclick="jhExportSourcesTable()" class="btn-ghost" style="font-size:11px; padding:6px 14px; display:inline-flex; align-items:center; gap:5px;">
                <x-lucide-download style="width:12px;height:12px;" /> Export CSV
            </button>
        </div>

        <div style="overflow-x:auto; border:1px solid var(--rule); border-radius:3px;">
            <table style="width:100%; border-collapse:collapse; font-size:12px; min-width:620px;" id="allSourcesTable">
                <thead>
                    <tr style="background:var(--parchment); border-bottom:2px solid var(--rule);">
                        <th style="padding:10px 14px; text-align:left; font-size:9px; font-weight:700; letter-spacing:0.07em; text-transform:uppercase; color:var(--ink-3); min-width:180px;">Source</th>
                        <th style="padding:10px 14px; text-align:left; font-size:9px; font-weight:700; letter-spacing:0.07em; text-transform:uppercase; color:var(--ink-3);">Group</th>
                        <th style="padding:10px 14px; text-align:right; font-size:9px; font-weight:700; letter-spacing:0.07em; text-transform:uppercase; color:var(--ink-3);">Intakes</th>
                        <th style="padding:10px 14px; text-align:right; font-size:9px; font-weight:700; letter-spacing:0.07em; text-transform:uppercase; color:var(--ink-3);">Share</th>
                        <th style="padding:10px 14px; text-align:right; font-size:9px; font-weight:700; letter-spacing:0.07em; text-transform:uppercase; color:var(--ink-3);">Govt</th>
                        <th style="padding:10px 14px; text-align:right; font-size:9px; font-weight:700; letter-spacing:0.07em; text-transform:uppercase; color:var(--ink-3);">NGO / CSO</th>
                        <th style="padding:10px 14px; text-align:right; font-size:9px; font-weight:700; letter-spacing:0.07em; text-transform:uppercase; color:var(--ink-3);">Other</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allSourcesTable as $r)
                    <tr style="border-bottom:1px solid var(--rule-2); transition:background 80ms;"
                        onmouseenter="this.style.background='var(--parchment)'" onmouseleave="this.style.background=''">
                        <td style="padding:10px 14px; font-size:12px; font-weight:500; color:var(--ink);">{{ $r['source'] }}</td>
                        <td style="padding:10px 14px;">
                            <span style="display:inline-flex; align-items:center; gap:5px; font-size:11px; color:var(--ink-3);">
                                <span style="width:7px; height:7px; border-radius:50%; background:{{ $r['dot'] }}; flex-shrink:0; display:inline-block;"></span>
                                {{ $r['group'] }}
                            </span>
                        </td>
                        <td style="padding:10px 14px; text-align:right; font-weight:700; color:var(--ink);">{{ number_format($r['total']) }}</td>
                        <td style="padding:10px 14px; text-align:right; color:var(--ink-3); font-size:11px;">{{ $r['share'] }}%</td>
                        <td style="padding:10px 14px; text-align:right; color:{{ $r['govt'] ? 'var(--ink-2)' : 'var(--ink-4)' }};">{{ $r['govt'] ?: '0' }}</td>
                        <td style="padding:10px 14px; text-align:right; color:{{ $r['ngo'] ? 'var(--ink-2)' : 'var(--ink-4)' }};">{{ $r['ngo'] ?: '0' }}</td>
                        <td style="padding:10px 14px; text-align:right; color:{{ $r['other_pw'] ? 'var(--ink-2)' : 'var(--ink-4)' }};">{{ $r['other_pw'] ?: '0' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="padding:40px; text-align:center; color:var(--ink-4);">No data found.</td></tr>
                    @endforelse
                    {{-- Totals row --}}
                    <tr style="background:var(--parchment); border-top:2px solid var(--rule); font-weight:700;">
                        <td style="padding:10px 14px; font-size:12px; color:var(--ink);">Total</td>
                        <td style="padding:10px 14px;"></td>
                        <td style="padding:10px 14px; text-align:right; color:var(--ink);">{{ number_format($referralNetworkTotal) }}</td>
                        <td style="padding:10px 14px; text-align:right; color:var(--ink-3);">100%</td>
                        <td style="padding:10px 14px; text-align:right;">{{ $allSourcesTable->sum('govt') }}</td>
                        <td style="padding:10px 14px; text-align:right;">{{ $allSourcesTable->sum('ngo') }}</td>
                        <td style="padding:10px 14px; text-align:right;">{{ $allSourcesTable->sum('other_pw') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function jhExportSourcesTable() {
    var rows = document.querySelectorAll('#allSourcesTable tbody tr');
    var csv  = 'Source,Group,Intakes,Share,Govt,NGO/CSO,Other\n';
    rows.forEach(function(row) {
        var cells = row.querySelectorAll('td');
        if (cells.length < 7) return;
        var rowData = [];
        for (var i = 0; i < 7; i++) {
            rowData.push('"' + cells[i].textContent.trim().replace(/"/g, '""') + '"');
        }
        csv += rowData.join(',') + '\n';
    });
    var blob = new Blob([csv], { type: 'text/csv' });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'All_Sources_' + new Date().toISOString().slice(0,10) + '.csv';
    a.click();
}

function jhOpenReport() {
    var form = document.getElementById('refFilterForm');
    var data = new FormData(form);
    var params = new URLSearchParams();
    data.forEach(function(v, k){ if(v) params.append(k, v); });
    window.open('{{ route("referrals.report") }}?' + params.toString(), '_blank');
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
