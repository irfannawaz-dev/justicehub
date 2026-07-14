<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['sd', 'ur']) ? 'rtl' : 'ltr' }}" data-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Justice Hub CRM' }}</title>
    @vite(['resources/css/app.css', 'resources/css/justice-hub.css', 'resources/js/app.js'])
</head>
<body class="jh-app" style="margin: 0; min-height: 100vh;">
    <div style="display: flex; min-height: 100vh; direction: ltr;">

        {{-- Sidebar --}}
        @include('components.sidebar')

        {{-- Main area --}}
        <div style="flex: 1; display: flex; flex-direction: column; min-width: 0; direction: {{ in_array(app()->getLocale(), ['sd', 'ur']) ? 'rtl' : 'ltr' }};">

            {{-- Top bar --}}
            @include('components.topbar')

            {{-- Page content --}}
            <main class="jh-scroll" style="flex: 1; overflow-y: auto; background: var(--parchment);">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Global flash toast --}}
    @if(session('success') || session('error') || session('warning'))
    <div id="jh-toast"
         style="position:fixed;bottom:28px;right:28px;z-index:9999;max-width:420px;padding:14px 18px;border-radius:8px;
                font-size:13px;line-height:1.45;color:#fff;box-shadow:0 4px 20px rgba(0,0,0,0.18);
                background:{{ session('error') ? '#8b1e1e' : (session('warning') ? '#b87319' : '#2d6a4f') }};">
        <div style="display:flex;align-items:flex-start;gap:10px;">
            <span style="font-size:16px;line-height:1;">{{ session('error') ? '✕' : (session('warning') ? '!' : '✓') }}</span>
            <span>{{ session('success') ?? session('error') ?? session('warning') }}</span>
        </div>
    </div>
    <script>
        setTimeout(function() {
            var t = document.getElementById('jh-toast');
            if (t) { t.style.transition='opacity 0.4s'; t.style.opacity='0'; setTimeout(function(){t.remove();},400); }
        }, 5000);
    </script>
    @endif

    @if(session('open_slip'))
    <script>
        window.open({{ Js::from(session('open_slip')) }}, '_blank');
    </script>
    @endif

    @stack('scripts')

    {{-- Sidebar toggle --}}
    <style>
        #jh-sidebar.jh-sidebar-collapsed {
            width: 0 !important;
            opacity: 0;
            overflow: hidden;
            border-right: none;
            border-left: none;
        }
        #jh-sidebar-toggle.jh-sidebar-collapsed {
            background: var(--parchment-2) !important;
            color: var(--ink) !important;
        }
    </style>
    <script>
    (function () {
        var sidebar = document.getElementById('jh-sidebar');
        var btn     = document.getElementById('jh-sidebar-toggle');

        function apply(collapsed) {
            if (!sidebar) return;
            if (collapsed) {
                sidebar.classList.add('jh-sidebar-collapsed');
                btn && btn.classList.add('jh-sidebar-collapsed');
            } else {
                sidebar.classList.remove('jh-sidebar-collapsed');
                btn && btn.classList.remove('jh-sidebar-collapsed');
            }
        }

        // Restore preference on load
        apply(localStorage.getItem('jh_sidebar_collapsed') === '1');

        window.jhToggleSidebar = function () {
            var collapsed = sidebar.classList.contains('jh-sidebar-collapsed');
            apply(!collapsed);
            localStorage.setItem('jh_sidebar_collapsed', collapsed ? '0' : '1');
        };
    }());
    </script>
</body>
</html>
