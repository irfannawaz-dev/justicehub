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

</div>

<script>
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
