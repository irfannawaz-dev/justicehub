<x-layouts.app>
@php
    $stageConfig = [
        'Intake'          => ['dot' => '#8a8a84', 'color' => 'var(--ink-3)',    'subtitle' => 'Registered · awaiting referral action',      'icon' => 'inbox'],
        'Complaint Filed' => ['dot' => '#b87319', 'color' => 'var(--ochre)',    'subtitle' => 'Complaint filed or not filed · in progress',  'icon' => 'file-check'],
        'Closed'          => ['dot' => '#2f7a4d', 'color' => '#2f7a4d',         'subtitle' => 'Complaint closed · outcome recorded',         'icon' => 'check-circle-2'],
    ];
@endphp

<x-slot:title>ADR Complaints Scorecard</x-slot:title>

<div style="padding: 28px 32px; max-width: 1200px; margin: 0 auto;">

    {{-- Page header --}}
    <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:28px;">
        <div>
            <div class="label-cap" style="font-size:10px; color:var(--ink-4); margin-bottom:6px;">SERVICE DELIVERY · ADR / DISPUTE RESOLUTION</div>
            <h1 class="serif" style="font-size:32px; font-weight:400; margin:0; letter-spacing:-0.02em;">
                ADR <em style="color:var(--ochre); font-style:italic;">Complaints</em>
            </h1>
            <div style="font-size:13px; color:var(--ink-3); margin-top:6px;">
                Complaint pipeline for {{ $total }} ADR / Dispute Resolution cases · filed status and outcome tracking.
            </div>
        </div>
    </div>

    {{-- KPI cards --}}
    <div style="display:grid; grid-template-columns: repeat(5,1fr); gap:16px; margin-bottom:32px;">

        <div class="card" style="padding:20px 22px; border-top:3px solid var(--ink-3);">
            <div class="label-cap" style="font-size:9.5px; color:var(--ink-4); margin-bottom:8px;">Total Cases</div>
            <div class="serif" style="font-size:32px; font-weight:400; line-height:1;">{{ $total }}</div>
            <div style="font-size:11px; color:var(--ink-4); margin-top:4px;">ADR pathway</div>
        </div>

        <div class="card" style="padding:20px 22px; border-top:3px solid var(--ochre);">
            <div class="label-cap" style="font-size:9.5px; color:var(--ink-4); margin-bottom:8px;">Complaint Filed</div>
            <div class="serif" style="font-size:32px; font-weight:400; line-height:1; color:var(--ochre);">{{ $filed }}</div>
            <div style="font-size:11px; color:var(--ink-4); margin-top:4px;">tracking in progress</div>
        </div>

        <div class="card" style="padding:20px 22px; border-top:3px solid var(--burgundy);">
            <div class="label-cap" style="font-size:9.5px; color:var(--ink-4); margin-bottom:8px;">Not Filed</div>
            <div class="serif" style="font-size:32px; font-weight:400; line-height:1; color:var(--burgundy);">{{ $notFiled }}</div>
            <div style="font-size:11px; color:var(--ink-4); margin-top:4px;">with justification</div>
        </div>

        <div class="card" style="padding:20px 22px; border-top:3px solid #2f7a4d;">
            <div class="label-cap" style="font-size:9.5px; color:var(--ink-4); margin-bottom:8px;">Closed — In Favour</div>
            <div class="serif" style="font-size:32px; font-weight:400; line-height:1; color:#2f7a4d;">{{ $closedInFavour }}</div>
            <div style="font-size:11px; color:var(--ink-4); margin-top:4px;">of {{ $totalClosed }} closed</div>
        </div>

        <div class="card" style="padding:20px 22px; border-top:3px solid var(--forest);">
            <div class="label-cap" style="font-size:9.5px; color:var(--ink-4); margin-bottom:8px;">Success Rate</div>
            <div style="display:flex; align-items:baseline; gap:4px;">
                <div class="serif" style="font-size:32px; font-weight:400; line-height:1; color:var(--forest);">{{ $successRate }}</div>
                <div style="font-size:16px; color:var(--forest);">%</div>
            </div>
            <div style="font-size:11px; color:var(--ink-4); margin-top:4px;">closed in client's favour</div>
        </div>

    </div>

    {{-- Kanban pipeline --}}
    <div style="margin-bottom:10px;">
        <div style="font-size:15px; font-weight:600; color:var(--ink); margin-bottom:4px;">Complaint pipeline</div>
        <div style="font-size:12px; color:var(--ink-3);">{{ $total }} cases across 3 stages · sorted by intake date</div>
    </div>

    <div style="display:grid; grid-template-columns: repeat(3,1fr); gap:12px; align-items:start;">
        @foreach($pipeline as $stage => $stageCases)
        @php $cfg = $stageConfig[$stage]; @endphp
        <div style="background:var(--paper); border:1px solid var(--rule); border-top:3px solid {{ $cfg['dot'] }}; border-radius:2px;">

            {{-- Stage header --}}
            <div style="padding:12px 14px 10px; border-bottom:1px solid var(--rule-2);">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:3px;">
                    <div style="font-size:10px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase; color:{{ $cfg['color'] }};">
                        {{ $stage }}
                    </div>
                    <span class="mono" style="font-size:12px; font-weight:600; color:var(--ink-2);">{{ count($stageCases) }}</span>
                </div>
                <div style="font-size:10px; color:var(--ink-4);">{{ $cfg['subtitle'] }}</div>
            </div>

            {{-- Cards --}}
            <div style="padding:8px; display:flex; flex-direction:column; gap:6px; min-height:60px; max-height:calc(100vh - 380px); overflow-y:auto;">
                @forelse($stageCases as $c)
                @php
                    $latestRef = $c->caseReferrals->first();
                    $days = $c->intake_date ? (int)$c->intake_date->diffInDays(now()) : 0;
                    $daysColor = $days > 30 ? 'var(--burgundy)' : ($days > 14 ? 'var(--ochre)' : 'var(--ink-3)');
                    $initials = collect(explode(' ', $c->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
                    $avatarBg = ['#163029','#8a2e1d','#b87319','#4a7a5c','#6b6a65'][crc32($c->name) % 5];

                    // Outcome badge for Closed column — read from case meta (saved by resolve popup)
                    $metaOutcome   = $c->meta['outcome'] ?? null;
                    $isClosed      = in_array($c->status, [\App\Enums\CaseStatus::Closed, \App\Enums\CaseStatus::Settlement]);
                    $outcomeFavour = false;
                    if ($isClosed && $metaOutcome) {
                        $oc = strtolower($metaOutcome);
                        $outcomeFavour = str_contains($oc, 'favour') || str_contains($oc, 'favor') || str_contains($oc, 'success');
                    }
                @endphp
                <a href="{{ route('cases.show', $c) }}#referrals"
                   style="display:block; padding:10px 12px; background:var(--surface); border:1px solid var(--rule-2); text-decoration:none; color:inherit;">

                    {{-- Top row --}}
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:7px;">
                        <div style="display:flex; align-items:center; gap:7px;">
                            <div style="width:26px; height:26px; border-radius:50%; background:{{ $avatarBg }}; color:#fff; font-size:9px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                {{ $initials }}
                            </div>
                            <div>
                                <div style="font-size:12px; font-weight:600; color:var(--ink); line-height:1.2;">{{ $c->name }}</div>
                                <div class="mono" style="font-size:10px; color:var(--ink-4);">{{ $c->case_uid }}</div>
                            </div>
                        </div>
                        <div style="font-size:10px; color:{{ $daysColor }}; font-weight:600;">{{ $days }}d</div>
                    </div>

                    {{-- Issue --}}
                    @if($c->primary_issue)
                    <div style="font-size:11px; color:var(--ink-3); margin-bottom:5px;">{{ $c->primary_issue }}</div>
                    @endif

                    {{-- Filing status badge (Complaint Filed column) --}}
                    @if($latestRef && $latestRef->filing_status)
                    <div style="display:flex; align-items:center; gap:5px; margin-bottom:4px;">
                        <span style="font-size:10px; padding:1px 7px; font-weight:600;
                            background:{{ $latestRef->filing_status === 'Filed' ? 'rgba(184,115,25,0.1)' : 'rgba(138,46,29,0.1)' }};
                            color:{{ $latestRef->filing_status === 'Filed' ? 'var(--ochre)' : 'var(--burgundy)' }};">
                            {{ $latestRef->filing_status }}
                        </span>
                        @if($latestRef->tracking_number)
                        <span class="mono" style="font-size:10px; color:var(--ink-4);">#{{ $latestRef->tracking_number }}</span>
                        @endif
                    </div>
                    @endif

                    {{-- Closed: outcome badge from case meta --}}
                    @if($isClosed && $metaOutcome)
                    <div style="display:flex; align-items:center; gap:5px; margin-bottom:4px;">
                        <span style="font-size:10px; padding:2px 8px; font-weight:700; letter-spacing:0.04em;
                            background:{{ $outcomeFavour ? 'rgba(47,122,77,0.12)' : ($metaOutcome === 'Withdrawn' ? 'rgba(107,106,101,0.1)' : 'rgba(138,46,29,0.1)') }};
                            color:{{ $outcomeFavour ? '#2f7a4d' : ($metaOutcome === 'Withdrawn' ? 'var(--ink-3)' : 'var(--burgundy)') }};">
                            {{ $metaOutcome }}
                        </span>
                    </div>
                    @endif

                    {{-- Referred to --}}
                    @if($latestRef && $latestRef->referred_to)
                    <div style="font-size:10px; color:var(--ink-4); margin-top:2px;">→ {{ $latestRef->referred_to }}</div>
                    @endif

                    {{-- Hub --}}
                    <div style="font-size:10px; color:var(--ink-4); margin-top:4px;">{{ $c->hub_id }}</div>
                </a>
                @empty
                <div style="padding:24px 12px; text-align:center; color:var(--ink-4); font-size:12px;">
                    No cases in this stage
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>

</div>
</x-layouts.app>
