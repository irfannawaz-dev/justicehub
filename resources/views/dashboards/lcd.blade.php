<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Justice Hub — Live Dashboard</title>
    <meta http-equiv="refresh" content="120">
    @vite(['resources/css/app.css', 'resources/css/justice-hub.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0f1a14; color: #f7f3eb; font-family: 'Instrument Sans', system-ui, sans-serif; overflow-x: hidden; }

        .lcd-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 28px; background: #163029; border-bottom: 3px solid #d9a05b; }
        .lcd-brand { display: flex; align-items: center; gap: 14px; }
        .lcd-icon { width: 40px; height: 40px; background: #d9a05b; display: flex; align-items: center; justify-content: center; }
        .lcd-title { font-family: 'Fraunces', Georgia, serif; font-size: 22px; font-weight: 500; }
        .lcd-sub { font-size: 10px; opacity: 0.5; letter-spacing: 0.1em; text-transform: uppercase; font-family: 'JetBrains Mono', monospace; }
        .lcd-date { text-align: right; }
        .lcd-date-main { font-family: 'Fraunces', Georgia, serif; font-size: 18px; }
        .lcd-date-sub { font-size: 11px; opacity: 0.5; margin-top: 2px; }

        .lcd-hub-filter { display: flex; align-items: center; gap: 12px; padding: 10px 28px; background: #1a2e24; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .lcd-hub-filter label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.5; }
        .lcd-hub-filter select { background: #0f1a14; color: #f7f3eb; border: 1px solid rgba(255,255,255,0.15); padding: 6px 12px; font-size: 13px; font-family: inherit; }

        .lcd-body { padding: 18px 28px 28px; }

        /* ── Big Number Tiles ── */
        .lcd-tiles { display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; margin-bottom: 16px; }
        .lcd-tile { padding: 16px 18px; text-align: center; border-left: 4px solid; }
        .lcd-tile-label { font-size: 9.5px; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.7; margin-bottom: 6px; }
        .lcd-tile-value { font-family: 'Fraunces', Georgia, serif; font-size: 42px; font-weight: 500; line-height: 1; }

        .tile-forest { background: rgba(22,48,41,0.6); border-color: #4a7a5c; }
        .tile-forest .lcd-tile-value { color: #7bc496; }
        .tile-blue { background: rgba(30,60,100,0.4); border-color: #4a90d9; }
        .tile-blue .lcd-tile-value { color: #6ab4f7; }
        .tile-ochre { background: rgba(184,115,25,0.15); border-color: #d9a05b; }
        .tile-ochre .lcd-tile-value { color: #d9a05b; }
        .tile-red { background: rgba(138,46,29,0.2); border-color: #d94f3a; }
        .tile-red .lcd-tile-value { color: #f06050; }
        .tile-moss { background: rgba(74,122,92,0.15); border-color: #4a7a5c; }
        .tile-moss .lcd-tile-value { color: #6bbd8a; }
        .tile-cyan { background: rgba(40,120,140,0.2); border-color: #3cc0d0; }
        .tile-cyan .lcd-tile-value { color: #4dd8e8; }

        /* ── Grid Sections ── */
        .lcd-grid { display: flex; flex-wrap: wrap; gap: 14px; margin-bottom: 16px; }
        .lcd-grid > .lcd-draggable { flex: 1 1 calc(33.333% - 10px); min-width: 300px; }
        .lcd-grid > .lcd-wide { flex: 1 1 calc(66.666% - 10px); }

        /* Drag & drop */
        .lcd-draggable { cursor: grab; transition: transform 0.15s, box-shadow 0.15s; }
        .lcd-draggable:active { cursor: grabbing; }
        .lcd-dragging { opacity: 0.4; transform: scale(0.97); }
        .lcd-drag-over { box-shadow: 0 0 0 2px #d9a05b, 0 0 20px rgba(217,160,91,0.2); }
        .lcd-drag-handle { display: flex; align-items: center; gap: 6px; margin-bottom: 14px; cursor: grab; }
        .lcd-drag-handle:hover svg { opacity: 0.7; }

        .lcd-panel { background: rgba(22,48,41,0.4); border: 1px solid rgba(255,255,255,0.06); padding: 16px 18px; }
        .lcd-panel-title { font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; color: #d9a05b; margin-bottom: 14px; font-weight: 600; }

        /* ── Bar Rows ── */
        .lcd-bar-row { display: flex; align-items: center; gap: 10px; padding: 6px 0; border-bottom: 1px solid rgba(255,255,255,0.04); }
        .lcd-bar-label { width: 160px; font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .lcd-bar-track { flex: 1; height: 22px; background: rgba(255,255,255,0.04); overflow: hidden; }
        .lcd-bar-fill { height: 100%; display: flex; align-items: center; justify-content: flex-end; padding-right: 8px; font-size: 11px; font-weight: 700; min-width: 36px; transition: width 0.6s ease; }
        .lcd-bar-count { width: 50px; text-align: right; font-size: 16px; font-weight: 700; font-family: 'JetBrains Mono', monospace; }

        /* Bar colors */
        .bar-green { background: linear-gradient(90deg, #2d6b45, #4a7a5c); color: #fff; }
        .bar-blue { background: linear-gradient(90deg, #2a5a8a, #4a90d9); color: #fff; }
        .bar-ochre { background: linear-gradient(90deg, #8a5a10, #d9a05b); color: #fff; }
        .bar-red { background: linear-gradient(90deg, #8a2e1d, #d94f3a); color: #fff; }
        .bar-cyan { background: linear-gradient(90deg, #1a6a7a, #3cc0d0); color: #fff; }
        .bar-purple { background: linear-gradient(90deg, #5a3a8a, #8a6ad9); color: #fff; }

        /* ── Staff Grid ── */
        .lcd-staff-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 8px; }
        .lcd-staff-card { text-align: center; padding: 12px 8px; border: 1px solid rgba(255,255,255,0.08); }
        .lcd-staff-name { font-size: 12px; font-weight: 600; margin-bottom: 4px; color: #f7f3eb; }
        .lcd-staff-count { font-family: 'Fraunces', Georgia, serif; font-size: 32px; font-weight: 500; }

        .staff-yellow { background: rgba(220,180,40,0.15); border-color: rgba(220,180,40,0.3); }
        .staff-yellow .lcd-staff-count { color: #f0d040; }
        .staff-pink { background: rgba(220,80,120,0.15); border-color: rgba(220,80,120,0.3); }
        .staff-pink .lcd-staff-count { color: #f06090; }
        .staff-blue { background: rgba(60,140,220,0.15); border-color: rgba(60,140,220,0.3); }
        .staff-blue .lcd-staff-count { color: #60b0f0; }
        .staff-green { background: rgba(60,180,100,0.15); border-color: rgba(60,180,100,0.3); }
        .staff-green .lcd-staff-count { color: #60d090; }
        .staff-orange { background: rgba(220,140,40,0.15); border-color: rgba(220,140,40,0.3); }
        .staff-orange .lcd-staff-count { color: #f0a030; }
        .staff-cyan { background: rgba(40,180,200,0.15); border-color: rgba(40,180,200,0.3); }
        .staff-cyan .lcd-staff-count { color: #40d0e0; }

        /* ── Footer ── */
        .lcd-footer { padding: 10px 28px; background: #163029; border-top: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between; font-size: 10px; opacity: 0.4; text-transform: uppercase; letter-spacing: 0.08em; }

        /* Auto-refresh pulse */
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }
        .lcd-live { display: inline-flex; align-items: center; gap: 6px; }
        .lcd-live-dot { width: 7px; height: 7px; background: #4dd84d; border-radius: 50%; animation: pulse 2s infinite; }
    </style>
</head>
<body>

{{-- ═══ HEADER ═══ --}}
<div class="lcd-header">
    <div class="lcd-brand">
        <div class="lcd-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#163029" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3L2 12h3v8h6v-6h2v6h6v-8h3L12 3z"/><path d="m17 3 4 4"/><path d="M2 12 12 3l10 9"/><line x1="3" y1="21" x2="21" y2="21"/><path d="M12 7v5"/><path d="M8 11h8"/><path d="M7 21V11"/><path d="M17 21V11"/></svg>
        </div>
        <div>
            <div class="lcd-title">Justice Hub — Live Dashboard</div>
            <div class="lcd-sub">Legal Aid Society · Case Management System</div>
        </div>
    </div>
    <div class="lcd-date">
        <div class="lcd-date-main">{{ now()->format('l, d M Y') }}</div>
        <div class="lcd-date-sub">
            <span class="lcd-live"><span class="lcd-live-dot"></span> LIVE</span>
            &nbsp;·&nbsp; Auto-refresh every 2 min &nbsp;·&nbsp; {{ now()->format('h:i A') }}
        </div>
    </div>
</div>

{{-- ═══ HUB FILTER ═══ --}}
<div class="lcd-hub-filter">
    <label>Filter by Hub:</label>
    <select onchange="window.location.href='/lcd?hub='+this.value">
        <option value="all" {{ $hubId === 'all' ? 'selected' : '' }}>All Hubs</option>
        @foreach($hubs as $id => $name)
            <option value="{{ $id }}" {{ $hubId === $id ? 'selected' : '' }}>{{ $name }}</option>
        @endforeach
    </select>
    <span style="margin-left: auto; font-size: 11px; opacity: 0.4;">
        Showing: {{ $hubId === 'all' ? 'All Hubs' : ($hubs[$hubId] ?? $hubId) }}
    </span>
</div>

<div class="lcd-body">

    {{-- ═══ BIG NUMBER TILES ═══ --}}
    <div class="lcd-tiles">
        <div class="lcd-tile tile-forest">
            <div class="lcd-tile-label">Total Cases</div>
            <div class="lcd-tile-value">{{ number_format($lcd['total']) }}</div>
        </div>
        <div class="lcd-tile tile-blue">
            <div class="lcd-tile-label">Active Cases</div>
            <div class="lcd-tile-value">{{ number_format($lcd['active']) }}</div>
        </div>
        <div class="lcd-tile tile-cyan">
            <div class="lcd-tile-label">Today's Intakes</div>
            <div class="lcd-tile-value">{{ number_format($todayCases) }}</div>
        </div>
        <div class="lcd-tile tile-ochre">
            <div class="lcd-tile-label">Pending Approval</div>
            <div class="lcd-tile-value">{{ number_format($lcd['pending']) }}</div>
        </div>
        <div class="lcd-tile tile-moss">
            <div class="lcd-tile-label">Resolved / Closed</div>
            <div class="lcd-tile-value">{{ number_format($lcd['closed']) }}</div>
        </div>
        <div class="lcd-tile tile-red">
            <div class="lcd-tile-label">High / Immediate</div>
            <div class="lcd-tile-value">{{ number_format($lcd['high_urgency']) }}</div>
        </div>
    </div>

    {{-- ═══ DRAGGABLE PANELS ═══ --}}
    <div id="lcd-drag-container" class="lcd-grid">

        {{-- Heard From / Referral Sources --}}
        <div class="lcd-panel lcd-draggable" draggable="true">
            <div class="lcd-drag-handle"><x-lucide-grip-vertical style="width:14px;height:14px;opacity:0.3;" /> <span class="lcd-panel-title" style="margin:0;">Heard From / Referral Source</span></div>
            @if(count($sources) > 0)
                @php $maxSrc = max($sources); @endphp
                @foreach($sources as $src => $count)
                <div class="lcd-bar-row">
                    <div class="lcd-bar-label">{{ $src }}</div>
                    <div class="lcd-bar-track">
                        <div class="lcd-bar-fill bar-blue" style="width: {{ $maxSrc > 0 ? max(round(($count / $maxSrc) * 100), 8) : 8 }}%;">
                            {{ $count }}
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div style="opacity: 0.3; text-align: center; padding: 20px;">No data</div>
            @endif
        </div>

        {{-- Category Wise — Doughnut Chart --}}
        <div class="lcd-panel lcd-draggable" draggable="true">
            <div class="lcd-drag-handle"><x-lucide-grip-vertical style="width:14px;height:14px;opacity:0.3;" /> <span class="lcd-panel-title" style="margin:0;">Category Wise</span></div>
            @if(count($categories) > 0)
                <div style="position: relative; height: 380px;">
                    <canvas id="lcd-pie-chart"></canvas>
                </div>
            @else
                <div style="opacity: 0.3; text-align: center; padding: 20px;">No data</div>
            @endif
        </div>

        {{-- Monthly Intake Trend — Line Chart --}}
        <div class="lcd-panel lcd-draggable" draggable="true">
            <div class="lcd-drag-handle"><x-lucide-grip-vertical style="width:14px;height:14px;opacity:0.3;" /> <span class="lcd-panel-title" style="margin:0;">Monthly Intake Trend</span></div>
            @if(count($monthlyTrend) > 0)
                <div style="position: relative; height: 340px;">
                    <canvas id="lcd-line-chart"></canvas>
                </div>
            @else
                <div style="opacity: 0.3; text-align: center; padding: 20px;">No data</div>
            @endif
        </div>

        {{-- Staff Performance --}}
        <div class="lcd-panel lcd-draggable lcd-wide" draggable="true">
            <div class="lcd-drag-handle"><x-lucide-grip-vertical style="width:14px;height:14px;opacity:0.3;" /> <span class="lcd-panel-title" style="margin:0;">Staff — Active Caseload</span></div>
            @if(count($advisors) > 0)
                @php
                    $staffColors = ['staff-yellow','staff-pink','staff-blue','staff-green','staff-orange','staff-cyan'];
                    $visibleStaff = array_slice($advisors, 0, 8, true);
                    $hiddenStaff = array_slice($advisors, 8, null, true);
                @endphp
                <div class="lcd-staff-grid">
                    @foreach($visibleStaff as $name => $count)
                    <div class="lcd-staff-card {{ $staffColors[$loop->index % count($staffColors)] }}">
                        <div class="lcd-staff-name">{{ $name }}</div>
                        <div class="lcd-staff-count">{{ $count }}</div>
                    </div>
                    @endforeach

                    @if(count($hiddenStaff) > 0)
                    <div id="lcd-show-all-btn" class="lcd-staff-card" style="background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.15); cursor: pointer;" onclick="document.querySelectorAll('.lcd-hidden-staff').forEach(el => el.style.display=''); document.getElementById('lcd-show-less-btn').style.display=''; this.style.display='none';">
                        <div class="lcd-staff-name" style="color: #6ab4f7;">Show All</div>
                        <div class="lcd-staff-count" style="color: #6ab4f7; font-size: 22px;">+{{ count($hiddenStaff) }}</div>
                    </div>
                    @endif

                    @foreach($hiddenStaff as $name => $count)
                    <div class="lcd-staff-card lcd-hidden-staff {{ $staffColors[($loop->index + 8) % count($staffColors)] }}" style="display: none;">
                        <div class="lcd-staff-name">{{ $name }}</div>
                        <div class="lcd-staff-count">{{ $count }}</div>
                    </div>
                    @endforeach

                    @if(count($hiddenStaff) > 0)
                    <div id="lcd-show-less-btn" class="lcd-staff-card" style="display: none; background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.15); cursor: pointer;" onclick="document.querySelectorAll('.lcd-hidden-staff').forEach(el => el.style.display='none'); document.getElementById('lcd-show-all-btn').style.display=''; this.style.display='none';">
                        <div class="lcd-staff-name" style="color: #f06050;">Show Less</div>
                        <div class="lcd-staff-count" style="color: #f06050; font-size: 22px;">&minus;</div>
                    </div>
                    @endif

                </div>
            @else
                <div style="opacity: 0.3; text-align: center; padding: 20px;">No active assignments</div>
            @endif
        </div>

        {{-- Hub Wise + Pathways --}}
        <div class="lcd-panel lcd-draggable" draggable="true">
            <div class="lcd-drag-handle"><x-lucide-grip-vertical style="width:14px;height:14px;opacity:0.3;" /> <span class="lcd-panel-title" style="margin:0;">Hub Wise Breakdown</span></div>
            @if(count($hubBreakdown) > 0)
                @php $maxHub = max($hubBreakdown); @endphp
                @foreach($hubBreakdown as $hid => $count)
                <div class="lcd-bar-row">
                    <div class="lcd-bar-label">{{ $hubNames[$hid] ?? $hid }}</div>
                    <div class="lcd-bar-track">
                        <div class="lcd-bar-fill bar-green" style="width: {{ $maxHub > 0 ? max(round(($count / $maxHub) * 100), 8) : 8 }}%;">
                            {{ $count }}
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div style="opacity: 0.3; text-align: center; padding: 20px;">No data</div>
            @endif

            <div class="lcd-panel-title" style="margin-top: 18px;">Pathway Distribution</div>
            @if(count($pathways) > 0)
                @php $maxPw = max($pathways); @endphp
                @foreach($pathways as $pw => $count)
                <div class="lcd-bar-row">
                    <div class="lcd-bar-label">{{ $pw }}</div>
                    <div class="lcd-bar-track">
                        <div class="lcd-bar-fill bar-purple" style="width: {{ $maxPw > 0 ? max(round(($count / $maxPw) * 100), 8) : 8 }}%;">
                            {{ $count }}
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div style="opacity: 0.3; text-align: center; padding: 20px;">No data</div>
            @endif
        </div>
    </div>

    {{-- ═══ RISK STRIP ═══ --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
        <div class="lcd-tile tile-red" style="padding: 12px 16px;">
            <div class="lcd-tile-label">Safeguarding Active (GBV/Child)</div>
            <div class="lcd-tile-value" style="font-size: 32px;">{{ $lcd['safeguarding'] }}</div>
        </div>
        <div class="lcd-tile {{ $lcd['sla_pct'] >= 90 ? 'tile-moss' : 'tile-red' }}" style="padding: 12px 16px;">
            <div class="lcd-tile-label">SLA Compliance</div>
            <div class="lcd-tile-value" style="font-size: 32px;">{{ $lcd['sla_pct'] }}%</div>
        </div>
        <div class="lcd-tile {{ $lcd['sla_breach'] > 0 ? 'tile-red' : 'tile-moss' }}" style="padding: 12px 16px;">
            <div class="lcd-tile-label">SLA Breaches</div>
            <div class="lcd-tile-value" style="font-size: 32px;">{{ $lcd['sla_breach'] }}</div>
        </div>
    </div>
</div>

{{-- ═══ FOOTER ═══ --}}
<div class="lcd-footer">
    <span>Legal Aid Society · Justice Hub CMS v1.0</span>
    <span>Last refreshed: {{ now()->format('d M Y, h:i:s A') }}</span>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for Chart.js to load via Vite
    const waitForChart = setInterval(function() {
        if (typeof Chart === 'undefined') return;
        clearInterval(waitForChart);

        Chart.defaults.color = '#b0a890';
        Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
        Chart.defaults.font.family = "'Instrument Sans', system-ui, sans-serif";

        // ── Doughnut: Category Wise ──
        const pieEl = document.getElementById('lcd-pie-chart');
        if (pieEl) {
            const pieColors = ['#4a7a5c','#d9a05b','#4a90d9','#d94f3a','#3cc0d0','#8a6ad9','#6bbd8a','#f06090','#f0a030','#40d0e0'];
            new Chart(pieEl, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_map(fn($k, $v) => "$k ($v)", array_keys($categories), array_values($categories))) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($categories)) !!},
                        backgroundColor: pieColors.slice(0, {{ count($categories) }}),
                        borderWidth: 2,
                        borderColor: '#0f1a14',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '55%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                color: '#ffffff',
                                font: { size: 11, weight: '500' },
                                padding: 12,
                                boxWidth: 14,
                                boxHeight: 10,
                            }
                        },
                        tooltip: {
                            backgroundColor: '#1a2e24',
                            titleColor: '#f7f3eb',
                            bodyColor: '#d0c8b0',
                            borderColor: 'rgba(255,255,255,0.1)',
                            borderWidth: 1,
                        }
                    }
                }
            });
        }

        // ── Line: Monthly Intake Trend ──
        const lineEl = document.getElementById('lcd-line-chart');
        if (lineEl) {
            const months = {!! json_encode(array_keys($monthlyTrend)) !!};
            const monthLabels = months.map(m => {
                const [y, mo] = m.split('-');
                return ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][parseInt(mo)-1] + ' ' + y.slice(2);
            });
            const dataLabelPlugin = {
                id: 'lcdDataLabels',
                afterDatasetsDraw(chart) {
                    const { ctx } = chart;
                    chart.data.datasets.forEach((dataset, di) => {
                        const meta = chart.getDatasetMeta(di);
                        meta.data.forEach((point, i) => {
                            const value = dataset.data[i];
                            ctx.save();
                            ctx.fillStyle = '#ffffff';
                            ctx.font = 'bold 13px "Instrument Sans", system-ui, sans-serif';
                            ctx.textAlign = 'center';
                            ctx.fillText(value, point.x, point.y - 14);
                            ctx.restore();
                        });
                    });
                }
            };

            new Chart(lineEl, {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'Cases Registered',
                        data: {!! json_encode(array_values($monthlyTrend)) !!},
                        borderColor: '#4dd8e8',
                        backgroundColor: 'rgba(77,216,232,0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#4dd8e8',
                        pointBorderColor: '#0f1a14',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 9,
                        fill: true,
                        tension: 0.3,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { top: 24 } },
                    scales: {
                        x: {
                            ticks: { color: '#b0a890', font: { size: 12 } },
                            grid: { color: 'rgba(255,255,255,0.04)' },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#b0a890', font: { size: 12 } },
                            grid: { color: 'rgba(255,255,255,0.06)' },
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1a2e24',
                            titleColor: '#f7f3eb',
                            bodyColor: '#d0c8b0',
                            borderColor: 'rgba(255,255,255,0.1)',
                            borderWidth: 1,
                            callbacks: {
                                label: ctx => ctx.parsed.y + ' cases'
                            }
                        }
                    }
                },
                plugins: [dataLabelPlugin]
            });
        }
    }, 100);

    // ── Drag & Drop for panels (order saved to localStorage) ──
    const container = document.getElementById('lcd-drag-container');
    if (container) {
        const STORAGE_KEY = 'lcd_panel_order';

        // Assign data-panel IDs from title text
        container.querySelectorAll('.lcd-draggable').forEach((panel, i) => {
            const title = panel.querySelector('.lcd-panel-title');
            panel.dataset.panel = title ? title.textContent.trim() : 'panel-' + i;
        });

        // Restore saved order on load
        try {
            const saved = JSON.parse(localStorage.getItem(STORAGE_KEY));
            if (saved && saved.length) {
                saved.forEach(id => {
                    const panel = container.querySelector('[data-panel="' + id + '"]');
                    if (panel) container.appendChild(panel);
                });
            }
        } catch (e) {}

        function saveOrder() {
            const order = [...container.querySelectorAll('.lcd-draggable')].map(p => p.dataset.panel);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(order));
        }

        let dragEl = null;

        container.addEventListener('dragstart', e => {
            const panel = e.target.closest('.lcd-draggable');
            if (!panel) return;
            dragEl = panel;
            panel.classList.add('lcd-dragging');
            e.dataTransfer.effectAllowed = 'move';
        });

        container.addEventListener('dragend', e => {
            if (dragEl) dragEl.classList.remove('lcd-dragging');
            container.querySelectorAll('.lcd-draggable').forEach(el => el.classList.remove('lcd-drag-over'));
            dragEl = null;
        });

        container.addEventListener('dragover', e => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            const target = e.target.closest('.lcd-draggable');
            if (target && target !== dragEl) {
                container.querySelectorAll('.lcd-draggable').forEach(el => el.classList.remove('lcd-drag-over'));
                target.classList.add('lcd-drag-over');
            }
        });

        container.addEventListener('dragleave', e => {
            const target = e.target.closest('.lcd-draggable');
            if (target) target.classList.remove('lcd-drag-over');
        });

        container.addEventListener('drop', e => {
            e.preventDefault();
            const target = e.target.closest('.lcd-draggable');
            if (!target || !dragEl || target === dragEl) return;
            target.classList.remove('lcd-drag-over');

            const allPanels = [...container.querySelectorAll('.lcd-draggable')];
            const dragIdx = allPanels.indexOf(dragEl);
            const targetIdx = allPanels.indexOf(target);

            // Place dragged element relative to target
            const rect = target.getBoundingClientRect();
            const midX = rect.left + rect.width / 2;
            if (e.clientX < midX) {
                target.before(dragEl);
            } else {
                target.after(dragEl);
            }

            saveOrder();
        });
    }
});
</script>

</body>
</html>
