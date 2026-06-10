<x-layouts.app>
@php
    $p1Count = $indicators->where('priority', 'P1')->count();
    $levelDescriptions = [
        'Goal'      => 'Justice Hubs are progressively institutionalised as community-based access to justice platforms with a clear pathway for replication.',
        'Outcome 1' => 'Vulnerable individuals experience improved access to justice through effective legal representation, mediation & justice navigation services.',
        'Outcome 2' => 'Enhanced legal awareness and agency among communities resulting in informed decision-making and exercise of legal rights.',
        'Outcome 3' => 'Strengthened, institutionalised coordination between justice sector institutions and communities.',
        'Output 1'  => 'Justice Hubs operate with standardised governance, trained staff, SOPs, and functional case management systems.',
        'Output 2'  => 'Eligible/vulnerable individuals receive legal advice, representation, mediation, documentation, and justice navigation services.',
        'Output 3'  => 'Community legal awareness and early problem identification strengthened through paralegal-led outreach.',
        'Output 4'  => 'Referral pathways, data systems, learning products and accountability mechanisms support coordination and evidence-informed engagement.',
    ];
    $levelColors = [
        'Goal' => 'var(--forest)', 'Outcome 1' => 'var(--ochre)', 'Outcome 2' => 'var(--ochre)', 'Outcome 3' => 'var(--ochre)',
        'Output 1' => 'var(--ink-2)', 'Output 2' => 'var(--ink-2)', 'Output 3' => 'var(--ink-2)', 'Output 4' => 'var(--ink-2)',
    ];
@endphp

<div style="padding: 28px 36px 60px; max-width: 1640px; margin: 0 auto;">

    {{-- ═══ Header ═══ --}}
    <div style="margin-bottom: 22px; padding-bottom: 18px; border-bottom: 1px solid var(--rule);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 28px;">
            <div style="flex: 1;">
                <div class="label-cap" style="font-size: 9.5px; color: var(--ochre); margin-bottom: 6px;">Results Framework &middot; Q2 {{ now()->year }}</div>
                <h1 class="serif" style="font-size: 34px; font-weight: 400; letter-spacing: -0.015em; margin: 0 0 10px 0;">
                    {{ $counts['total'] }} indicators tracked across the <em>Goal</em> &rarr; <em>Outcomes</em> &rarr; <em>Outputs</em> hierarchy, aligned to SDG 16 and the institutionalisation pathway.
                </h1>
            </div>
            {{-- Stats strip --}}
            <div class="card" style="padding: 16px 22px; flex-shrink: 0; display: grid; grid-template-columns: repeat(5, auto); gap: 0; min-width: 420px;">
                <div style="text-align: center; padding: 0 16px; border-right: 1px solid var(--rule-2);">
                    <div class="label-cap" style="font-size: 8.5px; color: var(--ochre); margin-bottom: 5px;">Total</div>
                    <div class="serif" style="font-size: 28px; font-weight: 500; line-height: 1;">{{ $counts['total'] }}</div>
                </div>
                <div style="text-align: center; padding: 0 16px; border-right: 1px solid var(--rule-2);">
                    <div class="label-cap" style="font-size: 8.5px; margin-bottom: 5px;">Priority</div>
                    <div style="font-size: 12.5px; font-weight: 600; line-height: 1.5;">
                        <span style="color: var(--burgundy);">P0</span>:{{ $counts['p0'] }}&ensp;<span style="color: var(--ink-3);">P1</span>:{{ $p1Count }}
                    </div>
                </div>
                <div style="text-align: center; padding: 0 16px; border-right: 1px solid var(--rule-2);">
                    <div class="label-cap" style="font-size: 8.5px; margin-bottom: 5px;"><span style="color: var(--moss);">&bull;</span> On Track</div>
                    <div class="serif" style="font-size: 28px; font-weight: 500; line-height: 1; color: var(--moss);">{{ $counts['green'] }}</div>
                </div>
                <div style="text-align: center; padding: 0 16px; border-right: 1px solid var(--rule-2);">
                    <div class="label-cap" style="font-size: 8.5px; margin-bottom: 5px;"><span style="color: var(--ochre);">&bull;</span> Attention</div>
                    <div class="serif" style="font-size: 28px; font-weight: 500; line-height: 1; color: var(--ochre);">{{ $counts['amber'] }}</div>
                </div>
                <div style="text-align: center; padding: 0 16px;">
                    <div class="label-cap" style="font-size: 8.5px; margin-bottom: 5px;"><span style="color: var(--burgundy);">&bull;</span> Off Track</div>
                    <div class="serif" style="font-size: 28px; font-weight: 500; line-height: 1; color: var(--burgundy);">{{ $counts['red'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ CMS-fed summary + Filters ═══ --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 24px; font-size: 12.5px; color: var(--ink-3);">
            <span style="display: flex; align-items: center; gap: 6px;">
                <span style="width: 7px; height: 7px; background: var(--moss); border-radius: 50;"></span>
                <strong style="color: var(--ink);">{{ $counts['total'] }}</strong> live &middot; CMS-fed
                <span style="font-size: 11px; color: var(--ink-4);">Auto-derived from cases, outreach, partners</span>
            </span>
            <span style="display: flex; align-items: center; gap: 6px;">
                <span style="width: 7px; height: 7px; background: var(--ochre); border-radius: 50;"></span>
                <strong style="color: var(--ink);">0</strong> manual entry
                <span style="font-size: 11px; color: var(--ink-4);">Need a feedback / staff / evidence module</span>
            </span>
            <span style="font-size: 11px; color: var(--ink-4); font-style: italic; margin-left: 20px;">
                Live indicators recompute every time the page loads. Manual ones are entered each cycle until their source module is built.
            </span>
        </div>
    </div>

    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span class="label-cap" style="font-size: 9.5px; color: var(--ink-3);">Filter</span>
            <button onclick="indFilter('all', this)" data-ind-f="all"
                    style="padding: 5px 12px; font-size: 11.5px; font-weight: 600; font-family: inherit; cursor: pointer; border: 1px solid var(--forest); background: var(--forest); color: var(--cream); letter-spacing: 0.02em;">All priorities</button>
            <button onclick="indFilter('P0', this)" data-ind-f="P0"
                    style="padding: 5px 12px; font-size: 11.5px; font-weight: 500; font-family: inherit; cursor: pointer; border: 1px solid var(--rule); background: transparent; color: var(--ink-2); letter-spacing: 0.02em;">P0</button>
            <button onclick="indFilter('P1', this)" data-ind-f="P1"
                    style="padding: 5px 12px; font-size: 11.5px; font-weight: 500; font-family: inherit; cursor: pointer; border: 1px solid var(--rule); background: transparent; color: var(--ink-2); letter-spacing: 0.02em;">P1</button>

            <select onchange="indFilterLevel(this.value)" style="padding: 5px 10px; font-size: 11.5px; border: 1px solid var(--rule); background: var(--parchment); color: var(--ink); font-family: inherit; margin-left: 8px; cursor: pointer;">
                <option value="all">All levels</option>
                @foreach($grouped->keys() as $lvl)
                <option value="{{ $lvl }}">{{ $lvl }}</option>
                @endforeach
            </select>
        </div>

        <a href="{{ route('impact.index') }}" style="display: flex; align-items: center; gap: 6px; font-size: 11.5px; padding: 5px 12px; border: 1px solid var(--rule); color: var(--ink-2); text-decoration: none; font-family: inherit;">
            <x-lucide-external-link style="width:11px;height:11px;" /> Export for donor reporting
        </a>
    </div>

    {{-- ═══ Indicator Groups ═══ --}}
    @foreach($grouped as $level => $levelIndicators)
    <div style="margin-bottom: 36px;" data-ind-level="{{ $level }}">
        {{-- Level header --}}
        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 14px;">
            <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; padding: 3px 10px; background: {{ $levelColors[$level] ?? 'var(--ink-2)' }}; color: var(--cream);">{{ $level }}</span>
            <div style="font-size: 13.5px; color: var(--ink-2); font-style: italic; line-height: 1.4; flex: 1;">
                {{ $levelDescriptions[$level] ?? '' }}
            </div>
        </div>
        <div style="font-size: 11px; color: var(--ink-4); margin-bottom: 12px;">{{ $levelIndicators->count() }} indicators</div>

        {{-- Indicator cards grid --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 12px;">
            @foreach($levelIndicators as $ind)
            @php
                $ragColor = match($ind->rag) {
                    'green' => 'var(--moss)', 'amber' => 'var(--ochre)', 'red' => 'var(--burgundy)', default => 'var(--ink-3)',
                };
                $ragLabel = match($ind->rag) {
                    'green' => 'GREEN', 'amber' => 'AMBER', 'red' => 'RED', default => 'GREY',
                };
                $ragBarColor = match($ind->rag) {
                    'green' => 'var(--moss)', 'amber' => 'var(--ochre)', 'red' => 'var(--burgundy)', default => 'var(--rule)',
                };
                $pct = $ind->target > 0
                    ? ($ind->is_inverse
                        ? min(100, round(($ind->target / max($ind->actual, 0.01)) * 100))
                        : min(100, round(($ind->actual / $ind->target) * 100)))
                    : 100;
                $cadenceLabel = match($ind->cadence ?? '') {
                    'monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'annual' => 'Annual', default => ucfirst($ind->cadence ?? ''),
                };
            @endphp
            <div class="card" data-ind-priority="{{ $ind->priority }}"
                 onclick="indOpenPanel({{ $ind->id }})"
                 style="padding: 18px 20px; border-top: 3px solid {{ $ragBarColor }}; cursor: pointer; transition: transform 100ms, border-color 100ms;"
                 onmouseenter="this.style.transform='translateY(-1px)'" onmouseleave="this.style.transform='none'">
                {{-- Top row: code + badges + RAG --}}
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 5px;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span class="mono" style="font-size: 12px; font-weight: 600; color: var(--ink);">{{ $ind->code }}</span>
                        <span style="font-size: 9px; padding: 1px 5px; font-weight: 700; letter-spacing: 0.05em; background: {{ $ind->priority === 'P0' ? 'var(--forest)' : 'var(--paper)' }}; color: {{ $ind->priority === 'P0' ? 'var(--cream)' : 'var(--ink-3)' }}; border: 1px solid {{ $ind->priority === 'P0' ? 'var(--forest)' : 'var(--rule)' }};">{{ $ind->priority }}</span>
                        <span style="font-size: 10px; color: var(--ink-4);">{{ $cadenceLabel }}</span>
                        <span style="font-size: 9px; padding: 1px 5px; background: rgba(74,122,92,0.1); color: var(--moss); font-weight: 600; letter-spacing: 0.03em;">&bull;LIVE&middot;CMS</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <span style="width: 8px; height: 8px; background: {{ $ragColor }}; border-radius: 50; display: inline-block;"></span>
                        <span style="font-size: 10.5px; font-weight: 600; color: {{ $ragColor }}; letter-spacing: 0.03em;">{{ $ragLabel }}</span>
                    </div>
                </div>

                {{-- Description --}}
                <div style="font-size: 12.5px; color: var(--ink-2); line-height: 1.45; margin-bottom: 16px; min-height: 34px;">{{ $ind->name }}</div>

                {{-- Actual vs Target --}}
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                    <div>
                        <div class="label-cap" style="font-size: 8.5px; margin-bottom: 2px;">Actual</div>
                        <div class="serif" style="font-size: 28px; font-weight: 500; line-height: 1;">
                            {{ $ind->unit === '%' ? round($ind->actual) . '%' : number_format((float)$ind->actual) }}
                        </div>
                        <div style="font-size: 10px; color: var(--ink-4); margin-top: 1px;">{{ $ind->unit !== '%' ? $ind->unit : '' }}</div>
                    </div>
                    <div style="text-align: right;">
                        <div class="label-cap" style="font-size: 8.5px; margin-bottom: 2px;">Target</div>
                        <div class="serif" style="font-size: 28px; font-weight: 500; line-height: 1; color: var(--ink-3);">
                            {{ $ind->unit === '%' ? round($ind->target) . '%' : number_format((float)$ind->target) }}
                        </div>
                        <div style="font-size: 10.5px; color: {{ $ragColor }}; font-weight: 500; margin-top: 1px;">{{ $pct }}% achieved</div>
                    </div>
                </div>

                {{-- Progress bar --}}
                <div style="height: 5px; background: var(--rule-2); margin-bottom: 12px;">
                    <div style="height: 100%; width: {{ $pct }}%; background: {{ $ragBarColor }}; transition: width 400ms;"></div>
                </div>

                {{-- Source --}}
                <div style="font-size: 10.5px; color: var(--ink-4); border-top: 1px solid var(--rule-2); padding-top: 8px;">
                    <span style="font-weight: 600; color: var(--ink-3);">SOURCE</span>&ensp;{{ $ind->source_line ?? '—' }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    {{-- ═══ RAG Legend ═══ --}}
    <div style="padding: 14px 18px; background: var(--paper); border: 1px solid var(--rule); font-size: 11.5px; color: var(--ink-3); line-height: 1.55;">
        <strong style="color: var(--ink-2);">RAG logic:</strong>
        Green &ge; 90% of target achieved &middot; Amber 70&ndash;89.9% &middot; Red below 70% &middot; Grey = target or actual not yet entered. Inverse indicators (cost, SLA days) are calculated as target &divide; actual.
    </div>

</div>

<script>
function indFilter(priority, btn) {
    document.querySelectorAll('[data-ind-f]').forEach(function(b) {
        var active = b.dataset.indF === priority;
        b.style.background  = active ? 'var(--forest)' : 'transparent';
        b.style.color       = active ? 'var(--cream)'  : 'var(--ink-2)';
        b.style.borderColor = active ? 'var(--forest)' : 'var(--rule)';
        b.style.fontWeight  = active ? '600' : '500';
    });
    document.querySelectorAll('[data-ind-priority]').forEach(function(card) {
        card.style.display = (priority === 'all' || card.dataset.indPriority === priority) ? '' : 'none';
    });
}

function indFilterLevel(level) {
    document.querySelectorAll('[data-ind-level]').forEach(function(section) {
        section.style.display = (level === 'all' || section.dataset.indLevel === level) ? '' : 'none';
    });
}

// ── Indicator detail panel ──
var _indPanelData = @json($panelData);
var _indTrendChart = null;

function indOpenPanel(id) {
    var d = _indPanelData[id];
    if (!d) return;

    var ragColors = { green: '#4a7a5c', amber: '#b87319', red: '#8a2e1d', grey: '#6b6a65' };
    var ragLabels = { green: 'GREEN', amber: 'AMBER', red: 'RED', grey: 'GREY' };
    var rc = ragColors[d.rag] || ragColors.grey;

    // Header
    document.getElementById('ipCode').textContent = d.code;
    document.getElementById('ipPriority').textContent = d.priority;
    document.getElementById('ipCadence').textContent = d.cadence + ' · ' + d.level;
    document.getElementById('ipTitle').textContent = d.name;

    // Current
    var actualDisplay = d.unit === '%' ? Math.round(d.actual) + '%' : Number(d.actual).toLocaleString();
    var targetDisplay = d.unit === '%' ? Math.round(d.target) + '%' : Number(d.target).toLocaleString();
    document.getElementById('ipActual').textContent = actualDisplay;
    document.getElementById('ipActualUnit').textContent = d.unit !== '%' ? d.unit : '';
    document.getElementById('ipTarget').textContent = targetDisplay;
    document.getElementById('ipPct').textContent = d.pct + '% achieved';
    document.getElementById('ipPct').style.color = rc;
    document.getElementById('ipRagDot').style.background = rc;
    document.getElementById('ipBar').style.width = d.pct + '%';
    document.getElementById('ipBar').style.background = rc;

    // Trend chart
    var trendCanvas = document.getElementById('ipTrendCanvas');
    if (_indTrendChart) { _indTrendChart.destroy(); _indTrendChart = null; }
    var barColors = d.trend_labels.map(function(_, i) {
        return i === d.trend_labels.length - 1 ? rc : '#9a9892';
    });
    _indTrendChart = new Chart(trendCanvas, {
        type: 'bar',
        data: {
            labels: d.trend_labels,
            datasets: [{ data: d.trend_values, backgroundColor: barColors }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                annotation: { annotations: {
                    targetLine: { type: 'line', yMin: d.target, yMax: d.target, borderColor: rc, borderWidth: 1, borderDash: [4,3],
                        label: { display: true, content: 'TARGET ' + targetDisplay, position: 'end', font: { size: 9 }, color: rc, backgroundColor: 'transparent' }
                    }
                }}
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { beginAtZero: true, ticks: { font: { size: 10 }, maxTicksLimit: 5 } }
            }
        }
    });

    // Trend subtitle
    var trendChange = d.trend_values.length >= 2 ? (d.trend_values[d.trend_values.length - 1] - d.trend_values[0]) : 0;
    var sign = trendChange >= 0 ? '+' : '';
    document.getElementById('ipTrendChange').textContent = sign + Math.round(trendChange) + ' since ' + (d.trend_labels[0] || '');

    // Evidence
    var evBody = document.getElementById('ipEvidenceBody');
    if (d.evidence && d.evidence.length > 0) {
        document.getElementById('ipEvidenceCount').textContent = d.evidence.length + ' / ' + d.evidence.length + ' match the criterion';
        var html = '';
        d.evidence.forEach(function(e) {
            html += '<div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--rule-2);">';
            html += '<div><span class="mono" style="font-size:11px;color:var(--forest);font-weight:600;">' + e.uid + '</span>';
            html += '<div style="font-size:12px;color:var(--ink-2);margin-top:2px;">' + e.title + '</div>';
            html += '<div style="font-size:10.5px;color:var(--ink-4);">' + (e.issuer || '') + '</div></div>';
            html += e.verified ? '<span style="font-size:9px;padding:2px 7px;background:var(--moss-tint);color:var(--moss);font-weight:700;letter-spacing:0.04em;">VERIFIED</span>' : '';
            html += '</div>';
        });
        evBody.innerHTML = html;
    } else {
        document.getElementById('ipEvidenceCount').textContent = 'No linked evidence';
        evBody.innerHTML = '<div style="padding:16px 0;color:var(--ink-4);font-size:12px;text-align:center;">No evidence entries linked to this indicator.</div>';
    }

    // Source + Methodology
    document.getElementById('ipSource').textContent = d.source_line || '—';
    document.getElementById('ipMethodology').textContent = d.methodology || d.name;

    // Show panel
    document.getElementById('indPanel').style.transform = 'translateX(0)';
    document.body.style.overflow = 'hidden';
}

function indClosePanel() {
    document.getElementById('indPanel').style.transform = 'translateX(100%)';
    document.body.style.overflow = '';
    if (_indTrendChart) { _indTrendChart.destroy(); _indTrendChart = null; }
}
</script>

{{-- ═══ Indicator Detail Panel (slide-out) ═══ --}}
<div id="indPanel" onclick="if(event.target===this)indClosePanel()"
     style="position:fixed; top:0; right:0; bottom:0; left:0; z-index:1050; background:rgba(0,0,0,0.3); transition:transform 300ms ease; transform:translateX(100%);">
    <div style="position:absolute; top:0; right:0; bottom:0; width:480px; background:var(--parchment); box-shadow:-8px 0 32px rgba(0,0,0,.15); display:flex; flex-direction:column; overflow-y:auto;">

        {{-- Header --}}
        <div style="padding:20px 24px 16px; border-bottom:1px solid var(--rule); flex-shrink:0;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                <div style="display:flex; align-items:center; gap:6px;">
                    <span class="mono" style="font-size:14px; font-weight:700; color:var(--forest);" id="ipCode"></span>
                    <span style="font-size:9px; padding:1px 5px; font-weight:700; background:var(--paper); border:1px solid var(--rule); color:var(--ink-3);" id="ipPriority"></span>
                    <span style="font-size:10.5px; color:var(--ink-4);" id="ipCadence"></span>
                    <span style="font-size:9px; padding:1px 5px; background:rgba(74,122,92,0.1); color:var(--moss); font-weight:600;">&bull;LIVE&middot;CMS</span>
                </div>
                <button onclick="indClosePanel()" style="background:none; border:1px solid var(--rule); cursor:pointer; padding:5px 7px; color:var(--ink-3); border-radius:3px;">
                    <x-lucide-x style="width:14px;height:14px;" />
                </button>
            </div>
            <h2 class="serif" style="font-size:22px; font-weight:400; margin:0; line-height:1.25;" id="ipTitle"></h2>
        </div>

        {{-- Current --}}
        <div style="padding:20px 24px; border-bottom:1px solid var(--rule);">
            <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:8px;">
                <div>
                    <div class="label-cap" style="font-size:8.5px; margin-bottom:3px;">Current</div>
                    <div class="serif" style="font-size:40px; font-weight:500; line-height:1;" id="ipActual"></div>
                    <div style="font-size:10.5px; color:var(--ink-4); margin-top:2px;" id="ipActualUnit"></div>
                </div>
                <div style="text-align:right;">
                    <div class="label-cap" style="font-size:8.5px; margin-bottom:3px;">Target</div>
                    <div class="serif" style="font-size:40px; font-weight:500; line-height:1; color:var(--ink-3);" id="ipTarget"></div>
                    <div style="display:flex; align-items:center; gap:4px; justify-content:flex-end; margin-top:4px;">
                        <span id="ipRagDot" style="width:7px; height:7px; border-radius:50%; display:inline-block;"></span>
                        <span style="font-size:11px; font-weight:600;" id="ipPct"></span>
                    </div>
                </div>
            </div>
            <div style="height:6px; background:var(--rule-2);">
                <div id="ipBar" style="height:100%; transition:width 400ms;"></div>
            </div>
        </div>

        {{-- Trend --}}
        <div style="padding:20px 24px; border-bottom:1px solid var(--rule);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <div class="label-cap" style="font-size:9px;">Trend &middot; Last 6 Months</div>
                <span style="font-size:10.5px; color:var(--ink-3);" id="ipTrendChange"></span>
            </div>
            <div style="height:160px; position:relative;">
                <canvas id="ipTrendCanvas"></canvas>
            </div>
            <div style="display:flex; gap:14px; margin-top:8px; font-size:10px; color:var(--ink-4);">
                <span style="display:flex; align-items:center; gap:4px;"><span style="width:10px; height:10px; background:#9a9892; display:inline-block;"></span> Month-end snapshots</span>
                <span style="display:flex; align-items:center; gap:4px;"><span style="width:10px; height:10px; background:var(--burgundy); display:inline-block;"></span> Live · current month</span>
            </div>
        </div>

        {{-- Underlying records --}}
        <div style="padding:20px 24px; border-bottom:1px solid var(--rule);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div class="label-cap" style="font-size:9px;">Underlying Records</div>
                <span style="font-size:10.5px; color:var(--ink-4);" id="ipEvidenceCount"></span>
            </div>
            <div id="ipEvidenceBody"></div>
        </div>

        {{-- Methodology --}}
        <div style="padding:20px 24px; border-bottom:1px solid var(--rule);">
            <div class="label-cap" style="font-size:9px; margin-bottom:8px;">Methodology</div>
            <div style="font-size:12.5px; color:var(--ink-2); line-height:1.55; padding:12px 14px; background:var(--paper); border:1px solid var(--rule);" id="ipMethodology"></div>
        </div>

        {{-- Source --}}
        <div style="padding:16px 24px; border-bottom:1px solid var(--rule);">
            <div class="label-cap" style="font-size:9px; margin-bottom:4px;">Source</div>
            <div style="font-size:11.5px; color:var(--ink-3);" id="ipSource"></div>
        </div>

        {{-- Footer --}}
        <div style="padding:16px 24px; flex-shrink:0;">
            <a href="{{ route('evidence.index') }}" style="display:flex; align-items:center; justify-content:center; gap:8px; padding:12px 0; background:var(--forest); color:var(--cream); text-decoration:none; font-size:13px; font-weight:500;">
                <x-lucide-arrow-right style="width:13px;height:13px;" /> Open Evidence Register
            </a>
        </div>
    </div>
</div>
</x-layouts.app>
