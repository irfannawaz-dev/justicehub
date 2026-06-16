<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intake Token — {{ $case->case_uid }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 30px;
            min-height: 100vh;
        }

        .slip {
            background: #fff;
            width: 750px;
            border: 1px solid #ccc;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }

        /* ── Header ── */
        .slip-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px 14px;
            border-bottom: 2px solid #1a2e4a;
        }
        .slip-header .logo-las {
            display: flex;
            flex-direction: column;
        }
        .slip-header .logo-las .las-box {
            font-size: 28px;
            font-weight: 900;
            color: #1a2e4a;
            letter-spacing: 2px;
            line-height: 1;
        }
        .slip-header .logo-las .las-sub {
            font-size: 8px;
            color: #1a2e4a;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-top: 3px;
        }
        .slip-header .center-title {
            text-align: center;
            flex: 1;
            padding: 0 20px;
        }
        .slip-header .center-title .token-label {
            font-size: 8px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #b8860b;
            margin-bottom: 4px;
        }
        .slip-header .center-title h1 {
            font-size: 26px;
            font-weight: 900;
            color: #1a2e4a;
            letter-spacing: 3px;
            text-transform: uppercase;
        }
        .slip-header .center-title .tagline {
            font-size: 9px;
            color: #888;
            font-style: italic;
            margin-top: 3px;
        }
        .slip-header .logo-jh img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .slip-header .logo-jh-placeholder {
            width: 60px;
            height: 60px;
            border: 2px solid #1a2e4a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            font-weight: 700;
            color: #1a2e4a;
            text-align: center;
            line-height: 1.2;
        }

        /* ── Hub info strip ── */
        .hub-strip {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 24px;
            border-bottom: 1px solid #ddd;
            background: #fafafa;
        }
        .hub-strip .hub-name {
            font-size: 13px;
            font-weight: 700;
            color: #1a2e4a;
        }
        .hub-strip .hub-address {
            font-size: 10.5px;
            color: #555;
            margin-top: 2px;
        }
        .hub-strip .hub-phones {
            font-size: 11px;
            color: #333;
            font-weight: 600;
            white-space: nowrap;
        }

        /* ── Token banner ── */
        .token-banner {
            background: #1a2e4a;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 24px;
            margin: 0;
        }
        .token-banner .token-title {
            font-size: 9px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #b8c8d8;
        }
        .token-banner .token-number {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: 1px;
            color: #fff;
        }

        /* ── Fields grid ── */
        .fields-section {
            padding: 16px 24px;
            border-bottom: 1px solid #eee;
        }
        .fields-row {
            display: flex;
            gap: 0;
            margin-bottom: 14px;
        }
        .fields-row:last-child { margin-bottom: 0; }
        .field-cell {
            flex: 1;
            padding-right: 20px;
        }
        .field-cell:last-child { padding-right: 0; }
        .field-label {
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #888;
            margin-bottom: 4px;
        }
        .field-value {
            font-size: 12.5px;
            color: #111;
            font-weight: 500;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
            min-height: 22px;
        }
        .field-cell.full { flex: 3; }
        .field-cell.half { flex: 1.5; }

        /* ── Referred section ── */
        .referred-section {
            display: flex;
            gap: 0;
            padding: 14px 24px;
            border-bottom: 1px solid #eee;
        }
        .referred-cell {
            flex: 1;
            padding-right: 20px;
        }
        .referred-cell:last-child { padding-right: 0; }

        /* ── Footer ── */
        .slip-footer {
            display: flex;
            gap: 0;
            border-top: 2px solid #1a2e4a;
        }
        .footer-cell {
            flex: 1;
            padding: 14px 18px;
            border-right: 1px solid #ddd;
        }
        .footer-cell:last-child { border-right: none; }
        .footer-label {
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #888;
            margin-bottom: 5px;
        }
        .footer-sub {
            font-size: 9px;
            color: #555;
            line-height: 1.5;
            margin-bottom: 6px;
        }
        .footer-number {
            font-size: 18px;
            font-weight: 900;
            color: #1a2e4a;
            letter-spacing: 1px;
        }

        /* ── Print styles ── */
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .slip {
                box-shadow: none;
                border: none;
                width: 100%;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

{{-- Print button (hidden on print) --}}
<div class="no-print" style="position:fixed; top:20px; right:20px; display:flex; gap:8px; z-index:100;">
    <button onclick="window.print()"
        style="padding:8px 18px; background:#1a2e4a; color:#fff; border:none; font-size:13px; font-weight:600; cursor:pointer; border-radius:4px; font-family:Arial,sans-serif;">
        🖨 Print Slip
    </button>
    <button onclick="window.history.back()"
        style="padding:8px 14px; background:#fff; color:#333; border:1px solid #ccc; font-size:13px; cursor:pointer; border-radius:4px; font-family:Arial,sans-serif;">
        ← Back
    </button>
</div>

<div class="slip">

    {{-- ── Header ── --}}
    <div class="slip-header">
        <div class="logo-las">
            <div class="las-box">LAS</div>
            <div class="las-sub">Legal Aid Society</div>
        </div>

        <div class="center-title">
            <div class="token-label">Client Intake Token</div>
            <h1>Justice Hub</h1>
            <div class="tagline">A One-Stop Solution Closer to Communities</div>
        </div>

        <div class="logo-jh-placeholder">
            JUSTICE<br>HUB
        </div>
    </div>

    {{-- ── Hub info ── --}}
    <div class="hub-strip">
        <div>
            <div class="hub-name">Justice Hub — {{ $case->hub?->name ?? $case->hub_id }}</div>
            @if($case->hub?->address)
            <div class="hub-address">{{ $case->hub->address }}</div>
            @endif
        </div>
        <div class="hub-phones">
            @if($case->hub?->phone)
                {{ $case->hub->phone }}
                @if($case->hub?->phone2) · {{ $case->hub->phone2 }} @endif
            @endif
        </div>
    </div>

    {{-- ── Token banner ── --}}
    <div class="token-banner">
        <span class="token-title">Client Intake Token</span>
        <span class="token-number">LAS-{{ $case->hub?->district ?? $case->hub_id }}-{{ ltrim(substr($case->case_uid, 3), '0') ?: '0' }}</span>
    </div>

    {{-- ── Row 1: Date, Client Name, Father/Husband ── --}}
    <div class="fields-section">
        <div class="fields-row">
            <div class="field-cell">
                <div class="field-label">Date of Intake</div>
                <div class="field-value">{{ $case->intake_date ? \Carbon\Carbon::parse($case->intake_date)->format('d – M – Y') : '—' }}</div>
            </div>
            <div class="field-cell">
                <div class="field-label">Client Name</div>
                <div class="field-value">{{ $case->name }}</div>
            </div>
            <div class="field-cell">
                <div class="field-label">Father / Husband Name</div>
                <div class="field-value">{{ $case->father_husband_name ?? '—' }}</div>
            </div>
        </div>

        {{-- ── Row 2: CNIC, Mobile, Alternate ── --}}
        <div class="fields-row">
            <div class="field-cell">
                <div class="field-label">CNIC</div>
                <div class="field-value">{{ $case->cnic ?? '—' }}</div>
            </div>
            <div class="field-cell">
                <div class="field-label">Mobile Number</div>
                <div class="field-value">{{ $case->primary_contact ?? '—' }}</div>
            </div>
            <div class="field-cell">
                <div class="field-label">Alternate Number</div>
                <div class="field-value">{{ $case->alternative_contact ?? '—' }}</div>
            </div>
        </div>

        {{-- ── Row 3: Full Address ── --}}
        <div class="fields-row" style="margin-bottom:0;">
            <div class="field-cell full">
                <div class="field-label">Client Address</div>
                <div class="field-value">{{ $case->full_address ?? ($case->union_council ? $case->union_council . ', ' : '') . ($case->tehsil ?? '') . ($case->tehsil && $case->district ? ', ' : '') . ($case->district ?? '') }}</div>
            </div>
        </div>
    </div>

    {{-- ── Referred From / Referred To ── --}}
    <div class="referred-section">
        <div class="referred-cell">
            <div class="field-label">Referred From</div>
            <div class="field-value">Source: {{ $case->referral_source ?? '—' }}</div>
        </div>
        <div class="referred-cell">
            <div class="field-label">Referred To</div>
            <div class="field-value">{{ $case->assigned_pathway ?? '—' }}{{ $case->pathway_specific ? ' — ' . $case->pathway_specific : '' }}</div>
        </div>
    </div>

    {{-- ── Footer ── --}}
    <div class="slip-footer">
        <div class="footer-cell">
            <div class="footer-label">SLACC · 24/7 Toll-Free</div>
            <div class="footer-sub">For free legal advice, call the Sindh Legal Advisory Call Centre (SLACC) — 24/7 Toll-Free</div>
            <div class="footer-number">0800-70806</div>
        </div>
        <div class="footer-cell">
            <div class="footer-label">LAS · Feedback &amp; Complaints</div>
            <div class="footer-sub">Contact the Legal Aid Society for any further questions, feedback, or complaints.</div>
            <div class="footer-number">0345-8270806</div>
        </div>
    </div>

</div>

</body>
</html>
