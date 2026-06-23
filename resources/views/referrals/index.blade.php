<x-layouts.app>
@php
    $currentCategory = request('category', 'all');
@endphp
<div style="padding: 28px 36px 60px; max-width: 1640px; margin: 0 auto;">

    {{-- ═══ Editorial Header ═══ --}}
    <div style="margin-bottom: 28px; padding-bottom: 22px; border-bottom: 1px solid var(--rule);">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 32;">
            <div style="flex: 1; max-width: 820px;">
                <div class="label-cap" style="margin-bottom: 8px;">Service Delivery · External Partnerships</div>
                <h1 class="serif" style="font-size: 42px; font-weight: 400; letter-spacing: -0.018em; line-height: 1.02; margin: 0;">
                    Referral <em style="color: var(--forest);">Network</em>
                </h1>
                <div style="font-size: 13.5px; color: var(--ink-2); margin-top: 14px; line-height: 1.6; max-width: 680px;">
                    The living directory of every shelter, government office, clinic, and partner NGO the Hub routes clients to &mdash; with loop-closure tracked against indicator <span class="mono" style="font-size: 12px;">O2.4</span>. A referral isn&rsquo;t a handoff; it&rsquo;s a promise the Hub keeps until the service actually arrives.
                </div>
            </div>
            <div style="display: flex; gap: 10px; flex-shrink: 0;">
                <button class="btn-ghost"><x-lucide-filter style="width:13px;height:13px;" /> Export directory</button>
            </div>
        </div>
    </div>

    {{-- ═══ KPI Row (4 tiles) ═══ --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 30px;">
        {{-- Loop-closure rate --}}
        <div class="card" style="padding: 18px 20px; border-top: 3px solid var(--moss);">
            <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                <div class="label-cap" style="font-size: 9.5px;">O2.4 Loop-closure rate</div>
                <x-lucide-check-circle-2 style="width:16px;height:16px;color:var(--moss);" />
            </div>
            <div class="serif" style="font-size: 36px; font-weight: 500; line-height: 1; margin-top: 10px; color: var(--moss);">{{ $closureRate }}%</div>
            <div style="font-size: 11px; color: var(--ink-3); margin-top: 6px; line-height: 1.4;">of referrals successfully completed · target 85%</div>
        </div>
        {{-- Active referrals --}}
        <div class="card" style="padding: 18px 20px; border-top: 3px solid var(--ochre);">
            <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                <div class="label-cap" style="font-size: 9.5px;">Active referrals</div>
                <x-lucide-share-2 style="width:16px;height:16px;color:var(--ochre);" />
            </div>
            <div class="serif" style="font-size: 36px; font-weight: 500; line-height: 1; margin-top: 10px; color: var(--ochre);">{{ $totalActive }}</div>
            <div style="font-size: 11px; color: var(--ink-3); margin-top: 6px; line-height: 1.4;">open across {{ $partners->count() }} partner organisations</div>
        </div>
        {{-- Partner organisations --}}
        <div class="card" style="padding: 18px 20px; border-top: 3px solid var(--forest);">
            <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                <div class="label-cap" style="font-size: 9.5px;">Partner organisations</div>
                <x-lucide-building-2 style="width:16px;height:16px;color:var(--forest);" />
            </div>
            <div class="serif" style="font-size: 36px; font-weight: 500; line-height: 1; margin-top: 10px;">{{ $partners->count() }}</div>
            <div style="font-size: 11px; color: var(--ink-3); margin-top: 6px; line-height: 1.4;">{{ $mouAttention->count() }} MOU{{ $mouAttention->count() === 1 ? '' : 's' }} needing attention</div>
        </div>
        {{-- Avg response time --}}
        <div class="card" style="padding: 18px 20px; border-top: 3px solid {{ $avgResponseHrs < 30 ? 'var(--forest)' : 'var(--ochre)' }};">
            <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                <div class="label-cap" style="font-size: 9.5px;">Avg response time</div>
                <x-lucide-clock style="width:16px;height:16px;color:{{ $avgResponseHrs < 30 ? 'var(--forest)' : 'var(--ochre)' }};" />
            </div>
            <div class="serif" style="font-size: 36px; font-weight: 500; line-height: 1; margin-top: 10px;">{{ $avgResponseHrs }}h</div>
            <div style="font-size: 11px; color: var(--ink-3); margin-top: 6px; line-height: 1.4;">from referral sent to partner acknowledgement</div>
        </div>
    </div>

    {{-- ═══ Loop Closure by Partner Category ═══ --}}
    <div style="margin-bottom: 36px;">
        <div style="display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 16px;">
            <div>
                <h2 class="serif" style="font-size: 22px; font-weight: 400; margin: 0; letter-spacing: -0.01em;">
                    Loop closure by <em style="color: var(--forest);">partner category</em>
                </h2>
                <div style="font-size: 12px; color: var(--ink-3); margin-top: 4px;">
                    Completed vs. failed referrals · active count shown separately · bar width = total volume
                </div>
            </div>
            <span class="mono" style="font-size: 11px; color: var(--ink-3);">Indicator O2.4</span>
        </div>

        <div class="card" style="padding: 22px;">
            <div style="display: flex; flex-direction: column; gap: 18px;">
                @foreach($categoryStats as $cat)
                @php
                    $total = $cat['completed'] + $cat['failed'];
                    $completedPct = $total > 0 ? round(($cat['completed'] / $total) * 100) : 0;
                    $failedPct = 100 - $completedPct;
                    $widthPct = round(($cat['volume'] / $maxVolume) * 100);
                    $rateColor = $cat['closureRate'] >= 90 ? 'var(--moss)' : ($cat['closureRate'] >= 85 ? 'var(--ochre)' : 'var(--burgundy)');
                    $rateBg = $cat['closureRate'] >= 90 ? 'var(--moss-tint)' : ($cat['closureRate'] >= 85 ? 'var(--ochre-tint)' : 'var(--burgundy-tint)');
                @endphp
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 28px; height: 28px; background: {{ $cat['color'] }}; color: var(--cream); display: flex; align-items: center; justify-content: center;">
                                <x-dynamic-component :component="'lucide-' . $cat['icon']" style="width:14px;height:14px;" />
                            </div>
                            <div>
                                <div style="font-size: 13px; font-weight: 500; letter-spacing: -0.005em;">{{ $cat['category'] }}</div>
                                <div style="font-size: 10.5px; color: var(--ink-3); margin-top: 1px;">
                                    {{ $cat['active'] }} active · {{ $cat['completed'] + $cat['failed'] }} resolved
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: baseline; gap: 18px; font-size: 11px; color: var(--ink-3);">
                            <span>
                                <span class="serif" style="font-size: 17px; font-weight: 500; color: var(--moss);">{{ $cat['completed'] }}</span>
                                <span style="margin-left: 4px;">completed</span>
                            </span>
                            <span>
                                <span class="serif" style="font-size: 17px; font-weight: 500; color: var(--burgundy);">{{ $cat['failed'] }}</span>
                                <span style="margin-left: 4px;">failed</span>
                            </span>
                            <span style="padding: 3px 9px; background: {{ $rateBg }}; color: {{ $rateColor }}; font-size: 11px; font-weight: 600; letter-spacing: 0.01em; min-width: 50px; text-align: center;">
                                {{ $cat['closureRate'] }}%
                            </span>
                        </div>
                    </div>
                    {{-- Stacked bar --}}
                    <div style="display: flex; height: 10px; width: {{ $widthPct }}%; min-width: 100px;">
                        <div style="width: {{ $completedPct }}%; background: var(--moss);"></div>
                        <div style="width: {{ $failedPct }}%; background: var(--burgundy); opacity: 0.85;"></div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Legend --}}
            <div style="margin-top: 22px; padding-top: 16px; border-top: 1px solid var(--rule-2); display: flex; gap: 20px; font-size: 11px; color: var(--ink-3); align-items: center;">
                <span style="display: flex; align-items: center; gap: 6px;">
                    <span style="width: 10px; height: 10px; background: var(--moss);"></span> Loop closed (service confirmed received)
                </span>
                <span style="display: flex; align-items: center; gap: 6px;">
                    <span style="width: 10px; height: 10px; background: var(--burgundy); opacity: 0.85;"></span> Failed (declined / lost to follow-up)
                </span>
                <span style="margin-left: auto; font-style: italic;">
                    Active referrals excluded from rate &mdash; counted once resolved.
                </span>
            </div>
        </div>
    </div>

    {{-- ═══ Partner Directory ═══ --}}
    <div style="margin-bottom: 36px;">
        <div style="display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 16px;">
            <div>
                <h2 class="serif" style="font-size: 22px; font-weight: 400; margin: 0; letter-spacing: -0.01em;">Partner directory</h2>
                <div style="font-size: 12px; color: var(--ink-3); margin-top: 4px;">
                    <span id="pdCount">{{ $partners->count() }}</span> organisation{{ $partners->count() === 1 ? '' : 's' }} · showing active caseloads, response times, and MOU status
                </div>
            </div>
            <div style="display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end;">
                @php
                    $filterTabs = collect([['id' => 'all', 'label' => 'All partners', 'count' => $partners->count()]]);
                    foreach(array_keys($categoryConfig) as $cat) {
                        $filterTabs->push(['id' => $cat, 'label' => $cat, 'count' => $filterCounts[$cat] ?? 0]);
                    }
                @endphp
                @foreach($filterTabs as $f)
                <button onclick="pdFilter('{{ $f['id'] }}', this)"
                        data-pd-filter="{{ $f['id'] }}"
                        style="padding: 6px 10px; font-size: 11.5px; font-weight: 500; cursor: pointer; font-family: inherit;
                               background: transparent; color: var(--ink-2);
                               border: 1px solid var(--rule);
                               letter-spacing: 0.02em; display: inline-flex; align-items: center; gap: 6px; transition: all 120ms;">
                    {{ $f['label'] }}
                    <span data-pd-count="{{ $f['id'] }}"
                          style="font-size: 10px; padding: 1px 5px; font-weight: 600; background: var(--paper); color: var(--ink-3);">{{ $f['count'] }}</span>
                </button>
                @endforeach
            </div>
        </div>

        <div id="pdGrid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px;">
            @foreach($partners as $p)
            @php
                $catCfg = $categoryConfig[$p->category] ?? ['color' => 'var(--ink-3)', 'tint' => 'var(--paper)', 'icon' => 'circle'];
                $ps = $partnerStats[$p->name] ?? ['active' => 0, 'completed' => 0, 'failed' => 0, 'closureRate' => 0];
                $pClosureRate = $ps['closureRate'];
                $rateColor = $pClosureRate >= 90 ? 'var(--moss)' : ($pClosureRate >= 85 ? 'var(--ochre)' : 'var(--burgundy)');
                $mouDot = match($p->mou_status) { 'active' => 'var(--moss)', 'expiring' => 'var(--ochre)', 'expired' => 'var(--burgundy)', default => 'var(--ink-3)' };
                $hubList = $p->hubs->pluck('id')->toArray();
                $isAllHubs = count($hubList) >= 5;
            @endphp
            <div class="card pd-card" data-category="{{ $p->category }}"
                 style="padding: 18px; display: flex; flex-direction: column; position: relative; transition: all 0.15s ease;"
                 onmouseenter="this.style.borderColor='{{ $catCfg['color'] }}';this.style.transform='translateY(-1px)';"
                 onmouseleave="this.style.borderColor='var(--rule)';this.style.transform='none';">
                {{-- Top strip --}}
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: {{ $catCfg['color'] }};"></div>

                {{-- Category pill --}}
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; margin-top: 4px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 26px; height: 26px; background: {{ $catCfg['tint'] }}; color: {{ $catCfg['color'] }}; display: flex; align-items: center; justify-content: center;">
                            <x-dynamic-component :component="'lucide-' . $catCfg['icon']" style="width:13px;height:13px;" />
                        </div>
                        <span class="label-cap" style="font-size: 9.5px; color: {{ $catCfg['color'] }}; font-weight: 600;">{{ $p->category }}</span>
                    </div>
                    <span class="mono" style="font-size: 10px; color: var(--ink-4);">{{ $p->id }}</span>
                </div>

                {{-- Name + type --}}
                <h3 class="serif" style="font-size: 18px; font-weight: 500; line-height: 1.15; margin: 0 0 3px 0; letter-spacing: -0.005em;">{{ $p->name }}</h3>
                <div style="font-size: 11.5px; color: var(--ink-3); font-style: italic; margin-bottom: 14px;">{{ $p->type }}</div>

                {{-- Stats row --}}
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; background: var(--rule-2); margin-bottom: 12px;">
                    <div style="padding: 8px 10px; background: var(--parchment-2);">
                        <div class="label-cap" style="font-size: 9px; margin-bottom: 3px;">Active</div>
                        <div class="serif" style="font-size: 17px; font-weight: 500; color: {{ $ps['active'] > 0 ? $catCfg['color'] : 'var(--ink-4)' }}; line-height: 1;">{{ $ps['active'] }}</div>
                    </div>
                    <div style="padding: 8px 10px; background: var(--parchment-2);">
                        <div class="label-cap" style="font-size: 9px; margin-bottom: 3px;">Closed</div>
                        <div class="serif" style="font-size: 17px; font-weight: 500; line-height: 1;">{{ $ps['completed'] }}</div>
                    </div>
                    <div style="padding: 8px 10px; background: var(--parchment-2);">
                        <div class="label-cap" style="font-size: 9px; margin-bottom: 3px;">Rate</div>
                        <div class="serif" style="font-size: 17px; font-weight: 500; line-height: 1; color: {{ $rateColor }};">{{ $pClosureRate }}%</div>
                    </div>
                </div>

                {{-- Hub coverage --}}
                <div style="margin-bottom: 10px;">
                    <div class="label-cap" style="font-size: 9px; margin-bottom: 5px;">Hub coverage</div>
                    <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                        @if($isAllHubs)
                            <span class="mono" style="font-size: 10px; padding: 2px 6px; background: var(--forest); color: var(--cream);">ALL {{ count($hubList) }} HUBS</span>
                        @else
                            @foreach($hubList as $h)
                                <span class="mono" style="font-size: 10px; padding: 2px 6px; background: var(--paper); border: 1px solid var(--rule-2); color: var(--ink-2);">{{ $h }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>

                {{-- Footer --}}
                <div style="margin-top: auto; padding-top: 12px; border-top: 1px solid var(--rule-2); display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                    <div style="font-size: 10.5px; color: var(--ink-3); display: flex; align-items: center; gap: 5px; overflow: hidden;">
                        <x-lucide-user style="width:10px;height:10px;flex-shrink:0;" />
                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $p->focal_person }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 5px; font-size: 10px; color: var(--ink-3);" title="MOU {{ $p->mou_status }} — expires {{ $p->mou_expires?->format('M Y') }}">
                        <span style="width: 7px; height: 7px; background: {{ $mouDot }}; border-radius: 50%;"></span>
                        <span>MOU {{ $p->mou_status }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ═══ Referral Tracker ═══ --}}
    <div style="margin-bottom: 36px;">
        <div style="display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 16px;">
            <div>
                <h2 class="serif" style="font-size: 22px; font-weight: 400; margin: 0; letter-spacing: -0.01em;">Referral tracker</h2>
                <div style="font-size: 12px; color: var(--ink-3); margin-top: 4px;">
                    {{ $trackerCounts['all'] }} referral{{ $trackerCounts['all'] === 1 ? '' : 's' }} &middot; pipeline stages tracked until loop closure or failure
                </div>
            </div>
            {{-- Filter pills --}}
            <div style="display: flex; gap: 6px;">
                @foreach([
                    ['id' => 'active',    'label' => 'Active',    'count' => $trackerCounts['active']],
                    ['id' => 'completed', 'label' => 'Completed', 'count' => $trackerCounts['completed']],
                    ['id' => 'failed',    'label' => 'Failed',    'count' => $trackerCounts['failed']],
                    ['id' => 'all',       'label' => 'All',       'count' => $trackerCounts['all']],
                ] as $tf)
                <button onclick="rtFilter('{{ $tf['id'] }}', this)"
                        data-rt-filter="{{ $tf['id'] }}"
                        style="padding: 6px 10px; font-size: 11.5px; font-weight: 500; cursor: pointer; font-family: inherit;
                               background: transparent; color: var(--ink-2);
                               border: 1px solid var(--rule);
                               letter-spacing: 0.02em; display: inline-flex; align-items: center; gap: 6px; transition: all 120ms;">
                    {{ $tf['label'] }}
                    <span style="font-size: 10px; padding: 1px 5px; font-weight: 600; background: var(--paper); color: var(--ink-3);">{{ $tf['count'] }}</span>
                </button>
                @endforeach
            </div>
        </div>

        @if($referralTracker->isEmpty())
        <div class="card" style="padding: 40px; text-align: center; color: var(--ink-3);">
            <x-lucide-share-2 style="width:28px;height:28px;color:var(--ink-4);margin:0 auto 12px;" />
            <div style="font-size: 13px;">No referrals logged yet &mdash; use <strong>Log new referral</strong> to start tracking.</div>
        </div>
        @else
        <div class="card" style="padding: 0; overflow: hidden;">
            {{-- Table header --}}
            <div style="display: grid; grid-template-columns: 110px 1fr 180px 200px 200px 80px 100px; gap: 0; border-bottom: 2px solid var(--rule);">
                @foreach(['REF · DATE','CLIENT & CASE','ROUTE','SERVICE REQUESTED','PIPELINE STATUS','DAYS OPEN','FOLLOW-UP'] as $col)
                <div style="padding: 10px 14px; font-size: 9.5px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink-3); background: var(--paper); {{ !$loop->last ? 'border-right: 1px solid var(--rule-2);' : '' }}">
                    {{ $col }}
                </div>
                @endforeach
            </div>

            {{-- Rows --}}
            @foreach($referralTracker as $r)
            @php
                $stageMap  = ['Sent' => 1, 'Acknowledged' => 2, 'In progress' => 3, 'Completed' => 4, 'Failed' => 4];
                $stageIdx  = $stageMap[$r['stage']] ?? 1;
                $stagePct  = round(($stageIdx / 4) * 100);
                $isTerminal = in_array($r['stage'], ['Completed', 'Failed']);
                $stageColor = $r['stage'] === 'Completed' ? 'var(--moss)' : ($r['stage'] === 'Failed' ? 'var(--burgundy)' : 'var(--forest)');
                $urgColor   = match($r['urgency']) { 'High' => 'var(--burgundy)', 'Low' => 'var(--moss)', default => 'var(--ochre)' };
                $urgBg      = match($r['urgency']) { 'High' => 'var(--burgundy-tint)', 'Low' => 'var(--moss-tint)', default => 'var(--ochre-tint)' };
                $daysColor  = $r['days_open'] > 14 ? 'var(--burgundy)' : ($r['days_open'] > 7 ? 'var(--ochre)' : 'var(--ink-2)');
                $rtStatus   = in_array($r['stage'], ['Sent','Acknowledged','In progress']) ? 'active' : strtolower($r['stage']);
                $refDate    = $r['date'] ? \Carbon\Carbon::parse($r['date']) : null;
            @endphp
            <div data-rt-row="{{ $rtStatus }}"
                 style="display: grid; grid-template-columns: 110px 1fr 180px 200px 200px 80px 100px; gap: 0; align-items: center;
                        border-bottom: 1px solid var(--rule-2); transition: background 100ms;"
                 onmouseenter="this.style.background='var(--paper)'" onmouseleave="this.style.background=''">

                {{-- REF · DATE --}}
                <div style="padding: 13px 14px; border-right: 1px solid var(--rule-2);">
                    <div class="mono" style="font-size: 12px; font-weight: 600; color: var(--ink); letter-spacing: 0.02em;">{{ $r['ref'] }}</div>
                    <div style="font-size: 10.5px; color: var(--ink-3); margin-top: 3px;">
                        {{ $refDate ? $refDate->format('d M Y') : '—' }}
                    </div>
                </div>

                {{-- CLIENT & CASE --}}
                <div style="padding: 13px 14px; border-right: 1px solid var(--rule-2);">
                    <div style="font-size: 13px; font-weight: 500; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $r['client_name'] }}</div>
                    <div style="margin-top: 3px;">
                        <span class="mono" style="font-size: 10px; padding: 1px 5px; background: var(--paper); border: 1px solid var(--rule-2); color: var(--ink-3);">{{ $r['case_uid'] }}</span>
                    </div>
                </div>

                {{-- ROUTE --}}
                <div style="padding: 13px 14px; border-right: 1px solid var(--rule-2);">
                    <div style="display: flex; align-items: center; gap: 5px; font-size: 11px; flex-wrap: wrap;">
                        <span class="mono" style="font-size: 10px; padding: 1px 5px; background: var(--paper); border: 1px solid var(--rule-2); color: var(--ink-2);">{{ $r['hub_id'] }}</span>
                        <x-lucide-arrow-right style="width:11px;height:11px;color:var(--ink-4);flex-shrink:0;" />
                        <span style="font-size: 11px; color: var(--forest); font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 90px;" title="{{ $r['partner_name'] }}">{{ $r['partner_name'] }}</span>
                    </div>
                    <div style="font-size: 10px; color: var(--ink-4); margin-top: 3px;">{{ $r['partner_cat'] }}</div>
                </div>

                {{-- SERVICE REQUESTED --}}
                <div style="padding: 13px 14px; border-right: 1px solid var(--rule-2);">
                    <div style="display: flex; align-items: flex-start; gap: 6px;">
                        <span style="font-size: 10px; font-weight: 600; padding: 2px 6px; background: {{ $urgBg }}; color: {{ $urgColor }}; white-space: nowrap; flex-shrink: 0; letter-spacing: 0.02em; margin-top: 1px;">{{ $r['urgency'] }}</span>
                        <span style="font-size: 11.5px; color: var(--ink-2); overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; line-height: 1.4;">{{ Str::limit($r['service'], 80) }}</span>
                    </div>
                </div>

                {{-- PIPELINE STATUS --}}
                <div style="padding: 13px 14px; border-right: 1px solid var(--rule-2);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px;">
                        <span style="font-size: 11px; font-weight: 500; color: {{ $stageColor }};">{{ $r['stage'] }}</span>
                        <span style="font-size: 10px; color: var(--ink-4);">{{ $stagePct }}%</span>
                    </div>
                    <div style="height: 5px; background: var(--rule-2); overflow: hidden;">
                        <div style="height: 100%; width: {{ $stagePct }}%; background: {{ $stageColor }}; transition: width 300ms;"></div>
                    </div>
                    @if(!$isTerminal)
                    <div style="font-size: 9.5px; color: var(--ink-4); margin-top: 4px; letter-spacing: 0.02em;">
                        @php
                            $stages = ['Sent','Acknowledged','In progress','Completed'];
                            $nextIdx = array_search($r['stage'], $stages);
                            $nextStage = ($nextIdx !== false && isset($stages[$nextIdx + 1])) ? $stages[$nextIdx + 1] : null;
                        @endphp
                        @if($nextStage) next: {{ $nextStage }} @endif
                    </div>
                    @endif
                </div>

                {{-- DAYS OPEN --}}
                <div style="padding: 13px 14px; text-align: center; border-right: 1px solid var(--rule-2);">
                    <div class="serif" style="font-size: 20px; font-weight: 500; line-height: 1; color: {{ $daysColor }};">{{ $r['days_open'] }}</div>
                    <div style="font-size: 10px; color: var(--ink-4); margin-top: 2px;">days</div>
                </div>

                {{-- FOLLOW-UP --}}
                <div style="padding: 13px 14px;">
                    @if($r['follow_up'])
                    @php $fuDate = \Carbon\Carbon::parse($r['follow_up']); $isPast = $fuDate->isPast(); @endphp
                    <div style="font-size: 11.5px; font-weight: 500; color: {{ $isPast ? 'var(--burgundy)' : 'var(--ink-2)' }};">
                        {{ $fuDate->format('d M') }}
                    </div>
                    <div style="font-size: 10px; color: var(--ink-4); margin-top: 1px;">{{ $fuDate->format('Y') }}</div>
                    @else
                    <span style="font-size: 11px; color: var(--ink-4);">—</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ═══ MOU Attention ═══ --}}
    <div style="margin-bottom: 24px;">
        <div style="margin-bottom: 16px;">
            <h2 class="serif" style="font-size: 22px; font-weight: 400; margin: 0; letter-spacing: -0.01em;">MOU attention</h2>
            <div style="font-size: 12px; color: var(--ink-3); margin-top: 4px;">
                {{ $mouAttention->count() }} partnership{{ $mouAttention->count() === 1 ? '' : 's' }} with agreements expiring or expired · assign to Partnerships Lead for renewal
            </div>
        </div>

        @if($mouAttention->isEmpty())
        <div class="card" style="padding: 40px; text-align: center; color: var(--ink-3);">
            <x-lucide-check-circle-2 style="width:28px;height:28px;color:var(--moss);margin:0 auto 12px;" />
            <div style="font-size: 13px;">All MOUs are currently active &mdash; no renewals needed this quarter.</div>
        </div>
        @else
        <div class="card" style="padding: 0; overflow: hidden;">
            @foreach($mouAttention as $i => $p)
            @php
                $catCfg = $categoryConfig[$p->category] ?? ['color' => 'var(--ink-3)', 'icon' => 'circle'];
                $isExpired = $p->mou_status === 'expired';
            @endphp
            <div style="display: grid; grid-template-columns: 34px 1fr 160px 140px 120px; gap: 16px; align-items: center; padding: 14px 18px;
                        border-bottom: {{ !$loop->last ? '1px solid var(--rule-2)' : 'none' }};
                        background: {{ $isExpired ? 'var(--burgundy-tint)' : 'transparent' }};">
                <div style="width: 32px; height: 32px; background: {{ $catCfg['color'] }}; color: var(--cream); display: flex; align-items: center; justify-content: center;">
                    <x-dynamic-component :component="'lucide-' . $catCfg['icon']" style="width:14px;height:14px;" />
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="serif" style="font-size: 15px; font-weight: 500;">{{ $p->name }}</span>
                        <span class="label-cap" style="font-size: 9px; color: {{ $catCfg['color'] }};">{{ $p->category }}</span>
                    </div>
                    <div style="font-size: 11px; color: var(--ink-3); margin-top: 2px;">
                        Focal: {{ $p->focal_person }} · {{ $p->active_referrals }} active referral{{ $p->active_referrals === 1 ? '' : 's' }}
                    </div>
                </div>
                <div>
                    <div class="label-cap" style="font-size: 9px; margin-bottom: 2px;">MOU expires</div>
                    <div class="mono" style="font-size: 12px; color: {{ $isExpired ? 'var(--burgundy)' : 'var(--ochre)' }}; font-weight: 500;">
                        {{ $p->mou_expires?->format('M d, Y') }}
                    </div>
                </div>
                <div>
                    <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; font-size: 11px; font-weight: 600;
                                 background: {{ $isExpired ? 'var(--burgundy)' : 'var(--ochre)' }}; color: var(--cream); letter-spacing: 0.03em;">
                        @if($isExpired)
                            <x-lucide-alert-triangle style="width:11px;height:11px;" /> EXPIRED
                        @else
                            <x-lucide-clock style="width:11px;height:11px;" /> EXPIRING
                        @endif
                    </span>
                </div>
                <button class="btn-ghost" style="font-size: 11px;">
                    Initiate renewal <x-lucide-chevron-right style="width:12px;height:12px;" />
                </button>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ═══ Methodology Note ═══ --}}
    <div style="margin-top: 8px; padding: 14px 18px; background: var(--paper); border: 1px solid var(--rule); font-size: 11.5px; color: var(--ink-3); line-height: 1.55;">
        <strong style="color: var(--ink-2);">Loop closure:</strong> A referral is counted closed only when the receiving partner confirms service delivery back to the Hub &mdash; not when the Hub sends it. Paralegals conduct follow-up calls at the 3, 7, and 14-day marks. Referrals with no contact after 21 days are auto-escalated to the Hub Coordinator and, if still unresolved, categorised as <em>failed · lost to follow-up</em> for O2.4 calculation.
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════
     NEW EXTERNAL REFERRAL MODAL
     ═══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modal-referral-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 640px; margin: 1.75rem auto;">
        <div class="modal-content" style="border: 1px solid var(--rule); border-radius: 4px; background: var(--parchment); box-shadow: 0 16px 48px rgba(0,0,0,.18); display: flex; flex-direction: column; max-height: 90vh;">

            {{-- Sticky header --}}
            <div style="padding: 22px 24px 18px; border-bottom: 1px solid var(--rule); flex-shrink: 0; background: var(--parchment);">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                    <div>
                        <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); letter-spacing: 0.09em; margin-bottom: 6px;">SERVICE DELIVERY &middot; PARTNER NETWORK</div>
                        <h2 class="serif" style="font-size: 30px; font-weight: 400; margin: 0; line-height: 1.1; letter-spacing: -0.015em;">
                            New external <em style="font-style: italic;">referral</em>
                        </h2>
                    </div>
                    <button type="button" data-bs-dismiss="modal"
                            style="background:none; border:1px solid var(--rule); cursor:pointer; padding:6px 8px; color:var(--ink-3); line-height:1; border-radius:3px; flex-shrink:0; margin-top:2px; transition: border-color 120ms;"
                            onmouseenter="this.style.borderColor='var(--ink-2)';this.style.color='var(--ink)'"
                            onmouseleave="this.style.borderColor='var(--rule)';this.style.color='var(--ink-3)'">
                        <x-lucide-x style="width:16px; height:16px;" />
                    </button>
                </div>
            </div>

            {{-- Scrollable form body --}}
            <div style="flex: 1; overflow-y: auto; padding: 0;">
                <form method="POST" action="{{ route('referrals.store') }}" id="rfForm">
                    @csrf

                    {{-- §1 CASE & CLIENT --}}
                    <div style="padding: 22px 24px; border-bottom: 1px solid var(--rule);">
                        <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.09em; color: var(--ink-2); margin-bottom: 3px;">CASE &amp; CLIENT</div>
                        <div style="font-size: 12px; color: var(--ink-3); margin-bottom: 14px;">Which client is being referred</div>

                        <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">
                            CASE <span style="color:var(--burgundy);">*</span>
                        </label>
                        <select name="case_id" id="rfCaseSelect" required
                                style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; cursor:pointer; appearance:auto; border-radius:2px;">
                            <option value="">Search by case ID or client name…</option>
                            @foreach($activeCases as $ac)
                            <option value="{{ $ac->id }}">{{ $ac->case_uid }} &ndash; {{ $ac->name }}{{ $ac->primary_issue ? ' ('.$ac->primary_issue.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- §2 PARTNER ORGANISATION --}}
                    <div style="padding: 22px 24px; border-bottom: 1px solid var(--rule);">
                        <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.09em; color: var(--ink-2); margin-bottom: 3px;">PARTNER ORGANISATION</div>
                        <div style="font-size: 12px; color: var(--ink-3); margin-bottom: 14px;">Filter by category, then choose the receiving organisation</div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">
                                    PARTNER CATEGORY <span style="color:var(--burgundy);">*</span>
                                </label>
                                <select name="partner_category" required
                                        onchange="rfFilterPartners(this.value)"
                                        style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                                    <option value="">Select category</option>
                                    @foreach(array_keys($categoryConfig) as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">
                                    PARTNER ORGANISATION <span style="color:var(--burgundy);">*</span>
                                </label>
                                <select name="partner_id" id="rfPartnerSelect" required
                                        style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink-3); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                                    <option value="">Pick a category first</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- §3 SERVICE REQUESTED --}}
                    <div style="padding: 22px 24px; border-bottom: 1px solid var(--rule);">
                        <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.09em; color: var(--ink-2); margin-bottom: 3px;">SERVICE REQUESTED</div>
                        <div style="font-size: 12px; color: var(--ink-3); margin-bottom: 14px;">What does the client need from this partner</div>

                        <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">
                            SERVICE DESCRIPTION <span style="color:var(--burgundy);">*</span>
                        </label>
                        <textarea name="service_description" rows="4" required
                                  placeholder="e.g. Emergency shelter for DV survivor and two children, expected 2-week stay"
                                  style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; resize:vertical; box-sizing:border-box; border-radius:2px; outline:none; line-height:1.5; margin-bottom:18px;"></textarea>

                        {{-- Urgency + Expected by + First follow-up --}}
                        <div style="display: grid; grid-template-columns: auto 1fr 1fr; gap: 18px; align-items: start;">
                            {{-- Urgency toggle --}}
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">
                                    URGENCY <span style="color:var(--burgundy);">*</span>
                                </label>
                                <div style="display:flex; border:1px solid var(--rule);">
                                    @foreach(['Low','Med','High'] as $u)
                                    <button type="button" id="rfUrg{{ $u }}"
                                            onclick="rfSetUrgency('{{ $u }}')"
                                            style="padding: 9px 16px; font-size: 12.5px; font-weight: 600; border: none; cursor: pointer; font-family: inherit; transition: all 100ms; background: {{ $u === 'Med' ? 'var(--ochre)' : 'var(--parchment)' }}; color: {{ $u === 'Med' ? '#fff' : 'var(--ink-2)' }}; {{ !$loop->last ? 'border-right:1px solid var(--rule);' : '' }}">
                                        {{ $u }}
                                    </button>
                                    @endforeach
                                </div>
                                <input type="hidden" name="urgency" id="rfUrgencyVal" value="Med">
                            </div>
                            {{-- Expected by --}}
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">EXPECTED BY</label>
                                <input type="date" name="expected_by"
                                       style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; box-sizing:border-box; border-radius:2px; outline:none;">
                                <div style="font-size:11px; color:var(--ink-4); margin-top:5px;">When the service is needed</div>
                            </div>
                            {{-- First follow-up --}}
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">FIRST FOLLOW-UP</label>
                                <input type="date" name="follow_up_date" value="{{ now()->addDays(3)->format('Y-m-d') }}"
                                       style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; box-sizing:border-box; border-radius:2px; outline:none;">
                                <div style="font-size:11px; color:var(--ink-4); margin-top:5px;">Default: 3 days from now</div>
                            </div>
                        </div>
                    </div>

                    {{-- §4 NOTES FOR PARTNER --}}
                    <div style="padding: 22px 24px; border-bottom: 1px solid var(--rule);">
                        <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.09em; color: var(--ink-2); margin-bottom: 14px;">NOTES FOR PARTNER</div>

                        <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">
                            BRIEFING FOR RECEIVING FOCAL POINT
                        </label>
                        <textarea name="partner_notes" rows="4"
                                  placeholder="Context, sensitivities, prior history with partner, accessibility needs…"
                                  style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; resize:vertical; box-sizing:border-box; border-radius:2px; outline:none; line-height:1.5; margin-bottom:8px;"></textarea>
                        <div style="font-size:11.5px; color:var(--ink-4); font-style:italic; line-height:1.5;">
                            Information that helps the partner respond appropriately. Avoid sensitive details unless protocol requires.
                        </div>
                    </div>

                    {{-- §5 CLIENT CONSENT --}}
                    <div style="padding: 22px 24px 28px;">
                        <div class="label-cap" style="font-size: 9.5px; letter-spacing: 0.09em; color: var(--ink-2); margin-bottom: 3px;">CLIENT CONSENT</div>
                        <div style="font-size: 12px; color: var(--ink-3); margin-bottom: 14px;">Required &mdash; referrals cannot be sent without explicit informed consent</div>

                        <label style="display:flex; align-items:flex-start; gap:13px; padding:15px 16px; border:1.5px solid var(--rule); background:var(--paper); cursor:pointer; border-radius:2px; transition:border-color 120ms;"
                               onmouseenter="this.style.borderColor='var(--forest)'" onmouseleave="this.style.borderColor='var(--rule)'">
                            <input type="checkbox" name="client_consent" value="1" required
                                   style="width:16px; height:16px; margin-top:3px; flex-shrink:0; accent-color:var(--forest); cursor:pointer;">
                            <div>
                                <div style="font-size:13.5px; font-weight:600; color:var(--ink); margin-bottom:5px;">Client has given informed consent for this referral</div>
                                <div style="font-size:12px; color:var(--ink-3); line-height:1.55;">
                                    Client has been told who the referral is to, what information will be shared, and what to expect. Consent is documented in their case file.
                                </div>
                            </div>
                        </label>
                    </div>

                </form>
            </div>

            {{-- Sticky footer --}}
            <div style="flex-shrink: 0; padding: 14px 24px; border-top: 1px solid var(--rule); display: flex; justify-content: flex-end; gap: 10px; background: var(--parchment);">
                <button type="button" data-bs-dismiss="modal" class="btn-ghost">Cancel</button>
                <button type="submit" form="rfForm" class="btn-primary" style="display:inline-flex; align-items:center; gap:7px;">
                    <x-lucide-share-2 style="width:13px; height:13px;" /> Send referral
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ── Partner directory client-side filter ──────────────────────
function pdFilter(category, btn) {
    // Update pill styles
    document.querySelectorAll('[data-pd-filter]').forEach(b => {
        const isActive = b.dataset.pdFilter === category;
        b.style.background = isActive ? 'var(--forest)' : 'transparent';
        b.style.color      = isActive ? 'var(--cream)' : 'var(--ink-2)';
        b.style.borderColor = isActive ? 'var(--forest)' : 'var(--rule)';
        const badge = b.querySelector('[data-pd-count]');
        if (badge) {
            badge.style.background = isActive ? 'rgba(255,255,255,.15)' : 'var(--paper)';
            badge.style.color      = isActive ? 'var(--cream)' : 'var(--ink-3)';
        }
    });

    // Show/hide cards
    let visible = 0;
    document.querySelectorAll('.pd-card').forEach(card => {
        const show = category === 'all' || card.dataset.category === category;
        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    // Update subtitle count
    const countEl = document.getElementById('pdCount');
    if (countEl) countEl.textContent = visible;
}

// Apply initial active state on load
document.addEventListener('DOMContentLoaded', () => {
    const initial = '{{ $currentCategory }}';
    const btn = document.querySelector('[data-pd-filter="' + initial + '"]');
    if (btn) pdFilter(initial, btn);
    else pdFilter('all', document.querySelector('[data-pd-filter="all"]'));
});

// ── Partners data for referral modal ─────────────────────────
const rfPartners = @json($partners->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'category' => $p->category])->values());

function rfFilterPartners(category) {
    const sel = document.getElementById('rfPartnerSelect');
    sel.innerHTML = '';
    if (!category) {
        sel.innerHTML = '<option value="">Pick a category first</option>';
        sel.style.color = 'var(--ink-3)';
        return;
    }
    const matches = rfPartners.filter(p => p.category === category);
    if (matches.length === 0) {
        sel.innerHTML = '<option value="">No partners in this category</option>';
        return;
    }
    sel.innerHTML = '<option value="">Select organisation…</option>';
    sel.style.color = 'var(--ink)';
    matches.forEach(p => {
        const o = document.createElement('option');
        o.value = p.id;
        o.textContent = p.name;
        sel.appendChild(o);
    });
}

// ── Referral tracker client-side filter ──────────────────────
function rtFilter(status, btn) {
    document.querySelectorAll('[data-rt-filter]').forEach(b => {
        const isActive = b.dataset.rtFilter === status;
        b.style.background  = isActive ? 'var(--forest)' : 'transparent';
        b.style.color       = isActive ? 'var(--cream)'  : 'var(--ink-2)';
        b.style.borderColor = isActive ? 'var(--forest)' : 'var(--rule)';
        const badge = b.querySelector('span');
        if (badge) {
            badge.style.background = isActive ? 'rgba(255,255,255,.15)' : 'var(--paper)';
            badge.style.color      = isActive ? 'var(--cream)' : 'var(--ink-3)';
        }
    });
    document.querySelectorAll('[data-rt-row]').forEach(row => {
        const show = status === 'all' || row.dataset.rtRow === status;
        row.style.display = show ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const rtBtn = document.querySelector('[data-rt-filter="active"]');
    if (rtBtn) rtFilter('active', rtBtn);
});

function rfSetUrgency(val) {
    ['Low','Med','High'].forEach(u => {
        const btn = document.getElementById('rfUrg' + u);
        if (!btn) return;
        if (u === val) {
            btn.style.background = 'var(--ochre)';
            btn.style.color = '#fff';
        } else {
            btn.style.background = 'var(--parchment)';
            btn.style.color = 'var(--ink-2)';
        }
    });
    document.getElementById('rfUrgencyVal').value = val;
}
</script>
</x-layouts.app>
