<x-layouts.app>
@php $canWrite = auth()->user()->canWrite(); @endphp

<div style="padding: 28px 36px 60px; max-width: 1640px; margin: 0 auto;">

    {{-- ═══ Editorial Header ═══ --}}
    <div style="margin-bottom: 30px; padding-bottom: 22px; border-bottom: 1px solid var(--rule);">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 32px;">
            <div style="flex: 1; max-width: 820px;">
                <div class="label-cap" style="font-size: 9.5px; margin-bottom: 8px;">Complaints Register</div>
                <h1 class="serif" style="font-size: 44px; font-weight: 400; letter-spacing: -0.018em; line-height: 1.02; margin: 0;">
                    Service users hold us <em style="color: var(--ochre);">accountable</em>
                </h1>
                <div style="font-size: 13.5px; color: var(--ink-2); margin-top: 12px; line-height: 1.65; max-width: 700px;">
                    Every complaint logged, the SLA it must be resolved within, and the action taken. Indicator <span class="mono" style="font-size: 12px;">OP4.3</span> measures only one thing: do we close complaints within the timeline we promised?
                </div>
            </div>
            @if($canWrite)
            <button class="btn-primary" onclick="jhOpenModal('log-complaint')">
                <x-lucide-plus style="width:14px;height:14px;" /> Log complaint
            </button>
            @endif
        </div>
    </div>

    {{-- ═══ 4 KPI Cards ═══ --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 28px;">

        {{-- OP4.3 – Resolved within SLA --}}
        <div class="card" style="padding: 20px 22px; border-left: 3px solid var(--ochre);">
            <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 12px;">OP4.3 &middot; Resolved within SLA</div>
            <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 4px;">
                <span class="serif" style="font-size: 52px; font-weight: 400; line-height: 1; color: {{ $slaRate >= 90 ? 'var(--forest)' : ($slaRate >= 75 ? 'var(--ochre)' : 'var(--burgundy)' ) }};">{{ $slaRate }}</span>
                <span style="font-size: 24px; color: var(--ink-3); font-weight: 300;">%</span>
                <span style="font-size: 13px; color: var(--ink-3); margin-left: 2px;">{{ $withinSla }} of {{ $resolvedCount }}<br><span style="font-size: 11px;">resolved in time</span></span>
            </div>
            <div style="font-size: 11px; color: var(--ink-4); margin-top: 8px; line-height: 1.5;">
                Target <strong style="color: var(--ink-2);">90%</strong> &middot; SLA varies by severity (3&ndash;30 days).
            </div>
        </div>

        {{-- Currently Open --}}
        <div class="card" style="padding: 20px 22px;">
            <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 12px;">Currently Open</div>
            <div class="serif" style="font-size: 52px; font-weight: 400; line-height: 1; margin-bottom: 8px;">{{ $currentlyOpen }}</div>
            <div style="font-size: 11px; color: var(--ink-3); line-height: 1.5;">
                @if($currentlyOpen > 0 && $openWithinSla === $currentlyOpen)
                    All within SLA window
                @elseif($counts['overdue'] > 0)
                    <span style="color: var(--burgundy); font-weight: 600;">{{ $counts['overdue'] }} overdue</span> past SLA deadline
                @else
                    {{ $currentlyOpen }} complaint{{ $currentlyOpen === 1 ? '' : 's' }} awaiting resolution
                @endif
            </div>
        </div>

        {{-- Avg resolution time --}}
        <div class="card" style="padding: 20px 22px;">
            <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 12px;">Avg Resolution Time</div>
            <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 8px;">
                <span class="serif" style="font-size: 52px; font-weight: 400; line-height: 1;">{{ $avgResolutionDays }}</span>
                <span style="font-size: 18px; color: var(--ink-3); font-weight: 300;">days</span>
            </div>
            <div style="font-size: 11px; color: var(--ink-3); line-height: 1.5;">
                across {{ $resolvedCount }} resolved complaint{{ $resolvedCount === 1 ? '' : 's' }}
            </div>
        </div>

        {{-- Total logged --}}
        <div class="card" style="padding: 20px 22px;">
            <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 12px;">Total Logged</div>
            <div class="serif" style="font-size: 52px; font-weight: 400; line-height: 1; margin-bottom: 8px;">{{ $counts['total'] }}</div>
            <div style="font-size: 11px; color: var(--ink-3); line-height: 1.5; margin-top: 4px;">
                since programme inception
            </div>
        </div>
    </div>

    {{-- ═══ Analytics Row ═══ --}}
    <div style="display: grid; grid-template-columns: 1fr 360px; gap: 14px; margin-bottom: 30px;">

        {{-- Severity bars --}}
        <div class="card" style="padding: 22px 24px;">
            <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 18px;">By Severity &middot; Open vs Resolved</div>
            <div style="display: flex; flex-direction: column; gap: 16px;">
                @foreach($severityBars as $sev => $sb)
                @php
                    $sevColor = match($sev) {
                        'critical' => 'var(--burgundy)',
                        'high'     => 'var(--ochre)',
                        'medium'   => 'var(--ink-1)',
                        'low'      => 'var(--ink-2)',
                        default    => 'var(--ink-3)',
                    };
                    $totalSev = $sb['open'] + $sb['resolved'];
                    $openW    = $maxSeverityTotal > 0 ? round(($sb['open'] / $maxSeverityTotal) * 100) : 0;
                    $resolvedW= $maxSeverityTotal > 0 ? round(($sb['resolved'] / $maxSeverityTotal) * 100) : 0;
                @endphp
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 11.5px; font-weight: 700; letter-spacing: 0.04em; color: {{ $sevColor }};">{{ strtoupper($sb['label']) }}</span>
                            <span class="mono" style="font-size: 10px; color: var(--ink-4); background: var(--paper); padding: 1px 6px; border: 1px solid var(--rule-2);">SLA {{ $sb['sla'] }}d</span>
                        </div>
                        <div style="font-size: 11px; color: var(--ink-3);">
                            <span style="color: var(--ink-2); font-weight: 500;">{{ $sb['open'] }} open</span>
                            &middot;
                            <span style="color: var(--moss); font-weight: 500;">{{ $sb['resolved'] }} resolved</span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 2px; height: 10px;">
                        <div style="width: {{ $openW }}%; background: {{ $sevColor }}; min-width: {{ $sb['open'] > 0 ? '4px' : '0' }};"></div>
                        <div style="width: {{ $resolvedW }}%; background: var(--moss); opacity: 0.65; min-width: {{ $sb['resolved'] > 0 ? '4px' : '0' }};"></div>
                        <div style="flex: 1; background: var(--rule-2);"></div>
                    </div>
                </div>
                @endforeach
            </div>
            <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--rule-2); display: flex; gap: 18px; font-size: 10.5px; color: var(--ink-3);">
                <span style="display:flex; align-items:center; gap:5px;"><span style="width:10px;height:10px;background:var(--ink-2);display:inline-block;"></span> Open</span>
                <span style="display:flex; align-items:center; gap:5px;"><span style="width:10px;height:10px;background:var(--moss);opacity:.65;display:inline-block;"></span> Resolved</span>
            </div>
        </div>

        {{-- Top categories --}}
        <div class="card" style="padding: 22px 24px;">
            <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 18px;">Top Categories</div>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($categoryStats as $cat => $cnt)
                @php
                    $catLabel = ucwords(str_replace(['-','_'], ' ', $cat));
                    $barW = round(($cnt / $maxCategoryCount) * 100);
                @endphp
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 4px;">
                        <span style="font-size: 13px; color: var(--ink);">{{ $catLabel }}</span>
                        <span style="font-size: 13px; font-weight: 600; color: var(--ink);">{{ $cnt }}</span>
                    </div>
                    <div style="height: 4px; background: var(--rule-2);">
                        <div style="height: 100%; width: {{ $barW }}%; background: var(--ink);"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══ Filter Bar ═══ --}}
    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 14px; flex-wrap: wrap;">
        {{-- Status filters --}}
        <div style="display: flex; align-items: center; gap: 8px;">
            <span class="label-cap" style="font-size: 9.5px; color: var(--ink-3); white-space: nowrap;">Status</span>
            @foreach([
                ['all',      'All',      $counts['total']],
                ['open',     'Open',     $counts['open']],
                ['resolved', 'Resolved', $counts['resolved']],
            ] as [$val, $lbl, $cnt])
            <button onclick="cmpFilter('status', '{{ $val }}', this)"
                    data-cmp-status="{{ $val }}"
                    style="padding: 5px 10px; font-size: 11.5px; font-weight: {{ $val === 'all' ? '600' : '500' }}; font-family: inherit; cursor: pointer; border: 1px solid var(--rule); letter-spacing: 0.01em; transition: all 120ms;
                           background: {{ $val === 'all' ? 'var(--forest)' : 'transparent' }}; color: {{ $val === 'all' ? 'var(--cream)' : 'var(--ink-2)' }}; border-color: {{ $val === 'all' ? 'var(--forest)' : 'var(--rule)' }};">
                {{ $lbl }} <span style="font-size: 10px; font-weight: 600; margin-left: 2px; opacity: .75;">{{ $cnt }}</span>
            </button>
            @endforeach
        </div>

        <div style="width: 1px; height: 20px; background: var(--rule);"></div>

        {{-- Severity filters --}}
        <div style="display: flex; align-items: center; gap: 8px;">
            <span class="label-cap" style="font-size: 9.5px; color: var(--ink-3); white-space: nowrap;">Severity</span>
            @foreach(['all' => 'All', 'critical' => 'Critical', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $val => $lbl)
            <button onclick="cmpFilter('severity', '{{ $val }}', this)"
                    data-cmp-sev="{{ $val }}"
                    style="padding: 5px 10px; font-size: 11.5px; font-weight: {{ $val === 'all' ? '600' : '500' }}; font-family: inherit; cursor: pointer; border: 1px solid var(--rule); letter-spacing: 0.01em; transition: all 120ms;
                           background: {{ $val === 'all' ? 'var(--forest)' : 'transparent' }}; color: {{ $val === 'all' ? 'var(--cream)' : 'var(--ink-2)' }}; border-color: {{ $val === 'all' ? 'var(--forest)' : 'var(--rule)' }};">
                {{ $lbl }}
            </button>
            @endforeach
        </div>

        <div style="margin-left: auto; font-size: 12px; color: var(--ink-3);" id="cmpCount">
            Showing {{ $counts['total'] }} of {{ $counts['total'] }}
        </div>
    </div>

    {{-- ═══ Complaints Table ═══ --}}
    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 28px;">

        {{-- Header --}}
        <div style="display: grid; grid-template-columns: 140px 140px 1fr 180px 130px 160px; border-bottom: 2px solid var(--rule);">
            @foreach(['Code','Severity','Description','Hub · Case','Submitted','SLA Status'] as $col)
            <div style="padding: 10px 14px; font-size: 9.5px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ink-3); background: var(--paper);">
                {{ $col }}
            </div>
            @endforeach
        </div>

        {{-- Rows --}}
        @forelse($complaints as $c)
        @php
            $isResolved  = $c->status->value === 'resolved';
            $isOverdue   = $c->isOverdue();
            $daysLeft    = $c->daysRemaining();
            $channelLabel= match($c->channel) {
                'in-person' => 'In-Person',
                'phone'     => 'Phone',
                'written'   => 'Written',
                'digital'   => 'Digital',
                default     => ucfirst($c->channel ?? '—'),
            };
            $catLabel    = ucwords(str_replace(['-','_'], ' ', $c->category));
            $submitter   = $c->is_anonymous ? 'Anonymous' : (Str::of($c->submitted_by)->limit(20, '')->toString());
            $hubName     = $c->hub?->name ?? $c->hub_id;
            $caseUid     = $c->caseRecord?->case_uid ?? '—';

            // SLA status display
            if ($isResolved && $c->resolved_date) {
                $daysToResolve = $c->submitted_date->diffInDays($c->resolved_date);
                $onTime = $daysToResolve <= $c->sla_days;
            } else {
                $daysToResolve = null;
                $onTime = null;
            }
        @endphp
        <div data-cmp-row
             data-status="{{ $c->status->value === 'in-progress' ? 'open' : $c->status->value }}"
             data-severity="{{ $c->severity->value }}"
             style="display: grid; grid-template-columns: 140px 140px 1fr 180px 130px 160px; align-items: start;
                    border-bottom: 1px solid var(--rule-2); cursor: pointer; transition: background 100ms;"
             onclick="window.location='{{ route('complaints.show', $c) }}'"
             onmouseenter="this.style.background='var(--paper)'" onmouseleave="this.style.background=''">

            {{-- CODE --}}
            <div style="padding: 14px 14px;">
                <div class="mono" style="font-size: 12.5px; font-weight: 600; color: {{ $isResolved ? 'var(--ink-2)' : 'var(--ochre)' }}; margin-bottom: 4px;">{{ $c->complaint_uid }}</div>
                <div style="font-size: 10.5px; color: var(--ink-4);">{{ $channelLabel }}</div>
            </div>

            {{-- SEVERITY --}}
            <div style="padding: 14px 14px;">
                <span style="display: inline-block; font-size: 10.5px; font-weight: 700; letter-spacing: 0.05em; padding: 3px 8px; background: {{ $c->severity->tint() }}; color: {{ $c->severity->color() }}; margin-bottom: 5px;">
                    {{ strtoupper($c->severity->label()) }}
                </span>
                <div class="mono" style="font-size: 10px; color: var(--ink-4);">SLA {{ $c->sla_days }}d</div>
            </div>

            {{-- DESCRIPTION --}}
            <div style="padding: 14px 14px;">
                <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px; flex-wrap: wrap;">
                    <span style="font-size: 9.5px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; padding: 2px 7px; background: var(--paper); border: 1px solid var(--rule-2); color: var(--ink-2);">{{ $catLabel }}</span>
                    <span style="font-size: 12px; color: var(--ink-3);">{{ $submitter }}</span>
                </div>
                <div style="font-size: 13px; color: var(--ink-2); line-height: 1.45; font-style: italic;">
                    {{ Str::limit($c->description, 120) }}
                </div>
            </div>

            {{-- HUB · CASE --}}
            <div style="padding: 14px 14px;">
                <div style="font-size: 13px; color: var(--ink); font-weight: 500; margin-bottom: 3px;">{{ $hubName }}</div>
                <div class="mono" style="font-size: 10.5px; color: var(--ink-3);">{{ $caseUid }}</div>
            </div>

            {{-- SUBMITTED --}}
            <div style="padding: 14px 14px;">
                <div style="font-size: 12.5px; color: var(--ink-2);">{{ $c->submitted_date->format('M j, Y') }}</div>
            </div>

            {{-- SLA STATUS --}}
            <div style="padding: 14px 14px;">
                @if($isResolved && $daysToResolve !== null)
                    @if($onTime)
                    <div style="display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--moss); font-weight: 500;">
                        <x-lucide-check-circle style="width:13px;height:13px;flex-shrink:0;" />
                        Resolved in {{ $daysToResolve }}d
                    </div>
                    @else
                    <div style="display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--burgundy); font-weight: 500;">
                        <x-lucide-x-circle style="width:13px;height:13px;flex-shrink:0;" />
                        Resolved late &middot; {{ $daysToResolve }}d / {{ $c->sla_days }}d
                    </div>
                    @endif
                @elseif($isOverdue)
                <div style="display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--burgundy); font-weight: 500;">
                    <x-lucide-alert-triangle style="width:13px;height:13px;flex-shrink:0;" />
                    {{ abs($daysLeft) }}d overdue
                </div>
                @else
                @php $urgColor = $daysLeft <= 2 ? 'var(--ochre)' : 'var(--ink-3)'; @endphp
                <div style="display: flex; align-items: center; gap: 5px; font-size: 12px; color: {{ $urgColor }};">
                    <x-lucide-clock style="width:13px;height:13px;flex-shrink:0;" />
                    {{ $daysLeft }}d to SLA
                </div>
                @endif
            </div>
        </div>
        @empty
        <div style="padding: 52px; text-align: center; color: var(--ink-3);">
            <x-lucide-check-circle-2 style="width:28px;height:28px;color:var(--moss);margin:0 auto 12px;" />
            <div style="font-size: 13px;">No complaints logged &mdash; a quiet register is good news.</div>
        </div>
        @endforelse
    </div>

    {{-- ═══ OP4.3 methodology note ═══ --}}
    <div style="padding: 14px 18px; background: var(--paper); border-left: 3px solid var(--rule); font-size: 11.5px; color: var(--ink-3); line-height: 1.6;">
        <strong style="color: var(--ink-2);">How OP4.3 reads health:</strong>
        The denominator is resolved complaints; open ones don&rsquo;t affect the figure until they close. A high complaint count is not failure &mdash; silence often signals lack of trust in the channel. The failure signal is unresolved complaints past their SLA. Look at OP4.3 alongside the &ldquo;currently open&rdquo; and &ldquo;overdue&rdquo; counts to read the system honestly.
    </div>

</div>

{{-- ═══ Log Complaint Modal ═══ --}}
@if($canWrite)
<div class="modal fade" id="modal-log-complaint" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 560px; margin: 1.75rem auto;">
        <div class="modal-content" style="border: 1px solid var(--rule); border-radius: 4px; background: var(--parchment); box-shadow: 0 16px 48px rgba(0,0,0,.18); display: flex; flex-direction: column; max-height: 90vh;">

            {{-- Header --}}
            <div style="padding: 22px 24px 18px; border-bottom: 1px solid var(--rule); flex-shrink: 0; background: var(--parchment);">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                    <div>
                        <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 6px;">Accountability &middot; Complaints Register</div>
                        <h2 class="serif" style="font-size: 28px; font-weight: 400; margin: 0; line-height: 1.1; letter-spacing: -0.01em;">Log complaint</h2>
                    </div>
                    <button type="button" data-bs-dismiss="modal"
                            style="background:none; border:1px solid var(--rule); cursor:pointer; padding:6px 9px; color:var(--ink-3); border-radius:3px; flex-shrink:0; margin-top:2px; transition: border-color 120ms;"
                            onmouseenter="this.style.borderColor='var(--ink-2)'" onmouseleave="this.style.borderColor='var(--rule)'">
                        <x-lucide-x style="width:15px;height:15px;" />
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div style="flex:1; overflow-y:auto; padding: 22px 24px;">
                <form method="POST" action="{{ route('complaints.store') }}" id="cmpForm">
                    @csrf

                    <div style="margin-bottom: 16px;">
                        <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">
                            Complaint description <span style="color:var(--burgundy);">*</span>
                        </label>
                        <textarea name="description" rows="4" required
                                  placeholder="Describe the complaint in detail — what happened, when, who was involved…"
                                  style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; resize:vertical; box-sizing:border-box; border-radius:2px; line-height:1.5; outline:none;"></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Category <span style="color:var(--burgundy);">*</span></label>
                            <select name="category" required style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px; appearance:auto; cursor:pointer;">
                                <option value="">Select…</option>
                                <option value="communication">Communication</option>
                                <option value="service-delay">Service delay</option>
                                <option value="service-quality">Service quality</option>
                                <option value="coordination">Coordination</option>
                                <option value="staff-conduct">Staff conduct</option>
                                <option value="data-privacy">Data privacy</option>
                                <option value="discrimination">Discrimination</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Severity <span style="color:var(--burgundy);">*</span></label>
                            <select name="severity" required style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px; appearance:auto; cursor:pointer;">
                                <option value="">Select…</option>
                                <option value="critical">Critical (SLA 3d)</option>
                                <option value="high">High (SLA 7d)</option>
                                <option value="medium" selected>Medium (SLA 14d)</option>
                                <option value="low">Low (SLA 30d)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Reporter name <span style="color:var(--burgundy);">*</span></label>
                            <input type="text" name="submitted_by" required value="{{ auth()->user()->name }}"
                                   style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Channel</label>
                            <select name="channel" style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px; appearance:auto; cursor:pointer;">
                                <option value="in-person">In-Person</option>
                                <option value="phone">Phone</option>
                                <option value="written">Written</option>
                                <option value="digital">Digital</option>
                            </select>
                        </div>
                    </div>

                    <label style="display:flex; align-items:center; gap:10px; padding:12px 14px; border:1px solid var(--rule); background:var(--paper); cursor:pointer; border-radius:2px; transition:border-color 120ms;"
                           onmouseenter="this.style.borderColor='var(--ink-2)'" onmouseleave="this.style.borderColor='var(--rule)'">
                        <input type="checkbox" name="is_anonymous" value="1" style="width:14px;height:14px;flex-shrink:0;accent-color:var(--forest);cursor:pointer;">
                        <div>
                            <div style="font-size:13px;font-weight:600;color:var(--ink);">Report anonymously</div>
                            <div style="font-size:11.5px;color:var(--ink-3);margin-top:2px;line-height:1.4;">Complainant name will not appear in the register</div>
                        </div>
                    </label>
                </form>
            </div>

            {{-- Footer --}}
            <div style="flex-shrink:0; padding:14px 24px; border-top:1px solid var(--rule); display:flex; justify-content:flex-end; gap:10px; background:var(--parchment);">
                <button type="button" data-bs-dismiss="modal" class="btn-ghost">Cancel</button>
                <button type="submit" form="cmpForm" class="btn-primary" style="background:var(--burgundy); border-color:var(--burgundy); display:inline-flex; align-items:center; gap:7px;"
                        onmouseenter="this.style.opacity='.88'" onmouseleave="this.style.opacity='1'">
                    <x-lucide-alert-triangle style="width:13px;height:13px;" /> Log complaint
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
// ── Complaint table filter (status + severity, AND logic) ─────
let cmpActiveStatus = 'all';
let cmpActiveSev    = 'all';

function cmpFilter(type, val, btn) {
    if (type === 'status') {
        cmpActiveStatus = val;
        document.querySelectorAll('[data-cmp-status]').forEach(b => {
            const active = b.dataset.cmpStatus === val;
            b.style.background  = active ? 'var(--forest)' : 'transparent';
            b.style.color       = active ? 'var(--cream)'  : 'var(--ink-2)';
            b.style.borderColor = active ? 'var(--forest)' : 'var(--rule)';
            b.style.fontWeight  = active ? '600' : '500';
        });
    } else {
        cmpActiveSev = val;
        document.querySelectorAll('[data-cmp-sev]').forEach(b => {
            const active = b.dataset.cmpSev === val;
            b.style.background  = active ? 'var(--forest)' : 'transparent';
            b.style.color       = active ? 'var(--cream)'  : 'var(--ink-2)';
            b.style.borderColor = active ? 'var(--forest)' : 'var(--rule)';
            b.style.fontWeight  = active ? '600' : '500';
        });
    }

    let visible = 0;
    document.querySelectorAll('[data-cmp-row]').forEach(row => {
        const statusOk = cmpActiveStatus === 'all' || row.dataset.status === cmpActiveStatus;
        const sevOk    = cmpActiveSev    === 'all' || row.dataset.severity === cmpActiveSev;
        const show     = statusOk && sevOk;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    const total = document.querySelectorAll('[data-cmp-row]').length;
    const countEl = document.getElementById('cmpCount');
    if (countEl) countEl.textContent = 'Showing ' + visible + ' of ' + total;
}
</script>
</x-layouts.app>
