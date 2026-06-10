<x-layouts.app>
@php $canWrite = auth()->user()->canWrite(); @endphp

<div style="padding: 24px 34px 64px; max-width: 1600px; margin: 0 auto;">

    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 22px;">
        <div>
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">Quality & Accountability</div>
            <h1 class="serif" style="font-size: 34px; font-weight: 400; letter-spacing: -0.02em; margin: 0;">
                Client Feedback
            </h1>
            <p style="margin: 6px 0 0 0; font-size: 13px; color: var(--ink-3);">
                {{ $counts['total'] }} responses · Avg score {{ $avgScore }}/5 · {{ $counts['positive'] }} positive
            </p>
        </div>
        @if($canWrite)
        <button class="btn-primary" onclick="jhOpenModal('capture-feedback')">
            <x-lucide-plus style="width: 12px; height: 12px;" /> Capture feedback
        </button>
        @endif
    </div>

    {{-- ═══ KPI Strip ═══ --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 22px;">
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--forest);">
            <div class="label-cap" style="font-size: 9px;">Avg Overall Score</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px; color: var(--forest);">{{ $avgScore }}<span style="font-size: 16px; color: var(--ink-3);">/5</span></div>
            <div style="display: flex; gap: 2px; margin-top: 4px;">
                @for($i = 1; $i <= 5; $i++)
                <x-lucide-star style="width:11px;height:11px;color:{{ $i <= $avgScore ? 'var(--ochre)' : 'var(--rule)' }};" />
                @endfor
            </div>
        </div>
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--moss);">
            <div class="label-cap" style="font-size: 9px;">Positive (≥ 4/5)</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px; color: var(--moss);">{{ $counts['positive'] }}</div>
            <div style="font-size: 11px; color: var(--ink-3);">
                {{ $counts['total'] > 0 ? round(($counts['positive'] / $counts['total']) * 100) : 0 }}% satisfaction rate
            </div>
        </div>
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--ochre);">
            <div class="label-cap" style="font-size: 9px;">Would Recommend</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px;">{{ $counts['would_recommend'] }}</div>
            <div style="font-size: 11px; color: var(--ink-3);">
                {{ $counts['total'] > 0 ? round(($counts['would_recommend'] / $counts['total']) * 100) : 0 }}% of clients
            </div>
        </div>
        <div class="card" style="padding: 16px 18px; border-top: 3px solid #7e57c2;">
            <div class="label-cap" style="font-size: 9px;">Understood Rights</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px;">{{ $counts['understood'] }}</div>
            <div style="font-size: 11px; color: var(--ink-3);">
                {{ $counts['total'] > 0 ? round(($counts['understood'] / $counts['total']) * 100) : 0 }}% of clients
            </div>
        </div>
    </div>

    {{-- ═══ Charts Row ═══ --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 22px;">
        {{-- Trend chart --}}
        <div class="card" style="padding: 18px;">
            <div style="font-size: 11px; font-weight: 600; color: var(--ink-3); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 12px;">Monthly Satisfaction Trend</div>
            <div x-data="feedbackTrendChart({{ $trendJson }})" x-init="init()" style="height: 180px;">
                <canvas x-ref="chart"></canvas>
            </div>
        </div>
        {{-- Service breakdown --}}
        <div class="card" style="padding: 18px;">
            <div style="font-size: 11px; font-weight: 600; color: var(--ink-3); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 12px;">By Service</div>
            @foreach($byService as $svc)
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 9px;">
                <div style="font-size: 11px; color: var(--ink-2); min-width: 100px; flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $svc->service ?? 'Unknown' }}</div>
                <div style="flex: 2; height: 6px; background: var(--rule); border-radius: 3px; overflow: hidden;">
                    <div style="height: 100%; width: {{ min(100, round((float)$svc->avg_score / 5 * 100)) }}%; background: var(--forest); border-radius: 3px;"></div>
                </div>
                <div class="mono" style="font-size: 11px; font-weight: 600; min-width: 28px; text-align: right;">{{ round((float)$svc->avg_score, 1) }}</div>
                <div style="font-size: 10px; color: var(--ink-4);">×{{ $svc->cnt }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ═══ Filter Bar ═══ --}}
    <form method="GET" action="{{ route('feedback.index') }}" style="display: flex; gap: 10px; align-items: center; margin-bottom: 16px;">
        <x-form-select name="service" lookup-group="feedback.service" :selected="request('service')" style="min-width: 140px;" />
        <x-form-select name="channel" lookup-group="feedback.channel" :selected="request('channel')" style="min-width: 140px;" />
        <button type="submit" class="btn-ghost" style="font-size: 12px; padding: 6px 12px;">Filter</button>
        @if(request('service') || request('channel'))
        <a href="{{ route('feedback.index') }}" style="font-size: 12px; color: var(--ink-3); text-decoration: none;">
            <x-lucide-x style="width:12px;height:12px;display:inline;vertical-align:-1px;" /> Clear
        </a>
        @endif
    </form>

    {{-- ═══ Feedback Table ═══ --}}
    <div class="card" style="padding: 0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 1px solid var(--rule);">
                    <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">ID</th>
                    <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Date</th>
                    <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Client</th>
                    <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Service</th>
                    <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Lawyer</th>
                    <th style="text-align: center; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Overall</th>
                    <th style="text-align: center; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Help</th>
                    <th style="text-align: center; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Respect</th>
                    <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Channel</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feedback as $fb)
                @php
                    $scoreColor = fn($s) => $s >= 4 ? 'var(--moss)' : ($s >= 3 ? 'var(--ochre)' : 'var(--burgundy)');
                @endphp
                <tr style="border-bottom: 1px solid var(--rule-2);">
                    <td style="padding: 11px 14px;">
                        <span class="mono" style="font-size: 11px; color: var(--forest); font-weight: 500;">{{ $fb->feedback_uid }}</span>
                    </td>
                    <td style="padding: 11px 14px; white-space: nowrap;" class="mono">{{ $fb->date->format('M d') }}</td>
                    <td style="padding: 11px 14px; font-size: 12px; color: var(--ink-2);">
                        @if($fb->is_anonymous)
                        <span style="color: var(--ink-4); font-style: italic;">Anonymous</span>
                        @else
                        {{ $fb->client_name }}
                        @endif
                    </td>
                    <td style="padding: 11px 14px; font-size: 12px; color: var(--ink-2);">{{ $fb->service ?? '—' }}</td>
                    <td style="padding: 11px 14px; font-size: 12px; color: var(--ink-2);">{{ $fb->lawyer ?? '—' }}</td>
                    <td style="padding: 11px 14px; text-align: center;">
                        <span style="display: inline-flex; align-items: center; gap: 3px; font-weight: 700; font-size: 13px; color: {{ $scoreColor($fb->score_overall) }};">
                            {{ $fb->score_overall }}
                            <x-lucide-star style="width:10px;height:10px;color:var(--ochre);" />
                        </span>
                    </td>
                    <td style="padding: 11px 14px; text-align: center; font-size: 12px; color: {{ $scoreColor($fb->score_helpfulness) }};" class="mono">{{ $fb->score_helpfulness }}</td>
                    <td style="padding: 11px 14px; text-align: center; font-size: 12px; color: {{ $scoreColor($fb->score_respect) }};" class="mono">{{ $fb->score_respect }}</td>
                    <td style="padding: 11px 14px; font-size: 11px; color: var(--ink-3);">{{ ucfirst($fb->channel) }}</td>
                </tr>
                @if($fb->comment)
                <tr style="border-bottom: 1px solid var(--rule-2); background: var(--surface-2);">
                    <td colspan="9" style="padding: 4px 14px 10px 36px; font-size: 11px; color: var(--ink-3); font-style: italic;">
                        "{{ $fb->comment }}"
                    </td>
                </tr>
                @endif
                @empty
                <tr><td colspan="9"><x-empty-state icon="message-square" message="No feedback recorded yet." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($feedback->hasPages())
    <div style="margin-top: 16px; display: flex; justify-content: center;">
        {{ $feedback->links() }}
    </div>
    @endif
</div>

{{-- ═══ Capture Feedback Modal ═══ --}}
@if($canWrite)
<div class="modal fade" id="modal-capture-feedback" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 580px; margin: 1.75rem auto;">
        <div class="modal-content" style="border: 1px solid var(--rule); border-radius: 4px; background: var(--parchment); box-shadow: 0 16px 48px rgba(0,0,0,.18); display: flex; flex-direction: column; max-height: 92vh;">

            {{-- Header --}}
            <div style="padding: 22px 24px 16px; border-bottom: 1px solid var(--rule); flex-shrink: 0;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 6px;">Capture Client Feedback &middot; Indicator O1.4</div>
                        <h2 class="serif" style="font-size: 26px; font-weight: 400; margin: 0;">Exit survey &middot; 3 questions</h2>
                    </div>
                    <button type="button" data-bs-dismiss="modal" style="background:none; border:1px solid var(--rule); cursor:pointer; padding:6px 8px; color:var(--ink-3); border-radius:3px;">
                        <x-lucide-x style="width:15px;height:15px;" />
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div style="flex: 1; overflow-y: auto; padding: 0;">
                <form method="POST" action="{{ route('feedback.store') }}" id="fbForm">
                    @csrf

                    {{-- Case selector --}}
                    <div style="padding: 20px 24px; border-bottom: 1px solid var(--rule);">
                        <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Case</label>
                        <select name="case_id" id="fbCaseSelect" onchange="fbUpdateCaseInfo(this)"
                                style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px; appearance:auto;">
                            <option value="">Select a case…</option>
                            @foreach($cases as $c)
                            <option value="{{ $c->id }}" data-hub="{{ $c->hub?->name ?? $c->hub_id }}" data-assigned="{{ $c->assigned_to }}">
                                {{ $c->case_ref }} &middot; {{ $c->name }} &middot; {{ $c->primary_issue }}
                            </option>
                            @endforeach
                        </select>
                        <div id="fbCaseInfo" style="font-size: 11.5px; color: var(--ink-3); margin-top: 6px;"></div>
                    </div>

                    {{-- 3 Star rating questions --}}
                    <div style="padding: 20px 24px; border-bottom: 1px solid var(--rule);">
                        @foreach([
                            ['field' => 'score_overall',      'num' => 1, 'label' => 'Overall, how was your experience with the Hub?',    'hint' => 'This is the headline indicator (O1.4)'],
                            ['field' => 'score_helpfulness',  'num' => 2, 'label' => 'How helpful was the help you received?',              'hint' => 'The substantive value of the service'],
                            ['field' => 'score_respect',      'num' => 3, 'label' => 'Were you treated with respect and dignity?',          'hint' => 'Safeguarding signal'],
                        ] as $q)
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; margin-bottom: 8px; background: var(--paper); border: 1px solid var(--rule);">
                            <div>
                                <div style="font-size: 13px; font-weight: 600; color: var(--ink);">{{ $q['num'] }}. {{ $q['label'] }}</div>
                                <div style="font-size: 10.5px; color: var(--ink-4); margin-top: 2px;">{{ $q['hint'] }}</div>
                            </div>
                            <div style="display: flex; gap: 3px; flex-shrink: 0;" id="fbStars_{{ $q['field'] }}">
                                @for($i = 1; $i <= 5; $i++)
                                <button type="button" onclick="fbSetStar('{{ $q['field'] }}', {{ $i }})"
                                        data-star="{{ $i }}"
                                        style="background:none; border:none; cursor:pointer; padding:2px; font-size:20px; color:var(--rule); transition:color 100ms;">&#9733;</button>
                                @endfor
                            </div>
                            <input type="hidden" name="{{ $q['field'] }}" id="fbVal_{{ $q['field'] }}" value="" required>
                        </div>
                        @endforeach
                    </div>

                    {{-- Understood rights + Would recommend --}}
                    <div style="padding: 20px 24px; border-bottom: 1px solid var(--rule);">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:8px;">Understood your rights?</label>
                                <div style="display: flex; border: 1px solid var(--rule);">
                                    @foreach(['Yes', 'Partial', 'No'] as $opt)
                                    <button type="button" onclick="fbSetToggle('understood_rights', '{{ strtolower($opt) }}', this)"
                                            style="flex:1; padding:9px 0; font-size:12.5px; font-weight:600; border:none; cursor:pointer; font-family:inherit; background:var(--parchment); color:var(--ink-2); transition:all 100ms; {{ !$loop->last ? 'border-right:1px solid var(--rule);' : '' }}">
                                        {{ $opt }}
                                    </button>
                                    @endforeach
                                </div>
                                <input type="hidden" name="understood_rights" id="fbVal_understood_rights" value="yes">
                            </div>
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:8px;">Would you recommend the Hub?</label>
                                <div style="display: flex; border: 1px solid var(--rule);">
                                    @foreach(['Yes', 'Maybe', 'No'] as $opt)
                                    <button type="button" onclick="fbSetToggle('would_recommend', '{{ strtolower($opt) }}', this)"
                                            style="flex:1; padding:9px 0; font-size:12.5px; font-weight:600; border:none; cursor:pointer; font-family:inherit; background:var(--parchment); color:var(--ink-2); transition:all 100ms; {{ !$loop->last ? 'border-right:1px solid var(--rule);' : '' }}">
                                        {{ $opt }}
                                    </button>
                                    @endforeach
                                </div>
                                <input type="hidden" name="would_recommend" id="fbVal_would_recommend" value="yes">
                            </div>
                        </div>
                    </div>

                    {{-- Comment --}}
                    <div style="padding: 20px 24px; border-bottom: 1px solid var(--rule);">
                        <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Comment (optional)</label>
                        <textarea name="comment" rows="3" placeholder="Anything else you'd like to share — what worked, what didn't, what could be better."
                                  style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; resize:vertical; box-sizing:border-box; border-radius:2px; line-height:1.5;"></textarea>
                    </div>

                    {{-- Capture channel --}}
                    <div style="padding: 20px 24px; border-bottom: 1px solid var(--rule);">
                        <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:8px;">Capture Channel</label>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; border: 1px solid var(--rule);">
                            @foreach(['In-person' => 'in-person', 'SMS link' => 'sms', 'Phone follow-up' => 'phone'] as $label => $val)
                            <button type="button" onclick="fbSetToggle('channel', '{{ $val }}', this)"
                                    style="padding: 10px 0; font-size: 12.5px; font-weight: 600; border: none; cursor: pointer; font-family: inherit; transition: all 100ms; {{ $val === 'in-person' ? 'background:var(--forest);color:var(--cream);' : 'background:var(--parchment);color:var(--ink-2);' }} {{ !$loop->last ? 'border-right:1px solid var(--rule);' : '' }}">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                        <input type="hidden" name="channel" id="fbVal_channel" value="in-person">
                    </div>

                    {{-- Checkboxes --}}
                    <div style="padding: 18px 24px;">
                        <label style="display:flex; align-items:flex-start; gap:12px; padding:12px 14px; border:1px solid var(--rule); background:var(--paper); cursor:pointer; margin-bottom:8px;"
                               onmouseenter="this.style.borderColor='var(--ink-2)'" onmouseleave="this.style.borderColor='var(--rule)'">
                            <input type="checkbox" name="is_anonymous" value="1" style="width:15px;height:15px;margin-top:2px;flex-shrink:0;accent-color:var(--forest);">
                            <div>
                                <div style="font-size:13px; font-weight:600; color:var(--ink);">Submit anonymously</div>
                                <div style="font-size:11.5px; color:var(--ink-3); margin-top:2px;">The case stays linked, but the client name is replaced with &ldquo;Anonymous&rdquo;.</div>
                            </div>
                        </label>
                        <label style="display:flex; align-items:flex-start; gap:12px; padding:12px 14px; border:1px solid var(--rule); background:var(--paper); cursor:pointer;"
                               onmouseenter="this.style.borderColor='var(--ink-2)'" onmouseleave="this.style.borderColor='var(--rule)'">
                            <input type="checkbox" name="consent_to_share" value="1" checked style="width:15px;height:15px;margin-top:2px;flex-shrink:0;accent-color:var(--forest);">
                            <div>
                                <div style="font-size:13px; font-weight:600; color:var(--ink);">Consent to share comment in reports</div>
                                <div style="font-size:11.5px; color:var(--ink-3); margin-top:2px;">Without this, the score still counts toward indicators but the comment text is kept internal.</div>
                            </div>
                        </label>
                    </div>
                </form>
            </div>

            {{-- Footer --}}
            <div style="flex-shrink:0; padding:14px 24px; border-top:1px solid var(--rule); display:flex; align-items:center; justify-content:space-between;">
                <span style="font-size:11.5px; color:var(--ink-4);">Complete all 5 questions to save</span>
                <div style="display:flex; gap:10px;">
                    <button type="button" data-bs-dismiss="modal" class="btn-ghost">Cancel</button>
                    <button type="submit" form="fbForm" class="btn-primary" style="display:inline-flex; align-items:center; gap:7px;">
                        <x-lucide-check style="width:13px;height:13px;" /> Save feedback
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fbSetStar(field, rating) {
    document.getElementById('fbVal_' + field).value = rating;
    var container = document.getElementById('fbStars_' + field);
    container.querySelectorAll('button').forEach(function(btn) {
        var s = parseInt(btn.dataset.star);
        btn.style.color = s <= rating ? 'var(--ochre)' : 'var(--rule)';
    });
}

function fbSetToggle(field, value, btn) {
    document.getElementById('fbVal_' + field).value = value;
    var parent = btn.parentNode;
    parent.querySelectorAll('button').forEach(function(b) {
        b.style.background = 'var(--parchment)';
        b.style.color = 'var(--ink-2)';
    });
    btn.style.background = 'var(--forest)';
    btn.style.color = 'var(--cream)';
}

function fbUpdateCaseInfo(sel) {
    var opt = sel.options[sel.selectedIndex];
    var info = document.getElementById('fbCaseInfo');
    if (!opt || !opt.value) { info.textContent = ''; return; }
    var hub = opt.dataset.hub || '';
    var assigned = opt.dataset.assigned || '';
    info.textContent = (hub ? hub + ' Hub' : '') + (assigned ? ' · handled by ' + assigned : '');
}
</script>
@endif

<script>
function feedbackTrendChart(data) {
    return {
        init() {
            new Chart(this.$refs.chart, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Avg Score',
                        data: data.values,
                        borderColor: '#4a7a5c',
                        backgroundColor: 'rgba(74,122,92,0.08)',
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
                            min: 0, max: 5,
                            ticks: { stepSize: 1, font: { size: 10 } },
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
