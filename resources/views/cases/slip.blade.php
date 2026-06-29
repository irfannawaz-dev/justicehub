<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Intake Token — {{ $case->case_uid }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #ccc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 28px 16px;
            min-height: 100vh;
        }

        .no-print {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            width: 105mm;
        }
        .no-print .hint {
            flex: 1;
            font-size: 10px;
            color: #555;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        /* ── Slip card: A6 size ── */
        .slip {
            width: 105mm;
            background: #fff;
            border: 1.5px solid #222;
            display: flex;
            flex-direction: column;
        }

        /* ── Header ── */
        .sl-header {
            display: flex;
            align-items: center;
            padding: 5px 7px;
            border-bottom: 1.5px solid #111;
            gap: 6px;
        }
        .sl-las .las-word {
            font-size: 15px;
            font-weight: 900;
            color: #111;
            letter-spacing: 3px;
            line-height: 1;
        }
        .sl-las .las-rule { height: 1.5px; background: #c9a227; margin: 2px 0; }
        .sl-las .las-sub {
            font-size: 5px; font-weight: 700; letter-spacing: 1px;
            text-transform: uppercase; color: #111; white-space: nowrap;
        }
        .sl-center {
            flex: 1; text-align: center;
            border-left: 0.5px solid #ccc; border-right: 0.5px solid #ccc;
            padding: 0 6px;
        }
        .sl-center .eyebrow {
            font-size: 5px; letter-spacing: 2px; text-transform: uppercase;
            color: #c9a227; font-weight: 700;
        }
        .sl-center h1 {
            font-size: 12px; font-weight: 900; color: #111;
            letter-spacing: 3px; text-transform: uppercase; line-height: 1.1;
        }
        .sl-center .tagline { font-size: 5px; color: #999; font-style: italic; }
        .sl-seal {
            width: 34px; height: 34px; border: 1.5px solid #111; border-radius: 50%;
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; position: relative; flex-shrink: 0;
        }
        .sl-seal::before {
            content: ''; position: absolute; inset: 3px;
            border-radius: 50%; border: 0.5px solid #c9a227;
        }
        .sl-seal .seal-jh { font-size: 10px; font-weight: 900; color: #111; line-height: 1; }
        .sl-seal .seal-sub { font-size: 4px; color: #555; letter-spacing: 1px; text-transform: uppercase; font-weight: 700; }

        /* ── Hub bar ── */
        .sl-hub {
            display: flex; justify-content: space-between; align-items: center;
            padding: 2px 7px; border-bottom: 0.5px solid #bbb; background: #f5f5f5;
        }
        .sl-hub .hub-name { font-size: 7.5px; font-weight: 700; color: #111; }
        .sl-hub .hub-phone { font-size: 7px; color: #555; }

        /* ── Reference ── */
        .sl-ref {
            display: flex; align-items: center; justify-content: space-between;
            padding: 5px 7px; border-bottom: 1px solid #ccc; background: #fafafa;
        }
        .ref-num { font-size: 13px; font-weight: 900; color: #111; letter-spacing: 1px; line-height: 1; }
        .ref-meta { font-size: 6px; color: #555; margin-top: 2px; }
        .ref-meta b { color: #111; }
        .ref-badges { display: flex; flex-direction: column; align-items: flex-end; gap: 3px; }
        .badge {
            display: inline-flex; align-items: center; gap: 3px;
            padding: 2px 5px; border: 0.5px solid #bbb;
            font-size: 5.5px; font-weight: 700; letter-spacing: 0.5px;
            text-transform: uppercase; color: #444;
        }
        .badge.active { border-color: #2e7d32; color: #2e7d32; }
        .badge .dot { width: 4px; height: 4px; border-radius: 50%; background: currentColor; }

        /* ── Section label ── */
        .sl-sec {
            font-size: 5.5px; font-weight: 800; letter-spacing: 2px;
            text-transform: uppercase; color: #444;
            background: #efefef; border-top: 0.5px solid #ccc;
            border-bottom: 0.5px solid #ccc; padding: 2px 7px;
        }

        /* ── Field rows ── */
        .sl-row { display: flex; border-bottom: 0.5px solid #e8e8e8; }
        .fc { flex: 1; padding: 3px 7px 3px; border-right: 0.5px solid #e8e8e8; }
        .fc:last-child { border-right: none; }
        .fc.w2 { flex: 2; }
        .fc.w3 { flex: 3; }
        .fc .lbl {
            font-size: 5px; font-weight: 800; text-transform: uppercase;
            letter-spacing: 1.2px; color: #bbb; margin-bottom: 1px;
        }
        .fc .val { font-size: 8px; color: #111; font-weight: 600; line-height: 1.3; }
        .fc .val.mono { font-family: 'Courier New', monospace; font-size: 7.5px; letter-spacing: 0.3px; }
        .fc .val.em { color: #1a2e4a; font-weight: 700; border-bottom: 1px solid #c9a227; padding-bottom: 1px; }

        /* ── Signatures ── */
        .sl-sigs { display: flex; border-top: 0.5px solid #ccc; }
        .sig { flex: 1; padding: 3px 7px 5px; border-right: 0.5px solid #e8e8e8; }
        .sig:last-child { border-right: none; }
        .sig .lbl { font-size: 5px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #bbb; margin-bottom: 10px; }
        .sig-line { border-top: 0.5px solid #bbb; }

        /* ── Footer ── */
        .sl-footer { display: flex; border-top: 1px solid #222; margin-top: auto; }
        .fc-foot { flex: 1; padding: 3px 7px; border-right: 0.5px solid #ccc; }
        .fc-foot:last-child { border-right: none; }
        .f-lbl { font-size: 5px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.2px; color: #999; margin-bottom: 1px; }
        .f-sub { font-size: 5.5px; color: #888; line-height: 1.3; margin-bottom: 1px; }
        .f-num { font-size: 10px; font-weight: 900; color: #111; letter-spacing: 0.5px; }

        /* ── Print: A6 page so 4 fit on A4 ── */
        @page { margin: 0; size: 105mm 148mm; }
        @media print {
            body { background: none; padding: 0; align-items: flex-start; }
            .no-print { display: none !important; }
            .slip { width: 105mm; border: 0.5px solid #888; }
        }
    </style>
</head>
<body>

@php
    $intakeDate = $case->intake_date ? \Carbon\Carbon::parse($case->intake_date)->format('d M Y') : '—';
    $address    = $case->full_address ?? trim(implode(', ', array_filter([$case->union_council, $case->tehsil, $case->district])), ', ') ?: '—';
@endphp

<div class="no-print">
    <div class="hint">💡 To print 4 slips per A4, choose <b>4 pages per sheet</b> in your print dialog.</div>
    <button onclick="window.history.back()"
        style="padding:6px 12px; background:#fff; color:#333; border:1px solid #bbb; font-size:11px; cursor:pointer; font-family:inherit;">
        ← Back
    </button>
    <button onclick="window.print()"
        style="padding:6px 14px; background:#111; color:#fff; border:none; font-size:11px; font-weight:700; cursor:pointer; font-family:inherit;">
        🖨 Print
    </button>
</div>

<div class="slip">

    {{-- Header --}}
    <div class="sl-header">
        <div class="sl-las">
            <div class="las-word">LAS</div>
            <div class="las-rule"></div>
            <div class="las-sub">Legal Aid Society</div>
        </div>
        <div class="sl-center">
            <div class="eyebrow">Client Intake Token</div>
            <h1>Justice Hub</h1>
            <div class="tagline">A One-Stop Solution Closer to Communities</div>
        </div>
        <div class="sl-seal">
            <div class="seal-jh">JH</div>
            <div class="seal-sub">Sindh</div>
        </div>
    </div>

    {{-- Hub bar --}}
    <div class="sl-hub">
        <div class="hub-name">Justice Hub &mdash; {{ $case->hub?->name ?? $case->hub_id }}</div>
        @if($case->hub?->phone)
        <div class="hub-phone">{{ $case->hub->phone }}</div>
        @endif
    </div>

    {{-- Reference --}}
    <div class="sl-ref">
        <div>
            <div class="ref-num">{{ $case->case_uid }}</div>
            <div class="ref-meta">
                Date: <b>{{ $intakeDate }}</b>
                &nbsp;·&nbsp; Staff: <b>{{ $case->staff_receiving ?? '—' }}</b>
            </div>
        </div>
        <div class="ref-badges">
            <div class="badge">
                <span class="dot" style="background:#555;"></span>
                {{ $case->hub?->district ?? $case->hub_id }}
            </div>
            <div class="badge active">
                <span class="dot"></span>
                {{ ucfirst($case->status?->value ?? 'Active') }}
            </div>
        </div>
    </div>

    {{-- Client Info --}}
    <div class="sl-sec">Client Information</div>

    <div class="sl-row">
        <div class="fc w2">
            <div class="lbl">Full Name</div>
            <div class="val">{{ $case->name }}</div>
        </div>
        <div class="fc w2">
            <div class="lbl">Father / Husband</div>
            <div class="val">{{ $case->father_husband_name ?? '—' }}</div>
        </div>
        <div class="fc">
            <div class="lbl">Gender</div>
            <div class="val">{{ $case->gender ?? '—' }}</div>
        </div>
        <div class="fc">
            <div class="lbl">Age</div>
            <div class="val">{{ $case->age ? $case->age . 'y' : '—' }}</div>
        </div>
    </div>

    <div class="sl-row">
        <div class="fc w2">
            <div class="lbl">CNIC</div>
            <div class="val mono">{{ $case->cnic ?? '—' }}</div>
        </div>
        <div class="fc w2">
            <div class="lbl">Mobile</div>
            <div class="val mono">{{ $case->primary_contact ?? '—' }}</div>
        </div>
        <div class="fc w2">
            <div class="lbl">Address</div>
            <div class="val" style="font-size:7px;">{{ $address }}</div>
        </div>
    </div>

    {{-- Pathway --}}
    <div class="sl-sec">Pathway &amp; Assignment</div>

    <div class="sl-row">
        <div class="fc">
            <div class="lbl">Referred From</div>
            <div class="val">{{ $case->referral_source ?? '—' }}</div>
        </div>
        <div class="fc">
            <div class="lbl">Pathway</div>
            <div class="val">{{ $case->assigned_pathway ?? '—' }}</div>
        </div>
        <div class="fc">
            <div class="lbl">Specific</div>
            <div class="val">{{ $case->pathway_specific ?? '—' }}</div>
        </div>
    </div>

    <div class="sl-row">
        <div class="fc w2">
            <div class="lbl">Assigned Lawyer / Staff</div>
            <div class="val em">{{ $case->assigned_to ?? '—' }}</div>
        </div>
        <div class="fc w2">
            <div class="lbl">Staff Contact</div>
            <div class="val mono">{{ $lawyerPhone ?? '—' }}</div>
        </div>
    </div>

    {{-- Signatures --}}
    <div class="sl-sigs">
        <div class="sig">
            <div class="lbl">Client Signature / Thumb</div>
            <div class="sig-line"></div>
        </div>
        <div class="sig">
            <div class="lbl">Staff Signature</div>
            <div class="sig-line"></div>
        </div>
        <div class="sig">
            <div class="lbl">Office Stamp</div>
            <div class="sig-line"></div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="sl-footer">
        <div class="fc-foot">
            <div class="f-lbl">SLACC · 24/7 Toll-Free</div>
            <div class="f-sub">Free legal advice, any time.</div>
            <div class="f-num">0800-70806</div>
        </div>
        <div class="fc-foot">
            <div class="f-lbl">LAS · Feedback</div>
            <div class="f-sub">Questions or complaints.</div>
            <div class="f-num">0345-8270806</div>
        </div>
    </div>

</div>{{-- .slip --}}

<script>
    window.addEventListener('load', function () { window.print(); });
</script>
</body>
</html>
