<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Justice Hub Dashboard Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #181714; font-size: 10px; line-height: 1.4; padding: 24px 28px; background: #fff; }

        .header { border-bottom: 3px solid #163029; padding-bottom: 12px; margin-bottom: 16px; }
        .header .org { font-size: 8px; color: #8a8a84; text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 2px; }
        .header h1 { font-size: 20px; font-weight: 400; color: #163029; }
        .header .sub { font-size: 9px; color: #6b6a65; margin-top: 3px; }

        .section { margin-bottom: 14px; }
        .section-title { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #163029; border-bottom: 1px solid #e5e2da; padding-bottom: 3px; margin-bottom: 8px; }

        /* KPI Strip */
        .kpi-strip { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .kpi-strip td { width: 20%; padding: 10px 12px; border: 1px solid #e5e2da; vertical-align: top; }
        .kpi-label { font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #6b6a65; margin-bottom: 4px; }
        .kpi-value { font-size: 24px; font-weight: 300; line-height: 1; }
        .kpi-sub { font-size: 8px; color: #8a8a84; margin-top: 3px; }
        .kpi-badge { display: inline-block; padding: 1px 5px; font-size: 7px; font-weight: 600; border-radius: 2px; }

        /* Colors */
        .c-forest { color: #163029; }
        .c-moss { color: #4a7a5c; }
        .c-ochre { color: #b87319; }
        .c-burgundy { color: #8a2e1d; }
        .c-ink { color: #181714; }
        .bg-green-soft { background: rgba(74,122,92,0.1); color: #4a7a5c; }
        .bg-red-soft { background: rgba(138,46,29,0.1); color: #8a2e1d; }
        .bg-ochre-soft { background: rgba(184,115,25,0.1); color: #b87319; }

        /* Tables */
        table.data { width: 100%; border-collapse: collapse; font-size: 9px; }
        table.data th { background: #f4f2eb; font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #6b6a65; padding: 5px 6px; text-align: left; border-bottom: 2px solid #e5e2da; }
        table.data td { padding: 4px 6px; border-bottom: 1px solid #f0ede6; }

        /* Bar charts (pure CSS) */
        .bar-row { margin-bottom: 4px; overflow: hidden; }
        .bar-label { font-size: 8.5px; color: #181714; display: inline-block; width: 48%; overflow: hidden; }
        .bar-val { font-size: 8.5px; color: #6b6a65; display: inline-block; width: 8%; text-align: right; }
        .bar-wrap { display: inline-block; width: 40%; height: 7px; background: #f0ede6; vertical-align: middle; }
        .bar-fill { height: 100%; }

        /* Two column layout */
        .two-col { width: 100%; }
        .two-col td { width: 50%; vertical-align: top; padding: 0; }
        .two-col td:first-child { padding-right: 10px; }
        .two-col td:last-child { padding-left: 10px; }

        /* Alert cards */
        .alert-card { padding: 6px 10px; border-left: 3px solid #163029; background: #faf9f6; margin-bottom: 4px; overflow: hidden; }
        .alert-label { font-size: 9px; color: #181714; display: inline-block; width: 75%; }
        .alert-value { font-size: 14px; font-weight: 600; display: inline-block; width: 20%; text-align: right; }

        /* Doughnut simulation */
        .donut-row { margin-bottom: 3px; overflow: hidden; }
        .donut-swatch { display: inline-block; width: 8px; height: 8px; vertical-align: middle; margin-right: 4px; }
        .donut-label { font-size: 8.5px; color: #181714; display: inline-block; width: 50%; }
        .donut-bar-wrap { display: inline-block; width: 28%; height: 7px; background: #f0ede6; vertical-align: middle; }
        .donut-val { font-size: 8.5px; font-weight: 600; display: inline-block; width: 10%; text-align: right; }

        .footer { margin-top: 14px; padding-top: 8px; border-top: 1px solid #e5e2da; font-size: 7.5px; color: #8a8a84; }

        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    {{-- ═══ Header ═══ --}}
    <div class="header">
        <div class="org">Legal Aid Society &middot; Justice Hub CMS</div>
        <h1>Strategic Overview — Dashboard Report</h1>
        <div class="sub">
            Generated {{ now()->format('d M Y, H:i') }} by {{ $user->name }}
            &middot; {{ $filterLabel }}
        </div>
    </div>

    {{-- ═══ KPI Strip ═══ --}}
    <table class="kpi-strip">
        <tr>
            <td>
                <div class="kpi-label">Total Cases</div>
                <div class="kpi-value c-ink">{{ number_format($m['total_cases']) }}</div>
                <div class="kpi-sub">{{ $casesLast7 ?? 0 }} new this week</div>
            </td>
            <td>
                <div class="kpi-label">Cases Resolved</div>
                <div class="kpi-value c-moss">{{ number_format($m['closed_cases']) }}</div>
                <div class="kpi-sub">{{ $resolvedLast7 ?? 0 }} this week</div>
            </td>
            <td>
                <div class="kpi-label">Active Cases</div>
                <div class="kpi-value c-ochre">{{ number_format($m['active_cases']) }}</div>
                <div class="kpi-sub">{{ $m['pending_approval'] }} pending approval</div>
            </td>
            <td>
                <div class="kpi-label">High Risk</div>
                <div class="kpi-value {{ $highRisk > 0 ? 'c-burgundy' : 'c-ink' }}">{{ $highRisk }}</div>
                <div class="kpi-sub">Flagged for escalation</div>
            </td>
            <td>
                <div class="kpi-label">SLA Compliance</div>
                <div class="kpi-value c-moss">{{ $m['sla_compliance'] }}%</div>
                <div style="height:3px; background:#f0ede6; margin-top:6px;">
                    <div style="height:100%; width:{{ $m['sla_compliance'] }}%; background:{{ $m['sla_compliance'] >= 90 ? '#4a7a5c' : '#b87319' }};"></div>
                </div>
                <div class="kpi-sub">{{ $m['sla_breach'] }} breach{{ $m['sla_breach'] !== 1 ? 'es' : '' }}</div>
            </td>
        </tr>
    </table>

    {{-- ═══ Row 2: Hub Distribution + Case Distribution + Status ═══ --}}
    <table class="two-col" style="margin-bottom: 14px;">
        <tr>
            <td>
                <div class="section-title">Hub Performance</div>
                <table class="data">
                    <thead>
                        <tr>
                            <th>Hub</th>
                            <th style="text-align:right;">Total</th>
                            <th style="text-align:right;">Active</th>
                            <th style="text-align:right;">Closed</th>
                            <th style="text-align:right;">SLA</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($m['hub_performance'] as $hub)
                    <tr>
                        <td>
                            <span style="display:inline-block; width:5px; height:5px; border-radius:50%; background:{{ $hub['sla_pct'] >= 90 ? '#4a7a5c' : ($hub['sla_pct'] >= 70 ? '#b87319' : '#8a2e1d') }}; vertical-align:middle; margin-right:3px;"></span>
                            {{ $hub['name'] }}
                        </td>
                        <td style="text-align:right; font-weight:600;">{{ $hub['total'] }}</td>
                        <td style="text-align:right; color:#4a7a5c;">{{ $hub['active'] }}</td>
                        <td style="text-align:right; color:#6b6a65;">{{ $hub['closed'] }}</td>
                        <td style="text-align:right;">
                            <span style="color:{{ $hub['sla_pct'] >= 90 ? '#4a7a5c' : ($hub['sla_pct'] >= 70 ? '#b87319' : '#8a2e1d') }}; font-weight:600;">{{ $hub['sla_pct'] }}%</span>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </td>
            <td>
                <div class="section-title">Hub Case Distribution</div>
                @foreach($hubDist as $hub)
                <div class="bar-row">
                    <span class="bar-label">{{ $hub['name'] }}</span>
                    <span class="bar-val">{{ $hub['count'] }}</span>
                    <span class="bar-wrap"><span class="bar-fill" style="width:{{ $hub['pct'] }}%; background:{{ $hub['color'] }};"></span></span>
                </div>
                @endforeach
            </td>
        </tr>
    </table>

    {{-- ═══ Row 3: Primary Issues + Service Pathways ═══ --}}
    @php
        $piColors = ['#163029','#4a7a5c','#b87319','#8a2e1d','#7e57c2','#6b6a65','#d9a05b','#3e6b53'];
        $svcTotal = array_sum($m['service_mix']) ?: 1;
        $piTotal = array_sum($primaryIssues) ?: 1;
    @endphp
    <table class="two-col" style="margin-bottom: 14px;">
        <tr>
            <td>
                <div class="section-title">Case Distribution by Type</div>
                @foreach($primaryIssues as $i => $issue)
                @php $idx = $loop->index; @endphp
                <div class="donut-row">
                    <span class="donut-swatch" style="background:{{ $piColors[$idx % 8] }};"></span>
                    <span class="donut-label">{{ $i }}</span>
                    <span class="donut-bar-wrap"><span class="bar-fill" style="width:{{ round(($issue / $piTotal) * 100) }}%; background:{{ $piColors[$idx % 8] }};"></span></span>
                    <span class="donut-val">{{ $issue }}</span>
                </div>
                @endforeach
            </td>
            <td>
                <div class="section-title">Service Pathways</div>
                @php $svcColors = ['#163029','#4a7a5c','#b87319','#8a2e1d','#7e57c2','#6b6a65','#d9a05b','#3e6b53']; @endphp
                @foreach($m['service_mix'] as $svc => $cnt)
                <div class="donut-row">
                    <span class="donut-swatch" style="background:{{ $svcColors[$loop->index % 8] }};"></span>
                    <span class="donut-label">{{ $svc }}</span>
                    <span class="donut-bar-wrap"><span class="bar-fill" style="width:{{ round(($cnt / $svcTotal) * 100) }}%; background:{{ $svcColors[$loop->index % 8] }};"></span></span>
                    <span class="donut-val">{{ $cnt }}</span>
                </div>
                @endforeach
            </td>
        </tr>
    </table>

    {{-- ═══ Row 4: Referral Sources + Alerts ═══ --}}
    @php $refMax = count($referralSources) > 0 ? max(array_values($referralSources)) : 1; @endphp
    <table class="two-col" style="margin-bottom: 14px;">
        <tr>
            <td>
                <div class="section-title">Referral Sources</div>
                @php $refColors = ['#163029','#4a7a5c','#b87319','#d9a05b','#6b6a65','#8a2e1d','#7e57c2','#3e6b53']; @endphp
                @foreach($referralSources as $src => $cnt)
                <div class="bar-row">
                    <span class="bar-label">{{ $src }}</span>
                    <span class="bar-val">{{ $cnt }}</span>
                    <span class="bar-wrap"><span class="bar-fill" style="width:{{ round(($cnt / $refMax) * 100) }}%; background:{{ $refColors[$loop->index % 8] }};"></span></span>
                </div>
                @endforeach
            </td>
            <td>
                <div class="section-title">Alerts &amp; Escalations</div>
                <div class="alert-card" style="border-left-color:#8a2e1d;">
                    <span class="alert-label">High Risk Cases</span>
                    <span class="alert-value c-burgundy">{{ $highRisk }}</span>
                </div>
                <div class="alert-card" style="border-left-color:#b87319;">
                    <span class="alert-label">SLA Breaches</span>
                    <span class="alert-value c-ochre">{{ $m['sla_breach'] }}</span>
                </div>
                <div class="alert-card" style="border-left-color:#163029;">
                    <span class="alert-label">Pending Approval</span>
                    <span class="alert-value c-forest">{{ $m['pending_approval'] }}</span>
                </div>
                <div class="alert-card" style="border-left-color:#7e57c2;">
                    <span class="alert-label">Open Complaints</span>
                    <span class="alert-value" style="color:#7e57c2;">{{ $m['complaints_open'] }}</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- ═══ Row 5: Gender + Age + Vulnerability ═══ --}}
    <table class="two-col" style="margin-bottom: 14px;">
        <tr>
            <td>
                <div class="section-title">Gender Breakdown</div>
                @php
                    $genderColors = ['Female' => '#4a7a5c', 'Male' => '#163029', 'Other' => '#b87319', 'Transgender' => '#7e57c2'];
                    $genderTotal = array_sum($m['gender_split']['counts'] ?? []) ?: 1;
                @endphp
                @foreach($m['gender_split']['counts'] ?? [] as $g => $cnt)
                <div style="margin-bottom: 5px;">
                    <div style="display:inline-block; width:55%; font-size:9px;">
                        <span style="display:inline-block; width:8px; height:8px; background:{{ $genderColors[$g] ?? '#6b6a65' }}; vertical-align:middle; margin-right:4px;"></span>
                        {{ $g }}
                    </div>
                    <div style="display:inline-block; width:12%; text-align:right; font-size:9px; font-weight:600;">{{ $cnt }}</div>
                    <div style="display:inline-block; width:10%; text-align:right; font-size:8px; color:#6b6a65;">{{ round(($cnt / $genderTotal) * 100, 1) }}%</div>
                    <div style="display:inline-block; width:20%; vertical-align:middle;">
                        <div style="height:7px; background:#f0ede6;">
                            <div style="height:100%; width:{{ round(($cnt / $genderTotal) * 100) }}%; background:{{ $genderColors[$g] ?? '#6b6a65' }};"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </td>
            <td>
                <div class="section-title">Age Distribution</div>
                @php $ageMax = max($m['age_distribution']['values'] ?? [1]); @endphp
                @foreach(($m['age_distribution']['labels'] ?? []) as $i => $label)
                @php $ageVal = $m['age_distribution']['values'][$i] ?? 0; @endphp
                <div class="bar-row">
                    <span class="bar-label">{{ $label }}</span>
                    <span class="bar-val">{{ $ageVal }}</span>
                    <span class="bar-wrap"><span class="bar-fill" style="width:{{ $ageMax > 0 ? round(($ageVal / $ageMax) * 100) : 0 }}%; background:#163029;"></span></span>
                </div>
                @endforeach
            </td>
        </tr>
    </table>

    {{-- ═══ Row 6: Vulnerability + Case Status + Additional Metrics ═══ --}}
    <table class="two-col" style="margin-bottom: 14px;">
        <tr>
            <td>
                <div class="section-title">Vulnerability Flags</div>
                @php
                    $vulnLabels = ['gbv' => 'GBV-Related', 'child' => 'Child-Related', 'minority' => 'Minority', 'disability' => 'Disability', 'underserved' => 'Underserved'];
                    $vulnColors = ['gbv' => '#8a2e1d', 'child' => '#b87319', 'minority' => '#7e57c2', 'disability' => '#6b6a65', 'underserved' => '#163029'];
                @endphp
                @foreach($m['vulnerability'] as $flag => $cnt)
                <div class="bar-row">
                    <span class="bar-label">
                        <span style="display:inline-block; width:8px; height:8px; background:{{ $vulnColors[$flag] ?? '#6b6a65' }}; vertical-align:middle; margin-right:3px;"></span>
                        {{ $vulnLabels[$flag] ?? ucfirst($flag) }}
                    </span>
                    <span class="bar-val">{{ $cnt }}</span>
                    <span class="bar-wrap"><span class="bar-fill" style="width:{{ $m['total_cases'] > 0 ? round(($cnt / $m['total_cases']) * 100) : 0 }}%; background:{{ $vulnColors[$flag] ?? '#6b6a65' }};"></span></span>
                </div>
                @endforeach
            </td>
            <td>
                <div class="section-title">Case Status</div>
                @php
                    $statusColors = ['Active' => '#4a7a5c', 'Closed' => '#6b6a65', 'Settlement' => '#163029', 'Pending Approval' => '#b87319', 'Rejected' => '#8a2e1d'];
                    $statusTotal = array_sum($m['status']) ?: 1;
                @endphp
                @foreach($m['status'] as $status => $cnt)
                <div style="margin-bottom: 5px;">
                    <div style="display:inline-block; width:48%; font-size:9px;">
                        <span style="display:inline-block; width:8px; height:8px; background:{{ $statusColors[$status] ?? '#6b6a65' }}; vertical-align:middle; margin-right:4px;"></span>
                        {{ $status }}
                    </div>
                    <div style="display:inline-block; width:10%; text-align:right; font-size:9px; font-weight:600;">{{ $cnt }}</div>
                    <div style="display:inline-block; width:8%; text-align:right; font-size:8px; color:#6b6a65;">{{ round(($cnt / $statusTotal) * 100) }}%</div>
                    <div style="display:inline-block; width:30%; vertical-align:middle;">
                        <div style="height:7px; background:#f0ede6;">
                            <div style="height:100%; width:{{ round(($cnt / $statusTotal) * 100) }}%; background:{{ $statusColors[$status] ?? '#6b6a65' }};"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </td>
        </tr>
    </table>

    {{-- ═══ Footer ═══ --}}
    <div class="footer">
        Justice Hub CMS &middot; Legal Aid Society &middot; Confidential &middot; Generated {{ now()->format('d M Y, H:i') }}
        &middot; This report reflects data as of the time of generation.
    </div>

</body>
</html>
