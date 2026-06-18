<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intake Token — {{ $case->case_uid }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #d6dce4;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            min-height: 100vh;
        }

        /* ── Outer wrapper ── */
        .slip-wrap {
            width: 760px;
            filter: drop-shadow(0 6px 28px rgba(0,0,0,0.18));
        }

        /* ── Top accent bar ── */
        .accent-bar {
            height: 6px;
            background: linear-gradient(90deg, #c9a227 0%, #e8c84a 40%, #c9a227 100%);
            border-radius: 3px 3px 0 0;
        }

        /* ── Main card ── */
        .slip {
            background: #fff;
            border: 1px solid #b0bac6;
            border-top: none;
            border-radius: 0 0 4px 4px;
        }

        /* ══════════════════════════════
           HEADER
        ══════════════════════════════ */
        .slip-header {
            display: flex;
            align-items: center;
            padding: 20px 26px 16px;
            border-bottom: 3px solid #1a2e4a;
            gap: 16px;
            background: linear-gradient(180deg, #f7f9fc 0%, #fff 100%);
        }

        /* Left: LAS wordmark */
        .hd-las {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            min-width: 90px;
        }
        .hd-las .las-word {
            font-size: 34px;
            font-weight: 900;
            color: #1a2e4a;
            letter-spacing: 4px;
            line-height: 1;
        }
        .hd-las .las-rule {
            width: 100%;
            height: 2px;
            background: #c9a227;
            margin: 4px 0 5px;
        }
        .hd-las .las-full {
            font-size: 7.5px;
            font-weight: 700;
            color: #1a2e4a;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        /* Center: title block */
        .hd-center {
            flex: 1;
            text-align: center;
            padding: 0 10px;
        }
        .hd-center .hdc-eyebrow {
            font-size: 8px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #c9a227;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .hd-center h1 {
            font-size: 28px;
            font-weight: 900;
            color: #1a2e4a;
            letter-spacing: 5px;
            text-transform: uppercase;
            line-height: 1;
        }
        .hd-center .hdc-tagline {
            font-size: 9px;
            color: #7a8a9a;
            font-style: italic;
            margin-top: 5px;
            letter-spacing: 0.5px;
        }

        /* Right: JH seal */
        .hd-seal {
            min-width: 72px;
            width: 72px;
            height: 72px;
            border: 2.5px solid #1a2e4a;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0;
            background: #f0f4f8;
            position: relative;
        }
        .hd-seal::before {
            content: '';
            position: absolute;
            inset: 4px;
            border-radius: 50%;
            border: 1px solid #c9a227;
        }
        .hd-seal .seal-jh {
            font-size: 18px;
            font-weight: 900;
            color: #1a2e4a;
            letter-spacing: 1px;
            line-height: 1;
        }
        .hd-seal .seal-sub {
            font-size: 6px;
            color: #1a2e4a;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 700;
            margin-top: 3px;
        }

        /* ══════════════════════════════
           HUB STRIP
        ══════════════════════════════ */
        .hub-strip {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 9px 26px;
            background: #1a2e4a;
        }
        .hub-strip .hs-left .hs-name {
            font-size: 12.5px;
            font-weight: 700;
            color: #fff;
            letter-spacing: 0.3px;
        }
        .hub-strip .hs-left .hs-addr {
            font-size: 10px;
            color: #a0b4c8;
            margin-top: 2px;
        }
        .hub-strip .hs-phone {
            font-size: 12px;
            color: #c9a227;
            font-weight: 700;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        /* ══════════════════════════════
           TOKEN HERO
        ══════════════════════════════ */
        .token-hero {
            padding: 20px 26px;
            background: #f7f9fc;
            border-bottom: 1px solid #dde3ea;
            display: flex;
            align-items: stretch;
            gap: 0;
        }
        .th-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .th-left .th-eyebrow {
            font-size: 7.5px;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #7a8a9a;
            margin-bottom: 6px;
        }
        .th-left .th-number {
            font-size: 32px;
            font-weight: 900;
            color: #1a2e4a;
            letter-spacing: 2px;
            line-height: 1;
        }
        .th-left .th-date {
            font-size: 11px;
            color: #555;
            margin-top: 7px;
            letter-spacing: 0.3px;
        }
        .th-left .th-date span {
            font-weight: 700;
            color: #1a2e4a;
        }

        .th-divider {
            width: 1px;
            background: #dde3ea;
            margin: 0 22px;
        }

        .th-right {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-end;
            gap: 6px;
        }
        .th-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .th-badge.badge-hub {
            background: #e8edf2;
            color: #1a2e4a;
        }
        .th-badge.badge-status {
            background: #e6f4ea;
            color: #2e7d32;
        }
        .th-badge .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        /* ══════════════════════════════
           SECTION HEADER
        ══════════════════════════════ */
        .sec-head {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 26px 0;
        }
        .sec-head .sh-label {
            font-size: 7.5px;
            font-weight: 800;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #1a2e4a;
            white-space: nowrap;
        }
        .sec-head .sh-rule {
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, #c9a227, transparent);
        }

        /* ══════════════════════════════
           FIELDS
        ══════════════════════════════ */
        .fields-section {
            padding: 10px 26px 16px;
            border-bottom: 1px solid #eaecf0;
        }
        .fields-row {
            display: flex;
            gap: 0;
            margin-top: 12px;
        }
        .field-cell {
            flex: 1;
            padding-right: 22px;
        }
        .field-cell:last-child { padding-right: 0; }
        .field-cell.w2 { flex: 2; }
        .field-cell.w3 { flex: 3; }

        .field-label {
            font-size: 7px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.8px;
            color: #9aa5b2;
            margin-bottom: 5px;
        }
        .field-value {
            font-size: 13px;
            color: #111;
            font-weight: 600;
            border-bottom: 1.5px solid #d0d7df;
            padding-bottom: 5px;
            min-height: 23px;
            letter-spacing: 0.2px;
        }
        .field-value.monospace {
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
        }

        /* ══════════════════════════════
           PATHWAY SECTION
        ══════════════════════════════ */
        .pathway-section {
            padding: 10px 26px 16px;
            border-bottom: 1px solid #eaecf0;
            background: #fafbfc;
        }
        .pathway-row {
            display: flex;
            gap: 0;
            margin-top: 12px;
        }
        .pathway-cell {
            flex: 1;
            padding-right: 20px;
        }
        .pathway-cell:last-child { padding-right: 0; }

        /* highlight the assigned staff cell */
        .pathway-cell.highlight .field-value {
            color: #1a2e4a;
            font-weight: 700;
            border-bottom-color: #c9a227;
        }

        /* ══════════════════════════════
           FOOTER
        ══════════════════════════════ */
        .slip-footer {
            display: flex;
            background: #1a2e4a;
            border-radius: 0 0 4px 4px;
        }
        .footer-cell {
            flex: 1;
            padding: 14px 20px;
            border-right: 1px solid rgba(255,255,255,0.1);
        }
        .footer-cell:last-child { border-right: none; }

        .footer-icon {
            font-size: 14px;
            margin-bottom: 3px;
        }
        .footer-label {
            font-size: 7px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #c9a227;
            margin-bottom: 4px;
        }
        .footer-sub {
            font-size: 9px;
            color: #8a9db5;
            line-height: 1.5;
            margin-bottom: 6px;
        }
        .footer-number {
            font-size: 20px;
            font-weight: 900;
            color: #fff;
            letter-spacing: 1.5px;
        }

        /* ── Print ── */
        @page {
            margin: 0;
            size: auto;
        }
        @media print {
            body { background: none; padding: 8mm; }
            .slip-wrap { filter: none; width: 100%; }
            .slip { border: none; }
            .accent-bar { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .hub-strip, .slip-footer, .token-hero { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

{{-- Print / Back buttons --}}
<div class="no-print" style="display:flex; gap:10px; margin-bottom:24px; width:760px; justify-content:flex-end;">
    <button onclick="window.history.back()"
        style="padding:9px 16px; background:#fff; color:#333; border:1px solid #bbb; font-size:13px; cursor:pointer; border-radius:6px; font-family:inherit;">
        ← Back
    </button>
    <button onclick="window.print()"
        style="padding:9px 20px; background:#1a2e4a; color:#fff; border:none; font-size:13px; font-weight:700; cursor:pointer; border-radius:6px; font-family:inherit; letter-spacing:0.5px;">
        🖨&nbsp; Print Slip
    </button>
</div>

<div class="slip-wrap">
    <div class="accent-bar"></div>
    <div class="slip">

        {{-- ── Header ── --}}
        <div class="slip-header">
            <div class="hd-las">
                <div class="las-word">LAS</div>
                <div class="las-rule"></div>
                <div class="las-full">Legal Aid Society</div>
            </div>

            <div class="hd-center">
                <div class="hdc-eyebrow">Client Intake Token</div>
                <h1>Justice Hub</h1>
                <div class="hdc-tagline">A One-Stop Solution Closer to Communities</div>
            </div>

            <div class="hd-seal">
                <div class="seal-jh">JH</div>
                <div class="seal-sub">Sindh</div>
            </div>
        </div>

        {{-- ── Hub strip ── --}}
        <div class="hub-strip">
            <div class="hs-left">
                <div class="hs-name">Justice Hub &mdash; {{ $case->hub?->name ?? $case->hub_id }}</div>
                @if($case->hub?->address)
                <div class="hs-addr">{{ $case->hub->address }}</div>
                @endif
            </div>
            <div class="hs-phone">
                @if($case->hub?->phone)
                    📞 {{ $case->hub->phone }}
                    @if($case->hub?->phone2) &nbsp;·&nbsp; {{ $case->hub->phone2 }} @endif
                @endif
            </div>
        </div>

        {{-- ── Token hero ── --}}
        <div class="token-hero">
            <div class="th-left">
                <div class="th-eyebrow">Intake Reference Number</div>
                <div class="th-number">{{ $case->case_uid }}</div>
                <div class="th-date">
                    Date of Intake:&nbsp;
                    <span>{{ $case->intake_date ? \Carbon\Carbon::parse($case->intake_date)->format('d M Y') : '—' }}</span>
                </div>
            </div>
            <div class="th-divider"></div>
            <div class="th-right">
                <div class="th-badge badge-hub">
                    <span class="dot"></span>
                    {{ $case->hub?->district ?? $case->hub_id }}
                </div>
                <div class="th-badge badge-status">
                    <span class="dot"></span>
                    {{ ucfirst($case->status?->value ?? 'Registered') }}
                </div>
            </div>
        </div>

        {{-- ── Client Information ── --}}
        <div class="sec-head">
            <div class="sh-label">Client Information</div>
            <div class="sh-rule"></div>
        </div>

        <div class="fields-section">
            {{-- Row 1: Name + Father/Husband --}}
            <div class="fields-row">
                <div class="field-cell w2">
                    <div class="field-label">Full Name</div>
                    <div class="field-value">{{ $case->name }}</div>
                </div>
                <div class="field-cell w2">
                    <div class="field-label">Father / Husband Name</div>
                    <div class="field-value">{{ $case->father_husband_name ?? '—' }}</div>
                </div>
            </div>

            {{-- Row 2: CNIC, Mobile, Alternate --}}
            <div class="fields-row">
                <div class="field-cell w2">
                    <div class="field-label">CNIC Number</div>
                    <div class="field-value monospace">{{ $case->cnic ?? '—' }}</div>
                </div>
                <div class="field-cell">
                    <div class="field-label">Mobile Number</div>
                    <div class="field-value monospace">{{ $case->primary_contact ?? '—' }}</div>
                </div>
                <div class="field-cell">
                    <div class="field-label">Alternate Number</div>
                    <div class="field-value monospace">{{ $case->alternative_contact ?? '—' }}</div>
                </div>
            </div>

            {{-- Row 3: Address --}}
            <div class="fields-row">
                <div class="field-cell w3">
                    <div class="field-label">Client Address</div>
                    <div class="field-value">{{ $case->full_address ?? trim(implode(', ', array_filter([$case->union_council, $case->tehsil, $case->district])), ', ') ?: '—' }}</div>
                </div>
            </div>
        </div>

        {{-- ── Pathway & Assignment ── --}}
        <div class="sec-head" style="padding-top:12px;">
            <div class="sh-label">Pathway &amp; Assignment</div>
            <div class="sh-rule"></div>
        </div>

        <div class="pathway-section">
            <div class="pathway-row">
                <div class="pathway-cell">
                    <div class="field-label">Referred From</div>
                    <div class="field-value">{{ $case->referral_source ?? '—' }}</div>
                </div>
                <div class="pathway-cell">
                    <div class="field-label">Referred To / Pathway</div>
                    <div class="field-value">{{ $case->assigned_pathway ?? '—' }}{{ $case->pathway_specific ? ' — ' . $case->pathway_specific : '' }}</div>
                </div>
                <div class="pathway-cell highlight">
                    <div class="field-label">Assigned Lawyer / Staff</div>
                    <div class="field-value">{{ $case->assigned_to ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- ── Footer ── --}}
        <div class="slip-footer">
            <div class="footer-cell">
                <div class="footer-label">SLACC &middot; 24/7 Toll-Free Legal Aid</div>
                <div class="footer-sub">Call the Sindh Legal Advisory Call Centre for free legal advice, any time of day.</div>
                <div class="footer-number">0800-70806</div>
            </div>
            <div class="footer-cell">
                <div class="footer-label">LAS &middot; Feedback &amp; Complaints</div>
                <div class="footer-sub">Contact the Legal Aid Society for questions, feedback, or to lodge a complaint.</div>
                <div class="footer-number">0345-8270806</div>
            </div>
        </div>

    </div>{{-- .slip --}}
</div>{{-- .slip-wrap --}}

<script>
    window.addEventListener('load', function () {
        window.print();
    });
</script>
</body>
</html>
