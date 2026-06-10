<x-layouts.app>
@php
    $canWrite = auth()->user()->canWrite();
    $fy = 'FY ' . (now()->month >= 7 ? now()->year : now()->year - 1) . '/' . substr(now()->month >= 7 ? now()->year + 1 : now()->year, -2);
    $pillarDefs = [
        'economy' => [
            'num'         => 1,
            'name'        => 'Economy',
            'sub'         => 'Cost of inputs',
            'question'    => 'Are we paying the right price for the resources we use?',
            'measurement' => 'All-in costs include lawyer time, paralegal follow-up, hub overhead, and amortised fixed costs. Benchmarks are drawn from the private legal market in Sindh and from comparator NGO programmes.',
            'icon'        => 'landmark',
            'color'       => '#5c3d1e',
            'bg'          => 'rgba(92,61,30,0.05)',
            'metrics_key' => 'economyMetrics',
        ],
        'efficiency' => [
            'num'         => 2,
            'name'        => 'Efficiency',
            'sub'         => 'Outputs per input',
            'question'    => 'Are we converting our inputs into outputs at a healthy rate?',
            'measurement' => 'Output ratios are calculated quarterly and adjusted for case complexity. Time-to-resolution starts at intake, ends at signed agreement or final referral closure.',
            'icon'        => 'activity',
            'color'       => '#b87319',
            'bg'          => 'rgba(184,115,25,0.05)',
            'metrics_key' => 'efficiencyMetrics',
        ],
        'effectiveness' => [
            'num'         => 3,
            'name'        => 'Effectiveness',
            'sub'         => 'Outcomes achieved',
            'question'    => 'Are our outputs actually changing people\'s lives?',
            'measurement' => 'Outcomes are confirmed at first follow-up (3 days), second (7 days), and final closure (14 days). Client satisfaction comes from post-service surveys; the recurrence indicator is tracked for 12 months.',
            'icon'        => 'heart',
            'color'       => '#2d6a4f',
            'bg'          => 'rgba(45,106,79,0.05)',
            'metrics_key' => 'effectivenessMetrics',
        ],
        'equity' => [
            'num'         => 4,
            'name'        => 'Equity',
            'sub'         => 'Reach to the underserved',
            'question'    => 'Are we serving the people who most need us?',
            'measurement' => 'Reach metrics use self-identification on the intake form. Where clients decline to disclose, they are recorded as undisclosed and excluded from numerator and denominator.',
            'icon'        => 'scale',
            'color'       => '#8b1e1e',
            'bg'          => 'rgba(139,30,30,0.05)',
            'metrics_key' => 'equityMetrics',
        ],
    ];
    $metricStatusLabel = ['exceeds' => 'EXCEEDS TARGET', 'on' => 'ON TARGET', 'below' => 'BELOW TARGET', 'no_data' => 'NOT CONFIGURED'];
    $metricStatusColor = ['exceeds' => '#2d6a4f', 'on' => '#b87319', 'below' => '#8b1e1e', 'no_data' => '#888888'];
    $metricBorderColor = ['exceeds' => '#2d6a4f', 'on' => '#b87319', 'below' => '#8b1e1e', 'no_data' => '#cccccc'];
@endphp

<div style="padding: 24px 34px 64px; max-width: 1600px; margin: 0 auto;">

    {{-- ═══ Header ═══ --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; padding-bottom: 28px; border-bottom: 1px solid var(--rule);">
        <div style="max-width: 680px;">
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 8px; color: var(--ink-3);">Measurement · Adaptive Programming</div>
            <h1 class="serif" style="font-size: 38px; font-weight: 400; letter-spacing: -0.02em; margin: 0 0 12px;">
                Learning &amp; <em style="color: var(--ochre);">Value for Money</em>
            </h1>
            <p style="margin: 0; font-size: 13.5px; color: var(--ink-2); line-height: 1.65; max-width: 560px;">
                The Hub is not a static service — it is a learning organisation. Every quarter the team pauses to ask what worked, what didn't, and what the data is telling us we don't yet know. This is where those conversations are documented, and where the four-pillar value-for-money assessment lives.
            </p>
        </div>
        @if($canWrite)
        <div style="display: flex; gap: 8px; flex-shrink: 0; margin-top: 4px;">
            <a href="{{ route('impact.index') }}" class="btn-ghost" style="display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
                <x-lucide-file-text style="width:13px;height:13px;" /> Annual report
            </a>
            <button class="btn-primary" onclick="jhOpenModal('add-reflection')">
                <x-lucide-plus style="width:13px;height:13px;" /> Log reflection
            </button>
        </div>
        @endif
    </div>

    {{-- flash --}}
    @if(session('success'))
    <div style="background:rgba(74,122,92,0.1);border:1px solid rgba(74,122,92,0.3);border-radius:6px;padding:10px 16px;margin-bottom:20px;font-size:13px;color:var(--moss);">{{ session('success') }}</div>
    @endif

    {{-- ═══ SECTION I — The four Es ═══ --}}
    <div x-data="{ active: 'economy' }" style="margin-bottom: 40px;">

        {{-- Section header --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 16px;">
            <div>
                <div class="label-cap" style="font-size: 9px; color: var(--ochre); margin-bottom: 6px; letter-spacing: 0.12em;">Section I</div>
                <h2 class="serif" style="font-size: 26px; font-weight: 400; margin: 0 0 4px;">The four <em>Es</em> of value</h2>
                <p style="font-size: 12px; color: var(--ink-3); margin: 0;">Economy, Efficiency, Effectiveness, Equity — the framework against which donor and government value-for-money is assessed.</p>
            </div>
            <div class="mono label-cap" style="font-size: 10px; color: var(--ink-4); flex-shrink: 0; margin-left: 20px;">{{ $fy }}</div>
        </div>

        {{-- ── Pillar tabs ── --}}
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:0; background:var(--surface); border:1px solid var(--rule); border-radius:10px 10px 0 0; overflow:hidden;">
            @foreach($pillarDefs as $key => $pd)
            @php $isLast = $loop->last; @endphp
            <button type="button"
                @click="active = '{{ $key }}'"
                :style="active === '{{ $key }}'
                    ? 'border-top:3px solid {{ $pd['color'] }};background:{{ $pd['bg'] }};'
                    : 'border-top:3px solid transparent;background:var(--surface);'"
                style="text-align:left; padding:28px 26px 24px; border:none; {{ $isLast ? '' : 'border-right:1px solid var(--rule);' }} cursor:pointer; font-family:inherit; transition:background 0.15s;">

                {{-- Icon --}}
                <div style="margin-bottom:16px;">
                    <div :style="active === '{{ $key }}'
                                 ? 'background:{{ $pd['color'] }};color:#fff;'
                                 : 'background:var(--parchment);color:var(--ink-3);'"
                         style="width:34px;height:34px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;transition:all 0.15s;">
                        @if($pd['icon']==='landmark')   <x-lucide-landmark   style="width:15px;height:15px;" />
                        @elseif($pd['icon']==='activity') <x-lucide-activity style="width:15px;height:15px;" />
                        @elseif($pd['icon']==='heart')  <x-lucide-heart      style="width:15px;height:15px;" />
                        @elseif($pd['icon']==='scale')  <x-lucide-scale      style="width:15px;height:15px;" />
                        @endif
                    </div>
                </div>

                {{-- Name --}}
                <div class="serif"
                     :style="active==='{{ $key }}' ? 'color:{{ $pd['color'] }};' : 'color:var(--ink-2);'"
                     style="font-size:20px;font-weight:400;margin-bottom:5px;line-height:1.2;transition:color 0.15s;">
                    {{ $pd['name'] }}
                </div>
                <div :style="active==='{{ $key }}' ? 'color:{{ $pd['color'] }};opacity:0.65;' : 'color:var(--ink-4);'"
                     style="font-size:11px;transition:color 0.15s;">{{ $pd['sub'] }}</div>
            </button>
            @endforeach
        </div>

        {{-- ── Detail panels ── --}}
        @foreach($pillarDefs as $key => $pd)
        @php $mk = $pd['metrics_key']; $metrics = $$mk; @endphp
        <div x-show="active === '{{ $key }}'"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             style="border:1px solid var(--rule); border-top:none; border-radius:0 0 10px 10px; background:var(--surface); overflow:hidden;">

            {{-- Question + Measurement --}}
            <div style="padding:36px 40px 32px; background:{{ $pd['bg'] }};">
                <div class="label-cap" style="font-size:9px;color:{{ $pd['color'] }};letter-spacing:0.14em;margin-bottom:16px;opacity:0.75;">The question this pillar answers</div>
                <blockquote class="serif" style="font-size:26px;font-weight:400;color:{{ $pd['color'] }};margin:0 0 24px;line-height:1.4;font-style:italic;max-width:700px;">
                    "{{ $pd['question'] }}"
                </blockquote>
                <p style="font-size:12.5px;color:var(--ink-3);line-height:1.8;margin:0;max-width:680px;border-top:1px solid {{ $pd['color'] }}20;padding-top:18px;">
                    {{ $pd['measurement'] }}
                </p>
            </div>

            {{-- Finance inputs strip (Economy only) --}}
            @if($key === 'economy' && $canWrite)
            <div style="padding:12px 40px;background:var(--surface);border-top:1px solid var(--rule-2);border-bottom:1px solid var(--rule-2);display:flex;align-items:center;gap:12px;">
                <span class="label-cap" style="font-size:8.5px;color:{{ $pd['color'] }};letter-spacing:0.12em;flex-shrink:0;">Finance Inputs</span>
                <div style="width:1px;height:14px;background:var(--rule);flex-shrink:0;"></div>
                @if($financeConfig)
                <span style="font-size:11px;color:var(--ink-4);flex:1;">Last saved {{ $financeConfig->updated_at->format('d M Y') }} · <em>{{ $financeConfig->updated_by }}</em></span>
                @else
                <span style="font-size:11px;color:var(--ink-4);flex:1;">Not configured — hub costs, targets &amp; benchmarks editable</span>
                @endif
                <button onclick="jhOpenModal('finance-config')" style="background:none;border:1px solid var(--rule);border-radius:4px;padding:5px 12px;font-size:11px;color:var(--ink-2);cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:5px;flex-shrink:0;white-space:nowrap;">
                    <x-lucide-settings style="width:11px;height:11px;" /> Edit inputs
                </button>
            </div>
            @endif

            {{-- 2 × 2 Metric cards --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;padding:20px;gap:16px;background:var(--parchment);">
                @foreach($metrics as $idx => $m)
                @php
                    $sc = $metricStatusColor[$m['status']];
                    $sl = $metricStatusLabel[$m['status']];
                @endphp
                <div style="background:var(--surface);border-radius:8px;padding:24px 26px 20px;box-shadow:0 1px 4px rgba(0,0,0,0.06);border:1px solid var(--rule-2);{{ $m['status'] === 'no_data' ? 'opacity:0.55;' : '' }}">
                    {{-- Label + badge --}}
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:18px;">
                        <div style="font-size:12px;color:var(--ink-3);line-height:1.4;">{{ $m['label'] }}</div>
                        <span style="flex-shrink:0;font-size:8px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;color:{{ $sc }};padding:3px 8px;border-radius:20px;background:{{ $sc }}15;">{{ $sl }}</span>
                    </div>
                    @if($m['status'] === 'no_data')
                    {{-- No data state --}}
                    <div style="font-size:13px;color:var(--ink-4);font-style:italic;margin-bottom:20px;padding:16px 0;">
                        Configure finance inputs to see this metric.
                    </div>
                    @else
                    {{-- Value --}}
                    <div class="serif" style="font-size:38px;font-weight:400;color:{{ $pd['color'] }};line-height:1;letter-spacing:-0.02em;margin-bottom:6px;">{{ $m['value'] }}</div>
                    {{-- Target + delta --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                        <div style="font-size:11px;color:var(--ink-4);">{{ $m['targetLabel'] }}</div>
                        <div style="font-size:10.5px;color:var(--ink-3);padding:3px 10px;border-radius:20px;background:var(--parchment);border:1px solid var(--rule);white-space:nowrap;">{{ $m['delta'] }}</div>
                    </div>
                    @endif
                    {{-- Source --}}
                    <div style="border-top:1px solid var(--rule-2);padding-top:10px;display:flex;align-items:center;gap:5px;">
                        <span class="label-cap" style="font-size:8px;color:var(--ink-4);letter-spacing:0.1em;">SOURCE</span>
                        <span style="font-size:10px;color:var(--ink-4);">{{ $m['source'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    {{-- ═══ SECTION II — Costs & Trend ═══ --}}
    <div style="margin-bottom: 40px;">
        <div style="margin-bottom: 16px;">
            <div class="label-cap" style="font-size: 9px; color: var(--ochre); margin-bottom: 6px; letter-spacing: 0.12em;">Section II</div>
            <h2 class="serif" style="font-size: 26px; font-weight: 400; margin: 0 0 4px;">Cost performance</h2>
            <p style="font-size: 12px; color: var(--ink-3); margin: 0;">Historical cost-per-case trend and current hub operating costs.</p>
        </div>

        <div style="display: grid; grid-template-columns: 3fr 2fr; gap: 16px;">
            <div class="card" style="padding: 20px;">
                <div class="label-cap" style="font-size: 10px; color: var(--ink-3); margin-bottom: 14px;">Cost per Case — Historical Trend</div>
                <div x-data="costTrendChart({{ $historyJson }})" x-init="init()" style="height: 190px;">
                    <canvas x-ref="chart"></canvas>
                </div>
            </div>
            <div class="card" style="padding: 20px;">
                <div class="label-cap" style="font-size: 10px; color: var(--ink-3); margin-bottom: 14px;">Hub Operating Costs</div>
                @forelse($hubCosts as $hc)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 9px 0; border-bottom: 1px solid var(--rule-2);">
                    <div>
                        <div style="font-size: 12px; font-weight: 500; color: var(--ink-1);">{{ $hc->hub?->name ?? $hc->hub_id }}</div>
                        <div style="font-size: 10px; color: var(--ink-4); margin-top: 1px;">{{ $hc->quarter }}</div>
                    </div>
                    <div style="text-align: right;">
                        <div class="mono" style="font-size: 12px; font-weight: 600; color: var(--forest);">PKR {{ number_format($hc->total_operational_cost) }}</div>
                        <div style="font-size: 10px; color: var(--ink-3);">PKR {{ number_format($hc->cost_per_case) }}/case</div>
                    </div>
                </div>
                @empty
                <div style="font-size: 12px; color: var(--ink-4); padding: 12px 0;">No hub cost data recorded yet.</div>
                @endforelse
                <div style="display: flex; justify-content: space-between; padding: 12px 0 0; margin-top: 4px;">
                    <div style="font-size: 12px; font-weight: 600; color: var(--ink-2);">Annualised Total</div>
                    <div class="mono" style="font-size: 13px; font-weight: 700; color: var(--forest);">PKR {{ number_format($totalCost) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ SECTION III — Reflections & Case Studies ═══ --}}
    <div>
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 16px;">
            <div>
                <div class="label-cap" style="font-size: 9px; color: var(--ochre); margin-bottom: 6px; letter-spacing: 0.12em;">Section III</div>
                <h2 class="serif" style="font-size: 26px; font-weight: 400; margin: 0 0 4px;">Learning record</h2>
                <p style="font-size: 12px; color: var(--ink-3); margin: 0;">{{ $reflections->count() }} reflections · {{ $caseStudies->count() }} case studies</p>
            </div>
            @if($canWrite)
            <div style="display: flex; gap: 8px;">
                <button class="btn-ghost" style="font-size: 12px;" onclick="jhOpenModal('add-reflection')">
                    <x-lucide-plus style="width:12px;height:12px;" /> Reflection
                </button>
                <button class="btn-ghost" style="font-size: 12px;" onclick="jhOpenModal('add-case-study')">
                    <x-lucide-plus style="width:12px;height:12px;" /> Case Study
                </button>
                @if(auth()->user()->can('settings.view'))
                <button class="btn-ghost" style="font-size: 12px;" onclick="jhOpenModal('finance-config')">
                    <x-lucide-settings style="width:12px;height:12px;" /> Finance Config
                </button>
                @endif
            </div>
            @endif
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">

            {{-- Reflections --}}
            <div>
                <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ink-3); margin-bottom: 10px;">
                    Learning Reflections
                </div>
                @forelse($reflections as $ref)
                <div class="card" style="padding: 16px; margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                        <div style="font-size: 13px; font-weight: 600; color: var(--ink-1);">{{ $ref->title }}</div>
                        <span style="font-size: 10px; color: var(--ink-4); white-space: nowrap; margin-left: 8px;" class="mono">{{ \Carbon\Carbon::parse($ref->date)->format('M d, Y') }}</span>
                    </div>
                    @if($ref->staff)
                    <div style="font-size: 11px; color: var(--ink-3); margin-bottom: 6px;">
                        <x-lucide-users style="width:10px;height:10px;display:inline;vertical-align:-1px;" /> {{ $ref->staff }}
                    </div>
                    @endif
                    <p style="font-size: 12px; color: var(--ink-2); margin: 0 0 8px;">{{ Str::limit($ref->description, 120) }}</p>
                    @if($ref->key_learning)
                    <div style="background: rgba(74,122,92,0.07); border-left: 3px solid var(--moss); padding: 8px 10px; border-radius: 0 4px 4px 0; font-size: 11px; color: var(--ink-2);">
                        <span style="font-weight: 600; color: var(--moss);">Key Learning:</span> {{ Str::limit($ref->key_learning, 100) }}
                    </div>
                    @endif
                    @if(isset($ref->meta['status']))
                    <div style="margin-top: 8px;">
                        <span style="font-size: 9px; padding: 2px 7px; border-radius: 10px; text-transform: uppercase; letter-spacing: 0.04em; font-weight: 600; background: rgba(184,115,25,0.1); color: var(--ochre);">
                            {{ $ref->meta['status'] }}
                        </span>
                    </div>
                    @endif
                </div>
                @empty
                <x-empty-state icon="lightbulb" message="No reflections recorded yet." />
                @endforelse
            </div>

            {{-- Case Studies --}}
            <div>
                <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: var(--ink-3); margin-bottom: 10px;">
                    Case Studies
                </div>
                @forelse($caseStudies as $cs)
                @php
                    $repColor = match($cs->replication_potential ?? 'medium') {
                        'high'   => 'var(--moss)',
                        'medium' => 'var(--ochre)',
                        'low'    => 'var(--burgundy)',
                        default  => 'var(--ink-3)',
                    };
                @endphp
                <div class="card" style="padding: 16px; margin-bottom: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <div style="font-size: 13px; font-weight: 600; color: var(--ink-1);">{{ $cs->title }}</div>
                        <span style="font-size: 9px; padding: 2px 7px; border-radius: 10px; font-weight: 600; letter-spacing: 0.03em; text-transform: uppercase; background: rgba(0,0,0,0.05); color: {{ $repColor }}; margin-left: 8px; white-space: nowrap;">
                            {{ ucfirst($cs->replication_potential ?? 'medium') }} replication
                        </span>
                    </div>
                    <p style="font-size: 12px; color: var(--ink-2); margin: 0 0 8px;">{{ Str::limit($cs->narrative, 120) }}</p>
                    @if($cs->impact_statement)
                    <div style="background: rgba(74,122,92,0.07); border-left: 3px solid var(--moss); padding: 8px 10px; border-radius: 0 4px 4px 0; font-size: 11px; color: var(--ink-2);">
                        <span style="font-weight: 600; color: var(--moss);">Impact:</span> {{ Str::limit($cs->impact_statement, 100) }}
                    </div>
                    @endif
                </div>
                @empty
                <x-empty-state icon="book-open" message="No case studies documented yet." />
                @endforelse
            </div>

        </div>
    </div>
</div>

{{-- ═══ Modals ═══ --}}
@if($canWrite)
<x-jh-modal name="add-reflection" max-width="580px" :no-padding="true">
    @php
        $qNum = ceil(now()->month / 3);
        $defaultQuarter = "Q{$qNum} " . now()->year;
    @endphp
    {{-- Custom header --}}
    <div style="padding: 22px 26px 16px; border-bottom: 1px solid var(--rule); flex-shrink: 0; display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <div class="label-cap" style="font-size: 9px; color: var(--ink-4); margin-bottom: 8px; letter-spacing: 0.1em;">Learning Log</div>
            <h2 class="serif" style="font-size: 26px; font-weight: 400; margin: 0 0 6px; line-height: 1.2;">
                Log a quarterly <em style="color: var(--ochre);">reflection</em>
            </h2>
            <p style="font-size: 12px; color: var(--ink-3); margin: 0; line-height: 1.5;">
                Capture the question the team sat with, what you saw in the data, and what you decided to do about it.
            </p>
        </div>
        <button type="button" data-bs-dismiss="modal"
            style="background:none; border:1px solid var(--rule); cursor:pointer; padding:5px 7px; color:var(--ink-3); border-radius:3px; flex-shrink:0; margin-left:12px; line-height:1;">
            <x-lucide-x style="width:14px;height:14px;" />
        </button>
    </div>

    <form method="POST" action="{{ route('learning.reflection') }}" x-data="{ status: 'in-progress' }">
        @csrf
        <div style="padding: 20px 26px; display: flex; flex-direction: column; gap: 14px; max-height: 62vh; overflow-y: auto;">

            {{-- Quarter + Date --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div>
                    <label class="label-cap" style="font-size: 9px; display:block; margin-bottom:5px; color:var(--ink-3);">Quarter</label>
                    <input type="text" name="quarter" value="{{ $defaultQuarter }}"
                        style="width:100%; padding:9px 11px; border:1px solid var(--rule); background:var(--surface); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:3px;">
                </div>
                <div>
                    <label class="label-cap" style="font-size:9px; display:block; margin-bottom:5px; color:var(--ink-3);">Date Held</label>
                    <input type="date" name="date" required value="{{ now()->format('Y-m-d') }}"
                        style="width:100%; padding:9px 11px; border:1px solid var(--rule); background:var(--surface); color:var(--ink); font-size:13px; box-sizing:border-box; border-radius:3px;">
                </div>
            </div>

            {{-- Hub Scope + Location --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div>
                    <label class="label-cap" style="font-size:9px; display:block; margin-bottom:5px; color:var(--ink-3);">Hub Scope</label>
                    <select name="hub_scope"
                        style="width:100%; padding:9px 11px; border:1px solid var(--rule); background:var(--surface); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:3px; appearance:auto;">
                        <option value="all">All hubs (provincial)</option>
                        @foreach($hubs as $hub)
                        <option value="{{ $hub->id }}" {{ (session('active_hub') === $hub->id) ? 'selected' : '' }}>{{ $hub->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label-cap" style="font-size:9px; display:block; margin-bottom:5px; color:var(--ink-3);">Location <span style="color:var(--burgundy);">*</span></label>
                    <input type="text" name="location" placeholder="Karachi (provincial review)"
                        style="width:100%; padding:9px 11px; border:1px solid var(--rule); background:var(--surface); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:3px;">
                </div>
            </div>

            {{-- The Question --}}
            <div>
                <label class="label-cap" style="font-size:9px; display:block; margin-bottom:5px; color:var(--ink-3);">The Question We Sat With <span style="color:var(--burgundy);">*</span></label>
                <input type="text" name="title" required placeholder="What's slowing our court referrals down?"
                    style="width:100%; padding:9px 11px; border:1px solid var(--rule); background:var(--surface); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:3px;">
                <p style="font-size:10px; color:var(--ink-4); margin:4px 0 0;">State the question as the team posed it. Quotes will be added in display.</p>
            </div>

            {{-- Attendees --}}
            <div>
                <label class="label-cap" style="font-size:9px; display:block; margin-bottom:5px; color:var(--ink-3);">Attendees</label>
                <input type="text" name="attendees" placeholder="14 staff · hub coordinator · 2 specialists" value="{{ auth()->user()->name }}"
                    style="width:100%; padding:9px 11px; border:1px solid var(--rule); background:var(--surface); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:3px;">
            </div>

            {{-- Insight --}}
            <div>
                <label class="label-cap" style="font-size:9px; display:block; margin-bottom:5px; color:var(--ink-3);">Insight <span style="color:var(--burgundy);">*</span></label>
                <textarea name="description" required rows="3" placeholder="What did the data, the cases, or the team's experience tell us?"
                    style="width:100%; padding:9px 11px; border:1px solid var(--rule); background:var(--surface); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:3px; resize:vertical;"></textarea>
            </div>

            {{-- Decision Taken --}}
            <div>
                <label class="label-cap" style="font-size:9px; display:block; margin-bottom:5px; color:var(--ink-3);">Decision Taken <span style="color:var(--burgundy);">*</span></label>
                <textarea name="key_learning" required rows="3" placeholder="What did we decide to change? Be specific about who does what by when."
                    style="width:100%; padding:9px 11px; border:1px solid var(--rule); background:var(--surface); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:3px; resize:vertical;"></textarea>
            </div>

            {{-- Status toggle --}}
            <div>
                <label class="label-cap" style="font-size:9px; display:block; margin-bottom:8px; color:var(--ink-3);">Status</label>
                <input type="hidden" name="status" :value="status">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <button type="button" @click="status = 'in-progress'"
                        :style="status === 'in-progress' ? 'background:var(--forest);color:#fff;border-color:var(--forest);' : 'background:var(--surface);color:var(--ink-2);border-color:var(--rule);'"
                        style="padding:12px 14px; border:1px solid; border-radius:4px; cursor:pointer; text-align:left; font-family:inherit; transition: all 0.12s;">
                        <div style="font-size:13px; font-weight:600; margin-bottom:3px;">In progress</div>
                        <div style="font-size:10px; opacity:0.75;">Decision agreed; verification pending</div>
                    </button>
                    <button type="button" @click="status = 'completed'"
                        :style="status === 'completed' ? 'background:var(--forest);color:#fff;border-color:var(--forest);' : 'background:var(--surface);color:var(--ink-2);border-color:var(--rule);'"
                        style="padding:12px 14px; border:1px solid; border-radius:4px; cursor:pointer; text-align:left; font-family:inherit; transition: all 0.12s;">
                        <div style="font-size:13px; font-weight:600; margin-bottom:3px;">Completed</div>
                        <div style="font-size:10px; opacity:0.75;">Decision implemented; outcome known</div>
                    </button>
                </div>
            </div>

            {{-- Follow-up (in-progress only) --}}
            <div x-show="status === 'in-progress'" x-transition>
                <label class="label-cap" style="font-size:9px; display:block; margin-bottom:5px; color:var(--ink-3);">Follow-up — When Do We Verify? <span style="color:var(--burgundy);">*</span></label>
                <input type="text" name="follow_up" :required="status === 'in-progress'" placeholder="Review at Q2 2026 reflection"
                    style="width:100%; padding:9px 11px; border:1px solid var(--rule); background:var(--surface); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:3px;">
            </div>

            {{-- Outcome (completed only) --}}
            <div x-show="status === 'completed'" x-transition>
                <label class="label-cap" style="font-size:9px; display:block; margin-bottom:5px; color:var(--ink-3);">Outcome — What Actually Happened? <span style="color:var(--burgundy);">*</span></label>
                <textarea name="outcome" :required="status === 'completed'" rows="3"
                    placeholder="Result observed since the decision was implemented. Numbers where possible."
                    style="width:100%; padding:9px 11px; border:1px solid var(--rule); background:var(--surface); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:3px; resize:vertical;"></textarea>
            </div>

        </div>

        {{-- Footer --}}
        <div style="padding: 14px 26px; border-top: 1px solid var(--rule); display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size:10px; color:var(--ink-4);">* required</span>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-primary">
                    <x-lucide-plus style="width:12px;height:12px;" /> Log reflection
                </button>
            </div>
        </div>
    </form>
</x-jh-modal>

<x-jh-modal name="add-case-study" title="Document Case Study" max-width="580px">
    <form method="POST" action="{{ route('learning.case-study') }}">
        @csrf
        <div style="margin-bottom: 14px;">
            <label style="display: block; font-size: 11px; font-weight: 600; color: var(--ink-2); margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.05em;">Linked Case (optional)</label>
            <select name="case_id" style="width: 100%; padding: 8px 10px; border: 1px solid var(--rule); border-radius: 6px; font-size: 13px; background: var(--surface); color: var(--ink-1);">
                <option value="">— No specific case —</option>
                @foreach($casesForSelect as $case)
                <option value="{{ $case->id }}">{{ $case->case_uid }} · {{ $case->name }}</option>
                @endforeach
            </select>
        </div>
        <x-form-input name="title" label="Case Study Title" required />
        <div style="margin-top: 14px;">
            <x-form-input name="narrative" label="Narrative (what happened?)" type="textarea" required />
        </div>
        <div style="margin-top: 14px;">
            <x-form-input name="impact_statement" label="Impact Statement" type="textarea" required placeholder="What changed for the client/community?" />
        </div>
        <div style="margin-top: 14px;">
            <x-form-input name="lessons_learned" label="Lessons Learned (optional)" type="textarea" />
        </div>
        <div style="margin-top: 14px;">
            <label style="display: block; font-size: 11px; font-weight: 600; color: var(--ink-2); margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.05em;">Replication Potential</label>
            <select name="replication_potential" style="width: 100%; padding: 8px 10px; border: 1px solid var(--rule); border-radius: 6px; font-size: 13px; background: var(--surface); color: var(--ink-1);">
                <option value="high">High</option>
                <option value="medium" selected>Medium</option>
                <option value="low">Low</option>
            </select>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
            <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn-primary">
                <x-lucide-check style="width:12px;height:12px;" /> Save Case Study
            </button>
        </div>
    </form>
</x-jh-modal>

@if(auth()->user()->can('settings.view'))
@php
    $fc       = $financeConfig;
    $fcConfig = $fc ? ($fc->config ?? []) : [];
    $fcTargets= $fcConfig['targets'] ?? [];
    $fcProj   = $fcConfig['projections'] ?? [];
    $fcHist   = $fcConfig['history'] ?? [];
    $hubCostMap = $hubCosts->keyBy('hub_id');
    $histJson = json_encode(array_values($fcHist));
    $hubCostJson = json_encode($hubCosts->mapWithKeys(fn($h) => [$h->hub_id => (int)$h->total_operational_cost])->toArray());
@endphp
<x-jh-modal name="finance-config" max-width="680px" :no-padding="true">
    {{-- Header --}}
    <div style="padding: 18px 24px 14px; border-bottom: 1px solid var(--rule); flex-shrink: 0; display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <div class="label-cap" style="font-size: 9px; color: var(--ink-4); margin-bottom: 6px;">Economy Pillar · Finance Inputs</div>
            <h2 class="serif" style="font-size: 24px; font-weight: 400; margin: 0 0 6px;">Edit <em style="color: var(--ochre);">finance &amp; targets</em></h2>
            <p style="font-size: 12px; color: var(--ink-3); margin: 0; line-height: 1.5;">
                These figures drive every metric in the Economy pillar and the cost-per-case trend chart.
                @if($fc) Last saved {{ $fc->updated_at->format('Y-m-d') }} by <em>{{ $fc->updated_by }}</em>.@endif
            </p>
        </div>
        <button type="button" data-bs-dismiss="modal" style="background:none;border:1px solid var(--rule);cursor:pointer;padding:5px 7px;color:var(--ink-3);border-radius:3px;flex-shrink:0;margin-left:12px;line-height:1;">
            <x-lucide-x style="width:14px;height:14px;" />
        </button>
    </div>

    <form method="POST" action="{{ route('learning.finance-inputs') }}"
          x-data="financeModal({{ $hubCostJson }}, {{ $histJson }})"
          @submit.prevent="$el.submit()">
        @csrf

        {{-- Scrollable body --}}
        <div style="overflow-y: auto; max-height: 62vh; padding: 0;">

            {{-- §1 Hub operating costs --}}
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--rule);">
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                    <h3 style="font-size: 15px; font-weight: 600; color: var(--ink-1); margin: 0;">1 · Hub operating costs</h3>
                    <span class="mono" style="font-size: 10px; color: var(--ink-4);">{{ $fy }}</span>
                </div>
                <p style="font-size: 11px; color: var(--ochre); margin: 0 0 14px;">Annual all-in cost per hub in PKR. Includes lawyer time, paralegal follow-up, hub overhead, and amortised fixed costs.</p>
                @foreach($hubs as $hub)
                <div style="display: flex; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--rule-2);">
                    <div style="flex: 1; font-size: 13px; color: var(--ink-1);">
                        {{ $hub->name }}
                        <span class="mono" style="font-size: 9px; color: var(--ink-4); margin-left: 6px;">{{ $hub->id }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; color: var(--ink-3);">PKR</span>
                        <input type="number" name="hub_cost[{{ $hub->id }}]" min="0" step="1000"
                               x-model.number="hubCosts['{{ $hub->id }}']"
                               value="{{ (int)($hubCostMap[$hub->id]->total_operational_cost ?? 0) }}"
                               style="width:130px; padding:7px 10px; border:1px solid var(--rule); background:var(--surface); color:var(--ink); font-size:13px; text-align:right; border-radius:3px; font-family:var(--font-mono,monospace);">
                    </div>
                </div>
                @endforeach
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 10px; margin-top: 4px;">
                    <span style="font-size: 12px; color: var(--ink-3);">Total annual operating cost</span>
                    <span class="serif mono" style="font-size: 16px; font-weight: 600; color: var(--ink-1);">
                        PKR <span x-text="totalCost.toLocaleString()">0</span>
                    </span>
                </div>
            </div>

            {{-- §2 Budget allocations --}}
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--rule);">
                <h3 style="font-size: 15px; font-weight: 600; color: var(--ink-1); margin: 0 0 6px;">2 · Budget allocations</h3>
                <p style="font-size: 11px; color: var(--ochre); margin: 0 0 14px;">How the total budget is split across functions. Drives the "operating overhead share" metric and the cost-per-outreach-session derivation.</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div>
                        <label class="label-cap" style="font-size:9px;display:block;margin-bottom:5px;color:var(--ink-3);">Operating Overhead Share</label>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <input type="number" name="overhead_pct" min="0" max="100" step="0.1"
                                   value="{{ $fcConfig['overheadPct'] ?? 17 }}"
                                   style="flex:1;padding:9px 11px;border:1px solid var(--rule);background:var(--surface);color:var(--ink);font-size:13px;text-align:right;border-radius:3px;font-family:inherit;">
                            <span style="font-size:13px;color:var(--ink-3);">%</span>
                        </div>
                        <p style="font-size:10px;color:var(--ink-4);margin:4px 0 0;">Admin, finance, M&amp;E, leadership.</p>
                    </div>
                    <div>
                        <label class="label-cap" style="font-size:9px;display:block;margin-bottom:5px;color:var(--ink-3);">Outreach Allocation</label>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <input type="number" name="outreach_allocation" min="0" max="100" step="0.1"
                                   value="{{ $fcConfig['outreachAllocationPct'] ?? 8 }}"
                                   style="flex:1;padding:9px 11px;border:1px solid var(--rule);background:var(--surface);color:var(--ink);font-size:13px;text-align:right;border-radius:3px;font-family:inherit;">
                            <span style="font-size:13px;color:var(--ink-3);">%</span>
                        </div>
                        <p style="font-size:10px;color:var(--ink-4);margin:4px 0 0;">Community sessions, awareness, mobile clinics.</p>
                    </div>
                </div>
            </div>

            {{-- §3 Targets & benchmarks --}}
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--rule);">
                <h3 style="font-size: 15px; font-weight: 600; color: var(--ink-1); margin: 0 0 6px;">3 · Targets &amp; benchmarks</h3>
                <p style="font-size: 11px; color: var(--ochre); margin: 0 0 14px;">Threshold values used to decide whether each KPI shows as <em>Below target</em>, <em>On target</em>, or <em>Exceeds target</em>.</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div>
                        <label class="label-cap" style="font-size:9px;display:block;margin-bottom:5px;color:var(--ink-3);">Cost per Individual Served</label>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span style="font-size:11px;color:var(--ink-3);">PKR</span>
                            <input type="number" name="target_cost_individual" min="0"
                                   value="{{ $fcTargets['costPerIndividual'] ?? 1400 }}"
                                   style="flex:1;padding:9px 11px;border:1px solid var(--rule);background:var(--surface);color:var(--ink);font-size:13px;text-align:right;border-radius:3px;font-family:inherit;">
                        </div>
                    </div>
                    <div>
                        <label class="label-cap" style="font-size:9px;display:block;margin-bottom:5px;color:var(--ink-3);">Cost per Case (All-in)</label>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span style="font-size:11px;color:var(--ink-3);">PKR</span>
                            <input type="number" name="target_cost_case" min="0"
                                   value="{{ $fcTargets['costPerCase'] ?? 30000 }}"
                                   style="flex:1;padding:9px 11px;border:1px solid var(--rule);background:var(--surface);color:var(--ink);font-size:13px;text-align:right;border-radius:3px;font-family:inherit;">
                        </div>
                    </div>
                    <div>
                        <label class="label-cap" style="font-size:9px;display:block;margin-bottom:5px;color:var(--ink-3);">Operating Overhead Ceiling</label>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <input type="number" name="target_overhead_ceiling" min="0" max="100"
                                   value="{{ $fcTargets['overheadCeiling'] ?? 20 }}"
                                   style="flex:1;padding:9px 11px;border:1px solid var(--rule);background:var(--surface);color:var(--ink);font-size:13px;text-align:right;border-radius:3px;font-family:inherit;">
                            <span style="font-size:13px;color:var(--ink-3);">%</span>
                        </div>
                    </div>
                    <div>
                        <label class="label-cap" style="font-size:9px;display:block;margin-bottom:5px;color:var(--ink-3);">Cost per Outreach Session</label>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span style="font-size:11px;color:var(--ink-3);">PKR</span>
                            <input type="number" name="target_cost_outreach" min="0"
                                   value="{{ $fcTargets['costPerOutreach'] ?? 10000 }}"
                                   style="flex:1;padding:9px 11px;border:1px solid var(--rule);background:var(--surface);color:var(--ink);font-size:13px;text-align:right;border-radius:3px;font-family:inherit;">
                        </div>
                    </div>
                </div>
            </div>

            {{-- §4 Projection multipliers --}}
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--rule);">
                <h3 style="font-size: 15px; font-weight: 600; color: var(--ink-1); margin: 0 0 6px;">4 · Projection multipliers</h3>
                <p style="font-size: 11px; color: var(--ochre); margin: 0 0 14px;">Scale the live demo dataset to annual figures. Set to <strong>1</strong> once real annual numbers are available — at that point the metrics will reflect actuals rather than projections.</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                    <div>
                        <label class="label-cap" style="font-size:9px;display:block;margin-bottom:5px;color:var(--ink-3);">Cases → Projected Reach</label>
                        <input type="number" name="reach_per_case" min="1"
                               value="{{ $fcProj['reachPerCase'] ?? 200 }}"
                               style="width:100%;padding:9px 11px;border:1px solid var(--rule);background:var(--surface);color:var(--ink);font-size:13px;text-align:right;border-radius:3px;box-sizing:border-box;font-family:inherit;">
                        <p style="font-size:10px;color:var(--ink-4);margin:4px 0 0;">Each case represents this many individuals reached (family + community).</p>
                    </div>
                    <div>
                        <label class="label-cap" style="font-size:9px;display:block;margin-bottom:5px;color:var(--ink-3);">Cases → Annual Cases</label>
                        <input type="number" name="annual_cases" min="1"
                               value="{{ $fcProj['annualCases'] ?? 30 }}"
                               style="width:100%;padding:9px 11px;border:1px solid var(--rule);background:var(--surface);color:var(--ink);font-size:13px;text-align:right;border-radius:3px;box-sizing:border-box;font-family:inherit;">
                        <p style="font-size:10px;color:var(--ink-4);margin:4px 0 0;">Multiply current case count to estimate annual caseload.</p>
                    </div>
                    <div>
                        <label class="label-cap" style="font-size:9px;display:block;margin-bottom:5px;color:var(--ink-3);">Sessions → Annual Sessions</label>
                        <input type="number" name="annual_sessions" min="1"
                               value="{{ $fcProj['annualSessions'] ?? 30 }}"
                               style="width:100%;padding:9px 11px;border:1px solid var(--rule);background:var(--surface);color:var(--ink);font-size:13px;text-align:right;border-radius:3px;box-sizing:border-box;font-family:inherit;">
                        <p style="font-size:10px;color:var(--ink-4);margin:4px 0 0;">Outreach session scale-up to annual figure.</p>
                    </div>
                </div>
            </div>

            {{-- §5 Historical cost per case --}}
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--rule);">
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                    <h3 style="font-size: 15px; font-weight: 600; color: var(--ink-1); margin: 0;">5 · Historical cost per case</h3>
                    <button type="button" @click="addYear()"
                            style="font-size:11px;padding:4px 10px;border:1px solid var(--rule);background:var(--surface);cursor:pointer;border-radius:3px;color:var(--ink-2);font-family:inherit;">
                        + Add prior year
                    </button>
                </div>
                <p style="font-size: 11px; color: var(--ochre); margin: 0 0 14px;">Prior fiscal years shown as bars on the "Cost per case, over time" chart. The current year is computed live from the inputs above.</p>
                <div style="display: grid; grid-template-columns: 110px 1fr 32px; gap: 8px; align-items: center; margin-bottom: 6px;">
                    <span class="label-cap" style="font-size:9px;color:var(--ink-4);">Period</span>
                    <span class="label-cap" style="font-size:9px;color:var(--ink-4);">Cost per Case (PKR)</span>
                    <span></span>
                </div>
                <template x-for="(row, i) in history" :key="i">
                    <div style="display: grid; grid-template-columns: 110px 1fr 32px; gap: 8px; align-items: center; margin-bottom: 8px;">
                        <input type="text" :name="'history_period[' + i + ']'" x-model="row.period"
                               style="padding:8px 10px;border:1px solid var(--rule);background:var(--surface);color:var(--ink);font-size:13px;border-radius:3px;font-family:inherit;">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span style="font-size:11px;color:var(--ink-3);">PKR</span>
                            <input type="number" :name="'history_cost[' + i + ']'" x-model.number="row.cost" min="0"
                                   style="flex:1;padding:8px 10px;border:1px solid var(--rule);background:var(--surface);color:var(--ink);font-size:13px;text-align:right;border-radius:3px;font-family:inherit;">
                        </div>
                        <button type="button" @click="removeYear(i)"
                                style="background:none;border:1px solid var(--rule);border-radius:3px;cursor:pointer;padding:5px;color:var(--ink-4);line-height:1;">
                            <x-lucide-x style="width:12px;height:12px;" />
                        </button>
                    </div>
                </template>
            </div>

            {{-- §6 Audit trail --}}
            <div style="padding: 20px 24px;">
                <h3 style="font-size: 15px; font-weight: 600; color: var(--ink-1); margin: 0 0 6px;">6 · Audit trail</h3>
                <p style="font-size: 11px; color: var(--ochre); margin: 0 0 14px;">These edits will be timestamped and attributed when saved.</p>
                <label class="label-cap" style="font-size:9px;display:block;margin-bottom:5px;color:var(--ink-3);">Submitted By</label>
                <input type="text" name="submitted_by" required
                       value="{{ auth()->user()->name }}"
                       placeholder="Name and role — e.g. F. Khaskheli (Finance Lead)"
                       style="width:100%;padding:9px 11px;border:1px solid var(--rule);background:var(--surface);color:var(--ink);font-size:13px;font-family:inherit;box-sizing:border-box;border-radius:3px;">
            </div>
        </div>

        {{-- Sticky footer --}}
        <div style="padding: 12px 24px; border-top: 1px solid var(--rule); display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; background: var(--paper);">
            <div style="font-size: 11px; color: var(--ink-3);">
                Total annual cost <strong class="mono">PKR <span x-text="totalCost.toLocaleString()">0</span></strong>
                · <span x-text="history.length"></span> historical periods
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn-ghost" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-primary" style="display:inline-flex;align-items:center;gap:6px;">
                    <x-lucide-save style="width:12px;height:12px;" /> Save changes
                </button>
            </div>
        </div>
    </form>
</x-jh-modal>
@endif
@endif

<script>
function financeModal(hubCosts, history) {
    return {
        hubCosts: hubCosts || {},
        history: history || [],
        get totalCost() {
            return Object.values(this.hubCosts).reduce((s, v) => s + (parseFloat(v) || 0), 0);
        },
        addYear() {
            const lastYear = this.history.length ? parseInt(this.history[this.history.length - 1].period) - 1 : new Date().getFullYear() - 1;
            this.history.push({ period: String(lastYear), cost: '' });
        },
        removeYear(i) { this.history.splice(i, 1); }
    };
}

function costTrendChart(data) {
    return {
        init() {
            if (!data.labels || !data.labels.length) return;
            new Chart(this.$refs.chart, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Cost per Case (PKR)',
                        data: data.values,
                        borderColor: '#2d6a4f',
                        backgroundColor: 'rgba(45,106,79,0.08)',
                        borderWidth: 2,
                        pointRadius: 3,
                        tension: 0.3,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            ticks: { font: { size: 10 }, callback: v => 'PKR ' + (v/1000).toFixed(0) + 'k' },
                            grid: { color: 'rgba(0,0,0,0.05)' },
                        },
                        x: { ticks: { font: { size: 10 } }, grid: { display: false } },
                    }
                }
            });
        }
    };
}

</script>
</x-layouts.app>
