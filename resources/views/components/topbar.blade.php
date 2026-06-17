@php
    $pageTitles = [
        'dashboard'                  => 'Strategic Overview',
        'dashboard.litigation-adr'   => 'Litigation & ADR Dashboard',
        'dashboard.lcd'              => 'LCD Dashboard',
        'cases.index'                => 'Case Management',
        'cases.show'                 => 'Case File',
        'intake.create'              => 'Client Intake & Registration',
        'services.adr'               => 'ADR Scorecard',
        'services.adr-calendar'      => 'ADR Calendar',
        'services.litigation'        => 'Litigation Scorecard',
        'services.litigation-calendar'=> 'Litigation Calendar',
        'referrals.index'            => 'Referrals & Linkages',
        'outreach.index'             => 'Outreach & Legal Literacy',
        'complaints.index'           => 'Complaints Register',
        'indicators.index'           => 'Results Framework & Indicators',
        'evidence.index'             => 'Evidence Register',
        'feedback.index'             => 'Client Feedback',
        'staff.index'                => 'Staff & Training Register',
        'learning.index'             => 'Learning, Evidence & VfM',
        'impact.index'               => 'Impact Reports',
        'settings.index'             => 'Settings & Preferences',
    ];
    $currentRoute = Route::currentRouteName() ?? '';
    $pageTitle    = $pageTitles[$currentRoute] ?? 'Justice Hub';
    $isDark       = session('theme', 'light') === 'dark';
@endphp

<header style="background: var(--paper); border-bottom: 1px solid var(--rule); padding: 0 28px; height: 56px; display: flex; align-items: center; gap: 18px; flex-shrink: 0;">

    {{-- Breadcrumb / Page title --}}
    <div style="flex: 1; min-width: 0;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <div class="label-cap" style="font-size: 9px; white-space: nowrap;">Justice Hub</div>
            <x-lucide-chevron-right style="width: 10px; height: 10px; color: var(--ink-4);" />
            <div style="font-size: 14px; font-weight: 500; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ $pageTitle }}
            </div>
        </div>
    </div>

    {{-- Global search --}}
    <div data-jh-search style="position: relative; width: 280px;">
        <div style="position: relative;">
            <x-lucide-search style="width: 14px; height: 14px; position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--ink-4);" />
            <input
                id="jh-search-input"
                type="text"
                placeholder="Search cases… ⌘K"
                class="inp"
                style="padding-left: 34px; padding-right: 12px; font-size: 13px; height: 36px;"
                autocomplete="off"
            >
        </div>

        {{-- Search results dropdown --}}
        <div
            id="jh-search-dropdown"
            style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 100; background: var(--paper); border: 1px solid var(--rule); box-shadow: var(--shadow-card); max-height: 320px; overflow-y: auto; margin-top: 4px;"
        >
            <div id="jh-search-results"></div>
        </div>
    </div>

    {{-- Theme toggle --}}
    @php $themeUrl = route('settings.theme'); $csrf = csrf_token(); @endphp
    <button
        onclick="jhSetTheme(document.documentElement.getAttribute('data-theme')==='dark'?'light':'dark', '{{ $csrf }}', '{{ $themeUrl }}')"
        style="background: none; border: 1px solid var(--rule); padding: 7px 9px; cursor: pointer; color: var(--ink-2); display: flex; align-items: center;"
        title="Toggle theme"
    >
        <span id="jh-icon-sun"  style="{{ $isDark ? 'display:none' : '' }}">
            <x-lucide-sun style="width: 15px; height: 15px;" />
        </span>
        <span id="jh-icon-moon" style="{{ $isDark ? '' : 'display:none' }}">
            <x-lucide-moon style="width: 15px; height: 15px;" />
        </span>
    </button>

    {{-- Notification bell --}}
    <div style="position: relative;" id="jh-notif-wrap">
        <button id="jh-notif-btn" onclick="jhToggleNotifications()"
            style="background: none; border: none; cursor: pointer; color: var(--ink-3); position: relative; padding: 4px; display:flex; align-items:center;">
            <x-lucide-bell style="width: 17px; height: 17px;" />
            <span id="jh-notif-badge"
                style="display:none; position:absolute; top:-3px; right:-3px; min-width:16px; height:16px; background:var(--burgundy); color:#fff; border-radius:99px; font-size:9px; font-weight:700; line-height:16px; text-align:center; padding:0 3px;">
                0
            </span>
        </button>

        {{-- Dropdown panel --}}
        <div id="jh-notif-panel"
            style="display:none; position:absolute; top:calc(100% + 10px); right:-8px; width:360px; background:var(--paper); border:1px solid var(--rule); box-shadow:0 8px 28px rgba(0,0,0,.13); z-index:9990; max-height:480px; display:none; flex-direction:column;">

            {{-- Header --}}
            <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-bottom:1px solid var(--rule);">
                <span style="font-size:12px; font-weight:600; color:var(--ink);">Notifications</span>
                <button onclick="jhMarkAllRead()" style="background:none; border:none; cursor:pointer; font-size:11px; color:var(--forest); padding:0;">Mark all read</button>
            </div>

            {{-- List --}}
            <div id="jh-notif-list" style="overflow-y:auto; flex:1; max-height:380px;">
                <div id="jh-notif-empty" style="padding:28px 16px; text-align:center; font-size:12px; color:var(--ink-4);">No notifications</div>
            </div>
        </div>
    </div>

    {{-- User dropdown (Bootstrap) --}}
    <div class="dropdown" style="position: relative;">
        <button class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"
            style="background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; padding: 4px;">
            <div style="width: 28px; height: 28px; background: var(--forest); color: var(--cream); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 500;">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <x-lucide-chevron-down style="width: 11px; height: 11px; color: var(--ink-4);" />
        </button>

        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 180px; padding: 0; border: 1px solid var(--rule); border-radius: 4px; background: var(--paper); box-shadow: 0 6px 20px rgba(0,0,0,.12); margin-top: 8px;">
            <li style="padding: 12px 16px; border-bottom: 1px solid var(--rule-2);">
                <div style="font-size: 13px; font-weight: 500; color: var(--ink);">{{ auth()->user()->name }}</div>
                <div style="font-size: 11px; color: var(--ink-3); margin-top: 2px;">{{ auth()->user()->designation ?: auth()->user()->role->label() }}</div>
            </li>
            <li>
                <a href="{{ route('profile.edit') }}" class="dropdown-item tr-hover"
                   style="display: block; padding: 10px 16px; font-size: 13px; color: var(--ink-2); text-decoration: none; background: transparent;">
                    My Profile
                </a>
            </li>
            @if(auth()->user()->can('settings.view'))
            <li>
                <a href="{{ route('settings.index') }}" class="dropdown-item tr-hover"
                   style="display: block; padding: 10px 16px; font-size: 13px; color: var(--ink-2); text-decoration: none; background: transparent;">
                    Settings
                </a>
            </li>
            @endif
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item tr-hover"
                        style="width: 100%; padding: 10px 16px; font-size: 13px; color: var(--burgundy); text-align: left; border: none; background: none; cursor: pointer; font-family: inherit;">
                        Sign Out
                    </button>
                </form>
            </li>
        </ul>
    </div>
</header>

{{-- Remove Bootstrap's auto-caret on the user dropdown toggle --}}
<style>
    header .dropdown-toggle::after { display: none !important; }
    header .dropdown-item:hover, header .dropdown-item:focus { background: var(--parchment-2) !important; color: var(--ink) !important; }
    .jh-notif-item { display:flex; gap:10px; padding:11px 16px; border-bottom:1px solid var(--rule-2); cursor:pointer; text-decoration:none; }
    .jh-notif-item:hover { background:var(--parchment-2); }
    .jh-notif-item.unread { background:var(--parchment); }
    .jh-notif-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; margin-top:5px; }
</style>
<script>
(function () {
    const _notifUrl     = '{{ route("notifications.index") }}';
    const _readAllUrl   = '{{ route("notifications.read-all") }}';
    const _csrf         = '{{ csrf_token() }}';
    let _open = false;

    const typeColors = { assigned:'var(--forest)', updated:'var(--ink-3)', approved:'var(--moss)', rejected:'var(--burgundy)', resolved:'var(--moss)', sla:'var(--ochre)', info:'var(--ink-3)' };

    function post(url) {
        return fetch(url, { method:'POST', headers:{ 'X-CSRF-TOKEN': _csrf, 'Accept':'application/json' } });
    }

    window.jhToggleNotifications = function () {
        _open = !_open;
        const panel = document.getElementById('jh-notif-panel');
        panel.style.display = _open ? 'flex' : 'none';
        if (_open) jhLoadNotifications();
    };

    window.jhMarkAllRead = function () {
        post(_readAllUrl).then(() => jhLoadNotifications());
    };

    window.jhLoadNotifications = function () {
        fetch(_notifUrl, { headers:{ 'Accept':'application/json' } })
            .then(r => r.json())
            .then(data => {
                // Badge
                const badge = document.getElementById('jh-notif-badge');
                if (data.unread > 0) {
                    badge.textContent = data.unread > 99 ? '99+' : data.unread;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }

                // List
                const list  = document.getElementById('jh-notif-list');
                const empty = document.getElementById('jh-notif-empty');
                if (!data.items.length) {
                    list.innerHTML = '';
                    list.appendChild(empty);
                    empty.style.display = '';
                    return;
                }
                empty.style.display = 'none';
                list.innerHTML = data.items.map(n => {
                    const color = typeColors[n.type] || typeColors.info;
                    const href  = n.action_url ? `href="${n.action_url}"` : '';
                    const cls   = n.read ? '' : 'unread';
                    return `<a class="jh-notif-item ${cls}" ${href}
                                onclick="jhReadNotif('${n.id}', this)"
                                style="color:inherit;">
                                <span class="jh-notif-dot" style="background:${n.read ? 'var(--rule)' : color};"></span>
                                <div style="flex:1; min-width:0;">
                                    <div style="font-size:12px; font-weight:${n.read ? '400' : '600'}; color:var(--ink); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${n.title}</div>
                                    <div style="font-size:11px; color:var(--ink-3); margin-top:2px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">${n.message}</div>
                                    <div style="font-size:10px; color:var(--ink-4); margin-top:3px;">${n.time}</div>
                                </div>
                            </a>`;
                }).join('');
            })
            .catch(() => {});
    };

    window.jhReadNotif = function (id, el) {
        if (el.classList.contains('unread')) {
            el.classList.remove('unread');
            el.querySelector('.jh-notif-dot').style.background = 'var(--rule)';
            el.querySelector('div > div:first-child').style.fontWeight = '400';
            post(`/notifications/${id}/read`).then(() => {
                const badge = document.getElementById('jh-notif-badge');
                const cur = parseInt(badge.textContent) || 0;
                if (cur <= 1) { badge.style.display = 'none'; }
                else { badge.textContent = cur - 1; }
            });
        }
    };

    // Close on outside click
    document.addEventListener('click', function (e) {
        if (_open && !document.getElementById('jh-notif-wrap')?.contains(e.target)) {
            _open = false;
            document.getElementById('jh-notif-panel').style.display = 'none';
        }
    });

    // Load badge count on page load
    document.addEventListener('DOMContentLoaded', jhLoadNotifications);

    // Poll every 60 seconds
    setInterval(function () { if (!_open) jhLoadNotifications(); }, 60000);
})();
</script>
