<x-layouts.app>
@php
    $currentDisposition = request('disposition', 'all');
    $currentStatus = request('status', 'all');
    $currentHub = request('hub', 'all');
    $currentDistrict = request('district', 'all');
    $currentSearch = request('search', '');
    $currentPathway = request('pathway', '');
    $viewMode = request('view', 'list');
    $hasFilters = $currentDisposition !== 'all' || $currentStatus !== 'all' || $currentHub !== 'all' || $currentDistrict !== 'all' || $currentSearch || $currentPathway;
@endphp

<div style="padding: 24px 34px 64px; max-width: 1600px; margin: 0 auto;">

    {{-- ═══ Disposition Tabs ═══ --}}
    <div style="margin-bottom: 18px; border-bottom: 1px solid var(--rule);">
        <div class="label-cap" style="font-size: 9.5px; margin-bottom: 10px;">Case Disposition</div>
        <div style="display: flex; gap: 4px; flex-wrap: wrap;">
            @foreach([
                ['id' => 'all',         'label' => 'All Cases',                  'sub' => 'every intake',       'color' => 'var(--ink)'],
                ['id' => 'advice-only', 'label' => 'Advice given, client left',  'sub' => 'no further action',  'color' => 'var(--ink-3)'],
                ['id' => 'litigation',  'label' => 'Taken up for litigation',    'sub' => 'in court',           'color' => 'var(--burgundy)'],
                ['id' => 'adr',         'label' => 'Taken up for ADR',           'sub' => 'mediation pathway',  'color' => 'var(--ochre)'],
                ['id' => 'referred',    'label' => 'Referred',                   'sub' => 'partner pathway',    'color' => 'var(--moss)'],
                ['id' => 'pending',    'label' => 'Pending triage',             'sub' => 'awaiting assignment', 'color' => 'var(--ink-3)'],
            ] as $d)
                @php
                    $isActive = $currentDisposition === $d['id'];
                    $count = $dispositionCounts[$d['id']] ?? 0;
                @endphp
                <a href="{{ route('cases.index', array_merge(request()->query(), ['disposition' => $d['id'], 'page' => 1])) }}"
                   style="padding: 10px 16px 12px; background: transparent; border: none; border-bottom: 2px solid {{ $isActive ? $d['color'] : 'transparent' }}; margin-bottom: -1px; text-decoration: none; text-align: left; display: flex; flex-direction: column; align-items: flex-start; gap: 2px; min-width: 130px;"
                >
                    <div style="display: flex; align-items: baseline; gap: 7px;">
                        <span class="serif" style="font-size: 18px; font-weight: 500; color: {{ $isActive ? $d['color'] : 'var(--ink-2)' }};">{{ $count }}</span>
                        <span style="font-size: 12.5px; font-weight: {{ $isActive ? '600' : '500' }}; color: {{ $isActive ? 'var(--ink)' : 'var(--ink-2)' }};">{{ $d['label'] }}</span>
                    </div>
                    <div style="font-size: 10px; color: var(--ink-4); letter-spacing: 0.02em;">{{ $d['sub'] }}</div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- ═══ Service Pathways · At a Glance ═══ --}}
    <div class="label-cap" style="font-size: 9.5px; margin-bottom: 8px;">Service Pathways &middot; at a glance &middot; click to filter cases</div>
    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-bottom: 20px;">
        @foreach([
            ['key' => 'legal_advice',   'label' => 'Free Legal Advice',       'sub' => 'counsel sessions delivered',  'icon' => 'file-text',      'indicator' => 'O2.1', 'color' => 'var(--forest)',   'bg' => 'rgba(22,48,41,0.08)',  'filter' => 'legal_advice'],
            ['key' => 'mediation',      'label' => 'Mediation',               'sub' => 'cases on mediation pathway',  'icon' => 'heart-handshake','indicator' => 'O2.2', 'color' => 'var(--moss)',     'bg' => 'var(--moss-tint)',     'filter' => 'mediation'],
            ['key' => 'adr',            'label' => 'ADR',                     'sub' => 'dispute resolution support',  'icon' => 'scale',          'indicator' => 'O2.2', 'color' => 'var(--ochre)',    'bg' => 'var(--ochre-tint)',    'filter' => 'adr'],
            ['key' => 'court',          'label' => 'Representation in Court', 'sub' => 'litigation in progress',      'icon' => 'gavel',          'indicator' => 'O2.4', 'color' => 'var(--burgundy)', 'bg' => 'var(--burgundy-tint)', 'filter' => 'court'],
            ['key' => 'referred',       'label' => 'Referred (loop)',         'sub' => 'total referrals logged',      'icon' => 'share-2',        'indicator' => 'O3.2', 'color' => 'var(--forest)',   'bg' => 'rgba(22,48,41,0.08)', 'filter' => 'referred'],
        ] as $pw)
        @php
            $pwCount = $pathwayCounts[$pw['key']] ?? 0;
            $isActivePw = request('pathway') === $pw['filter'];
        @endphp
        <a href="{{ route('cases.index', array_merge(request()->only(['hub','disposition','status']), ['pathway' => $pw['filter']])) }}"
           class="card" style="padding: 16px 18px; text-decoration: none; transition: border-color 150ms, transform 150ms; {{ $isActivePw ? 'border-color:' . $pw['color'] . ';border-width:2px;' : '' }}"
           onmouseenter="this.style.borderColor='{{ $pw['color'] }}';this.style.transform='translateY(-1px)'"
           onmouseleave="this.style.borderColor='{{ $isActivePw ? $pw['color'] : 'var(--rule)' }}';this.style.transform='none'">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 10px;">
                <div style="width: 36px; height: 36px; background: {{ $pw['bg'] }}; display: flex; align-items: center; justify-content: center; border-radius: 3px;">
                    <x-dynamic-component :component="'lucide-' . $pw['icon']" style="width:17px;height:17px;color:{{ $pw['color'] }};" />
                </div>
                <span class="mono" style="font-size: 10px; padding: 2px 6px; background: var(--paper); border: 1px solid var(--rule-2); color: var(--ink-3);">{{ $pw['indicator'] }}</span>
            </div>
            <div class="serif" style="font-size: 28px; font-weight: 500; line-height: 1; margin-bottom: 6px; color: var(--ink);">{{ $pwCount }}</div>
            <div style="font-size: 12px; font-weight: 600; color: var(--ink); margin-bottom: 2px;">{{ $pw['label'] }}</div>
            <div style="font-size: 10.5px; color: var(--ink-3);">{{ $pw['sub'] }}</div>
        </a>
        @endforeach
    </div>

    {{-- ═══ Cohort KPI Tiles ═══ --}}
    <div class="label-cap" style="font-size: 9.5px; margin-bottom: 8px;">Cohorts &amp; safeguarding &middot; at a glance &middot; click to filter</div>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(115px, 1fr)); gap: 8px; margin-bottom: 18px;">
        @foreach([
            ['id' => 'total',      'label' => 'Total Cases',     'icon' => 'scale',           'value' => $counts['total'],      'hue' => 'var(--forest)'],
            ['id' => 'pending',    'label' => 'Pending Approval','icon' => 'clock',           'value' => $counts['pending'],    'hue' => 'var(--ochre)'],
            ['id' => 'female',     'label' => 'Female Clients',  'icon' => 'user',            'value' => $counts['female'],     'hue' => 'var(--burgundy)'],
            ['id' => 'male',       'label' => 'Male Clients',    'icon' => 'user',            'value' => $counts['male'],       'hue' => 'var(--ink-2)'],
            ['id' => 'minority',   'label' => 'Minority Served', 'icon' => 'heart-handshake', 'value' => $counts['minority'],   'hue' => 'var(--ochre)'],
            ['id' => 'disability', 'label' => 'Disabled Served', 'icon' => 'accessibility',   'value' => $counts['disability'], 'hue' => '#7e57c2'],
            ['id' => 'gbv',        'label' => 'GBV-flagged',     'icon' => 'shield',          'value' => $counts['gbv'],        'hue' => 'var(--burgundy)'],
            ['id' => 'child',      'label' => 'Child cases',     'icon' => 'user',            'value' => $counts['child'],      'hue' => 'var(--ochre)'],
            ['id' => 'high_risk',  'label' => 'High Risk',       'icon' => 'alert-triangle',  'value' => $counts['high_risk'],  'hue' => 'var(--burgundy)'],
            ['id' => 'sla_breach', 'label' => 'SLA Breach',      'icon' => 'clock',           'value' => $counts['sla_breach'], 'hue' => 'var(--burgundy)'],
            ['id' => 'underserved','label' => 'Underserved',     'icon' => 'users',           'value' => $counts['underserved'],'hue' => 'var(--moss)'],
        ] as $k)
        <div class="card" style="padding: 12px 12px 10px; border-radius: 4px; text-align: left;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 6px;">
                <x-dynamic-component :component="'lucide-' . $k['icon']" style="width: 13px; height: 13px; color: {{ $k['hue'] }};" />
            </div>
            <div class="serif" style="font-size: 22px; font-weight: 500; line-height: 1; margin-bottom: 4px; color: var(--ink);">{{ $k['value'] }}</div>
            <div style="font-size: 9.5px; letter-spacing: 0.05em; text-transform: uppercase; color: var(--ink-3); font-weight: 500;">{{ $k['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- ═══ Filter Bar ═══ --}}
    <form method="GET" action="{{ route('cases.index') }}" style="display: flex; align-items: center; gap: 12px; margin-bottom: 18px; flex-wrap: wrap;">
        {{-- Preserve existing filters --}}
        <input type="hidden" name="disposition" value="{{ $currentDisposition }}">

        <div class="label-cap" style="font-size: 9.5px;">Status</div>
        <div style="display: flex; gap: 1px; background: var(--rule); padding: 1px;">
            @foreach([
                ['id' => 'all',          'label' => 'All',          'n' => $cases->total()],
                ['id' => 'active',       'label' => 'Active',       'n' => $counts['active']],
                ['id' => 'safeguarding', 'label' => 'Safeguarding', 'n' => $counts['safeguarding']],
                ['id' => 'sla',          'label' => 'SLA breach',   'n' => $counts['sla_breach']],
                ['id' => 'closed',       'label' => 'Closed',       'n' => $counts['closed']],
            ] as $f)
                <button type="submit" name="status" value="{{ $f['id'] }}"
                    style="padding: 7px 14px; background: {{ $currentStatus === $f['id'] ? 'var(--forest)' : 'var(--paper)' }}; color: {{ $currentStatus === $f['id'] ? 'var(--cream)' : 'var(--ink-2)' }}; border: none; font-size: 12px; font-weight: 500; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 7px;">
                    {{ $f['label'] }}
                    <span class="mono" style="font-size: 9.5px; padding: 1px 5px; background: {{ $currentStatus === $f['id'] ? 'rgba(255,255,255,0.15)' : 'var(--rule-2)' }}; color: {{ $currentStatus === $f['id'] ? 'var(--cream)' : 'var(--ink-3)' }};">{{ $f['n'] }}</span>
                </button>
            @endforeach
        </div>

        <div style="height: 18px; width: 1px; background: var(--rule);"></div>

        <select name="hub" onchange="this.form.submit()" class="inp" style="width: 170px; padding: 7px 10px; font-size: 12px;">
            <option value="all" {{ $currentHub === 'all' ? 'selected' : '' }}>All Hubs</option>
            @foreach($hubs as $hub)
                <option value="{{ $hub->id }}" {{ $currentHub === $hub->id ? 'selected' : '' }}>{{ $hub->name }}</option>
            @endforeach
        </select>

        <select name="district" onchange="this.form.submit()" class="inp" style="width: 160px; padding: 7px 10px; font-size: 12px;">
            <option value="all" {{ $currentDistrict === 'all' ? 'selected' : '' }}>All Districts</option>
            @foreach($availableDistricts as $dist)
                <option value="{{ $dist }}" {{ $currentDistrict === $dist ? 'selected' : '' }}>{{ $dist }}</option>
            @endforeach
        </select>

        <div style="flex: 1;"></div>

        {{-- Search --}}
        <div style="position: relative;">
            <x-lucide-search style="width: 14px; height: 14px; position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--ink-4);" />
            <input type="text" name="search" value="{{ $currentSearch }}" placeholder="Search name, ID, issue…" class="inp" style="padding-left: 32px; width: 220px; font-size: 12px; height: 34px;">
        </div>

        @if($hasFilters)
            <a href="{{ route('cases.index') }}" style="display: inline-flex; align-items: center; gap: 5px; font-size: 11.5px; color: var(--ink-3); text-decoration: none; padding: 5px 10px; border: 1px solid var(--rule); transition: all 120ms;"
               onmouseenter="this.style.borderColor='var(--burgundy)';this.style.color='var(--burgundy)'"
               onmouseleave="this.style.borderColor='var(--rule)';this.style.color='var(--ink-3)'">
                <x-lucide-x style="width:11px;height:11px;" /> Reset all filters
            </a>
        @endif

        {{-- Export Excel --}}
        @can('reports.export')
        <a href="{{ route('cases.export', ['hub' => $currentHub, 'status' => $currentStatus, 'pathway' => $currentPathway]) }}"
           style="display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border:1px solid var(--moss); color:var(--moss); font-size:12px; font-weight:500; text-decoration:none; font-family:inherit; transition:all 120ms;"
           onmouseenter="this.style.background='var(--moss)';this.style.color='#fff'"
           onmouseleave="this.style.background='transparent';this.style.color='var(--moss)'">
            <x-lucide-download style="width:12px;height:12px;" /> Export Excel
        </a>
        @endcan

        {{-- View mode toggle --}}
        <div style="display: flex; gap: 1px; padding: 1px; background: var(--rule); border: 1px solid var(--rule);">
            <button id="jh-btn-list" type="button" onclick="jhSetViewMode('list')" title="List view"
                style="padding:7px 11px; background:{{ $viewMode === 'list' ? 'var(--forest)' : 'var(--paper)' }}; color:{{ $viewMode === 'list' ? 'var(--cream)' : 'var(--ink-2)' }}; border:none; cursor:pointer;">
                <x-lucide-list style="width: 14px; height: 14px;" />
            </button>
            <button id="jh-btn-grid" type="button" onclick="jhSetViewMode('grid')" title="Grid view"
                style="padding:7px 11px; background:{{ $viewMode === 'grid' ? 'var(--forest)' : 'var(--paper)' }}; color:{{ $viewMode === 'grid' ? 'var(--cream)' : 'var(--ink-2)' }}; border:none; cursor:pointer;">
                <x-lucide-layout-grid style="width: 14px; height: 14px;" />
            </button>
        </div>
    </form>

    {{-- ═══ List View ═══ --}}
    <div id="jh-view-list" style="{{ $viewMode === 'grid' ? 'display:none' : '' }}">
        <div class="card" style="padding: 0; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--rule);">
                        <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Case ID</th>
                        <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Client</th>
                        <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Issue</th>
                        <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Status</th>
                        <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Assigned To</th>
                        <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Assigned By</th>
                        <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Hub</th>
                        <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">SLA</th>
                        <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cases as $case)
                    <tr class="tr-hover" onclick="window.location='{{ route('cases.show', $case) }}'" style="border-bottom: 1px solid var(--rule-2);">
                        <td style="padding: 12px 14px;">
                            <span class="mono" style="font-size: 12px; color: var(--forest); font-weight: 500;">{{ $case->case_uid }}</span>
                        </td>
                        <td style="padding: 12px 14px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                @if($case->gender === 'Female')
                                    <x-lucide-user style="width: 13px; height: 13px; color: var(--burgundy);" />
                                @elseif($case->gender === 'Male')
                                    <x-lucide-user style="width: 13px; height: 13px; color: var(--ink-3);" />
                                @else
                                    <x-lucide-users style="width: 13px; height: 13px; color: var(--ink-3);" />
                                @endif
                                <div>
                                    <div style="font-weight: 500; color: var(--ink);">{{ $case->name }}</div>
                                    <div style="font-size: 11px; color: var(--ink-4);">{{ $case->gender }} · {{ $case->age }}y · {{ $case->district }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 12px 14px;">
                            <div style="color: var(--ink-2);">{{ $case->primary_issue }}</div>
                            @if($case->secondary_issue)
                                <div style="font-size: 11px; color: var(--ink-4);">{{ $case->secondary_issue }}</div>
                            @endif
                        </td>
                        <td style="padding: 12px 14px;">
                            <x-pill :color="$case->status->color()" :bg="$case->status->color() . '15'" :border-color="$case->status->color() . '40'">
                                {{ $case->status->value }}
                            </x-pill>
                        </td>
                        <td style="padding: 12px 14px; font-size: 12px; color: var(--ink-2);">
                            {{ $case->assigned_to ?? '—' }}
                        </td>
                        <td style="padding: 12px 14px; font-size: 12px; color: var(--ink-3);">
                            {{ $case->staff_receiving ?? '—' }}
                        </td>
                        <td style="padding: 12px 14px;">
                            <span class="mono" style="font-size: 11px; color: var(--ink-3);">{{ $case->hub_id }}</span>
                        </td>
                        <td style="padding: 12px 14px;">
                            @php
                                $slaC = $case->sla_met ? 'var(--moss)' : 'var(--burgundy)';
                                $slaT = $case->sla_met ? 'Met' : 'Breach';
                            @endphp
                            <span style="display:inline-block;padding:2px 7px;border-radius:4px;font-size:10px;font-weight:600;letter-spacing:0.05em;color:{{ $slaC }};background:{{ $slaC }}18;border:1px solid {{ $slaC }}40;">
                                {{ $slaT }}
                            </span>
                        </td>
                        <td style="padding: 12px 14px; font-size: 12px; color: var(--ink-3); white-space: nowrap;">
                            {{ $case->last_update?->format('M d') ?? $case->intake_date->format('M d') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <x-empty-state icon="folder" message="No cases match your filters." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══ Grid View ═══ --}}
    <div id="jh-view-grid" style="display: {{ $viewMode === 'grid' ? 'grid' : 'none' }}; grid-template-columns: repeat(4, 1fr); gap: 14px;">
        @forelse($cases as $case)
        <a href="{{ route('cases.show', $case) }}" class="card" style="padding: 18px 20px; text-decoration: none; color: inherit; display: flex; flex-direction: column; gap: 12px; transition: all 120ms;" onmouseenter="this.style.borderColor='var(--forest)'" onmouseleave="this.style.borderColor='var(--rule)'">
            {{-- Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span class="mono" style="font-size: 11px; color: var(--forest); font-weight: 500;">{{ $case->case_uid }}</span>
                <x-pill :color="$case->status->color()" :bg="$case->status->color() . '15'" :border-color="$case->status->color() . '40'">
                    {{ $case->status->value }}
                </x-pill>
            </div>
            {{-- Client --}}
            <div>
                <div style="font-size: 15px; font-weight: 500; color: var(--ink);">{{ $case->name }}</div>
                <div style="font-size: 12px; color: var(--ink-3); margin-top: 2px;">{{ $case->gender }} · {{ $case->age }}y · {{ $case->district }}</div>
            </div>
            {{-- Issue --}}
            <div style="font-size: 13px; color: var(--ink-2);">
                {{ $case->primary_issue }}
                @if($case->secondary_issue) · {{ $case->secondary_issue }} @endif
            </div>
            {{-- Footer --}}
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: auto; padding-top: 10px; border-top: 1px solid var(--rule-2);">
                <div style="font-size: 11px; color: var(--ink-3);">{{ $case->assigned_to }}</div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    @if($case->disposition)
                        <x-pill>{{ $case->disposition->label() }}</x-pill>
                    @endif
                    <span class="mono" style="font-size: 10px; color: var(--ink-4);">{{ $case->hub_id }}</span>
                </div>
            </div>
            {{-- Vulnerability flags --}}
            @if($case->is_gbv || $case->is_child || $case->is_minority || $case->is_disability || $case->is_underserved)
            <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                @if($case->is_gbv)<span style="font-size: 9px; padding: 1px 5px; background: var(--burgundy-tint); color: var(--burgundy); font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase;">GBV</span>@endif
                @if($case->is_child)<span style="font-size: 9px; padding: 1px 5px; background: var(--ochre-tint); color: var(--ochre); font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase;">CHILD</span>@endif
                @if($case->is_minority)<span style="font-size: 9px; padding: 1px 5px; background: var(--ochre-tint); color: var(--ochre); font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase;">MINORITY</span>@endif
                @if($case->is_disability)<span style="font-size: 9px; padding: 1px 5px; background: rgba(126,87,194,0.1); color: #7e57c2; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase;">DISABILITY</span>@endif
                @if($case->is_underserved)<span style="font-size: 9px; padding: 1px 5px; background: rgba(74,122,92,0.1); color: var(--moss); font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase;">UNDERSERVED</span>@endif
            </div>
            @endif
        </a>
        @empty
        <div style="grid-column: 1 / -1;">
            <x-empty-state icon="folder" message="No cases match your filters." />
        </div>
        @endforelse
    </div>

    {{-- ═══ Pagination ═══ --}}
    @if($cases->hasPages())
    <div style="margin-top: 20px; display: flex; justify-content: center;">
        {{ $cases->appends(request()->query())->links() }}
    </div>
    @endif

    {{-- Results count --}}
    <div style="text-align: center; margin-top: 12px; font-size: 11px; color: var(--ink-4);">
        Showing {{ $cases->firstItem() ?? 0 }}–{{ $cases->lastItem() ?? 0 }} of {{ $cases->total() }} cases
    </div>
</div>
</x-layouts.app>
