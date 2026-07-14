<x-layouts.app>
@php
    $user = auth()->user();
    $hubs = \App\Models\Hub::where('is_active', true)->get();
    $activeHubId = session('active_hub', 'all');
    $currentTheme = session('theme', 'light');
    $currentLocale = session('locale', $user->preferredLocale());

    // Data counts for lineage section
    $counts = [
        'Cases on file'      => \App\Models\CaseRecord::count(),
        'Documents indexed'  => \App\Models\Document::count(),
        'Feedback responses' => \App\Models\Feedback::count(),
        'Complaints logged'  => \App\Models\Complaint::count(),
        'Outreach sessions'  => \App\Models\OutreachActivity::count(),
        'Evidence entries'   => \App\Models\Evidence::count(),
        'Staff registered'   => \App\Models\Staff::count(),
        'Partners'           => \App\Models\Partner::count(),
    ];
    $indicatorCount = \App\Models\Indicator::count();
@endphp

<div style="padding: 28px 36px 60px; max-width: 1100px; margin: 0 auto;">

    {{-- Header --}}
    <div style="margin-bottom: 28px; padding-bottom: 22px; border-bottom: 1px solid var(--rule);">
        <div class="label-cap" style="font-size: 9.5px; margin-bottom: 8px;">System</div>
        <h2 class="serif" style="font-size: 30px; font-weight: 400; letter-spacing: -0.02em; margin: 0 0 8px 0; color: var(--ink);">
            Settings &amp; Preferences
        </h2>
        <p style="font-size: 13.5px; color: var(--ink-3); line-height: 1.55; margin: 0; max-width: 720px;">
            Personalise how the Justice Hub CMS behaves. Hub scope and theme apply immediately and persist across sessions.
        </p>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
        Section 1 — Appearance
        ═══════════════════════════════════════════════════════════════ --}}
    <div style="margin-bottom: 28px;">
        <div style="margin-bottom: 12px;">
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">1 · Appearance</div>
            <h3 class="serif" style="font-size: 19px; font-weight: 500; margin: 0; color: var(--ink);">Theme</h3>
            <p style="font-size: 12.5px; color: var(--ink-3); margin: 6px 0 0 0; line-height: 1.55; max-width: 640px;">
                Switches the entire interface between the parchment editorial light theme and a warm dark theme. Affects every view, modal, and chart.
            </p>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; max-width: 640px;">
            @foreach([
                ['id' => 'light', 'label' => 'Light', 'desc' => 'Parchment + ink. Default.', 'bg' => '#f7f3eb', 'fg' => '#181714', 'accent' => '#163029'],
                ['id' => 'dark',  'label' => 'Dark',  'desc' => 'Warm near-black + cream.',  'bg' => '#1a1612', 'fg' => '#f0e9d8', 'accent' => '#3a6e5e'],
            ] as $opt)
            <button
                data-theme-opt="{{ $opt['id'] }}"
                onclick="jhSetTheme('{{ $opt['id'] }}', '{{ csrf_token() }}', '{{ route('settings.theme') }}')"
                style="background: var(--paper); padding: 18px 20px; text-align: left; cursor: pointer; font-family: inherit; transition: all 140ms; display: flex; flex-direction: column; gap: 12px; border: 2px solid {{ $currentTheme === $opt['id'] ? 'var(--forest)' : 'var(--rule)' }};"
            >
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="serif" style="font-size: 16px; font-weight: 500; color: var(--ink);">{{ $opt['label'] }}</span>
                    <span class="jh-theme-active" style="font-size: 9.5px; font-weight: 600; letter-spacing: 0.04em; padding: 2px 7px; border-radius: 2px; background: rgba(74,122,92,0.14); color: var(--moss); text-transform: uppercase;{{ $currentTheme !== $opt['id'] ? ' display:none;' : '' }}">Active</span>
                </div>
                {{-- Preview swatch --}}
                <div style="height: 56px; background: {{ $opt['bg'] }}; position: relative; border: 1px solid var(--rule-2); overflow: hidden;">
                    <div style="position: absolute; top: 8px; left: 10px; font-size: 11px; color: {{ $opt['fg'] }}; font-family: Fraunces, serif; font-weight: 500;">Justice Hub</div>
                    <div style="position: absolute; top: 26px; left: 10px; font-size: 9px; color: {{ $opt['fg'] }}; opacity: 0.6;">Case management</div>
                    <div style="position: absolute; bottom: 8px; right: 10px; padding: 3px 7px; background: {{ $opt['accent'] }}; color: {{ $opt['bg'] }}; font-size: 9px; font-weight: 500;">14 cases</div>
                </div>
                <div style="font-size: 11.5px; color: var(--ink-3);">{{ $opt['desc'] }}</div>
            </button>
            @endforeach
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
        Section 1.5 — Language / زبان
        ═══════════════════════════════════════════════════════════════ --}}
    <div style="margin-bottom: 28px;">
        <div style="margin-bottom: 12px;">
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">2 · Language / زبان</div>
            <h3 class="serif" style="font-size: 19px; font-weight: 500; margin: 0; color: var(--ink);">Interface Language</h3>
            <p style="font-size: 12.5px; color: var(--ink-3); margin: 6px 0 0 0; line-height: 1.55; max-width: 640px;">
                Switch the interface language. Form labels, navigation, and buttons will display in your selected language. Data you enter is saved as-is in any language.
            </p>
        </div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; max-width: 640px;">
            @foreach([
                ['id' => 'en', 'label' => 'English',  'native' => 'English',   'desc' => 'Default interface language', 'sample' => 'Register Intake'],
                ['id' => 'sd', 'label' => 'Sindhi',   'native' => 'سنڌي',      'desc' => 'سنڌي ۾ انٽرفيس',            'sample' => 'نئون داخلو'],
                ['id' => 'ur', 'label' => 'Urdu',     'native' => 'اردو',      'desc' => 'اردو میں انٹرفیس',           'sample' => 'نیا اندراج'],
            ] as $lang)
            <form method="POST" action="{{ route('settings.locale') }}">
                @csrf
                <button type="submit" name="locale" value="{{ $lang['id'] }}"
                    style="width: 100%; background: var(--paper); border: 2px solid {{ $currentLocale === $lang['id'] ? 'var(--forest)' : 'var(--rule)' }}; padding: 18px 20px; text-align: left; cursor: pointer; font-family: inherit; transition: all 140ms; display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span class="serif" style="font-size: 16px; font-weight: 500; color: var(--ink);">{{ $lang['label'] }}</span>
                            @if($lang['id'] !== 'en')
                            <span style="font-size: 14px; color: var(--ink-2); margin-left: 6px; font-family: 'Noto Nastaliq Urdu', Tahoma, sans-serif;">{{ $lang['native'] }}</span>
                            @endif
                        </div>
                        @if($currentLocale === $lang['id'])
                        <span style="font-size: 9px; font-weight: 600; letter-spacing: 0.04em; padding: 2px 7px; background: rgba(74,122,92,0.14); color: var(--moss); text-transform: uppercase;">Active</span>
                        @endif
                    </div>
                    <div style="font-size: 11.5px; color: var(--ink-3);">{{ $lang['desc'] }}</div>
                    <div style="padding: 6px 10px; background: var(--surface); border: 1px solid var(--rule-2); font-size: 12px; color: var(--ink-2);{{ in_array($lang['id'], ['sd', 'ur']) ? ' direction:rtl; text-align:right; font-family:\"Noto Nastaliq Urdu\", Tahoma, sans-serif;' : '' }}">
                        {{ $lang['sample'] }}
                    </div>
                </button>
            </form>
            @endforeach
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
        Section 2 — Hub Scope (only for global roles)
        ═══════════════════════════════════════════════════════════════ --}}
    @if($user->canSeeAllHubs())
    <div style="margin-bottom: 28px;">
        <div style="margin-bottom: 12px;">
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">3 · Hub Scope</div>
            <h3 class="serif" style="font-size: 19px; font-weight: 500; margin: 0; color: var(--ink);">Active hub</h3>
            <p style="font-size: 12.5px; color: var(--ink-3); margin: 6px 0 0 0; line-height: 1.55; max-width: 640px;">
                Filters cases, indicators, complaints, and feedback to the selected hub. Reports and dashboards aggregate to programme level when &ldquo;All Hubs&rdquo; is selected.
            </p>
        </div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; max-width: 920px;">
            {{-- All Hubs option --}}
            <form method="POST" action="{{ route('settings.hub') }}">
                @csrf
                <button type="submit" name="hub_id" value="all" style="width: 100%; background: var(--paper); border: 2px solid {{ $activeHubId === 'all' ? 'var(--forest)' : 'var(--rule)' }}; padding: 16px 18px; text-align: left; cursor: pointer; font-family: inherit; transition: all 140ms;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <div style="width: 24px; height: 24px; background: var(--forest); display: flex; align-items: center; justify-content: center;">
                            <x-lucide-building-2 style="width: 12px; height: 12px; color: var(--cream);" />
                        </div>
                        <span class="serif" style="font-size: 15px; font-weight: 500; color: var(--ink);">All Hubs</span>
                        @if($activeHubId === 'all')
                        <span style="margin-left: auto; font-size: 9px; font-weight: 600; letter-spacing: 0.04em; padding: 1px 6px; background: rgba(74,122,92,0.14); color: var(--moss); text-transform: uppercase;">Active</span>
                        @endif
                    </div>
                    <div style="font-size: 11.5px; color: var(--ink-3); line-height: 1.5;">Programme-level view across all {{ $hubs->count() }} hubs in Sindh</div>
                    <div class="mono" style="font-size: 10px; color: var(--ink-4); margin-top: 8px;">{{ number_format(\App\Models\CaseRecord::count()) }} cases on file</div>
                </button>
            </form>

            {{-- Per-hub options --}}
            @foreach($hubs as $hub)
            <form method="POST" action="{{ route('settings.hub') }}">
                @csrf
                <button type="submit" name="hub_id" value="{{ $hub->id }}" style="width: 100%; background: var(--paper); border: 2px solid {{ $activeHubId === $hub->id ? 'var(--forest)' : 'var(--rule)' }}; padding: 16px 18px; text-align: left; cursor: pointer; font-family: inherit; transition: all 140ms;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <div style="width: 24px; height: 24px; background: var(--ochre); display: flex; align-items: center; justify-content: center;">
                            <x-lucide-map-pin style="width: 12px; height: 12px; color: var(--cream);" />
                        </div>
                        <span class="serif" style="font-size: 15px; font-weight: 500; color: var(--ink);">{{ $hub->name }}</span>
                        @if($activeHubId === $hub->id)
                        <span style="margin-left: auto; font-size: 9px; font-weight: 600; letter-spacing: 0.04em; padding: 1px 6px; background: rgba(74,122,92,0.14); color: var(--moss); text-transform: uppercase;">Active</span>
                        @endif
                    </div>
                    <div style="font-size: 11.5px; color: var(--ink-3); line-height: 1.5;">{{ $hub->district }}, {{ $hub->province }}</div>
                    <div class="mono" style="font-size: 10px; color: var(--ink-4); margin-top: 8px;">{{ $hub->id }} · {{ \App\Models\CaseRecord::where('hub_id', $hub->id)->count() }} cases</div>
                </button>
            </form>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
        Section 3 — Keyboard Shortcuts
        ═══════════════════════════════════════════════════════════════ --}}
    <div style="margin-bottom: 28px;">
        <div style="margin-bottom: 12px;">
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">{{ $user->canSeeAllHubs() ? '4' : '3' }} · Keyboard shortcuts</div>
            <h3 class="serif" style="font-size: 19px; font-weight: 500; margin: 0; color: var(--ink);">Available shortcuts</h3>
        </div>
        <div class="card" style="padding: 0; max-width: 640px;">
            @foreach([
                ['keys' => ['⌘', 'K'], 'alt' => 'Ctrl+K', 'action' => 'Focus the global search box', 'context' => 'Anywhere in the app'],
                ['keys' => ['↑'],       'alt' => null,      'action' => 'Move to previous search result', 'context' => 'Search dropdown open'],
                ['keys' => ['↓'],       'alt' => null,      'action' => 'Move to next search result', 'context' => 'Search dropdown open'],
                ['keys' => ['↵'],       'alt' => null,      'action' => 'Open the highlighted result', 'context' => 'Search dropdown open'],
                ['keys' => ['Esc'],     'alt' => null,      'action' => 'Dismiss the open dropdown or modal', 'context' => 'Anywhere in the app'],
            ] as $i => $shortcut)
            <div style="padding: 14px 18px; {{ $i < 4 ? 'border-bottom: 1px solid var(--rule-2);' : '' }} display: grid; grid-template-columns: 160px 1fr 200px; gap: 14px; align-items: center;">
                <div style="display: flex; gap: 6px; align-items: center;">
                    @foreach($shortcut['keys'] as $j => $key)
                        @if($j > 0)<span style="font-size: 10px; color: var(--ink-4);">+</span>@endif
                        <kbd class="mono" style="padding: 3px 8px; min-width: 22px; text-align: center; background: var(--parchment-2); border: 1px solid var(--rule); border-radius: 3px; font-size: 11px; color: var(--ink-2); font-weight: 500;">{{ $key }}</kbd>
                    @endforeach
                    @if($shortcut['alt'])
                        <span style="font-size: 10px; color: var(--ink-4); margin-left: 4px;">or {{ $shortcut['alt'] }}</span>
                    @endif
                </div>
                <div style="font-size: 13px; color: var(--ink-2);">{{ $shortcut['action'] }}</div>
                <div style="font-size: 10.5px; color: var(--ink-4); text-align: right;">{{ $shortcut['context'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
        Section — Performance & Caching
        ═══════════════════════════════════════════════════════════════ --}}
    @if($user->can('lookups.manage'))
    <div style="margin-bottom: 28px;">
        <div style="margin-bottom: 12px;">
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">Performance</div>
            <h3 class="serif" style="font-size: 19px; font-weight: 500; margin: 0; color: var(--ink);">Dashboard Cache</h3>
            <div style="font-size: 11.5px; color: var(--ink-3); margin-top: 4px;">
                Caching stores dashboard data in the database so repeat page loads skip expensive queries. Data refreshes automatically when cases are created or updated.
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; max-width: 920px;">

            {{-- Cache toggle --}}
            <div class="card" style="padding: 22px 24px;">
                <div class="label-cap" style="font-size: 9px; margin-bottom: 10px;">Status</div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <div style="font-size: 14px; font-weight: 600; color: {{ ($cacheSettings['enabled'] ?? 'on') === 'on' ? 'var(--forest)' : 'var(--ink-3)' }};">
                        {{ ($cacheSettings['enabled'] ?? 'on') === 'on' ? 'Enabled' : 'Disabled' }}
                    </div>
                    <div style="width: 10px; height: 10px; border-radius: 50%; background: {{ ($cacheSettings['enabled'] ?? 'on') === 'on' ? 'var(--forest)' : 'var(--ink-4)' }};"></div>
                </div>
                <form method="POST" action="{{ route('settings.cache.toggle') }}">
                    @csrf
                    <button type="submit" class="btn-ghost" style="width: 100%; font-size: 12px;">
                        {{ ($cacheSettings['enabled'] ?? 'on') === 'on' ? 'Disable Cache' : 'Enable Cache' }}
                    </button>
                </form>
            </div>

            {{-- Cache TTL --}}
            <div class="card" style="padding: 22px 24px;">
                <div class="label-cap" style="font-size: 9px; margin-bottom: 10px;">Cache Duration</div>
                <div style="font-size: 14px; font-weight: 600; color: var(--ink); margin-bottom: 14px;">
                    {{ (int)($cacheSettings['ttl'] ?? 300) / 60 }} minutes
                </div>
                <form method="POST" action="{{ route('settings.cache.ttl') }}" style="display: flex; gap: 8px;">
                    @csrf
                    <select name="ttl" class="inp" style="flex: 1; font-size: 12px;">
                        @foreach([120 => '2 min', 300 => '5 min', 600 => '10 min', 900 => '15 min', 1800 => '30 min'] as $val => $label)
                        <option value="{{ $val }}" @selected(($cacheSettings['ttl'] ?? '300') == $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-ghost" style="font-size: 12px; white-space: nowrap;">Set</button>
                </form>
            </div>

            {{-- Flush cache --}}
            <div class="card" style="padding: 22px 24px;">
                <div class="label-cap" style="font-size: 9px; margin-bottom: 10px;">Manual Flush</div>
                <div style="font-size: 12px; color: var(--ink-3); margin-bottom: 14px; line-height: 1.5;">
                    Clear all cached data immediately. Dashboards will recompute on next load.
                </div>
                <form method="POST" action="{{ route('settings.cache.flush') }}">
                    @csrf
                    <button type="submit" class="btn-ghost" style="width: 100%; font-size: 12px; color: var(--burgundy); border-color: var(--burgundy);">
                        Clear All Caches
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
        Section 4 — About
        ═══════════════════════════════════════════════════════════════ --}}
    <div style="margin-bottom: 28px;">
        <div style="margin-bottom: 12px;">
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">{{ $user->can('lookups.manage') ? '4' : ($user->canSeeAllHubs() ? '4' : '3') }} · About</div>
            <h3 class="serif" style="font-size: 19px; font-weight: 500; margin: 0; color: var(--ink);">System &amp; data lineage</h3>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; max-width: 920px;">
            {{-- System info --}}
            <div class="card" style="padding: 22px 24px;">
                <div class="label-cap" style="font-size: 9px; margin-bottom: 12px;">System</div>
                @foreach([
                    'Build version'         => 'v1.0 · May 2026 (Laravel)',
                    'Operating organisation' => config('justice_hub.contact.organization', 'Legal Aid Society'),
                    'Programme'             => 'Justice Hub Pakistan',
                    'Geography'             => 'Sindh — ' . $hubs->count() . ' hubs',
                    'Data residency'        => 'Pakistan (encrypted)',
                    'Build date'            => 'May 2026',
                ] as $label => $value)
                <div style="display: grid; grid-template-columns: 160px 1fr; gap: 10px; padding: 8px 0; border-top: 1px solid var(--rule-2);">
                    <div style="font-size: 11.5px; color: var(--ink-3);">{{ $label }}</div>
                    <div style="font-size: 12.5px; color: var(--ink-2); font-weight: 500;">{{ $value }}</div>
                </div>
                @endforeach
            </div>

            {{-- Data lineage --}}
            <div class="card" style="padding: 22px 24px;">
                <div class="label-cap" style="font-size: 9px; margin-bottom: 12px;">Data lineage</div>
                <div style="padding: 14px 16px; margin-bottom: 14px; background: var(--parchment); border-left: 3px solid var(--moss);">
                    <div class="serif" style="font-size: 22px; font-weight: 500; color: var(--moss); line-height: 1.1;">
                        {{ $indicatorCount }} of {{ $indicatorCount }}
                    </div>
                    <div style="font-size: 11.5px; color: var(--ink-3); margin-top: 4px;">
                        indicators computed live from CMS records · 0 manual entries
                    </div>
                </div>
                @foreach($counts as $label => $value)
                <div style="display: grid; grid-template-columns: 1fr 60px; gap: 10px; padding: 6px 0; border-top: 1px solid var(--rule-2); font-size: 12px;">
                    <div style="color: var(--ink-3);">{{ $label }}</div>
                    <div class="mono" style="color: var(--ink-2); font-weight: 500; text-align: right;">{{ $value }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Active modules --}}
        <div class="card" style="padding: 22px 24px; margin-top: 14px; max-width: 920px;">
            <div class="label-cap" style="font-size: 9px; margin-bottom: 12px;">Active modules · click toggle to enable / disable</div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                @foreach([
                    ['key' => 'cases',        'name' => 'Cases',               'route' => 'cases.index',         'icon' => 'folder'],
                    ['key' => 'intake',       'name' => 'Intake',              'route' => 'intake.create',       'icon' => 'file-plus'],
                    ['key' => 'adr',          'name' => 'Mediation Scorecard', 'route' => 'services.adr',        'icon' => 'heart-handshake'],
                    ['key' => 'litigation',   'name' => 'Litigation Scorecard','route' => 'services.litigation', 'icon' => 'gavel'],
                    ['key' => 'referrals',    'name' => 'Referrals',           'route' => 'referrals.index',     'icon' => 'share-2'],
                    ['key' => 'outreach',     'name' => 'Outreach',            'route' => 'outreach.index',      'icon' => 'megaphone'],
                    ['key' => 'complaints',   'name' => 'Complaints',          'route' => 'complaints.index',    'icon' => 'alert-triangle'],
                    ['key' => 'indicators',   'name' => 'Indicators',          'route' => 'indicators.index',    'icon' => 'bar-chart-3'],
                    ['key' => 'evidence',     'name' => 'Evidence Register',   'route' => 'evidence.index',      'icon' => 'book-open'],
                    ['key' => 'feedback',     'name' => 'Client Feedback',     'route' => 'feedback.index',      'icon' => 'heart-handshake'],
                    ['key' => 'staff',        'name' => 'Staff & Training',    'route' => 'staff.index',         'icon' => 'user-check'],
                    ['key' => 'learning',     'name' => 'Learning & VfM',      'route' => 'learning.index',      'icon' => 'graduation-cap'],
                    ['key' => 'impact',       'name' => 'Impact Reports',      'route' => 'impact.index',        'icon' => 'flag'],
                ] as $mod)
                @php
                    $isOff = ($moduleSettings['module_' . $mod['key']] ?? 'on') === 'off';
                @endphp
                <div style="background: {{ $isOff ? 'var(--rule-2)' : 'var(--parchment)' }}; border: 1px solid var(--rule-2); padding: 10px 12px; display: flex; align-items: center; gap: 9px; transition: all 120ms; {{ $isOff ? 'opacity: 0.6;' : '' }}">
                    <x-dynamic-component :component="'lucide-' . $mod['icon']" style="width: 13px; height: 13px; color: {{ $isOff ? 'var(--ink-4)' : 'var(--forest)' }}; flex-shrink: 0;" />
                    <a href="{{ route($mod['route']) }}" style="font-size: 12px; color: {{ $isOff ? 'var(--ink-4)' : 'var(--ink-2)' }}; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-decoration: none;">{{ $mod['name'] }}</a>
                    <form method="POST" action="{{ route('settings.module.toggle', $mod['key']) }}" style="margin: 0;">
                        @csrf
                        <button type="submit" title="{{ $isOff ? 'Enable module' : 'Disable module' }}"
                            style="display: inline-flex; align-items: center; gap: 4px; font-size: 8.5px; font-weight: 600; letter-spacing: 0.04em; padding: 2px 7px; border-radius: 2px; cursor: pointer; font-family: inherit; border: none; text-transform: uppercase;
                            {{ $isOff ? 'background: rgba(180,30,30,0.1); color: var(--burgundy);' : 'background: rgba(74,122,92,0.14); color: var(--moss);' }}">
                            {{ $isOff ? 'Offline' : 'Live' }}
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
        Section 5 — Lookup Management (Head / lookups.manage only)
        ═══════════════════════════════════════════════════════════════ --}}
    @can('lookups.manage')
    @if($lookupData)
    @php
        $sectionNum = $user->canSeeAllHubs() ? '5' : '4';
    @endphp
    <div style="margin-bottom: 28px;" id="jh-lookup-manager">

        {{-- Section header --}}
        <div style="margin-bottom: 16px;">
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">{{ $sectionNum }} · Lookup Management</div>
            <h3 class="serif" style="font-size: 19px; font-weight: 500; margin: 0; color: var(--ink);">Dropdown options</h3>
            <p style="font-size: 12.5px; color: var(--ink-3); margin: 6px 0 0 0; line-height: 1.55; max-width: 680px;">
                Manage all dropdown options used across the system. Changes take effect immediately after saving. Deactivating an option hides it from new records without deleting historical data.
            </p>
        </div>

        {{-- Flash message --}}
        @if(session('success'))
        <div style="padding: 10px 14px; background: rgba(74,122,92,0.12); border: 1px solid var(--moss); color: var(--moss); font-size: 12.5px; margin-bottom: 14px; max-width: 920px;">
            {{ session('success') }}
        </div>
        @endif
        @if($errors->any())
        <div style="padding: 10px 14px; background: rgba(138,46,29,0.08); border: 1px solid var(--burgundy); color: var(--burgundy); font-size: 12.5px; margin-bottom: 14px; max-width: 920px;">
            {{ $errors->first() }}
        </div>
        @endif

        {{-- Two-panel layout: group list (left) + options table (right) --}}
        <div style="display: grid; grid-template-columns: 260px 1fr; gap: 14px; max-width: 1060px; align-items: start;">

            {{-- ── Left panel: group list (URL-based — only group names loaded) ── --}}
            <div style="border: 1px solid var(--rule); background: var(--paper); overflow: hidden; position: sticky; top: 16px;">

                {{-- Search + New Group --}}
                <div style="padding: 10px 12px; border-bottom: 1px solid var(--rule-2); display: flex; gap: 8px; align-items: center;">
                    <input
                        type="text"
                        id="jh-lookup-search"
                        placeholder="Search groups…"
                        class="inp mono"
                        style="flex: 1; font-size: 11px; padding: 5px 8px;"
                        oninput="jhFilterLookupGroups(this.value)"
                    />
                    <button
                        type="button"
                        onclick="jhOpenModal('new-group')"
                        style="padding: 5px 9px; background: var(--forest); color: var(--cream); border: none; cursor: pointer; font-size: 11px; font-family: inherit; white-space: nowrap; flex-shrink: 0;"
                        title="Add new group"
                    >+ Group</button>
                </div>

                {{-- Group list — AJAX loaded --}}
                <div style="max-height: 520px; overflow-y: auto;" class="jh-scroll" id="lookup-group-list">
                    @foreach($lookupGroupKeys as $gk)
                    <a href="#"
                       data-group-link="{{ $gk }}"
                       onclick="jhLoadLookupGroup('{{ $gk }}', this); return false;"
                       style="width: 100%; text-align: left; padding: 9px 14px; border: none; cursor: pointer;
                              font-family: inherit; font-size: 11.5px; display: block;
                              border-bottom: 1px solid var(--rule-2); text-decoration: none;
                              {{ $activeLookupGroup === $gk ? 'background: var(--forest); color: var(--cream); font-weight: 600;' : 'background: transparent; color: var(--ink-3);' }}"
                    >{{ $gk }}</a>
                    @endforeach
                </div>

                {{-- Group count --}}
                <div style="padding: 8px 12px; border-top: 1px solid var(--rule-2); font-size: 10.5px; color: var(--ink-4);">
                    {{ $lookupTotalGroups }} groups · {{ $lookupTotalOptions }} options total
                </div>
            </div>

            {{-- ── Right panel: AJAX-loaded options ──────────────────── --}}
            <div id="lookup-right-panel">
                <div style="padding: 48px 24px; text-align: center; color: var(--ink-4); border: 1px solid var(--rule); background: var(--paper);">
                    <div style="font-size: 13px;">Select a group from the left to manage its options.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── New Group Modal ──────────────────────────────────────── --}}
    <x-jh-modal name="new-group" title="Add New Lookup Group" maxWidth="480px">
        <form method="POST" action="{{ route('lookups.group.store') }}" style="display: flex; flex-direction: column; gap: 14px;">
            @csrf
            <div>
                <label style="font-size: 11.5px; color: var(--ink-2); font-weight: 500; display: block; margin-bottom: 6px;">
                    Group key <span style="color: var(--burgundy);">*</span>
                </label>
                <input type="text" name="group_key" required placeholder="e.g. intake.new_field" class="inp mono" style="width: 100%; font-size: 13px;" />
                <div style="font-size: 11px; color: var(--ink-4); margin-top: 5px;">Use dot notation: <span class="mono">module.field_name</span> — lowercase, dots, hyphens, underscores only.</div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div>
                    <label style="font-size: 11.5px; color: var(--ink-2); font-weight: 500; display: block; margin-bottom: 6px;">
                        First option label <span style="color: var(--burgundy);">*</span>
                    </label>
                    <input type="text" name="first_label" required placeholder="e.g. Option One" class="inp" style="width: 100%;" />
                </div>
                <div>
                    <label style="font-size: 11.5px; color: var(--ink-2); font-weight: 500; display: block; margin-bottom: 6px;">
                        First option value <span style="color: var(--burgundy);">*</span>
                    </label>
                    <input type="text" name="first_value" required placeholder="e.g. option-one" class="inp mono" style="width: 100%;" />
                </div>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 6px; border-top: 1px solid var(--rule-2);">
                <button type="button" data-bs-dismiss="modal" style="padding: 8px 18px; background: transparent; border: 1px solid var(--rule); cursor: pointer; font-family: inherit; font-size: 13px; color: var(--ink-3);">Cancel</button>
                <button type="submit" class="btn-primary" style="padding: 8px 20px; font-size: 13px;">Create Group</button>
            </div>
        </form>
    </x-jh-modal>
    @endif
    @endcan

    {{-- ═══════════════════════════════════════════════════════════════
         TRAINING COURSE MANAGEMENT
    ═══════════════════════════════════════════════════════════════ --}}
    @can('lookups.manage')
    @php
        $trainingSectionNum = $user->canSeeAllHubs() ? '6' : '5';
        $trainingCourses = \App\Models\Training::orderBy('code')->get();
    @endphp
    <div style="margin-bottom: 32px; max-width: 920px;">
        <div style="margin-bottom: 16px;">
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">{{ $trainingSectionNum }} &middot; Training Courses</div>
            <h3 class="serif" style="font-size: 19px; font-weight: 500; margin: 0; color: var(--ink);">Training Course Management</h3>
            <p style="font-size: 12.5px; color: var(--ink-3); margin: 6px 0 0 0; line-height: 1.55; max-width: 680px;">
                Manage training courses available in the Staff & Training module. Mandatory courses are required for compliance tracking.
            </p>
        </div>

        <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 14px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--rule); background: var(--paper);">
                        <th style="text-align: left; padding: 10px 14px; font-size: 9.5px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink-3);">Code</th>
                        <th style="text-align: left; padding: 10px 14px; font-size: 9.5px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink-3);">Name</th>
                        <th style="text-align: center; padding: 10px 14px; font-size: 9.5px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink-3);">Mandatory</th>
                        <th style="text-align: center; padding: 10px 14px; font-size: 9.5px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink-3);">Refresh</th>
                        <th style="text-align: right; padding: 10px 14px; font-size: 9.5px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink-3);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trainingCourses as $tc)
                    <tr style="border-bottom: 1px solid var(--rule-2);">
                        <td style="padding: 10px 14px;" class="mono">{{ $tc->code }}</td>
                        <td style="padding: 10px 14px;">{{ $tc->name }}</td>
                        <td style="padding: 10px 14px; text-align: center;">
                            @if($tc->mandatory)
                            <span style="font-size: 10px; padding: 2px 7px; background: var(--ochre-tint); color: var(--ochre); font-weight: 700;">MANDATORY</span>
                            @else
                            <span style="font-size: 10px; color: var(--ink-4);">Optional</span>
                            @endif
                        </td>
                        <td style="padding: 10px 14px; text-align: center; font-size: 12px; color: var(--ink-3);">{{ $tc->refresh ?: '—' }}</td>
                        <td style="padding: 10px 14px; text-align: right;">
                            <form method="POST" action="{{ route('settings.training.delete', $tc) }}" onsubmit="return confirm('Delete this training course?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:none; border:none; cursor:pointer; color:var(--ink-4); padding:4px;" title="Delete">
                                    <x-lucide-trash-2 style="width:13px;height:13px;" />
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="padding: 24px; text-align: center; color: var(--ink-4); font-size: 12px;">No training courses defined yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Add training course form --}}
        <div class="card" style="padding: 18px 20px;">
            <div style="font-size: 11px; font-weight: 600; color: var(--ink-2); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">Add New Course</div>
            <form method="POST" action="{{ route('settings.training.store') }}" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
                @csrf
                <div style="flex: 0 0 120px;">
                    <label style="display:block; font-size:9px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:5px;">Code *</label>
                    <input type="text" name="code" required placeholder="SOP-CORE" class="mono"
                           style="width:100%; padding:8px 10px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:12px; box-sizing:border-box; border-radius:2px;">
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label style="display:block; font-size:9px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:5px;">Name *</label>
                    <input type="text" name="name" required placeholder="Justice Hub SOPs: core operations"
                           style="width:100%; padding:8px 10px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:12px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                </div>
                <div style="flex: 0 0 100px;">
                    <label style="display:block; font-size:9px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:5px;">Refresh</label>
                    <div style="display:flex; gap:6px;">
                        <input type="number" name="refresh_value" min="1" max="365" value="12" required
                               style="width:60px; padding:8px 6px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:12px; box-sizing:border-box; border-radius:2px; text-align:center;">
                        <select name="refresh_unit" style="width:80px; padding:8px 6px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:12px; font-family:inherit; box-sizing:border-box; border-radius:2px; appearance:auto;">
                            <option value="days">Days</option>
                            <option value="months" selected>Months</option>
                            <option value="one-off">One-off</option>
                        </select>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:6px; padding-bottom:2px;">
                    <input type="checkbox" name="mandatory" value="1" id="tcMandatory" style="accent-color:var(--ochre); width:14px; height:14px;">
                    <label for="tcMandatory" style="font-size:12px; color:var(--ink-2); cursor:pointer;">Mandatory</label>
                </div>
                <button type="submit" class="btn-primary" style="font-size:11px; padding:8px 14px;">
                    <x-lucide-plus style="width:11px;height:11px;" /> Add
                </button>
            </form>
        </div>
    </div>
    @endcan

    {{-- ═══════════════════════════════════════════════════════════════
         LOCATION MANAGEMENT
    ═══════════════════════════════════════════════════════════════ --}}
    @can('lookups.manage')
    @if($locationData)
    @php $locSectionNum = $user->canSeeAllHubs() ? '6' : '5'; @endphp
    <div style="margin-bottom: 32px; max-width: 920px;">
        <div style="margin-bottom: 16px;">
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">{{ $locSectionNum }} &middot; Location Management</div>
            <h3 class="serif" style="font-size: 19px; font-weight: 500; margin: 0; color: var(--ink);">District, Taluka &amp; Union Council</h3>
            <p style="font-size: 12.5px; color: var(--ink-3); margin: 6px 0 0 0; line-height: 1.55; max-width: 680px;">
                Manage the geographic cascade used in the intake form. Click a district to view its talukas and union councils. Changes take effect immediately.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: 300px 1fr; gap: 14px;">
            {{-- Left: District list --}}
            <div class="card" style="padding: 0; overflow: hidden; max-height: 500px; display: flex; flex-direction: column;">
                <div style="padding: 12px 14px; border-bottom: 1px solid var(--rule); background: var(--paper);">
                    <div style="font-size: 11px; font-weight: 600; color: var(--ink-2); letter-spacing: 0.04em; text-transform: uppercase; margin-bottom: 8px;">Districts ({{ $locationData->count() }})</div>
                    <input type="text" id="locSearchDistrict" placeholder="Search districts…" oninput="locFilterDistricts(this.value)"
                           style="width: 100%; padding: 7px 10px; border: 1px solid var(--rule); background: var(--parchment); font-size: 12px; box-sizing: border-box; font-family: inherit; border-radius: 2px;">
                </div>
                <div style="flex: 1; overflow-y: auto;" id="locDistrictList">
                    @foreach($locationData as $d)
                    <button onclick="locSelectDistrict('{{ addslashes($d->district) }}', this)"
                            class="loc-district-btn"
                            data-district="{{ $d->district }}"
                            style="display: flex; align-items: center; justify-content: space-between; width: 100%; padding: 10px 14px; border: none; border-bottom: 1px solid var(--rule-2); background: transparent; cursor: pointer; font-family: inherit; text-align: left; transition: background 100ms;"
                            onmouseenter="this.style.background='var(--paper)'" onmouseleave="if(!this.classList.contains('active'))this.style.background='transparent'">
                        <div>
                            <div style="font-size: 13px; font-weight: 500; color: var(--ink);">{{ $d->district }}</div>
                            <div style="font-size: 10.5px; color: var(--ink-4);">{{ $d->taluka_count }} talukas &middot; {{ $d->uc_count }} UCs</div>
                        </div>
                        <x-lucide-chevron-right style="width:12px;height:12px;color:var(--ink-4);flex-shrink:0;" />
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Right: Detail panel --}}
            <div class="card" style="padding: 0; overflow: hidden; max-height: 500px; display: flex; flex-direction: column;">
                <div style="padding: 12px 14px; border-bottom: 1px solid var(--rule); background: var(--paper); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 11px; font-weight: 600; color: var(--ink-2); letter-spacing: 0.04em; text-transform: uppercase;" id="locDetailTitle">Select a district</div>
                    </div>
                    <button onclick="jhOpenModal('add-location')" class="btn-ghost" style="font-size: 11px; padding: 4px 10px;">
                        <x-lucide-plus style="width:10px;height:10px;" /> Add entry
                    </button>
                </div>
                <div style="flex: 1; overflow-y: auto; padding: 0;" id="locDetailBody">
                    <div style="padding: 40px 20px; text-align: center; color: var(--ink-4); font-size: 12px;">
                        <x-lucide-map-pin style="width:24px;height:24px;color:var(--ink-4);margin:0 auto 10px;" />
                        Click a district on the left to view its locations.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Partner Organisations ═══ --}}
    @if(auth()->user()->can('settings.view'))
    <div style="margin-bottom: 32px; max-width: 920px;">
        <div style="margin-bottom: 16px;">
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">Partner Organisations</div>
            <h3 class="serif" style="font-size: 19px; font-weight: 500; margin: 0; color: var(--ink);">Partner Network</h3>
            <p style="font-size: 12.5px; color: var(--ink-3); margin: 6px 0 0 0; line-height: 1.55; max-width: 680px;">
                Manage the organisations the Hub refers clients to. These appear in the referral log modal.
            </p>
        </div>

        {{-- Partner table --}}
        <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 16px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--rule);">
                        <th style="padding: 10px 16px; text-align: left; font-size: 10px; font-weight: 700; color: var(--ink-3); text-transform: uppercase; letter-spacing: 0.05em;">ID</th>
                        <th style="padding: 10px 16px; text-align: left; font-size: 10px; font-weight: 700; color: var(--ink-3); text-transform: uppercase; letter-spacing: 0.05em;">Name</th>
                        <th style="padding: 10px 16px; text-align: left; font-size: 10px; font-weight: 700; color: var(--ink-3); text-transform: uppercase; letter-spacing: 0.05em;">Category</th>
                        <th style="padding: 10px 16px; text-align: left; font-size: 10px; font-weight: 700; color: var(--ink-3); text-transform: uppercase; letter-spacing: 0.05em;">Focal Person</th>
                        <th style="padding: 10px 16px; text-align: left; font-size: 10px; font-weight: 700; color: var(--ink-3); text-transform: uppercase; letter-spacing: 0.05em;">MOU Expires</th>
                        <th style="padding: 10px 16px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($partners as $p)
                    <tr style="border-bottom: 1px solid var(--rule-2);" id="partner-row-{{ $p->id }}">
                        <td style="padding: 10px 16px;" class="mono" style="font-size: 11px; color: var(--ink-4);">{{ $p->id }}</td>
                        <td style="padding: 10px 16px; font-weight: 500;">{{ $p->name }}</td>
                        <td style="padding: 10px 16px; color: var(--ink-3);">{{ $p->category }}</td>
                        <td style="padding: 10px 16px; color: var(--ink-3);">{{ $p->focal_person ?? '—' }}</td>
                        <td style="padding: 10px 16px; color: var(--ink-3);">
                            @if($p->mou_expires)
                                <span style="color: {{ $p->mou_expires->isPast() ? 'var(--burgundy)' : ($p->mou_expires->diffInDays() < 60 ? 'var(--ochre)' : 'var(--moss)') }};">
                                    {{ $p->mou_expires->format('d M Y') }}
                                </span>
                            @else —
                            @endif
                        </td>
                        <td style="padding: 10px 16px; text-align: right; white-space: nowrap;">
                            <button onclick="jhEditPartner('{{ $p->id }}','{{ addslashes($p->name) }}','{{ $p->category }}','{{ addslashes($p->focal_person ?? '') }}','{{ addslashes($p->type ?? '') }}','{{ $p->mou_expires?->format('Y-m-d') ?? '' }}')"
                                style="font-size: 11.5px; padding: 4px 10px; background: none; border: 1px solid var(--rule); color: var(--ink-2); cursor: pointer; margin-right: 6px;">Edit</button>
                            <form method="POST" action="{{ route('settings.partner.destroy', $p) }}" style="display: inline;" onsubmit="return confirm('Remove {{ addslashes($p->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="font-size: 11.5px; padding: 4px 10px; background: none; border: 1px solid var(--rule); color: var(--burgundy); cursor: pointer;">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="padding: 20px 16px; text-align: center; color: var(--ink-4); font-size: 13px;">No partner organisations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Categories management --}}
        <div class="card" style="padding: 16px 20px; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                <div style="font-size: 11px; font-weight: 600; color: var(--ink-2); text-transform: uppercase; letter-spacing: 0.05em;">Partner Categories</div>
                <button type="button" onclick="document.getElementById('new-category-form').style.display = document.getElementById('new-category-form').style.display === 'none' ? 'flex' : 'none'"
                    style="font-size: 12px; padding: 4px 12px; background: none; border: 1px solid var(--rule); color: var(--ink-2); cursor: pointer; font-family: inherit;">+ Add category</button>
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px;">
                @foreach($partnerCategories as $cat)
                <span style="font-size: 12px; padding: 4px 10px; background: var(--parchment); border: 1px solid var(--rule-2); color: var(--ink-2);">{{ $cat }}</span>
                @endforeach
            </div>
            <form id="new-category-form" method="POST" action="{{ route('settings.partner.category.store') }}"
                style="display: none; align-items: center; gap: 8px; margin-top: 8px; padding-top: 10px; border-top: 1px solid var(--rule-2);">
                @csrf
                <input type="text" name="category" placeholder="e.g. Education, Mental Health, Housing…" required
                    class="inp" style="flex: 1; font-size: 13px; max-width: 320px;" />
                <button type="submit" style="padding: 8px 18px; background: var(--forest); color: var(--cream); border: none; font-size: 13px; font-family: inherit; font-weight: 600; cursor: pointer;">Save</button>
                <button type="button" onclick="document.getElementById('new-category-form').style.display='none'"
                    style="padding: 8px 14px; background: none; border: 1px solid var(--rule); color: var(--ink-3); font-size: 13px; font-family: inherit; cursor: pointer;">Cancel</button>
            </form>
        </div>

        {{-- Add partner form --}}
        <div class="card" style="padding: 18px 20px;">
            <div style="font-size: 11px; font-weight: 600; color: var(--ink-2); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">Add New Partner</div>
            <form method="POST" action="{{ route('settings.partner.store') }}">
                @csrf
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr; gap: 10px; align-items: end; flex-wrap: wrap;">
                    <div>
                        <label style="display:block; font-size:9px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:5px;">Organisation Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Rozan Counselling" class="inp" style="width:100%; font-size:12.5px; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="display:block; font-size:9px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:5px;">Category *</label>
                        <select name="category" required class="inp" style="width:100%; font-size:12.5px; box-sizing:border-box;">
                            <option value="">Select…</option>
                            @foreach($partnerCategories as $cat)
                            <option>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-size:9px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:5px;">Focal Person</label>
                        <input type="text" name="focal_person" placeholder="Contact name" class="inp" style="width:100%; font-size:12.5px; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="display:block; font-size:9px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:5px;">Type</label>
                        <input type="text" name="type" placeholder="e.g. NGO, Clinic" class="inp" style="width:100%; font-size:12.5px; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="display:block; font-size:9px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:5px;">MOU Expires</label>
                        <input type="date" name="mou_expires" class="inp" style="width:100%; font-size:12.5px; box-sizing:border-box;" />
                    </div>
                </div>
                <button type="submit" style="margin-top: 12px; padding: 8px 20px; background: var(--forest); color: var(--cream); border: none; font-size: 13px; font-family: inherit; font-weight: 600; cursor: pointer;">+ Add partner</button>
            </form>
        </div>
    </div>
    @endif

    {{-- Edit Partner Modal --}}
    <div class="modal fade" id="modal-edit-partner" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 520px; margin: 1.75rem auto;">
            <div class="modal-content" style="border: 1px solid var(--rule); border-radius: 4px; background: var(--parchment); box-shadow: 0 16px 48px rgba(0,0,0,.18);">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--rule-2); display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 15px; font-weight: 600; color: var(--ink);">Edit Partner</div>
                    <button type="button" data-bs-dismiss="modal" style="background: none; border: none; color: var(--ink-3); font-size: 18px; cursor: pointer; line-height: 1;">×</button>
                </div>
                <form id="form-edit-partner" method="POST" action="">
                    @csrf @method('PATCH')
                    <div style="padding: 20px 24px; display: flex; flex-direction: column; gap: 14px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div style="grid-column: 1/-1;">
                                <label style="display:block; font-size:9px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:5px;">Organisation Name *</label>
                                <input type="text" id="ep-name" name="name" required class="inp" style="width:100%; font-size:13px; box-sizing:border-box;" />
                            </div>
                            <div>
                                <label style="display:block; font-size:9px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:5px;">Category *</label>
                                <select id="ep-category" name="category" required class="inp" style="width:100%; font-size:13px; box-sizing:border-box;">
                                    @foreach($partnerCategories as $cat)
                                    <option>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="display:block; font-size:9px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:5px;">Focal Person</label>
                                <input type="text" id="ep-focal" name="focal_person" class="inp" style="width:100%; font-size:13px; box-sizing:border-box;" />
                            </div>
                            <div>
                                <label style="display:block; font-size:9px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:5px;">Type</label>
                                <input type="text" id="ep-type" name="type" placeholder="e.g. NGO, Clinic" class="inp" style="width:100%; font-size:13px; box-sizing:border-box;" />
                            </div>
                            <div>
                                <label style="display:block; font-size:9px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:5px;">MOU Expires</label>
                                <input type="date" id="ep-mou" name="mou_expires" class="inp" style="width:100%; font-size:13px; box-sizing:border-box;" />
                            </div>
                        </div>
                    </div>
                    <div style="padding: 14px 24px; border-top: 1px solid var(--rule-2); display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" data-bs-dismiss="modal" style="padding: 8px 18px; background: none; border: 1px solid var(--rule); color: var(--ink-2); font-size: 13px; font-family: inherit; cursor: pointer;">Cancel</button>
                        <button type="submit" style="padding: 8px 18px; background: var(--forest); color: var(--cream); border: none; font-size: 13px; font-family: inherit; font-weight: 600; cursor: pointer;">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    function jhEditPartner(id, name, category, focal, type, mou) {
        document.getElementById('ep-name').value     = name;
        document.getElementById('ep-category').value = category;
        document.getElementById('ep-focal').value    = focal;
        document.getElementById('ep-type').value     = type;
        document.getElementById('ep-mou').value      = mou;
        document.getElementById('form-edit-partner').action = '/settings/partners/' + id;
        var modal = new bootstrap.Modal(document.getElementById('modal-edit-partner'));
        modal.show();
    }
    </script>

    {{-- Add Location Modal --}}
    <div class="modal fade" id="modal-add-location" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" style="max-width: 440px; margin: 1.75rem auto;">
            <div class="modal-content" style="border: 1px solid var(--rule); border-radius: 4px; background: var(--parchment); box-shadow: 0 16px 48px rgba(0,0,0,.18);">
                <div style="padding: 18px 22px 14px; border-bottom: 1px solid var(--rule);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="serif" style="font-size: 20px; font-weight: 400; margin: 0;">Add Location</h3>
                        <button type="button" data-bs-dismiss="modal" style="background:none; border:1px solid var(--rule); cursor:pointer; padding:5px 7px; color:var(--ink-3); border-radius:3px;">
                            <x-lucide-x style="width:14px;height:14px;" />
                        </button>
                    </div>
                </div>
                <form method="POST" action="{{ route('locations.store') }}">
                    @csrf
                    <div style="padding: 18px 22px;">
                        <div style="margin-bottom: 14px;">
                            <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">District <span style="color:var(--burgundy);">*</span></label>
                            <input type="text" name="district" id="locAddDistrict" required placeholder="e.g. Hyderabad"
                                   style="width:100%; padding:9px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                        </div>
                        <div style="margin-bottom: 14px;">
                            <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Taluka / Sub-Division</label>
                            <input type="text" name="taluka" placeholder="e.g. Qasimabad"
                                   style="width:100%; padding:9px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Union Council</label>
                            <input type="text" name="union_council" placeholder="e.g. UC-125"
                                   style="width:100%; padding:9px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                        </div>
                    </div>
                    <div style="padding: 12px 22px; border-top: 1px solid var(--rule); display: flex; justify-content: flex-end; gap: 8px;">
                        <button type="button" data-bs-dismiss="modal" class="btn-ghost">Cancel</button>
                        <button type="submit" class="btn-primary" style="font-size:12px;"><x-lucide-plus style="width:11px;height:11px;" /> Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    var _locActiveDistrict = '';

    function locFilterDistricts(search) {
        var s = search.toLowerCase();
        document.querySelectorAll('.loc-district-btn').forEach(function(btn) {
            btn.style.display = btn.dataset.district.toLowerCase().includes(s) ? '' : 'none';
        });
    }

    function locSelectDistrict(district, btn) {
        _locActiveDistrict = district;
        document.querySelectorAll('.loc-district-btn').forEach(function(b) {
            b.classList.remove('active');
            b.style.background = 'transparent';
        });
        btn.classList.add('active');
        btn.style.background = 'var(--paper)';

        document.getElementById('locDetailTitle').textContent = district;
        document.getElementById('locAddDistrict').value = district;

        // Fetch details via AJAX
        var body = document.getElementById('locDetailBody');
        body.innerHTML = '<div style="padding:30px;text-align:center;color:var(--ink-4);font-size:12px;">Loading…</div>';

        fetch('/settings/locations/details?district=' + encodeURIComponent(district))
            .then(function(r) { return r.json(); })
            .then(function(rows) {
                if (rows.length === 0) {
                    body.innerHTML = '<div style="padding:30px;text-align:center;color:var(--ink-4);font-size:12px;">No locations in this district.</div>';
                    return;
                }
                // Group by taluka
                var grouped = {};
                rows.forEach(function(r) {
                    var key = r.taluka || '(No taluka)';
                    if (!grouped[key]) grouped[key] = [];
                    grouped[key].push(r);
                });

                var html = '';
                Object.keys(grouped).sort().forEach(function(taluka) {
                    html += '<div style="padding:8px 14px 4px;background:var(--paper);border-bottom:1px solid var(--rule-2);">';
                    html += '<div style="font-size:11px;font-weight:600;color:var(--ink-2);letter-spacing:0.04em;text-transform:uppercase;">' + taluka + ' <span style="font-weight:400;color:var(--ink-4);">(' + grouped[taluka].length + ')</span></div>';
                    html += '</div>';
                    grouped[taluka].forEach(function(loc) {
                        html += '<div style="display:flex;align-items:center;justify-content:space-between;padding:7px 14px 7px 24px;border-bottom:1px solid var(--rule-2);font-size:12px;color:var(--ink-2);">';
                        html += '<span>' + (loc.union_council || '<em style=color:var(--ink-4)>—</em>') + '</span>';
                        html += '<form method="POST" action="/settings/locations/' + loc.id + '" onsubmit="return confirm(\'Delete this entry?\')" style="margin:0;">';
                        html += '<input type="hidden" name="_token" value="' + document.querySelector('meta[name=csrf-token]').content + '">';
                        html += '<input type="hidden" name="_method" value="DELETE">';
                        html += '<button type="submit" style="background:none;border:none;cursor:pointer;color:var(--ink-4);padding:2px;" title="Delete">';
                        html += '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>';
                        html += '</button></form></div>';
                    });
                });
                body.innerHTML = html;
            });
    }
    </script>
    @endif
    @endcan

    {{-- Closing note --}}
    <div style="padding: 18px 22px; background: var(--paper); border: 1px solid var(--rule); border-left: 3px solid var(--ochre); font-size: 12px; color: var(--ink-2); line-height: 1.65; max-width: 920px;">
        <strong style="color: var(--ink);">What&rsquo;s next.</strong>
        Cross-session preference persistence, audit logging of every CMS write, end-to-end document storage with version history, and an SMS feedback channel. Hub-level data exports and an offline-first mobile companion are on the roadmap for paralegal field use.
    </div>
</div>

{{-- ══ AJAX Lookup Group Loader ══ --}}
<script>
var _lookupCsrf = '{{ csrf_token() }}';
var _lookupJsonUrl = '{{ route("lookups.options.json") }}';
var _lookupStoreUrl = '{{ route("lookups.option.store") }}';
var _lookupUpdateUrl = '/settings/lookups/options/';
var _lookupToggleUrl = '/settings/lookups/options/';
var _lookupDestroyUrl = '/settings/lookups/options/';

function jhLoadLookupGroup(groupKey, linkEl) {
    // Highlight active link
    document.querySelectorAll('#lookup-group-list a').forEach(function(a) {
        a.style.background = 'transparent';
        a.style.color = 'var(--ink-3)';
        a.style.fontWeight = '400';
    });
    if (linkEl) {
        linkEl.style.background = 'var(--forest)';
        linkEl.style.color = 'var(--cream)';
        linkEl.style.fontWeight = '600';
    }

    var panel = document.getElementById('lookup-right-panel');
    panel.innerHTML = '<div style="padding:40px; text-align:center; color:var(--ink-4); font-size:12px;">Loading...</div>';

    fetch(_lookupJsonUrl + '?group_key=' + encodeURIComponent(groupKey), { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var gk = data.group_key;
            var opts = data.options || [];
            var html = '';

            // Header + Add form
            html += '<div style="background:var(--paper);border:1px solid var(--rule);padding:14px 16px;margin-bottom:10px;">';
            html += '<div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">';
            html += '<div><div class="mono" style="font-size:13px;font-weight:600;color:var(--ink);">' + gk + '</div>';
            html += '<div style="font-size:11px;color:var(--ink-4);margin-top:2px;">' + data.active_count + ' active &middot; ' + data.inactive_count + ' inactive</div></div></div>';
            html += '<details style="margin-top:4px;"><summary style="font-size:12px;color:var(--forest);cursor:pointer;font-weight:500;list-style:none;display:flex;align-items:center;gap:6px;">+ Add new option to this group</summary>';
            html += '<form method="POST" action="' + _lookupStoreUrl + '" style="margin-top:10px;display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:8px;align-items:end;">';
            html += '<input type="hidden" name="_token" value="' + _lookupCsrf + '">';
            html += '<input type="hidden" name="group_key" value="' + gk + '">';
            html += '<div><label style="font-size:10px;color:var(--ink-3);display:block;margin-bottom:4px;">Label *</label>';
            html += '<input type="text" name="label" required placeholder="e.g. Land Dispute" class="inp" style="width:100%;font-size:12px;"></div>';
            html += '<div><label style="font-size:10px;color:var(--ink-3);display:block;margin-bottom:4px;">Value (slug) *</label>';
            html += '<input type="text" name="value" required placeholder="e.g. land-dispute" class="inp mono" style="width:100%;font-size:12px;"></div>';
            html += '<div><label style="font-size:10px;color:var(--ink-3);display:block;margin-bottom:4px;">Parent (optional)</label>';
            html += '<input type="text" name="parent_value" placeholder="e.g. Legal Advice" class="inp" style="width:100%;font-size:12px;"></div>';
            html += '<button type="submit" class="btn-primary" style="padding:7px 16px;font-size:12px;white-space:nowrap;height:fit-content;">Add</button>';
            html += '</form></details></div>';

            // Options table
            html += '<div style="border:1px solid var(--rule);background:var(--paper);">';
            html += '<table style="width:100%;border-collapse:collapse;font-size:12px;"><thead>';
            html += '<tr style="border-bottom:1px solid var(--rule);">';
            var ths = ['Order', 'Label', 'Value', 'Parent', 'Status', ''];
            var thWidths = ['60px', '', '140px', '110px', '80px', '80px'];
            ths.forEach(function(t, i) {
                html += '<th style="padding:9px 12px;text-align:' + (t === 'Status' ? 'center' : 'left') + ';font-size:9.5px;letter-spacing:0.06em;text-transform:uppercase;color:var(--ink-3);font-weight:600;' + (thWidths[i] ? 'width:' + thWidths[i] + ';' : '') + '">' + t + '</th>';
            });
            html += '</tr></thead><tbody>';

            if (opts.length === 0) {
                html += '</tbody></table><div style="padding:20px;text-align:center;color:var(--ink-4);font-size:12px;">No options in this group yet.</div>';
            } else {
                opts.forEach(function(o) {
                    var active = o.is_active ? true : false;
                    var statusBadge = active
                        ? '<span style="font-size:9.5px;font-weight:600;letter-spacing:0.04em;padding:2px 7px;background:rgba(74,122,92,0.14);color:var(--moss);text-transform:uppercase;">Active</span>'
                        : '<span style="font-size:9.5px;font-weight:600;letter-spacing:0.04em;padding:2px 7px;background:rgba(138,46,29,0.10);color:var(--burgundy);text-transform:uppercase;">Off</span>';
                    var parentCell = o.parent_value
                        ? '<span class="mono" style="font-size:10.5px;color:var(--ink-4);background:var(--parchment);padding:2px 6px;border:1px solid var(--rule-2);">' + o.parent_value + '</span>'
                        : '<span style="color:var(--ink-4);font-size:11px;">&mdash;</span>';
                    var labelStyle = !active ? 'opacity:0.45;text-decoration:line-through;' : '';

                    html += '<tr style="border-bottom:1px solid var(--rule-2);">';
                    html += '<td style="padding:9px 12px;text-align:center;"><span class="mono" style="font-size:11.5px;color:var(--ink-3);">' + o.sort_order + '</span></td>';
                    html += '<td style="padding:9px 12px;font-size:12.5px;color:var(--ink);' + labelStyle + '">' + o.label + '</td>';
                    html += '<td style="padding:9px 12px;"><span class="mono" style="font-size:11px;color:var(--ink-3);">' + o.value + '</span></td>';
                    html += '<td style="padding:9px 12px;">' + parentCell + '</td>';
                    html += '<td style="padding:9px 12px;text-align:center;">' + statusBadge + '</td>';

                    // Actions
                    html += '<td style="padding:8px 12px;text-align:right;white-space:nowrap;">';
                    // Toggle
                    html += '<form method="POST" action="' + _lookupToggleUrl + o.id + '/toggle" style="display:inline;">';
                    html += '<input type="hidden" name="_token" value="' + _lookupCsrf + '">';
                    html += '<button type="submit" title="' + (active ? 'Deactivate' : 'Activate') + '" style="padding:4px 8px;background:transparent;border:1px solid var(--rule-2);cursor:pointer;font-family:inherit;font-size:11px;' + (active ? 'color:var(--ochre);' : 'color:var(--moss);') + '">';
                    html += active ? '&#x1F6AB;' : '&#x2705;';
                    html += '</button></form> ';
                    // Delete
                    html += '<form method="POST" action="' + _lookupDestroyUrl + o.id + '" style="display:inline;" onsubmit="return confirm(\'Delete this option?\');">';
                    html += '<input type="hidden" name="_token" value="' + _lookupCsrf + '">';
                    html += '<input type="hidden" name="_method" value="DELETE">';
                    html += '<button type="submit" title="Delete" style="padding:4px 8px;background:transparent;border:1px solid var(--rule-2);cursor:pointer;color:var(--burgundy);font-family:inherit;font-size:11px;">&#x1F5D1;</button>';
                    html += '</form>';
                    html += '</td></tr>';
                });
                html += '</tbody></table>';
            }
            html += '</div>';

            panel.innerHTML = html;
        })
        .catch(function() {
            panel.innerHTML = '<div style="padding:40px;text-align:center;color:var(--burgundy);font-size:12px;">Failed to load group options.</div>';
        });
}

// Auto-load if there was already an active group (from URL param on page load)
@if($activeLookupGroup)
document.addEventListener('DOMContentLoaded', function() {
    var link = document.querySelector('[data-group-link="{{ $activeLookupGroup }}"]');
    jhLoadLookupGroup('{{ $activeLookupGroup }}', link);
});
@endif
</script>
</x-layouts.app>
