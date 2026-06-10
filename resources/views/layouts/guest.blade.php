<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sign In' }} — Justice Hub CRM</title>
    @vite(['resources/css/app.css', 'resources/css/justice-hub.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        /* ── Shell ───────────────────────────────────────────────── */
        .auth-shell {
            display: flex;
            min-height: 100vh;
        }

        /* ── Left: Brand Panel ──────────────────────────────────── */
        .auth-brand {
            flex: 0 0 54%;
            background: var(--forest);
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 48px 56px;
            overflow: hidden;
        }

        /* grain overlay */
        .auth-brand-grain {
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none;
            opacity: 0.6;
        }

        .auth-brand-content {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* Wordmark */
        .auth-wordmark {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .auth-icon-box {
            width: 44px; height: 44px;
            background: var(--ochre-2);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .auth-brand-name {
            font-family: 'Fraunces', Georgia, serif;
            font-size: 24px;
            font-weight: 500;
            letter-spacing: -0.01em;
            color: var(--cream);
            line-height: 1.05;
        }
        .auth-brand-sub {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9px;
            color: rgba(247,243,235,.40);
            letter-spacing: 0.10em;
            text-transform: uppercase;
            margin-top: 3px;
        }

        /* Middle content */
        .auth-mid {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-headline {
            font-family: 'Fraunces', Georgia, serif;
            font-size: 38px;
            font-weight: 400;
            line-height: 1.16;
            letter-spacing: -0.025em;
            color: var(--cream);
            margin: 0 0 14px;
        }
        .auth-headline em {
            font-style: italic;
            color: var(--ochre-2);
        }
        .auth-subtext {
            font-size: 13.5px;
            color: rgba(247,243,235,.55);
            line-height: 1.65;
            max-width: 360px;
            margin-bottom: 36px;
        }

        .auth-rule {
            width: 36px; height: 2px;
            background: var(--ochre);
            margin-bottom: 32px;
        }

        /* Feature list */
        .auth-feature {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 22px;
        }
        .auth-feature-icon {
            width: 34px; height: 34px;
            background: rgba(217,160,91,.10);
            border: 1px solid rgba(217,160,91,.18);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .auth-feature-title {
            font-size: 13px;
            font-weight: 500;
            color: var(--cream);
            margin-bottom: 3px;
        }
        .auth-feature-desc {
            font-size: 12px;
            color: rgba(247,243,235,.45);
            line-height: 1.55;
        }

        /* Stats bar */
        .auth-stats {
            display: flex;
            gap: 0;
            margin-top: 40px;
            padding-top: 28px;
            border-top: 1px solid rgba(217,160,91,.14);
        }
        .auth-stat {
            flex: 1;
            padding-right: 20px;
        }
        .auth-stat + .auth-stat {
            padding-left: 20px;
            padding-right: 20px;
            border-left: 1px solid rgba(247,243,235,.08);
        }
        .auth-stat-num {
            font-family: 'Fraunces', Georgia, serif;
            font-size: 26px;
            font-weight: 500;
            color: var(--ochre-2);
            line-height: 1;
        }
        .auth-stat-label {
            font-size: 10px;
            color: rgba(247,243,235,.38);
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-top: 5px;
        }

        /* Bottom tag */
        .auth-bottom {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9.5px;
            color: rgba(247,243,235,.25);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        /* ── Right: Form Panel ──────────────────────────────────── */
        .auth-form-panel {
            flex: 1;
            background: var(--parchment);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
        }

        .auth-form-wrap {
            width: 100%;
            max-width: 390px;
        }

        /* Form heading */
        .auth-form-title {
            font-family: 'Fraunces', Georgia, serif;
            font-size: 28px;
            font-weight: 500;
            letter-spacing: -0.02em;
            color: var(--ink);
            margin: 0 0 6px;
        }
        .auth-form-sub {
            font-size: 13.5px;
            color: var(--ink-3);
            margin-bottom: 36px;
            line-height: 1.5;
        }

        /* Field label */
        .auth-label {
            display: block;
            font-size: 10.5px;
            font-weight: 500;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: var(--ink-3);
            margin-bottom: 7px;
        }

        /* Error */
        .auth-error {
            font-size: 12px;
            color: var(--burgundy);
            margin-top: 5px;
        }

        /* Remember checkbox */
        .auth-remember {
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: 13px;
            color: var(--ink-3);
            cursor: pointer;
            user-select: none;
        }
        .auth-remember input[type="checkbox"] {
            width: 14px; height: 14px;
            accent-color: var(--forest);
            cursor: pointer;
            flex-shrink: 0;
        }

        /* Submit button override for full width */
        .auth-submit {
            display: block;
            width: 100%;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.01em;
            text-align: center;
            cursor: pointer;
        }

        /* Divider line */
        .auth-field-divider {
            height: 1px;
            background: var(--rule-2);
            margin: 8px 0 24px;
        }

        /* Alert/status box */
        .auth-alert-success {
            background: rgba(74,122,92,.08);
            border: 1px solid rgba(74,122,92,.22);
            color: var(--moss);
            font-size: 13px;
            padding: 10px 14px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        /* Forgot password link */
        .auth-link {
            font-size: 12px;
            color: var(--ink-4);
            text-decoration: none;
            transition: color .15s;
        }
        .auth-link:hover { color: var(--ink-2); }

        /* Powered-by footer */
        .auth-powered {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9.5px;
            color: var(--ink-4);
            letter-spacing: 0.06em;
            text-align: center;
            margin-top: 52px;
            text-transform: uppercase;
        }

        /* Mobile: hide brand panel */
        .auth-mobile-logo { display: none; }

        @media (max-width: 900px) {
            .auth-brand { display: none; }
            .auth-form-panel { padding: 40px 24px; justify-content: flex-start; padding-top: 56px; }
            .auth-mobile-logo { display: flex; align-items: center; gap: 12px; margin-bottom: 40px; }
        }
    </style>
</head>
<body class="jh-app" style="margin:0;">

<div class="auth-shell">

    {{-- ─── Left: Branding Panel ───────────────────────────────── --}}
    <div class="auth-brand">
        <div class="auth-brand-grain"></div>
        <div class="auth-brand-content">

            {{-- Wordmark --}}
            <div class="auth-wordmark">
                <div class="auth-icon-box">
                    <x-lucide-scale style="width:21px;height:21px;color:var(--forest);" />
                </div>
                <div>
                    <div class="auth-brand-name">Justice Hub</div>
                    <div class="auth-brand-sub">Legal Aid Society · CMS · Sindh</div>
                </div>
            </div>

            {{-- Centre content --}}
            <div class="auth-mid">
                <h1 class="auth-headline">
                    Access to justice,<br><em>powered by data.</em>
                </h1>
                <p class="auth-subtext">
                    A unified case management and monitoring system coordinating services across 6 hub centres in Sindh, Pakistan.
                </p>

                <div class="auth-rule"></div>

                <div class="auth-feature">
                    <div class="auth-feature-icon">
                        <x-lucide-folder style="width:15px;height:15px;color:var(--ochre-2);" />
                    </div>
                    <div>
                        <div class="auth-feature-title">End-to-End Case Management</div>
                        <div class="auth-feature-desc">From intake to resolution — litigation, ADR, advice, and referrals in one place.</div>
                    </div>
                </div>

                <div class="auth-feature">
                    <div class="auth-feature-icon">
                        <x-lucide-trending-up style="width:15px;height:15px;color:var(--ochre-2);" />
                    </div>
                    <div>
                        <div class="auth-feature-title">29-Indicator M&amp;E Framework</div>
                        <div class="auth-feature-desc">Goal, outcome, and output indicators auto-computed from live case data.</div>
                    </div>
                </div>

                <div class="auth-feature">
                    <div class="auth-feature-icon">
                        <x-lucide-shield style="width:15px;height:15px;color:var(--ochre-2);" />
                    </div>
                    <div>
                        <div class="auth-feature-title">Role-Based Secure Access</div>
                        <div class="auth-feature-desc">6 roles with granular permissions — Head, Hub Admin, Data Entry, M&amp;E, Investigator, Viewer.</div>
                    </div>
                </div>

                {{-- Stats --}}
                <div class="auth-stats">
                    <div class="auth-stat">
                        <div class="auth-stat-num">6</div>
                        <div class="auth-stat-label">Hub Centres</div>
                    </div>
                    <div class="auth-stat">
                        <div class="auth-stat-num">29</div>
                        <div class="auth-stat-label">Indicators</div>
                    </div>
                    <div class="auth-stat">
                        <div class="auth-stat-num">14</div>
                        <div class="auth-stat-label">Partners</div>
                    </div>
                    <div class="auth-stat">
                        <div class="auth-stat-num">2013</div>
                        <div class="auth-stat-label">Est.</div>
                    </div>
                </div>
            </div>

            <div class="auth-bottom">Legal Aid Society · Sindh, Pakistan</div>
        </div>
    </div>

    {{-- ─── Right: Form Panel ──────────────────────────────────── --}}
    <div class="auth-form-panel">
        <div class="auth-form-wrap">

            {{-- Mobile-only logo --}}
            <div class="auth-mobile-logo">
                <div style="width:36px;height:36px;background:var(--ochre-2);display:flex;align-items:center;justify-content:center;">
                    <x-lucide-scale style="width:17px;height:17px;color:var(--forest);" />
                </div>
                <div>
                    <div class="serif" style="font-size:20px;font-weight:500;color:var(--ink);line-height:1.05;">Justice Hub</div>
                    <div class="mono" style="font-size:9px;color:var(--ink-4);letter-spacing:.08em;">LAS · CMS · SINCE 2013</div>
                </div>
            </div>

            {{ $slot }}

        </div>
        <div class="auth-powered">Justice Hub CRM &middot; v1.0</div>
    </div>

</div>

</body>
</html>
