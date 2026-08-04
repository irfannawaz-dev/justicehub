<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Referral Source Report · Justice Hub</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Georgia',serif;background:#e8e4dd;min-height:100vh;}
.preview-bar{position:sticky;top:0;z-index:100;display:flex;align-items:center;justify-content:space-between;padding:10px 24px;background:#1a1a1a;color:#fff;font-size:13px;font-family:Arial,sans-serif;gap:12px;}
.preview-bar span{opacity:.75;font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.preview-bar .btns{display:flex;gap:8px;flex-shrink:0;}
.preview-bar button{padding:6px 16px;border:none;border-radius:4px;font-size:12px;cursor:pointer;font-family:Arial,sans-serif;font-weight:500;}
.btn-copy{background:#2f2f2f;color:#fff;border:1px solid #555!important;}
.btn-print{background:#fff;color:#1a1a1a;}
.btn-close{background:transparent;color:#aaa;border:1px solid #555!important;}
.report-wrap{padding:32px 24px 60px;display:flex;justify-content:center;}
.page{background:#fff;width:794px;min-height:1122px;padding:44px 52px 48px;box-shadow:0 4px 32px rgba(0,0,0,.18);position:relative;}
/* Header */
.rpt-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px;}
.rpt-logo{display:flex;align-items:center;gap:10px;}
.rpt-logo-box{width:44px;height:44px;background:#163029;display:flex;align-items:center;justify-content:center;}
.rpt-logo-text{font-family:Arial,sans-serif;font-size:10px;font-weight:800;color:#fff;letter-spacing:.08em;}
.rpt-org-name{font-size:13px;font-weight:700;color:#163029;letter-spacing:.08em;text-transform:uppercase;font-family:Arial,sans-serif;}
.rpt-org-sub{font-size:8.5px;color:#888;letter-spacing:.06em;text-transform:uppercase;font-family:Arial,sans-serif;margin-top:2px;}
.rpt-header-right{text-align:right;font-family:Arial,sans-serif;font-size:8.5px;color:#888;line-height:1.7;letter-spacing:.04em;text-transform:uppercase;}
.rpt-header-right strong{color:#444;font-weight:600;}
.rpt-divider{height:2px;background:linear-gradient(to right,#163029,#b87319,#e0dbd2);margin:12px 0 18px;}
/* Title */
.rpt-title{font-size:26px;font-weight:400;color:#1a1a1a;line-height:1.1;margin-bottom:4px;}
.rpt-subtitle{font-size:13px;color:#555;font-family:Arial,sans-serif;margin-bottom:14px;}
.rpt-narrative{font-size:11.5px;color:#444;line-height:1.7;margin-bottom:22px;padding:12px 16px;background:#f8f6f2;border-left:3px solid #163029;}
/* KPI boxes */
.kpi-row{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:22px;}
.kpi-box{border:1px solid #ddd;padding:14px 16px;}
.kpi-val{font-size:28px;font-weight:700;color:#163029;line-height:1;margin-bottom:4px;}
.kpi-label{font-size:8px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#888;margin-bottom:2px;font-family:Arial,sans-serif;}
.kpi-sub{font-size:9.5px;color:#aaa;font-family:Arial,sans-serif;}
/* Section headers */
.sec-head{font-size:8.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#555;margin-bottom:10px;font-family:Arial,sans-serif;display:flex;justify-content:space-between;align-items:baseline;}
.sec-head span{font-weight:400;opacity:.65;}
/* Urgency bar */
.urgency-wrap{margin-bottom:22px;}
.urgency-bar{display:flex;height:14px;border-radius:2px;overflow:hidden;margin-bottom:8px;}
.urgency-legend{display:flex;gap:16px;flex-wrap:wrap;}
.urgency-legend-item{display:flex;align-items:center;gap:5px;font-size:9.5px;color:#555;font-family:Arial,sans-serif;}
.urgency-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0;}
/* Line chart */
.chart-wrap{margin-bottom:22px;}
/* Two-col section */
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:22px;}
/* Donut */
.donut-wrap{display:flex;gap:14px;align-items:flex-start;}
.donut-legend{flex:1;min-width:0;}
.donut-legend-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;gap:6px;}
.donut-dot{width:10px;height:10px;border-radius:2px;flex-shrink:0;}
.donut-label{font-size:10px;color:#333;flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-family:Arial,sans-serif;}
.donut-val{font-size:10px;font-weight:700;color:#333;font-family:Arial,sans-serif;flex-shrink:0;}
.donut-pct{font-size:9px;color:#999;font-family:Arial,sans-serif;flex-shrink:0;width:32px;text-align:right;}
/* Horiz bars (case categories) */
.hbar-row{margin-bottom:8px;}
.hbar-label{font-size:10px;color:#333;margin-bottom:3px;font-family:Arial,sans-serif;display:flex;justify-content:space-between;}
.hbar-label span{color:#999;font-size:9px;}
.hbar-track{height:8px;background:#eee;border-radius:2px;overflow:hidden;}
.hbar-fill{height:100%;background:#163029;border-radius:2px;}
/* Geographic */
.geo-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px 20px;margin-bottom:22px;}
.geo-row{display:flex;flex-direction:column;gap:3px;}
.geo-label{font-size:10px;font-weight:600;color:#333;font-family:Arial,sans-serif;}
.geo-sub{font-size:8.5px;color:#888;font-family:Arial,sans-serif;}
.geo-bar-row{display:flex;align-items:center;gap:8px;}
.geo-bar-track{flex:1;height:5px;background:#eee;border-radius:2px;overflow:hidden;}
.geo-bar-fill{height:100%;background:#163029;border-radius:2px;}
.geo-count{font-size:10px;font-weight:700;color:#333;font-family:Arial,sans-serif;width:28px;text-align:right;flex-shrink:0;}
.geo-pct{font-size:9px;color:#aaa;font-family:Arial,sans-serif;width:32px;text-align:right;flex-shrink:0;}
/* Footnote / footer */
.footnote{font-size:8.5px;color:#999;font-family:Arial,sans-serif;line-height:1.6;border-top:1px solid #e0dbd2;padding-top:10px;margin-top:10px;}
.rpt-footer{margin-top:18px;padding-top:10px;border-top:1px solid #e0dbd2;display:flex;justify-content:space-between;align-items:center;}
.rpt-footer-left{font-size:8.5px;color:#aaa;font-family:Arial,sans-serif;}
.rpt-footer-right{font-size:8.5px;color:#aaa;font-family:Arial,sans-serif;}
@media print{
    body{background:#fff;}
    .preview-bar{display:none!important;}
    .report-wrap{padding:0;}
    .page{width:100%;box-shadow:none;padding:0;min-height:auto;}
}
@page{size:A4 portrait;margin:15mm 18mm;}
</style>
</head>
<body>

{{-- ── Print preview bar ── --}}
<div class="preview-bar no-print">
    <span>Print preview — {{ $reportTitle }}</span>
    <div class="btns">
        <button class="btn-copy" onclick="copyNarrative()">Copy summary</button>
        <button class="btn-print" onclick="window.print()">Print / Save as PDF</button>
        <button class="btn-close" onclick="window.close()">Close</button>
    </div>
</div>

<div class="report-wrap">
<div class="page" id="report-page">

    {{-- ── Header ── --}}
    <div class="rpt-header">
        <div class="rpt-logo">
            <div class="rpt-logo-box"><span class="rpt-logo-text">LAS</span></div>
            <div>
                <div class="rpt-org-name">Justice Hub</div>
                <div class="rpt-org-sub">A One-Stop Solution Closer to Communities</div>
                <div class="rpt-org-sub">Powered by Legal Aid Society</div>
            </div>
        </div>
        <div class="rpt-header-right">
            <div>PERIOD&nbsp; <strong>{{ $periodStr }}</strong></div>
            <div>HUB SCOPE&nbsp; <strong>{{ $hubScope }}</strong></div>
            <div>PREPARED BY&nbsp; <strong>{{ $preparedBy }}</strong></div>
            <div>REFERENCE&nbsp; <strong>{{ $refCode }}</strong></div>
        </div>
    </div>

    <div class="rpt-divider"></div>

    {{-- ── Title ── --}}
    <div class="rpt-title">Referral Source Report</div>
    <div class="rpt-subtitle">{{ $reportTitle }}</div>

    {{-- ── Narrative ── --}}
    <div class="rpt-narrative" id="narrative">{{ $narrative }}</div>

    {{-- ── KPI boxes ── --}}
    <div class="kpi-row">
        <div class="kpi-box" style="border-top:3px solid #163029;">
            <div class="kpi-label">Referrals Received</div>
            <div class="kpi-val">{{ number_format($total) }}</div>
            <div class="kpi-sub">Total intakes</div>
        </div>
        <div class="kpi-box" style="border-top:3px solid #b87319;">
            <div class="kpi-label">Hubs Reached</div>
            <div class="kpi-val">{{ $hubsReached }}</div>
            <div class="kpi-sub">Active locations</div>
        </div>
        <div class="kpi-box" style="border-top:3px solid #4a4078;">
            <div class="kpi-label">Districts Reached</div>
            <div class="kpi-val">{{ $distsReached }}</div>
            <div class="kpi-sub">Geographic spread</div>
        </div>
        <div class="kpi-box" style="border-top:3px solid #2f7a4d;">
            <div class="kpi-label">Top Pathway Share</div>
            <div class="kpi-val" style="font-size:22px;">{{ $topRoutingPct }}%</div>
            <div class="kpi-sub">{{ $topPathShort }}</div>
        </div>
    </div>

    {{-- ── Urgency ── --}}
    <div class="urgency-wrap">
        <div class="sec-head">Urgency recorded at intake <span>n = {{ $urgencyTotal }}</span></div>
        <div class="urgency-bar">
            @foreach($urgencyData as $u)
            @if($u['val'] > 0)
            <div style="width:{{ round($u['val']/$urgencyTotal*100,1) }}%;background:{{ $u['color'] }};"></div>
            @endif
            @endforeach
        </div>
        <div class="urgency-legend">
            @foreach($urgencyData as $u)
            @if($u['val'] > 0)
            <div class="urgency-legend-item">
                <div class="urgency-dot" style="background:{{ $u['color'] }};"></div>
                {{ $u['label'] }} {{ $u['val'] }} &middot; {{ round($u['val']/$urgencyTotal*100,1) }}%
            </div>
            @endif
            @endforeach
        </div>
    </div>

    {{-- ── Monthly trend ── --}}
    @php
        $cm = $chartMeta;
        $monthCount = count($chartPoints);
    @endphp
    <div class="chart-wrap">
        <div class="sec-head">Referrals by month <span>{{ $monthCount }} months</span></div>
        @if($monthCount > 0)
        <svg viewBox="0 0 {{ $cm['cW'] }} {{ $cm['cH'] }}" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:auto;display:block;">
            {{-- Grid lines --}}
            <line x1="{{ $cm['pL'] }}" y1="{{ $cm['pT'] }}" x2="{{ $cm['cW']-10 }}" y2="{{ $cm['pT'] }}" stroke="#e8e4dd" stroke-width="1"/>
            <line x1="{{ $cm['pL'] }}" y1="{{ $cm['pT']+$cm['pH']/2 }}" x2="{{ $cm['cW']-10 }}" y2="{{ $cm['pT']+$cm['pH']/2 }}" stroke="#e8e4dd" stroke-width="1"/>
            <line x1="{{ $cm['pL'] }}" y1="{{ $cm['baseY'] }}" x2="{{ $cm['cW']-10 }}" y2="{{ $cm['baseY'] }}" stroke="#ccc" stroke-width="1"/>
            {{-- Y labels --}}
            <text x="{{ $cm['pL']-4 }}" y="{{ $cm['pT']+3 }}" text-anchor="end" font-size="8" fill="#aaa" font-family="Arial">{{ $cm['maxMonthly'] }}</text>
            <text x="{{ $cm['pL']-4 }}" y="{{ $cm['baseY']+3 }}" text-anchor="end" font-size="8" fill="#aaa" font-family="Arial">0</text>
            {{-- Area fill --}}
            @if($cm['areaPath'])
            <path d="{{ $cm['areaPath'] }}" fill="#163029" fill-opacity="0.07"/>
            @endif
            {{-- Line --}}
            <polyline points="{{ $cm['polyStr'] }}" fill="none" stroke="#163029" stroke-width="1.8" stroke-linejoin="round"/>
            {{-- Dots + labels --}}
            @foreach($chartPoints as $p)
            <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="3.5" fill="#163029"/>
            <text x="{{ $p['x'] }}" y="{{ $p['y']-7 }}" text-anchor="middle" font-size="8" fill="#333" font-family="Arial">{{ $p['val'] }}</text>
            <text x="{{ $p['x'] }}" y="{{ $cm['baseY']+14 }}" text-anchor="middle" font-size="7.5" fill="#888" font-family="Arial">{{ $p['lbl'] }}</text>
            @endforeach
        </svg>
        @else
        <div style="padding:20px;text-align:center;color:#aaa;font-family:Arial,sans-serif;font-size:11px;">No monthly data available for this period.</div>
        @endif
    </div>

    {{-- ── Routing + Categories ── --}}
    <div class="two-col">

        {{-- Left: Routing pathway donut --}}
        <div>
            <div class="sec-head">Routing pathway</div>
            <div class="donut-wrap">
                <svg viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg" style="width:130px;height:130px;flex-shrink:0;">
                    @if(count($donutSegs) === 1)
                    <circle cx="{{ $dcx }}" cy="{{ $dcy }}" r="{{ $dr }}" fill="{{ $donutSegs[0]['color'] }}"/>
                    <circle cx="{{ $dcx }}" cy="{{ $dcy }}" r="{{ $dr-($dr-$dr*0.65) }}" fill="white"/>
                    @else
                    @foreach($donutSegs as $seg)
                    <path d="{{ $seg['path'] }}" fill="{{ $seg['color'] }}"/>
                    @endforeach
                    @endif
                    {{-- Centre text --}}
                    <text x="{{ $dcx }}" y="{{ $dcy-4 }}" text-anchor="middle" font-size="18" font-weight="700" fill="#163029" font-family="Arial">{{ number_format($routingTotal) }}</text>
                    <text x="{{ $dcx }}" y="{{ $dcy+10 }}" text-anchor="middle" font-size="8" fill="#888" font-family="Arial">TOTAL</text>
                </svg>
                <div class="donut-legend">
                    @foreach($donutSegs as $seg)
                    <div class="donut-legend-row">
                        <div class="donut-dot" style="background:{{ $seg['color'] }};"></div>
                        <div class="donut-label" title="{{ $seg['full'] }}">{{ $seg['label'] }}</div>
                        <div class="donut-val">{{ number_format($seg['cnt']) }}</div>
                        <div class="donut-pct">{{ $seg['pct'] }}%</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right: Case categories --}}
        <div>
            <div class="sec-head">Case categories</div>
            @foreach($catTop as $issue => $cnt)
            @php $barW = $catMaxVal > 0 ? round($cnt/$catMaxVal*100) : 0; @endphp
            <div class="hbar-row">
                <div class="hbar-label">
                    <span style="color:#333;font-size:10px;">{{ $issue }}</span>
                    <span>{{ number_format($cnt) }}&nbsp;&nbsp;{{ round($cnt/$catTotal*100,1) }}%</span>
                </div>
                <div class="hbar-track">
                    <div class="hbar-fill" style="width:{{ $barW }}%;"></div>
                </div>
            </div>
            @endforeach
            @if($catOther > 0)
            <div class="hbar-row">
                <div class="hbar-label">
                    <span style="color:#aaa;font-size:10px;">Other categories</span>
                    <span>{{ number_format($catOther) }}&nbsp;&nbsp;{{ round($catOther/$catTotal*100,1) }}%</span>
                </div>
                <div class="hbar-track">
                    <div class="hbar-fill" style="width:{{ $catMaxVal > 0 ? round($catOther/$catMaxVal*100) : 0 }}%;background:#ccc;"></div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Geographic coverage ── --}}
    <div>
        <div class="sec-head">Geographic coverage <span>{{ $geoHubs }} {{ $geoHubs === 1 ? 'hub' : 'hubs' }} · {{ $geoDists }} {{ $geoDists === 1 ? 'district' : 'districts' }}</span></div>
        @if($geoRows->isEmpty())
        <div style="font-size:11px;color:#aaa;font-family:Arial,sans-serif;padding:12px 0;">No geographic data available.</div>
        @else
        <div class="geo-grid">
            @foreach($geoRows as $row)
            @php $geoBarW = round($row->cnt/$geoTotal*100); @endphp
            <div class="geo-row">
                <div style="display:flex;justify-content:space-between;align-items:baseline;">
                    <div>
                        <span class="geo-label">{{ $row->hname }}</span>
                        @if($row->hdist)
                        <span class="geo-sub"> · {{ $row->hdist }}</span>
                        @endif
                    </div>
                    <div style="display:flex;gap:8px;align-items:baseline;flex-shrink:0;">
                        <span class="geo-count">{{ number_format($row->cnt) }}</span>
                        <span class="geo-pct">{{ $geoBarW }}%</span>
                    </div>
                </div>
                <div class="geo-bar-track">
                    <div class="geo-bar-fill" style="width:{{ $geoBarW }}%;"></div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ── Footnote ── --}}
    <div class="footnote">
        Figures are exact counts of recorded intakes over the stated period, filtered to records carrying consent to share.
        No personal data is held in this report.
    </div>

    {{-- ── Footer ── --}}
    <div class="rpt-footer">
        <div class="rpt-footer-left">Justice Hub &middot; Powered by Legal Aid Society &middot; {{ $refCode }} &middot; Data as at {{ now()->format('Y-m-d') }}</div>
        <div class="rpt-footer-right">Page 1 of 1</div>
    </div>

</div>
</div>

<script>
function copyNarrative() {
    var text = document.getElementById('narrative').textContent.trim();
    navigator.clipboard.writeText(text).then(function() {
        var btn = document.querySelector('.btn-copy');
        var orig = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(function(){ btn.textContent = orig; }, 1800);
    });
}
</script>
</body>
</html>
