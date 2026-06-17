@php
    $user = auth()->user();
    $currentRoute = Route::currentRouteName() ?? '';

    $navGroups = [
        'Dashboards' => [
            ['route' => 'dashboard',               'label' => 'Main Dashboard',              'icon' => 'activity',         'permission' => 'cases.view'],
            ['route' => 'dashboard.litigation-adr', 'label' => 'Litigation & ADR Dashboard',  'icon' => 'gavel',            'permission' => 'cases.view'],
        ],
        'Work' => [
            ['route' => 'cases.index',  'label' => 'Cases',      'icon' => 'folder',    'permission' => 'cases.view'],
            ['route' => 'intake.create','label' => 'New Intake',  'icon' => 'file-plus', 'permission' => 'cases.create'],
        ],
        'Service Delivery' => [
            ['route' => 'services.adr',              'label' => 'ADR Scorecard',        'icon' => 'heart-handshake', 'permission' => 'cases.view'],
            ['route' => 'services.adr-calendar',     'label' => 'ADR Calendar',         'icon' => 'calendar',        'permission' => 'cases.view'],
            ['route' => 'services.litigation',       'label' => 'Litigation Scorecard',  'icon' => 'gavel',           'permission' => 'cases.view'],
            ['route' => 'services.litigation-calendar','label' => 'Litigation Calendar', 'icon' => 'calendar',        'permission' => 'cases.view'],
            ['route' => 'referrals.index',           'label' => 'Referrals',            'icon' => 'share-2',         'permission' => 'cases.view'],
            ['route' => 'outreach.index',            'label' => 'Outreach',             'icon' => 'megaphone',       'permission' => 'outreach.view'],
            ['route' => 'complaints.index',          'label' => 'Complaints',           'icon' => 'alert-triangle',  'permission' => 'complaints.view'],
        ],
        'Measurement' => [
            ['route' => 'indicators.index', 'label' => 'Indicators',       'icon' => 'bar-chart-3',     'permission' => 'indicators.view'],
            ['route' => 'evidence.index',   'label' => 'Evidence Register', 'icon' => 'book-open',       'permission' => 'evidence.view'],
            ['route' => 'feedback.index',   'label' => 'Client Feedback',  'icon' => 'heart-handshake', 'permission' => 'feedback.view'],
            ['route' => 'staff.index',      'label' => 'Staff & Training', 'icon' => 'user-check',      'permission' => 'staff.view'],
            ['route' => 'learning.index',   'label' => 'Learning & VfM',   'icon' => 'graduation-cap',  'permission' => 'indicators.view'],
        ],
        'Reporting' => [
            ['route' => 'impact.index', 'label' => 'Impact Reports', 'icon' => 'flag', 'permission' => 'reports.view'],
        ],
        'System' => [
            ['route' => 'settings.index', 'label' => 'Settings',         'icon' => 'settings', 'permission' => 'settings.view'],
            ['route' => 'users.index',    'label' => 'User Management',   'icon' => 'users',    'permission' => 'users.manage'],
        ],
    ];

    // Module on/off states
    $moduleStates = \Illuminate\Support\Facades\DB::table('settings')
        ->where('key', 'like', 'module_%')
        ->pluck('value', 'key');
    $moduleOff = fn(string $key) => ($moduleStates['module_' . $key] ?? 'on') === 'off';

    $complaintsOpen = \App\Models\Complaint::where('status', '!=', 'resolved')->count();
    $hubs = \App\Models\Hub::where('is_active', true)->get();
    $activeHubId   = $activeHub ?? session('active_hub', 'all');
    $activeHubName = $activeHubId === 'all' ? 'All Hubs' : ($hubs->firstWhere('id', $activeHubId)?->name ?? 'All Hubs');
    $activeHubCode = $activeHubId === 'all' ? $hubs->count() . ' hubs · Sindh' : $activeHubId;
@endphp

<aside class="jh-scroll grain-dark" style="width: 250px; background: var(--forest); color: var(--cream); border-right: 1px solid rgba(255,255,255,0.06); display: flex; flex-direction: column; overflow-y: auto; flex-shrink: 0;">

    {{-- Wordmark --}}
    <div style="padding: 26px 22px 22px; border-bottom: 1px solid rgba(255,255,255,0.08);">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 32px; height: 32px; background: var(--ochre-2); display: flex; align-items: center; justify-content: center;">
                <x-lucide-scale style="width: 17px; height: 17px; color: var(--forest);" />
            </div>
            <div>
                <div class="serif" style="font-size: 18px; font-weight: 500; line-height: 1.05; letter-spacing: -0.01em;">Justice Hub</div>
                <div class="mono" style="font-size: 9.5px; opacity: 0.65; letter-spacing: 0.08em; margin-top: 2px;">LAS · CMS · SINCE 2013</div>
            </div>
        </div>
    </div>

    {{-- Hub selector (Bootstrap dropdown — only for global roles) --}}
    @if($canSwitchHub ?? false)
    <div style="padding: 18px 22px 14px; border-bottom: 1px solid rgba(255,255,255,0.08);">
        <div class="label-cap" style="color: rgba(247,243,235,0.5); margin-bottom: 8px; font-size: 9.5px;">Active Hub</div>

        <div class="dropdown">
            <button class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"
                style="width: 100%; display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); padding: 9px 12px; color: var(--cream); font-size: 13px; text-align: left; cursor: pointer; font-family: inherit;">
                <x-lucide-map-pin style="width: 13px; height: 13px; color: var(--ochre-2); flex-shrink: 0;" />
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 500;">{{ $activeHubName }}</div>
                    <div class="mono" style="font-size: 10px; opacity: 0.55; margin-top: 1px;">{{ $activeHubCode }}</div>
                </div>
                <x-lucide-chevron-down style="width: 13px; height: 13px; opacity: 0.55; flex-shrink: 0;" />
            </button>

            <ul class="dropdown-menu" style="width: 100%; padding: 0; border: 1px solid var(--rule); border-radius: 0; background: var(--paper); box-shadow: var(--shadow-card); max-height: 260px; overflow-y: auto;">
                <li>
                    <form method="POST" action="{{ route('settings.hub') }}">
                        @csrf
                        <button type="submit" name="hub_id" value="all"
                            style="width: 100%; padding: 10px 14px; text-align: left; border: none; background: {{ $activeHubId === 'all' ? 'var(--parchment-2)' : 'transparent' }}; color: var(--ink); font-size: 13px; cursor: pointer; font-family: inherit; display: flex; align-items: center; gap: 8px;">
                            <x-lucide-globe style="width: 13px; height: 13px; color: var(--ink-3);" />
                            All Hubs
                        </button>
                    </form>
                </li>
                @foreach($hubs as $hub)
                <li>
                    <form method="POST" action="{{ route('settings.hub') }}">
                        @csrf
                        <button type="submit" name="hub_id" value="{{ $hub->id }}"
                            style="width: 100%; padding: 10px 14px; text-align: left; border: none; background: {{ $activeHubId === $hub->id ? 'var(--parchment-2)' : 'transparent' }}; color: var(--ink); font-size: 13px; cursor: pointer; font-family: inherit; display: flex; align-items: center; gap: 8px;">
                            <x-lucide-map-pin style="width: 13px; height: 13px; color: var(--ink-3);" />
                            <div>
                                <div>{{ $hub->name }}</div>
                                <div class="mono" style="font-size: 10px; color: var(--ink-4);">{{ $hub->id }}</div>
                            </div>
                        </button>
                    </form>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @else
    <div style="padding: 18px 22px 14px; border-bottom: 1px solid rgba(255,255,255,0.08);">
        <div class="label-cap" style="color: rgba(247,243,235,0.5); margin-bottom: 8px; font-size: 9.5px;">Your Hub</div>
        <div style="display: flex; align-items: center; gap: 10px; padding: 9px 0;">
            <x-lucide-map-pin style="width: 13px; height: 13px; color: var(--ochre-2); flex-shrink: 0;" />
            <div>
                <div style="font-weight: 500; font-size: 13px;">{{ $activeHubName }}</div>
                <div class="mono" style="font-size: 10px; opacity: 0.55; margin-top: 1px;">{{ $activeHubCode }}</div>
            </div>
        </div>
    </div>
    @endif

    {{-- Navigation groups --}}
    <nav style="flex: 1; padding: 14px 0;">
        @foreach($navGroups as $groupLabel => $items)
            @php
                // Map route → module key for offline check
                $routeModuleMap = [
                    'cases.index'                  => 'cases',
                    'intake.create'                => 'intake',
                    'services.adr'                 => 'adr',
                    'services.adr-calendar'        => 'adr',
                    'services.litigation'          => 'litigation',
                    'services.litigation-calendar' => 'litigation',
                    'referrals.index'              => 'referrals',
                    'outreach.index'               => 'outreach',
                    'complaints.index'             => 'complaints',
                    'indicators.index'             => 'indicators',
                    'evidence.index'               => 'evidence',
                    'feedback.index'               => 'feedback',
                    'staff.index'                  => 'staff',
                    'learning.index'               => 'learning',
                    'impact.index'                 => 'impact',
                ];
                $visibleItems = array_filter($items, function($item) use ($user, $moduleOff, $routeModuleMap) {
                    if (!$user->can($item['permission'])) return false;
                    $modKey = $routeModuleMap[$item['route']] ?? null;
                    if ($modKey && $moduleOff($modKey)) return false;
                    return true;
                });
            @endphp
            @if(count($visibleItems) > 0)
            <div style="margin-bottom: 18px;">
                <div class="label-cap" style="color: rgba(247,243,235,0.4); padding: 6px 22px 10px; font-size: 9.5px;">{{ $groupLabel }}</div>
                @foreach($visibleItems as $item)
                    @php
                        $isActive   = $currentRoute === $item['route'];
                        $badgeCount = null;
                        if (($item['badge'] ?? null) === 'complaints' && $complaintsOpen > 0) $badgeCount = $complaintsOpen;
                    @endphp
                    <a href="{{ route($item['route']) }}"
                        class="nav-item {{ $isActive ? 'active' : '' }}"
                        style="width: 100%; display: flex; align-items: center; gap: 12px; padding: 9px 22px; background: {{ $isActive ? 'rgba(217,160,91,0.08)' : 'transparent' }}; color: {{ $isActive ? 'var(--cream)' : 'rgba(247,243,235,0.72)' }}; font-size: 13.5px; text-decoration: none; font-weight: {{ $isActive ? '500' : '400' }};"
                        onmouseenter="if(!this.classList.contains('active'))this.style.background='rgba(255,255,255,0.03)'"
                        onmouseleave="if(!this.classList.contains('active'))this.style.background='transparent'"
                    >
                        <x-dynamic-component :component="'lucide-' . $item['icon']" style="width: 15px; height: 15px; stroke-width: {{ $isActive ? '2' : '1.6' }};" />
                        <span style="flex: 1;">{{ $item['label'] }}</span>
                        @if($badgeCount)
                            <span class="mono" style="font-size: 10px; padding: 1px 6px; background: var(--burgundy); color: var(--cream); font-weight: 600; letter-spacing: 0.02em;">{{ $badgeCount }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
            @endif
        @endforeach
    </nav>

    {{-- Footer --}}
    <div style="border-top: 1px solid rgba(255,255,255,0.08); padding: 14px 22px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 30px; height: 30px; background: var(--forest-3); color: var(--ochre-2); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 500;">
                {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $user->name)[1] ?? '', 0, 1)) }}
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-size: 12.5px; font-weight: 500;">{{ $user->name }}</div>
                <div style="font-size: 10.5px; opacity: 0.55; margin-top: 1px;">{{ $user->designation ?: $user->role->label() }}</div>
            </div>
            @if($user->can('settings.view'))
            <a href="{{ route('settings.index') }}" style="color: var(--cream); opacity: 0.55;">
                <x-lucide-settings style="width: 14px; height: 14px;" />
            </a>
            @endif
        </div>
        <div class="mono" style="font-size: 9px; opacity: 0.35; margin-top: 14px; letter-spacing: 0.08em;">CMS v1.0 · Q2 2026</div>
    </div>
</aside>

<style>
    /* Remove Bootstrap's caret on sidebar hub dropdown toggle */
    aside .dropdown-toggle::after { display: none !important; }
</style>
