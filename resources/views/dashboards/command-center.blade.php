<x-layouts.app>
@php
    $dispositionColors = ['adr' => '#b87319', 'litigation' => '#8a2e1d', 'advice-only' => '#6b6a65', 'referred' => '#4a7a5c'];
    $dispositionLabels = ['adr' => 'ADR', 'litigation' => 'Litigation', 'advice-only' => 'Advice Only', 'referred' => 'Referred'];
    $referralBarColors = ['#163029','#4a7a5c','#b87319','#8a2e1d','#7e57c2','#6b6a65','#d9a05b','#3e6b53'];
@endphp

<div class="md-canvas" style="padding: 24px 34px 64px;">

    @php
        $filterPeriods   = ['All time','Today','Last 7 days','Last 30 days','Last 90 days','Year to date'];
        $filterProvinces = ['All Provinces','Sindh'];
        $filterHubs      = array_merge(['All Hubs'], $hubDist->pluck('name')->toArray());
        $filterServices  = ['All Services','Legal Advice / Consultation','Court Representation','Mediation','ADR / Dispute Resolution Support','Government Department / Public Institution','Civil Society / NGO / CSO / NPO','Other'];
        $af = $activeFilters ?? ['period' => 'All time', 'hub' => 'All Hubs', 'service' => 'All Services'];
    @endphp

    {{-- Filter dropdown styles --}}
    <style>
        .filter-btn {
            display: flex; align-items: center; gap: 7px;
            font-size: 12px; padding: 6px 10px;
            background: var(--paper); border: 1px solid var(--rule);
            border-radius: 5px; color: var(--ink-2);
            cursor: pointer; font-family: inherit; white-space: nowrap;
            box-shadow: 0 1px 2px rgba(0,0,0,.06);
            transition: border-color .15s, box-shadow .15s, background .15s;
        }
        .filter-btn:hover { background: var(--parchment-2); border-color: var(--ink-3); box-shadow: 0 1px 4px rgba(0,0,0,.10); }
        .filter-btn.show  { background: var(--parchment-2); border-color: var(--ink-2); box-shadow: 0 0 0 2px rgba(44,62,48,.10); }
        .filter-btn::after { display: none !important; }
        .filter-chevron { transition: transform .15s ease; flex-shrink: 0; width: 11px; height: 11px; color: var(--ink-4); }
        .dropdown.show .filter-chevron { transform: rotate(180deg); }
        .filter-menu {
            padding: 4px 0; border: 1px solid var(--rule); border-radius: 6px;
            background: var(--paper); box-shadow: 0 6px 20px rgba(0,0,0,.12);
        }
        .filter-opt {
            width: 100%; padding: 7px 14px; font-size: 12.5px;
            font-family: inherit; border: none; text-align: left; cursor: pointer;
            background: transparent; color: var(--ink-2); transition: background .1s;
            display: block;
        }
        .filter-opt:hover { background: var(--parchment-2); color: var(--ink); }
        .filter-opt.active { background: var(--parchment-2); color: var(--ink); font-weight: 500; }
        .cc-trend-badge {
            display: inline-flex; align-items: center; gap: 3px;
            font-size: 11px; font-weight: 600; padding: 2px 7px;
            border-radius: 10px; line-height: 1.3;
        }
        .cc-trend-up { background: rgba(74,122,92,0.12); color: #2e6b45; }
        .cc-trend-down { background: rgba(138,46,29,0.10); color: #8a2e1d; }
        .cc-trend-neutral { background: rgba(107,106,101,0.10); color: #6b6a65; }
        .cc-alert-card {
            padding: 12px 14px; border-radius: 4px; background: var(--paper);
            border: 1px solid var(--rule-2); border-left: 3px solid;
            transition: box-shadow .15s;
        }
        .cc-alert-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,.06); }
        .cc-ref-bar {
            height: 22px; border-radius: 3px; min-width: 4px;
            transition: width .5s ease;
        }
    </style>

    {{-- ═══ Breadcrumb + Filter Bar ═══ --}}
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:22px; padding-bottom:16px; border-bottom:1px solid var(--rule-2);">

        <div style="display:flex; align-items:center; gap:6px;">
            <span class="mono" style="font-size:9.5px; color:var(--ink-4); letter-spacing:0.08em; text-transform:uppercase;">Legal Aid Society</span>
            <x-lucide-chevron-right style="width:10px; height:10px; color:var(--ink-4);" />
            <span class="mono" style="font-size:9.5px; color:var(--ink-3); letter-spacing:0.08em; text-transform:uppercase; font-weight:500;">Justice Hub CMS</span>
        </div>

        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">

            {{-- ── Period ── --}}
            <div class="dropdown">
                <button class="filter-btn" id="dd-period" data-bs-toggle="dropdown" aria-expanded="false">
                    <x-lucide-calendar style="width:12px; height:12px; color:var(--ink-4); flex-shrink:0;" />
                    <span id="cc-label-period">{{ $af['period'] }}</span>
                    <x-lucide-chevron-down class="filter-chevron" />
                </button>
                <ul class="dropdown-menu filter-menu" style="min-width:165px;" aria-labelledby="dd-period">
                    @foreach($filterPeriods as $opt)
                    <li>
                        <button class="filter-opt{{ $opt === $af['period'] ? ' active' : '' }}"
                            onclick="ccSetFilter('period', {{ json_encode($opt) }}, this)">{{ $opt }}</button>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- ── Province ── --}}
            <div class="dropdown">
                <button class="filter-btn" id="dd-province" data-bs-toggle="dropdown" aria-expanded="false">
                    <x-lucide-map-pin style="width:12px; height:12px; color:var(--ink-4); flex-shrink:0;" />
                    <span id="cc-label-province">All Provinces</span>
                    <x-lucide-chevron-down class="filter-chevron" />
                </button>
                <ul class="dropdown-menu filter-menu" style="min-width:155px;" aria-labelledby="dd-province">
                    @foreach($filterProvinces as $opt)
                    <li>
                        <button class="filter-opt{{ $opt === 'All Provinces' ? ' active' : '' }}"
                            onclick="ccSetFilter('province', {{ json_encode($opt) }}, this)">{{ $opt }}</button>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- ── Hub (multiselect) — hidden for hub-scoped users ── --}}
            @php $activeHubs = is_array($af['hub']) ? $af['hub'] : ($af['hub'] === 'All Hubs' ? [] : [$af['hub']]); @endphp
            @if(auth()->user()->canSeeAllHubs())
            <div class="dropdown">
                <button class="filter-btn" id="dd-hub" data-bs-toggle="dropdown" aria-expanded="false">
                    <x-lucide-building-2 style="width:12px; height:12px; color:var(--ink-4); flex-shrink:0;" />
                    <span id="cc-label-hub">{{ count($activeHubs) ? count($activeHubs) . ' Hub' . (count($activeHubs) > 1 ? 's' : '') : 'All Hubs' }}</span>
                    <x-lucide-chevron-down class="filter-chevron" />
                </button>
                <ul class="dropdown-menu filter-menu" style="min-width:220px; padding:6px 0;" aria-labelledby="dd-hub" onclick="event.stopPropagation()">
                    <li style="padding:2px 12px 6px; border-bottom:1px solid var(--rule-2); margin-bottom:4px;">
                        <button class="filter-opt" style="font-size:11px; font-weight:600; width:100%;"
                                onclick="ccMultiToggleAll('hub')">Select All / None</button>
                    </li>
                    @foreach($hubDist->pluck('name') as $hubName)
                    <li style="padding:0 12px;">
                        <label style="display:flex; align-items:center; gap:8px; padding:5px 0; cursor:pointer; font-size:12.5px; color:var(--ink);">
                            <input type="checkbox" data-multi="hub" value="{{ $hubName }}"
                                   {{ in_array($hubName, $activeHubs) ? 'checked' : '' }}
                                   onchange="ccMultiChanged('hub')"
                                   style="accent-color:var(--forest); width:14px; height:14px;">
                            {{ $hubName }}
                        </label>
                    </li>
                    @endforeach
                    <li style="padding:6px 12px 4px; border-top:1px solid var(--rule-2); margin-top:4px;">
                        <button onclick="ccApplyMulti('hub')" style="width:100%; padding:6px 0; background:var(--forest); color:var(--cream); border:none; font-size:11.5px; font-weight:600; cursor:pointer; font-family:inherit;">Apply</button>
                    </li>
                </ul>
            </div>
            @endif

            {{-- ── Service (multiselect) ── --}}
            @php $activeServices = is_array($af['service']) ? $af['service'] : ($af['service'] === 'All Services' ? [] : [$af['service']]); @endphp
            <div class="dropdown">
                <button class="filter-btn" id="dd-service" data-bs-toggle="dropdown" aria-expanded="false">
                    <x-lucide-briefcase style="width:12px; height:12px; color:var(--ink-4); flex-shrink:0;" />
                    <span id="cc-label-service">{{ count($activeServices) ? count($activeServices) . ' Service' . (count($activeServices) > 1 ? 's' : '') : 'All Services' }}</span>
                    <x-lucide-chevron-down class="filter-chevron" />
                </button>
                <ul class="dropdown-menu filter-menu" style="min-width:260px; padding:6px 0;" aria-labelledby="dd-service" onclick="event.stopPropagation()">
                    <li style="padding:2px 12px 6px; border-bottom:1px solid var(--rule-2); margin-bottom:4px;">
                        <button class="filter-opt" style="font-size:11px; font-weight:600; width:100%;"
                                onclick="ccMultiToggleAll('service')">Select All / None</button>
                    </li>
                    @foreach(array_slice($filterServices, 1) as $svc)
                    <li style="padding:0 12px;">
                        <label style="display:flex; align-items:center; gap:8px; padding:5px 0; cursor:pointer; font-size:12.5px; color:var(--ink);">
                            <input type="checkbox" data-multi="service" value="{{ $svc }}"
                                   {{ in_array($svc, $activeServices) ? 'checked' : '' }}
                                   onchange="ccMultiChanged('service')"
                                   style="accent-color:var(--forest); width:14px; height:14px;">
                            {{ $svc }}
                        </label>
                    </li>
                    @endforeach
                    <li style="padding:6px 12px 4px; border-top:1px solid var(--rule-2); margin-top:4px;">
                        <button onclick="ccApplyMulti('service')" style="width:100%; padding:6px 0; background:var(--forest); color:var(--cream); border:none; font-size:11.5px; font-weight:600; cursor:pointer; font-family:inherit;">Apply</button>
                    </li>
                </ul>
            </div>

            {{-- Reset --}}
            <button onclick="ccResetFilters()"
                style="display:flex; align-items:center; gap:5px; font-size:11.5px; color:var(--ink-4); background:none; border:none; cursor:pointer; font-family:inherit; padding:5px 2px;"
                onmouseenter="this.style.color='var(--ink-2)'" onmouseleave="this.style.color='var(--ink-4)'">
                <x-lucide-rotate-ccw style="width:11px; height:11px;" />
                Reset filters
            </button>

            {{-- Download --}}
            <a href="{{ route('impact.index') }}"
                style="display:flex; align-items:center; gap:6px; font-size:11.5px; padding:5px 12px; background:var(--forest); color:var(--cream); text-decoration:none; font-weight:500; letter-spacing:0.01em;">
                <x-lucide-download style="width:12px; height:12px;" />
                Download Report
            </a>
        </div>
    </div>

    {{-- ═══ Hero Greeting ═══ --}}
    <div style="margin-bottom:6px;">
        <div class="label-cap" style="font-size:9.5px; margin-bottom:8px; color:var(--ink-4);">Justice Hub · Command Centre</div>
        <h1 class="serif" style="font-size:38px; font-weight:400; letter-spacing:-0.02em; margin:0 0 6px 0; line-height:1.1;">
            {{ $greeting }}, {{ auth()->user()->name }}
        </h1>
        <p style="margin:0 0 6px; font-size:14px; color:var(--ink-3); line-height:1.5;">
            Real-time overview of Justice Hubs across Pakistan
        </p>
    </div>

    {{-- Filter status bar --}}
    <div style="display:flex; align-items:center; gap:8px; margin-bottom:24px; font-size:11.5px; color:var(--ink-4);">
        <span>Showing:
            <span id="cc-status-province">All Provinces</span> &middot;
            <span id="cc-status-hub">{{ is_array($af['hub']) && count($af['hub']) ? implode(', ', $af['hub']) : 'All Hubs' }}</span> &middot;
            <span id="cc-status-service">{{ is_array($af['service']) && count($af['service']) ? implode(', ', $af['service']) : 'All Services' }}</span> &middot;
            <span id="cc-status-period">{{ $af['period'] }}</span>
        </span>
        <span style="color:var(--rule);">|</span>
        <span>Last updated: {{ now()->format('d M Y, H:i') }}</span>
        <span style="display:inline-flex; align-items:center; gap:4px; color:var(--moss);">
            <span style="width:6px; height:6px; border-radius:50%; background:var(--moss); display:inline-block; animation:pulse-ring 2s infinite;"></span>
            Live
        </span>
    </div>


    {{-- ═══════════════════════════════════════════════════════════════
         ROW 1 — KPI STRIP (compact, with trend badges)
    ═══════════════════════════════════════════════════════════════ --}}
    @php
        $totalPrev     = max($m['total_cases'] - $casesLast7, 1);
        $resolvedPrev  = max($m['closed_cases'] - $resolvedLast7, 1);
        $totalTrendPct = round(($casesLast7 / $totalPrev) * 100, 1);
        $resTrendPct   = round(($resolvedLast7 / $resolvedPrev) * 100, 1);
    @endphp
    <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:12px; margin-bottom:20px;">

        {{-- Total Cases --}}
        <div class="card" style="padding:16px 18px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                <div class="label-cap" style="font-size:9px;">Total Cases</div>
                <div style="width:28px; height:28px; background:rgba(22,48,41,0.06); display:flex; align-items:center; justify-content:center; border-radius:6px;">
                    <x-lucide-folder style="width:14px; height:14px; color:var(--forest);" />
                </div>
            </div>
            <div style="display:flex; align-items:baseline; gap:8px;">
                <span class="serif" style="font-size:30px; font-weight:400; letter-spacing:-0.02em; color:var(--ink); line-height:1;">{{ number_format($m['total_cases']) }}</span>
                <span class="cc-trend-badge cc-trend-up">
                    <x-lucide-trending-up style="width:10px; height:10px;" />
                    +{{ $totalTrendPct }}%
                </span>
            </div>
            <div style="font-size:11px; color:var(--ink-4); margin-top:5px;">{{ $casesLast7 }} new this week</div>
        </div>

        {{-- Cases Resolved --}}
        <div class="card" style="padding:16px 18px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                <div class="label-cap" style="font-size:9px;">Cases Resolved</div>
                <div style="width:28px; height:28px; background:rgba(74,122,92,0.08); display:flex; align-items:center; justify-content:center; border-radius:6px;">
                    <x-lucide-check-circle-2 style="width:14px; height:14px; color:var(--moss);" />
                </div>
            </div>
            <div style="display:flex; align-items:baseline; gap:8px;">
                <span class="serif" style="font-size:30px; font-weight:400; letter-spacing:-0.02em; color:var(--ink); line-height:1;">{{ number_format($m['closed_cases']) }}</span>
                <span class="cc-trend-badge cc-trend-up">
                    <x-lucide-trending-up style="width:10px; height:10px;" />
                    +{{ $resTrendPct }}%
                </span>
            </div>
            <div style="font-size:11px; color:var(--ink-4); margin-top:5px;">{{ $resolvedLast7 }} this week</div>
        </div>

        {{-- Active Cases --}}
        <div class="card" style="padding:16px 18px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                <div class="label-cap" style="font-size:9px;">Active Cases</div>
                <div style="width:28px; height:28px; background:rgba(184,115,25,0.08); display:flex; align-items:center; justify-content:center; border-radius:6px;">
                    <x-lucide-activity style="width:14px; height:14px; color:var(--ochre);" />
                </div>
            </div>
            <div style="display:flex; align-items:baseline; gap:8px;">
                <span class="serif" style="font-size:30px; font-weight:400; letter-spacing:-0.02em; color:var(--ink); line-height:1;">{{ number_format($m['active_cases']) }}</span>
                <span class="cc-trend-badge cc-trend-neutral">
                    <x-lucide-minus style="width:10px; height:10px;" />
                    {{ $m['pending_approval'] }} pending
                </span>
            </div>
            <div style="font-size:11px; color:var(--ink-4); margin-top:5px;">{{ $m['pending_approval'] }} pending review</div>
        </div>

        {{-- High Risk --}}
        <div class="card" style="padding:16px 18px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                <div class="label-cap" style="font-size:9px;">High Risk</div>
                <div style="width:28px; height:28px; background:rgba(138,46,29,0.08); display:flex; align-items:center; justify-content:center; border-radius:6px;">
                    <x-lucide-alert-triangle style="width:14px; height:14px; color:var(--burgundy);" />
                </div>
            </div>
            <div style="display:flex; align-items:baseline; gap:8px;">
                <span class="serif" style="font-size:30px; font-weight:400; letter-spacing:-0.02em; color:{{ $highRisk > 0 ? 'var(--burgundy)' : 'var(--ink)' }}; line-height:1;">{{ $highRisk }}</span>
                @if($highRisk > 0)
                <span class="cc-trend-badge cc-trend-down">
                    <x-lucide-alert-circle style="width:10px; height:10px;" />
                    needs attention
                </span>
                @else
                <span class="cc-trend-badge cc-trend-up">clear</span>
                @endif
            </div>
            <div style="font-size:11px; color:var(--ink-4); margin-top:5px;">Flagged for escalation</div>
        </div>

        {{-- SLA Compliance --}}
        <div class="card" style="padding:16px 18px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                <div class="label-cap" style="font-size:9px;">SLA Compliance</div>
                <div style="width:28px; height:28px; background:{{ $m['sla_compliance'] >= 90 ? 'rgba(74,122,92,0.08)' : 'rgba(184,115,25,0.08)' }}; display:flex; align-items:center; justify-content:center; border-radius:6px;">
                    <x-lucide-clock style="width:14px; height:14px; color:{{ $m['sla_compliance'] >= 90 ? 'var(--moss)' : 'var(--ochre)' }};" />
                </div>
            </div>
            <div style="display:flex; align-items:baseline; gap:8px;">
                <span class="serif" style="font-size:30px; font-weight:400; letter-spacing:-0.02em; color:{{ $m['sla_compliance'] >= 90 ? 'var(--moss)' : 'var(--ochre)' }}; line-height:1;">{{ $m['sla_compliance'] }}%</span>
                @if($m['sla_breach'] > 0)
                <span class="cc-trend-badge cc-trend-down">
                    <x-lucide-trending-down style="width:10px; height:10px;" />
                    {{ $m['sla_breach'] }} breaches
                </span>
                @else
                <span class="cc-trend-badge cc-trend-up">
                    <x-lucide-check style="width:10px; height:10px;" />
                    on target
                </span>
                @endif
            </div>
            <div style="font-size:11px; color:var(--ink-4); margin-top:5px;">
                <div style="height:3px; background:var(--rule-2); border-radius:2px; overflow:hidden; margin-top:6px;">
                    <div style="height:100%; width:{{ $m['sla_compliance'] }}%; background:{{ $m['sla_compliance'] >= 90 ? 'var(--moss)' : 'var(--ochre)' }}; border-radius:2px; transition:width .4s;"></div>
                </div>
            </div>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════════════════════
         ROW 1B — Pakistan Hub Map
    ═══════════════════════════════════════════════════════════════ --}}
    @php
        $hubCoords = [
            'JH-KHI-01' => [24.8607, 67.0011],
            'JH-HYD-01' => [25.3960, 68.3578],
            'JH-DAD-01' => [26.7319, 67.7759],
            'JH-SAN-01' => [26.0468, 68.9438],
            'JH-SBA-01' => [26.2442, 68.4101],
            'JH-LAR-01' => [27.5580, 68.2116],
            'JH-SUK-01' => [27.7052, 68.8574],
            'JH-ISB-01' => [33.6844, 73.0479],
        ];
        $mapHubs = $hubDist->map(function($h) use ($hubCoords) {
            $h['lat'] = $hubCoords[$h['id']][0] ?? null;
            $h['lng'] = $hubCoords[$h['id']][1] ?? null;
            return $h;
        })->filter(fn($h) => $h['lat'] !== null)->values();
    @endphp

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="card" style="padding:0; overflow:hidden; margin-bottom:20px;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 20px; border-bottom:1px solid var(--rule);">
            <div>
                <div class="label-cap" style="font-size:9px;">Programme Coverage</div>
                <div style="font-size:11px; color:var(--ink-4); margin-top:2px;">Justice Hub locations across Pakistan · click a pin for details</div>
            </div>
            <div style="display:flex; align-items:center; gap:14px;">
                <div style="display:flex; align-items:center; gap:5px; font-size:11px; color:var(--ink-3);">
                    <span style="width:10px; height:10px; border-radius:50%; background:var(--forest); display:inline-block;"></span> Active hub
                </div>
                <span class="mono" style="font-size:10px; color:var(--ink-4);">{{ $mapHubs->count() }} hubs · {{ number_format($m['total_cases']) }} total cases</span>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 280px;">

            {{-- Map --}}
            <div id="jhPakMap" style="height:420px; z-index:1;"></div>

            {{-- Hub sidebar --}}
            <div style="border-left:1px solid var(--rule); overflow-y:auto; max-height:420px;">
                @foreach($mapHubs as $h)
                @php
                    $barW = $m['total_cases'] > 0 ? round(($h['count'] / $m['total_cases']) * 100) : 0;
                    $dotC = $h['count'] > 100 ? 'var(--forest)' : ($h['count'] > 30 ? 'var(--ochre)' : 'var(--ink-3)');
                @endphp
                <div class="jh-hub-sidebar-row" data-lat="{{ $h['lat'] }}" data-lng="{{ $h['lng'] }}"
                    style="padding:12px 16px; border-bottom:1px solid var(--rule-2); cursor:pointer; transition:background 120ms ease;"
                    onmouseover="this.style.background='var(--parchment)'" onmouseout="this.style.background=''"
                    onclick="flyToHub({{ $h['lat'] }}, {{ $h['lng'] }}, {{ json_encode($h['name']) }})">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                        <div style="display:flex; align-items:center; gap:7px;">
                            <span style="width:8px; height:8px; border-radius:50%; background:{{ $dotC }}; display:inline-block; flex-shrink:0;"></span>
                            <span style="font-size:12.5px; font-weight:500; color:var(--ink);">{{ $h['name'] }}</span>
                        </div>
                        <span class="mono" style="font-size:12px; font-weight:600; color:var(--ink);">{{ $h['count'] }}</span>
                    </div>
                    <div style="height:3px; background:var(--rule-2); border-radius:2px; overflow:hidden;">
                        <div style="height:100%; width:{{ $barW }}%; background:{{ $h['color'] }}; border-radius:2px;"></div>
                    </div>
                    <div style="font-size:10px; color:var(--ink-4); margin-top:4px;">{{ $h['pct'] }}% of total caseload</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    (function() {
        var mapHubs = {!! $mapHubs->toJson() !!};
        var totalCases = {{ $m['total_cases'] }};

        // Init map centered on Pakistan
        var map = L.map('jhPakMap', {
            center: [28.5, 68.5],
            zoom: 5,
            zoomControl: true,
            scrollWheelZoom: false,
        });

        // Muted monochrome tile — CartoDB Positron
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://carto.com/">CARTO</a>',
            maxZoom: 18,
        }).addTo(map);

        var markers = {};

        mapHubs.forEach(function(hub) {
            if (!hub.lat || !hub.lng) return;

            // Scale radius by caseload
            var radius = Math.max(12, Math.min(28, 10 + Math.sqrt(hub.count) * 1.4));

            var pct = totalCases > 0 ? Math.round((hub.count / totalCases) * 100) : 0;
            var color = hub.count > 100 ? '#163029' : (hub.count > 30 ? '#b87319' : '#4a7a5c');

            var circle = L.circleMarker([hub.lat, hub.lng], {
                radius: radius,
                fillColor: color,
                color: '#fff',
                weight: 2.5,
                opacity: 1,
                fillOpacity: 0.88,
            }).addTo(map);

            circle.bindPopup(
                '<div style="font-family:inherit; min-width:170px;">' +
                '<div style="font-size:10px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:#6b6a65; margin-bottom:4px;">Justice Hub</div>' +
                '<div style="font-size:16px; font-weight:600; color:#163029; margin-bottom:8px;">' + hub.name + '</div>' +
                '<div style="display:flex; justify-content:space-between; font-size:12px; color:#444; margin-bottom:3px;">' +
                  '<span>Total cases</span><strong>' + hub.count + '</strong>' +
                '</div>' +
                '<div style="display:flex; justify-content:space-between; font-size:12px; color:#444;">' +
                  '<span>Share</span><strong>' + pct + '%</strong>' +
                '</div>' +
                '<div style="height:4px; background:#e8e6e0; border-radius:2px; overflow:hidden; margin-top:8px;">' +
                  '<div style="height:100%; width:' + pct + '%; background:' + color + '; border-radius:2px;"></div>' +
                '</div>' +
                '</div>',
                { maxWidth: 220 }
            );

            markers[hub.name] = circle;
        });

        window.flyToHub = function(lat, lng, name) {
            map.flyTo([lat, lng], 8, { animate: true, duration: 0.8 });
            if (markers[name]) {
                setTimeout(function() { markers[name].openPopup(); }, 900);
            }
        };
    }());
    </script>

    {{-- ═══════════════════════════════════════════════════════════════
         ROW 2 — Charts: Intake Trend, Case Type Distribution, Status
    ═══════════════════════════════════════════════════════════════ --}}
    @php
        $chartDailyActivity = [
            'labels' => $m['daily_activity']['labels'],
            'values' => $m['daily_activity']['values'],
            'color'  => '#163029',
        ];

        // Primary issue doughnut
        $piLabels = array_keys($primaryIssues);
        $piValues = array_values($primaryIssues);
        $piColors = ['#163029','#4a7a5c','#b87319','#8a2e1d','#7e57c2','#6b6a65','#d9a05b','#3e6b53'];
        $chartPrimaryIssue = [
            'labels' => array_slice($piLabels, 0, 8),
            'values' => array_slice($piValues, 0, 8),
            'colors' => array_slice($piColors, 0, min(count($piLabels), 8)),
        ];

        // Status doughnut
        $stLabels = array_keys($m['status']);
        $stValues = array_values($m['status']);
        $stColors = ['#4a7a5c','#b87319','#163029','#8a2e1d','#7e57c2','#6b6a65'];
        $chartStatusDoughnut = [
            'labels' => $stLabels,
            'values' => $stValues,
            'colors' => array_slice($stColors, 0, count($stLabels)),
        ];
    @endphp

    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; margin-bottom:20px;">

        {{-- Case Intake & Resolution Trend --}}
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <div>
                    <div class="label-cap" style="font-size:9px;">Case Intake & Resolution Trend</div>
                    <div style="font-size:11px; color:var(--ink-4); margin-top:2px;">Last 30 days daily activity</div>
                </div>
                <x-lucide-trending-up style="width:14px; height:14px; color:var(--ink-4);" />
            </div>
            <div data-chart="kpiSparkline"
                 data-chart-config='{{ json_encode($chartDailyActivity) }}'
                 style="height:180px;">
                <canvas></canvas>
            </div>
            <div style="display:flex; gap:16px; justify-content:center; margin-top:10px;">
                <div style="display:flex; align-items:center; gap:5px; font-size:11px; color:var(--ink-3);">
                    <span style="width:10px; height:3px; background:#163029; border-radius:2px; display:inline-block;"></span>
                    Daily Intakes
                </div>
            </div>
        </div>

        {{-- Case Distribution by Type --}}
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <div>
                    <div class="label-cap" style="font-size:9px;">Case Distribution by Type</div>
                    <div style="font-size:11px; color:var(--ink-4); margin-top:2px;">Primary issue breakdown</div>
                </div>
                <x-lucide-pie-chart style="width:14px; height:14px; color:var(--ink-4);" />
            </div>
            <div data-chart="serviceMixPie"
                 data-chart-config='{{ json_encode($chartPrimaryIssue) }}'
                 style="height:200px;">
                <canvas></canvas>
            </div>
        </div>

        {{-- Cases by Status --}}
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <div>
                    <div class="label-cap" style="font-size:9px;">Cases by Status</div>
                    <div style="font-size:11px; color:var(--ink-4); margin-top:2px;">Current status distribution</div>
                </div>
                <x-lucide-bar-chart-3 style="width:14px; height:14px; color:var(--ink-4);" />
            </div>
            <div data-chart="serviceMixPie"
                 data-chart-config='{{ json_encode($chartStatusDoughnut) }}'
                 style="height:200px;">
                <canvas></canvas>
            </div>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════════════════════
         ROW 3 — Hub Performance, Referral Sources, Alerts & Escalations
    ═══════════════════════════════════════════════════════════════ --}}
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; margin-bottom:20px;">

        {{-- Hub Performance Table --}}
        <div class="card" style="padding:18px 20px; overflow:hidden;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                <div class="label-cap" style="font-size:9px;">Hub Performance</div>
                <span class="mono" style="font-size:10px; color:var(--ink-4);">{{ count($m['hub_performance']) }} hubs</span>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:12px;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--rule);">
                            <th style="text-align:left; padding:7px 8px; font-size:9px; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; color:var(--ink-3);">Hub</th>
                            <th style="text-align:right; padding:7px 6px; font-size:9px; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; color:var(--ink-3);">Total</th>
                            <th style="text-align:right; padding:7px 6px; font-size:9px; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; color:var(--ink-3);">Active</th>
                            <th style="text-align:right; padding:7px 6px; font-size:9px; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; color:var(--ink-3);">Closed</th>
                            <th style="text-align:right; padding:7px 8px; font-size:9px; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; color:var(--ink-3);">SLA %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($m['hub_performance'] as $hub)
                        <tr style="border-bottom:1px solid var(--rule-2);" class="tr-hover">
                            <td style="padding:8px;">
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <div style="width:6px; height:6px; border-radius:50%; background:{{ $hub['sla_pct'] >= 90 ? 'var(--moss)' : ($hub['sla_pct'] >= 70 ? 'var(--ochre)' : 'var(--burgundy)') }}; flex-shrink:0;"></div>
                                    <span style="font-weight:500; color:var(--ink); font-size:11.5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:110px;">{{ $hub['name'] }}</span>
                                </div>
                            </td>
                            <td style="padding:8px 6px; text-align:right;" class="mono">{{ $hub['total'] }}</td>
                            <td style="padding:8px 6px; text-align:right; color:var(--forest);" class="mono">{{ $hub['active'] }}</td>
                            <td style="padding:8px 6px; text-align:right; color:var(--ink-3);" class="mono">{{ $hub['closed'] }}</td>
                            <td style="padding:8px;">
                                <div style="display:flex; align-items:center; gap:6px; justify-content:flex-end;">
                                    <span class="mono" style="font-size:11px; font-weight:500; color:{{ $hub['sla_pct'] >= 90 ? 'var(--moss)' : ($hub['sla_pct'] >= 70 ? 'var(--ochre)' : 'var(--burgundy)') }};">{{ $hub['sla_pct'] }}%</span>
                                    <div style="width:40px; height:4px; background:var(--rule-2); border-radius:2px; overflow:hidden;">
                                        <div style="height:100%; width:{{ $hub['sla_pct'] }}%; background:{{ $hub['sla_pct'] >= 90 ? 'var(--moss)' : ($hub['sla_pct'] >= 70 ? 'var(--ochre)' : 'var(--burgundy)') }}; border-radius:2px;"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Referral Sources --}}
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <div class="label-cap" style="font-size:9px;">Referral Sources</div>
                <x-lucide-share-2 style="width:13px; height:13px; color:var(--ink-4);" />
            </div>
            @php $refMax = count($referralSources) > 0 ? max(array_values($referralSources)) : 1; @endphp
            <div style="display:flex; flex-direction:column; gap:8px;">
                @foreach($referralSources as $source => $count)
                @php $refIdx = $loop->index % count($referralBarColors); @endphp
                <div>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:3px;">
                        <span style="font-size:11.5px; color:var(--ink-2); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:180px;">{{ $source }}</span>
                        <span class="mono" style="font-size:11px; font-weight:500; color:var(--ink); flex-shrink:0; margin-left:8px;">{{ $count }}</span>
                    </div>
                    <div style="height:6px; background:var(--rule-2); border-radius:3px; overflow:hidden;">
                        <div class="cc-ref-bar" style="width:{{ round(($count / $refMax) * 100) }}%; background:{{ $referralBarColors[$refIdx] }};"></div>
                    </div>
                </div>
                @endforeach
                @if(count($referralSources) === 0)
                <div style="text-align:center; padding:20px 0; color:var(--ink-4); font-size:12px;">No referral data available</div>
                @endif
            </div>
        </div>

        {{-- Alerts & Escalations --}}
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <div class="label-cap" style="font-size:9px;">Alerts & Escalations</div>
                <x-lucide-bell style="width:13px; height:13px; color:var(--ink-4);" />
            </div>
            <div style="display:flex; flex-direction:column; gap:10px;">

                {{-- High Risk alert --}}
                <div class="cc-alert-card" style="border-left-color:var(--burgundy);">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <x-lucide-alert-triangle style="width:14px; height:14px; color:var(--burgundy);" />
                            <span style="font-size:12px; font-weight:500; color:var(--ink);">High Risk Cases</span>
                        </div>
                        <span class="serif" style="font-size:20px; font-weight:500; color:var(--burgundy);">{{ $highRisk }}</span>
                    </div>
                </div>

                {{-- SLA Breaches alert --}}
                <div class="cc-alert-card" style="border-left-color:var(--ochre);">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <x-lucide-clock style="width:14px; height:14px; color:var(--ochre);" />
                            <span style="font-size:12px; font-weight:500; color:var(--ink);">SLA Breaches</span>
                        </div>
                        <span class="serif" style="font-size:20px; font-weight:500; color:var(--ochre);">{{ $m['sla_breach'] }}</span>
                    </div>
                </div>

                {{-- Pending Approval alert --}}
                <div class="cc-alert-card" style="border-left-color:var(--forest);">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <x-lucide-hourglass style="width:14px; height:14px; color:var(--forest);" />
                            <span style="font-size:12px; font-weight:500; color:var(--ink);">Pending Approval</span>
                        </div>
                        <span class="serif" style="font-size:20px; font-weight:500; color:var(--forest);">{{ $m['pending_approval'] }}</span>
                    </div>
                </div>

                {{-- Complaints Open alert --}}
                <div class="cc-alert-card" style="border-left-color:#7e57c2;">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <x-lucide-message-circle style="width:14px; height:14px; color:#7e57c2;" />
                            <span style="font-size:12px; font-weight:500; color:var(--ink);">Complaints Open</span>
                        </div>
                        <span class="serif" style="font-size:20px; font-weight:500; color:#7e57c2;">{{ $m['complaints_open'] }}</span>
                    </div>
                </div>

            </div>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════════════════════
         ROW 4 — Vulnerability, Gender Split, Quick Stats
    ═══════════════════════════════════════════════════════════════ --}}
    @php
        $gLabels = array_keys($m['gender_split']['counts']);
        $gValues = array_values($m['gender_split']['counts']);
        $gColors = array_slice(['#8a2e1d', '#163029', '#b87319', '#6b6a65'], 0, count($gLabels));
        $chartGender = [
            'labels' => $gLabels,
            'values' => $gValues,
            'colors' => $gColors,
        ];
    @endphp

    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; margin-bottom:20px;">

        {{-- Vulnerability & Safeguarding --}}
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <div class="label-cap" style="font-size:9px;">Vulnerability & Safeguarding</div>
                <x-lucide-shield style="width:13px; height:13px; color:var(--ink-4);" />
            </div>
            <div style="display:flex; flex-direction:column; gap:10px;">
                @foreach([
                    ['label' => 'GBV',        'value' => $m['vulnerability']['gbv'],        'icon' => 'shield-alert',     'color' => 'var(--burgundy)', 'bg' => 'rgba(138,46,29,0.08)'],
                    ['label' => 'Child',       'value' => $m['vulnerability']['child'],       'icon' => 'baby',             'color' => 'var(--ochre)',    'bg' => 'rgba(184,115,25,0.08)'],
                    ['label' => 'Minority',    'value' => $m['vulnerability']['minority'],    'icon' => 'flag',             'color' => 'var(--ochre)',    'bg' => 'rgba(184,115,25,0.08)'],
                    ['label' => 'Disability',  'value' => $m['vulnerability']['disability'],  'icon' => 'heart-handshake',  'color' => '#7e57c2',         'bg' => 'rgba(126,87,194,0.08)'],
                    ['label' => 'Underserved', 'value' => $m['vulnerability']['underserved'], 'icon' => 'users',            'color' => 'var(--moss)',     'bg' => 'rgba(74,122,92,0.08)'],
                ] as $v)
                <div style="display:flex; align-items:center; gap:10px; padding:8px 10px; background:{{ $v['bg'] }}; border-radius:6px;">
                    <div style="width:30px; height:30px; background:{{ $v['bg'] }}; border:1px solid {{ $v['color'] }}20; display:flex; align-items:center; justify-content:center; border-radius:6px; flex-shrink:0;">
                        <x-dynamic-component :component="'lucide-' . $v['icon']" style="width:14px; height:14px; color:{{ $v['color'] }};" />
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:11.5px; color:var(--ink-2);">{{ $v['label'] }}</div>
                    </div>
                    <span class="serif" style="font-size:20px; font-weight:500; color:{{ $v['color'] }};">{{ $v['value'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Gender Split --}}
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <div class="label-cap" style="font-size:9px;">Gender Split</div>
                <x-lucide-users style="width:13px; height:13px; color:var(--ink-4);" />
            </div>
            <div data-chart="serviceMixPie"
                 data-chart-config='{{ json_encode($chartGender) }}'
                 style="height:160px;">
                <canvas></canvas>
            </div>
            <div style="display:flex; gap:14px; justify-content:center; margin-top:10px; flex-wrap:wrap;">
                @foreach($m['gender_split']['pct'] as $gender => $pct)
                <div style="text-align:center;">
                    <div class="serif" style="font-size:20px; font-weight:500; color:var(--ink);">{{ $pct }}%</div>
                    <div style="font-size:10px; color:var(--ink-3); margin-top:2px;">{{ $gender }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <div class="label-cap" style="font-size:9px;">Quick Stats</div>
                <x-lucide-zap style="width:13px; height:13px; color:var(--ink-4);" />
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">

                {{-- Satisfaction --}}
                <div style="padding:14px 12px; background:rgba(74,122,92,0.05); border:1px solid rgba(74,122,92,0.12); border-radius:6px; text-align:center;">
                    <x-lucide-star style="width:16px; height:16px; color:var(--moss); margin:0 auto 6px;" />
                    <div class="serif" style="font-size:24px; font-weight:500; color:var(--moss);">
                        {{ $m['satisfaction_avg'] > 0 ? $m['satisfaction_avg'] . '/5' : '—' }}
                    </div>
                    <div style="font-size:10px; color:var(--ink-3); margin-top:3px;">Satisfaction</div>
                </div>

                {{-- Outreach Sessions --}}
                <div style="padding:14px 12px; background:rgba(22,48,41,0.04); border:1px solid rgba(22,48,41,0.10); border-radius:6px; text-align:center;">
                    <x-lucide-megaphone style="width:16px; height:16px; color:var(--forest); margin:0 auto 6px;" />
                    <div class="serif" style="font-size:24px; font-weight:500; color:var(--forest);">{{ $m['outreach']['sessions'] }}</div>
                    <div style="font-size:10px; color:var(--ink-3); margin-top:3px;">Outreach Sessions</div>
                </div>

                {{-- Participants --}}
                <div style="padding:14px 12px; background:rgba(184,115,25,0.05); border:1px solid rgba(184,115,25,0.12); border-radius:6px; text-align:center;">
                    <x-lucide-user-check style="width:16px; height:16px; color:var(--ochre); margin:0 auto 6px;" />
                    <div class="serif" style="font-size:24px; font-weight:500; color:var(--ochre);">{{ number_format($m['outreach']['participants']) }}</div>
                    <div style="font-size:10px; color:var(--ink-3); margin-top:3px;">Participants</div>
                </div>

                {{-- Cost per Case --}}
                <div style="padding:14px 12px; background:rgba(138,46,29,0.04); border:1px solid rgba(138,46,29,0.10); border-radius:6px; text-align:center;">
                    <x-lucide-banknote style="width:16px; height:16px; color:var(--burgundy); margin:0 auto 6px;" />
                    <div class="serif" style="font-size:24px; font-weight:500; color:var(--burgundy);">{{ number_format($m['cost_per_case']) }}</div>
                    <div style="font-size:10px; color:var(--ink-3); margin-top:3px;">Cost / Case (PKR)</div>
                </div>

            </div>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════════════════════
         ROW 5 — Geographic Distribution (compact) + Service Mix + Age
    ═══════════════════════════════════════════════════════════════ --}}
    @php
        $smLabels = array_keys($m['service_mix']);
        $smValues = array_values($m['service_mix']);
        $smColors = ['#163029','#4a7a5c','#b87319','#8a2e1d','#6b6a65','#d9a05b'];
        $chartServiceMix = [
            'labels' => array_slice($smLabels, 0, 6),
            'values' => array_slice($smValues, 0, 6),
            'colors' => $smColors,
        ];

        $dispLabels = array_map(fn($k) => $dispositionLabels[$k] ?? ucfirst($k), array_keys($m['disposition']));
        $dispValues = array_values($m['disposition']);
        $dispColors = array_map(fn($k) => $dispositionColors[$k] ?? '#6b6a65', array_keys($m['disposition']));
        $chartDisposition = [
            'labels' => $dispLabels,
            'values' => $dispValues,
            'colors' => $dispColors,
        ];

        $chartAge = [
            'labels' => $m['age_distribution']['labels'],
            'values' => $m['age_distribution']['values'],
            'color'  => '#163029',
        ];
    @endphp

    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; margin-bottom:20px;">

        {{-- Geographic Distribution (compact) --}}
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                <div class="label-cap" style="font-size:9px;">Geographic Distribution</div>
                <span class="mono" style="font-size:10px; color:var(--ink-4);">{{ $hubDist->count() }} hubs</span>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                @foreach($hubDist as $hub)
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:8px; height:8px; border-radius:50%; background:{{ $hub['color'] }}; flex-shrink:0;"></div>
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; align-items:baseline; justify-content:space-between; margin-bottom:3px;">
                            <span style="font-size:11.5px; font-weight:500; color:var(--ink); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:130px;">{{ $hub['name'] }}</span>
                            <div style="display:flex; align-items:baseline; gap:4px; flex-shrink:0; margin-left:6px;">
                                <span class="mono" style="font-size:13px; font-weight:500; color:var(--ink);">{{ $hub['count'] }}</span>
                                <span style="font-size:9px; color:var(--ink-4);">{{ $hub['pct'] }}%</span>
                            </div>
                        </div>
                        <div style="height:3px; background:var(--rule-2); border-radius:2px; overflow:hidden;">
                            <div style="height:100%; width:{{ $hub['pct'] }}%; background:{{ $hub['color'] }}; border-radius:2px; transition:width .4s ease;"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Service Mix --}}
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <div class="label-cap" style="font-size:9px;">Service Mix</div>
                <x-lucide-layers style="width:13px; height:13px; color:var(--ink-4);" />
            </div>
            <div data-chart="serviceMixPie"
                 data-chart-config='{{ json_encode($chartServiceMix) }}'
                 style="height:200px;">
                <canvas></canvas>
            </div>
        </div>

        {{-- Age Distribution --}}
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <div class="label-cap" style="font-size:9px;">Age Distribution</div>
                <x-lucide-bar-chart style="width:13px; height:13px; color:var(--ink-4);" />
            </div>
            <div data-chart="resolutionBar"
                 data-chart-config='{{ json_encode($chartAge) }}'
                 style="height:200px;">
                <canvas></canvas>
            </div>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════════════════════
         ROW 6 — Disposition + SLA Risk + Status Bar
    ═══════════════════════════════════════════════════════════════ --}}
    @php
        $slaGreen = $m['total_cases'] - $m['sla_breach'] - $m['pending_approval'];
        $slaAmber = $m['pending_approval'];
        $slaRed   = $m['sla_breach'];

        $chartStatus = [
            'labels' => array_keys($m['status']),
            'values' => array_values($m['status']),
            'color'  => '#163029',
        ];
    @endphp

    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px;">

        {{-- Case Disposition --}}
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <div class="label-cap" style="font-size:9px;">Case Disposition</div>
                <x-lucide-git-branch style="width:13px; height:13px; color:var(--ink-4);" />
            </div>
            <div data-chart="serviceMixPie"
                 data-chart-config='{{ json_encode($chartDisposition) }}'
                 style="height:200px;">
                <canvas></canvas>
            </div>
        </div>

        {{-- SLA Risk Segmentation --}}
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <div class="label-cap" style="font-size:9px;">SLA Risk Segmentation</div>
                <x-lucide-gauge style="width:13px; height:13px; color:var(--ink-4);" />
            </div>
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:10px;">
                <div style="text-align:center; padding:18px 10px; background:rgba(74,122,92,0.06); border:1px solid rgba(74,122,92,0.15); border-radius:8px;">
                    <div class="serif" style="font-size:28px; font-weight:500; color:var(--moss);">{{ $slaGreen }}</div>
                    <div style="font-size:10px; color:var(--ink-3); margin-top:4px;">On Track</div>
                    <div style="width:24px; height:3px; background:var(--moss); border-radius:2px; margin:6px auto 0;"></div>
                </div>
                <div style="text-align:center; padding:18px 10px; background:rgba(184,115,25,0.06); border:1px solid rgba(184,115,25,0.15); border-radius:8px;">
                    <div class="serif" style="font-size:28px; font-weight:500; color:var(--ochre);">{{ $slaAmber }}</div>
                    <div style="font-size:10px; color:var(--ink-3); margin-top:4px;">At Risk</div>
                    <div style="width:24px; height:3px; background:var(--ochre); border-radius:2px; margin:6px auto 0;"></div>
                </div>
                <div style="text-align:center; padding:18px 10px; background:rgba(138,46,29,0.06); border:1px solid rgba(138,46,29,0.15); border-radius:8px;">
                    <div class="serif" style="font-size:28px; font-weight:500; color:var(--burgundy);">{{ $slaRed }}</div>
                    <div style="font-size:10px; color:var(--ink-3); margin-top:4px;">Breached</div>
                    <div style="width:24px; height:3px; background:var(--burgundy); border-radius:2px; margin:6px auto 0;"></div>
                </div>
            </div>
            {{-- SLA visual bar --}}
            <div style="margin-top:14px;">
                <div style="height:8px; border-radius:4px; overflow:hidden; display:flex; background:var(--rule-2);">
                    @if($m['total_cases'] > 0)
                    <div style="width:{{ round(($slaGreen / $m['total_cases']) * 100) }}%; background:var(--moss); transition:width .4s;"></div>
                    <div style="width:{{ round(($slaAmber / $m['total_cases']) * 100) }}%; background:var(--ochre); transition:width .4s;"></div>
                    <div style="width:{{ round(($slaRed / $m['total_cases']) * 100) }}%; background:var(--burgundy); transition:width .4s;"></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Status Bar Chart --}}
        <div class="card" style="padding:18px 20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <div class="label-cap" style="font-size:9px;">Status Breakdown</div>
                <x-lucide-bar-chart-2 style="width:13px; height:13px; color:var(--ink-4);" />
            </div>
            <div data-chart="resolutionBar"
                 data-chart-config='{{ json_encode($chartStatus) }}'
                 style="height:200px;">
                <canvas></canvas>
            </div>
        </div>
    </div>

</div>

<script>
(function () {
    var defaults = { period: 'All time', province: 'All Provinces', hub: [], service: [] };

    function applyFilters() {
        var params = new URLSearchParams();
        var period = document.getElementById('cc-label-period').textContent.trim();
        if (period && period !== 'All time') params.set('period', period);

        // Hub multiselect
        var hubs = getCheckedValues('hub');
        if (hubs.length > 0) hubs.forEach(function(h) { params.append('hub[]', h); });

        // Service multiselect
        var services = getCheckedValues('service');
        if (services.length > 0) services.forEach(function(s) { params.append('service[]', s); });

        var url = window.location.pathname;
        var qs = params.toString();
        window.location.href = url + (qs ? '?' + qs : '');
    }

    function getCheckedValues(key) {
        var checks = document.querySelectorAll('input[data-multi="' + key + '"]:checked');
        var vals = [];
        checks.forEach(function(c) { vals.push(c.value); });
        return vals;
    }

    // Single-select filters (period, province)
    window.ccSetFilter = function (key, value, btn) {
        document.getElementById('cc-label-' + key).textContent = value;
        btn.closest('ul').querySelectorAll('.filter-opt').forEach(function (b) {
            b.classList.toggle('active', b === btn);
        });
        applyFilters();
    };

    // Multiselect: update label on checkbox change
    window.ccMultiChanged = function (key) {
        var vals = getCheckedValues(key);
        var label = document.getElementById('cc-label-' + key);
        var total = document.querySelectorAll('input[data-multi="' + key + '"]').length;
        if (vals.length === 0 || vals.length === total) {
            label.textContent = key === 'hub' ? 'All Hubs' : 'All Services';
        } else if (vals.length === 1) {
            label.textContent = vals[0];
        } else {
            label.textContent = vals.length + (key === 'hub' ? ' Hubs' : ' Services');
        }
    };

    // Multiselect: toggle all/none
    window.ccMultiToggleAll = function (key) {
        var checks = document.querySelectorAll('input[data-multi="' + key + '"]');
        var allChecked = true;
        checks.forEach(function(c) { if (!c.checked) allChecked = false; });
        checks.forEach(function(c) { c.checked = !allChecked; });
        ccMultiChanged(key);
    };

    // Multiselect: apply button
    window.ccApplyMulti = function (key) {
        applyFilters();
    };

    window.ccResetFilters = function () {
        window.location.href = window.location.pathname;
    };
}());
</script>
</x-layouts.app>
