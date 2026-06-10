<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Login — Justice Hub CRM' }}</title>
    @vite(['resources/css/app.css', 'resources/css/justice-hub.css', 'resources/js/app.js'])
</head>
<body class="jh-app" style="margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--parchment);">
    <div style="width: 100%; max-width: 420px; padding: 24px;">

        {{-- Logo --}}
        <div style="text-align: center; margin-bottom: 32px;">
            <div style="display: inline-flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; background: var(--ochre-2); display: flex; align-items: center; justify-content: center;">
                    <x-lucide-scale style="width: 18px; height: 18px; color: var(--forest);" />
                </div>
                <div>
                    <div class="serif" style="font-size: 22px; font-weight: 500; line-height: 1.05; letter-spacing: -0.01em; color: var(--ink);">
                        Justice Hub
                    </div>
                    <div class="mono" style="font-size: 9.5px; color: var(--ink-3); letter-spacing: 0.08em; margin-top: 2px;">
                        LAS · CMS · SINCE 2013
                    </div>
                </div>
            </div>
        </div>

        {{-- Auth card --}}
        <div class="card" style="padding: 32px;">
            {{ $slot }}
        </div>

        <div class="mono" style="text-align: center; font-size: 10px; color: var(--ink-4); margin-top: 24px; letter-spacing: 0.06em;">
            Legal Aid Society · Justice Hub CRM
        </div>
    </div>
</body>
</html>
