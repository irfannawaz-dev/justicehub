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
        <div style="display:flex; gap:10px;">
            <button class="btn-ghost" onclick="jhOpenModal('detailed-feedback')" style="display:inline-flex; align-items:center; gap:6px;">
                <x-lucide-clipboard-list style="width:13px; height:13px;" /> Detailed Feedback
            </button>
            <button class="btn-primary" onclick="jhOpenModal('capture-feedback')">
                <x-lucide-plus style="width: 12px; height: 12px;" /> Capture feedback
            </button>
        </div>
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

    {{-- ═══ Detailed Surveys Table ═══ --}}
    @if($surveys->count() > 0)
    <div style="margin-top:36px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
            <div>
                <div class="label-cap" style="font-size:9px; margin-bottom:4px;">Experience Surveys</div>
                <div style="font-size:15px; font-weight:600; color:var(--ink);">Detailed Feedback Responses</div>
                <div style="font-size:12px; color:var(--ink-3); margin-top:2px;">{{ $surveys->total() }} survey{{ $surveys->total() !== 1 ? 's' : '' }} recorded</div>
            </div>
        </div>

        <div class="card" style="overflow-x:auto; padding:0;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="border-bottom:2px solid var(--rule); background:var(--surface);">
                        @foreach(['ID','Date','Case','Service','Hub','Access','Reception','Satisfaction','Trust','Recommend',''] as $col)
                        <th style="padding:10px 12px; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); text-align:{{ in_array($col, ['Access','Reception','Satisfaction','Trust']) ? 'center' : 'left' }}; white-space:nowrap;">
                            {{ $col }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $surveyScoreColor = function($v) {
                            if (!$v) return 'var(--ink-4)';
                            return $v >= 4 ? 'var(--forest)' : ($v >= 3 ? 'var(--ochre)' : 'var(--burgundy)');
                        };
                    @endphp
                    @foreach($surveys as $sv)
                    <tr style="border-bottom:1px solid var(--rule-2);" onmouseover="this.style.background='var(--parchment)'" onmouseout="this.style.background=''">
                        <td style="padding:10px 12px; font-weight:600;" class="mono">{{ $sv->survey_uid }}</td>
                        <td style="padding:10px 12px; white-space:nowrap;" class="mono">{{ $sv->created_at->format('M d, Y') }}</td>
                        <td style="padding:10px 12px; font-size:12px; color:var(--ink-2);">
                            @if($sv->case_id)
                            <a href="{{ route('cases.show', $sv->case_id) }}" style="color:var(--forest); text-decoration:none;">Case #{{ $sv->case_id }}</a>
                            @else —
                            @endif
                        </td>
                        <td style="padding:10px 12px; font-size:12px; color:var(--ink-2);">{{ $sv->service_type ?? '—' }}</td>
                        <td style="padding:10px 12px; font-size:11px; color:var(--ink-3);" class="mono">{{ $sv->hub_id }}</td>
                        <td style="padding:10px 12px; text-align:center;">
                            <span style="font-weight:700; color:{{ $surveyScoreColor($sv->q11_access) }};">{{ $sv->q11_access ?? '—' }}</span>
                        </td>
                        <td style="padding:10px 12px; text-align:center;">
                            <span style="font-weight:700; color:{{ $surveyScoreColor($sv->q12_reception) }};">{{ $sv->q12_reception ?? '—' }}</span>
                        </td>
                        <td style="padding:10px 12px; text-align:center;">
                            <span style="font-weight:700; font-size:14px; color:{{ $surveyScoreColor($sv->q28_satisfaction) }};">
                                {{ $sv->q28_satisfaction ?? '—' }}<span style="font-size:10px; color:var(--ink-4);">/5</span>
                            </span>
                        </td>
                        <td style="padding:10px 12px; text-align:center;">
                            <span style="font-weight:700; color:{{ $surveyScoreColor($sv->q31_trust) }};">{{ $sv->q31_trust ?? '—' }}</span>
                        </td>
                        <td style="padding:10px 12px;">
                            @if($sv->q30_recommend)
                            @php
                                $recColor = $sv->q30_recommend === 'Yes' ? 'var(--forest)' : ($sv->q30_recommend === 'Maybe' ? 'var(--ochre)' : 'var(--burgundy)');
                            @endphp
                            <span style="font-size:11px; font-weight:600; padding:2px 8px; background:{{ $recColor }}15; color:{{ $recColor }};">{{ $sv->q30_recommend }}</span>
                            @else — @endif
                        </td>
                        <td style="padding:10px 12px;">
                            <button class="btn-ghost" style="font-size:10px; padding:3px 8px;" onclick="dsViewSurvey({{ $sv->id }})" data-bs-toggle="collapse" data-bs-target="#sv-detail-{{ $sv->id }}">
                                View
                            </button>
                        </td>
                    </tr>
                    {{-- Expandable detail row --}}
                    <tr id="sv-detail-{{ $sv->id }}" class="collapse" style="background:var(--surface);">
                        <td colspan="11" style="padding:16px 24px;">
                            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; font-size:12px;">
                                <div>
                                    <div class="label-cap" style="font-size:8px; color:var(--ink-4); margin-bottom:6px;">Access & Welcome</div>
                                    <div style="display:grid; gap:4px;">
                                        <div>Access ease: <strong style="color:{{ $surveyScoreColor($sv->q11_access) }};">{{ $sv->q11_access ?? '—' }}/5</strong></div>
                                        <div>Staff reception: <strong style="color:{{ $surveyScoreColor($sv->q12_reception) }};">{{ $sv->q12_reception ?? '—' }}/5</strong></div>
                                        <div>Explanation: <strong style="color:{{ $surveyScoreColor($sv->q13_explanation) }};">{{ $sv->q13_explanation ?? '—' }}/5</strong></div>
                                        <div>Waiting time: <strong style="color:{{ $surveyScoreColor($sv->q14_waiting) }};">{{ $sv->q14_waiting ?? '—' }}/5</strong></div>
                                        @if($sv->q15_difficulty) <div>Difficulty: <strong>{{ $sv->q15_difficulty }}</strong></div> @endif
                                    </div>
                                </div>
                                <div>
                                    <div class="label-cap" style="font-size:8px; color:var(--ink-4); margin-bottom:6px;">Listening & Dignity</div>
                                    <div style="display:grid; gap:4px;">
                                        <div>Listened: <strong>{{ $sv->q16_listened ?? '—' }}</strong></div>
                                        <div>Comfortable: <strong>{{ $sv->q17_comfortable ?? '—' }}</strong></div>
                                        <div>Understood: <strong style="color:{{ $surveyScoreColor($sv->q18_understood) }};">{{ $sv->q18_understood ?? '—' }}/5</strong></div>
                                        <div>Fair treatment: <strong>{{ $sv->q19_fair_treatment ?? '—' }}</strong></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="label-cap" style="font-size:8px; color:var(--ink-4); margin-bottom:6px;">Confidentiality</div>
                                    <div style="display:grid; gap:4px;">
                                        <div>Info safety: <strong>{{ $sv->q20_info_safety ?? '—' }}</strong></div>
                                        <div>Data explained: <strong>{{ $sv->q21_data_explained ?? '—' }}</strong></div>
                                        <div>Confidence: <strong style="color:{{ $surveyScoreColor($sv->q22_confidence) }};">{{ $sv->q22_confidence ?? '—' }}/5</strong></div>
                                        <div>Complaint info: <strong>{{ $sv->q23_complaint_info ?? '—' }}</strong></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="label-cap" style="font-size:8px; color:var(--ink-4); margin-bottom:6px;">Service Quality</div>
                                    <div style="display:grid; gap:4px;">
                                        <div>Advice useful: <strong>{{ $sv->q24_advice_useful ?? '—' }}</strong></div>
                                        <div>Referral clarity: <strong>{{ $sv->q25_referral_clarity ?? '—' }}</strong></div>
                                        <div>Next steps: <strong>{{ $sv->q26_next_steps ?? '—' }}</strong></div>
                                        <div>Clarity: <strong>{{ $sv->q27_clarity ?? '—' }}</strong></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="label-cap" style="font-size:8px; color:var(--ink-4); margin-bottom:6px;">Satisfaction & Trust</div>
                                    <div style="display:grid; gap:4px;">
                                        <div>Satisfaction: <strong style="color:{{ $surveyScoreColor($sv->q28_satisfaction) }};">{{ $sv->q28_satisfaction ?? '—' }}/5</strong></div>
                                        <div>Resolution help: <strong>{{ $sv->q29_resolution_help ?? '—' }}</strong></div>
                                        <div>Recommend: <strong>{{ $sv->q30_recommend ?? '—' }}</strong></div>
                                        <div>Trust: <strong style="color:{{ $surveyScoreColor($sv->q31_trust) }};">{{ $sv->q31_trust ?? '—' }}/5</strong></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="label-cap" style="font-size:8px; color:var(--ink-4); margin-bottom:6px;">Open Feedback</div>
                                    <div style="display:grid; gap:4px; font-size:11px; color:var(--ink-2);">
                                        @if($sv->q32_helpful_part) <div><em>"{{ $sv->q32_helpful_part }}"</em></div> @endif
                                        @if($sv->q33_improvement) <div><em>"{{ $sv->q33_improvement }}"</em></div> @endif
                                        @if($sv->q34_additional) <div><em>"{{ $sv->q34_additional }}"</em></div> @endif
                                        @if(!$sv->q32_helpful_part && !$sv->q33_improvement && !$sv->q34_additional) <div style="color:var(--ink-4);">No open feedback</div> @endif
                                    </div>
                                </div>
                            </div>
                            <div style="margin-top:10px; font-size:10px; color:var(--ink-4);">
                                Recorded by {{ $sv->enumerator_name ?? '—' }} · {{ $sv->created_at->format('d M Y, H:i') }}
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($surveys->hasPages())
        <div style="margin-top:12px; display:flex; justify-content:center;">
            {{ $surveys->links() }}
        </div>
        @endif
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
                                {{ $c->case_uid }} — {{ $c->name }}
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

{{-- ═══ Detailed Feedback Survey Modal ═══ --}}
@if($canWrite)
<div class="modal fade" id="modal-detailed-feedback" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width:680px; margin:1.75rem auto; max-height:90vh;">
        <div class="modal-content" style="border:1px solid var(--rule); border-radius:4px; background:var(--parchment); box-shadow:0 16px 48px rgba(0,0,0,.18); display:flex; flex-direction:column; max-height:88vh;">

            {{-- Header --}}
            <div style="padding:18px 24px 14px; border-bottom:1px solid var(--rule); flex-shrink:0;">
                <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                    <div>
                        <div class="label-cap" style="font-size:9px; color:var(--ink-4); margin-bottom:4px;">Beneficiary Feedback & Experience Survey</div>
                        <h2 class="serif" style="font-size:22px; font-weight:400; margin:0;">
                            Section <span id="ds-section-num">A</span> · <span id="ds-section-title">Basic Information</span>
                        </h2>
                    </div>
                    <button type="button" data-bs-dismiss="modal" style="background:none; border:1px solid var(--rule); cursor:pointer; padding:5px 7px; color:var(--ink-3); border-radius:3px;">
                        <x-lucide-x style="width:14px;height:14px;" />
                    </button>
                </div>
                {{-- Progress bar --}}
                <div style="display:flex; gap:3px; margin-top:12px;">
                    @foreach(['A','B','C','D','E','F','G'] as $i => $sec)
                    <div id="ds-prog-{{ $sec }}" style="flex:1; height:3px; background:var(--rule-2); border-radius:2px; transition:background 200ms;">
                    </div>
                    @endforeach
                </div>
            </div>

            <form method="POST" action="{{ route('feedback.survey.store') }}" id="dsFeedbackForm">
                @csrf
                <div style="padding:20px 24px; overflow-y:auto; flex:1;">

                    {{-- ═══ SECTION A — Basic Information ═══ --}}
                    <div class="ds-section" data-section="A">
                        <div style="font-size:11px; color:var(--ink-4); margin-bottom:16px; line-height:1.5;">
                            Fields marked with * are required. Some fields auto-fill when a case is selected.
                        </div>

                        <div style="margin-bottom:14px;">
                            <label class="jh-field-label">Case Reference</label>
                            <select name="case_id" id="ds-case-select" class="inp" onchange="dsAutoFill(this)">
                                <option value="">— Select case (optional) —</option>
                                @foreach($cases as $c)
                                <option value="{{ $c->id }}"
                                    data-hub="{{ $c->hub_id ?? '' }}"
                                    data-name="{{ $c->name }}"
                                    data-pathway="{{ $c->assigned_pathway ?? '' }}"
                                    data-intake="{{ $c->intake_date?->format('Y-m-d') ?? '' }}"
                                    data-returning="{{ $c->returning_client ? '1' : '0' }}"
                                    data-consent="{{ $c->consent ? '1' : '0' }}">
                                    {{ $c->case_uid }} — {{ $c->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                            <div>
                                <label class="jh-field-label">4. Date of Visit</label>
                                <input type="date" name="visit_date" class="inp" value="{{ now()->format('Y-m-d') }}">
                            </div>
                            <div>
                                <label class="jh-field-label">5. Date Service Received</label>
                                <input type="date" name="service_date" class="inp">
                            </div>
                        </div>

                        <div style="margin-bottom:14px;">
                            <label class="jh-field-label">6. Type of Service Received *</label>
                            <select name="service_type" class="inp" required>
                                <option value="">— Select —</option>
                                <option value="Legal advice">Legal advice</option>
                                <option value="Mediation / ADR">Mediation / ADR</option>
                                <option value="Documentation support">Documentation support</option>
                                <option value="Referral to another service">Referral to another service</option>
                                <option value="Multiple services">Multiple services</option>
                            </select>
                        </div>

                        <div style="margin-bottom:14px;">
                            <label class="jh-field-label">7. First visit to the Justice Hub?</label>
                            <div style="display:flex; gap:8px;">
                                <label class="ds-toggle-btn" onclick="dsToggle('first_visit','yes',this)">Yes</label>
                                <label class="ds-toggle-btn" onclick="dsToggle('first_visit','no',this)">No</label>
                            </div>
                            <input type="hidden" name="first_visit" id="ds-first_visit" value="">
                        </div>

                        <div style="margin-bottom:14px;">
                            <label class="jh-field-label">Consent to participate? *</label>
                            <div style="display:flex; gap:8px;">
                                <label class="ds-toggle-btn ds-active" onclick="dsToggle('consent','yes',this)">Yes</label>
                                <label class="ds-toggle-btn" onclick="dsToggle('consent','no',this)">No</label>
                            </div>
                            <input type="hidden" name="consent" id="ds-consent" value="yes">
                        </div>
                    </div>

                    {{-- ═══ SECTION B — Access & Welcome ═══ --}}
                    <div class="ds-section" data-section="B" style="display:none;">
                        <div style="font-size:11px; color:var(--ink-4); margin-bottom:16px;">
                            Scale: 1 = Very Poor, 2 = Poor, 3 = Fair, 4 = Good, 5 = Very Good
                        </div>

                        @foreach([
                            ['q11_access', '11. How easy was it to access the Justice Hub?'],
                            ['q12_reception', '12. How respectfully were you received by staff?'],
                            ['q13_explanation', '13. Did staff explain their role and services clearly?'],
                            ['q14_waiting', '14. How satisfied were you with the waiting time?'],
                        ] as [$field, $label])
                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">{{ $label }}</label>
                            <div style="display:flex; gap:6px;">
                                @for($r = 1; $r <= 5; $r++)
                                <label class="ds-scale-btn" onclick="dsScale('{{ $field }}',{{ $r }},this)">{{ $r }}</label>
                                @endfor
                            </div>
                            <input type="hidden" name="{{ $field }}" id="ds-{{ $field }}" value="">
                        </div>
                        @endforeach

                        <div style="margin-bottom:14px;">
                            <label class="jh-field-label">15. Did you face any difficulty accessing services?</label>
                            <select name="q15_difficulty" class="inp">
                                <option value="">— Select —</option>
                                <option value="No">No</option>
                                <option value="Distance / transport">Distance / transport</option>
                                <option value="Timing">Timing</option>
                                <option value="Disability / accessibility">Disability / accessibility</option>
                                <option value="Language barrier">Language barrier</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    {{-- ═══ SECTION C — Listening & Dignity ═══ --}}
                    <div class="ds-section" data-section="C" style="display:none;">
                        @foreach([
                            ['q16_listened', '16. Do you feel your problem was properly listened to?', ['Yes, completely','To some extent','No']],
                            ['q17_comfortable', '17. Did you feel comfortable sharing your issue?', ['Yes, completely','Somewhat','No']],
                        ] as [$field, $label, $opts])
                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">{{ $label }}</label>
                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                @foreach($opts as $opt)
                                <label class="ds-toggle-btn" onclick="dsToggle('{{ $field }}','{{ $opt }}',this)">{{ $opt }}</label>
                                @endforeach
                            </div>
                            <input type="hidden" name="{{ $field }}" id="ds-{{ $field }}" value="">
                        </div>
                        @endforeach

                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">18. How well did staff understand your problem? (1-5)</label>
                            <div style="display:flex; gap:6px;">
                                @for($r = 1; $r <= 5; $r++)
                                <label class="ds-scale-btn" onclick="dsScale('q18_understood',{{ $r }},this)">{{ $r }}</label>
                                @endfor
                            </div>
                            <input type="hidden" name="q18_understood" id="ds-q18_understood" value="">
                        </div>

                        <div style="margin-bottom:14px;">
                            <label class="jh-field-label">19. Were you treated fairly regardless of gender, age, background?</label>
                            <div style="display:flex; gap:6px;">
                                @foreach(['Yes','Somewhat','No'] as $opt)
                                <label class="ds-toggle-btn" onclick="dsToggle('q19_fair_treatment','{{ $opt }}',this)">{{ $opt }}</label>
                                @endforeach
                            </div>
                            <input type="hidden" name="q19_fair_treatment" id="ds-q19_fair_treatment" value="">
                        </div>
                    </div>

                    {{-- ═══ SECTION D — Confidentiality ═══ --}}
                    <div class="ds-section" data-section="D" style="display:none;">
                        @foreach([
                            ['q20_info_safety', '20. Did you feel your information would not be shared without permission?', ['Yes','Somewhat','No']],
                            ['q21_data_explained', '21. Did staff explain how your data would be used?', ['Yes, clearly','Somewhat','No']],
                        ] as [$field, $label, $opts])
                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">{{ $label }}</label>
                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                @foreach($opts as $opt)
                                <label class="ds-toggle-btn" onclick="dsToggle('{{ $field }}','{{ $opt }}',this)">{{ $opt }}</label>
                                @endforeach
                            </div>
                            <input type="hidden" name="{{ $field }}" id="ds-{{ $field }}" value="">
                        </div>
                        @endforeach

                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">22. How confident are you that your information is handled safely? (1-5)</label>
                            <div style="display:flex; gap:6px;">
                                @for($r = 1; $r <= 5; $r++)
                                <label class="ds-scale-btn" onclick="dsScale('q22_confidence',{{ $r }},this)">{{ $r }}</label>
                                @endfor
                            </div>
                            <input type="hidden" name="q22_confidence" id="ds-q22_confidence" value="">
                        </div>

                        <div style="margin-bottom:14px;">
                            <label class="jh-field-label">23. Were you informed about how to raise a complaint?</label>
                            <div style="display:flex; gap:6px;">
                                @foreach(["Yes","No","Don't remember"] as $opt)
                                <label class="ds-toggle-btn" onclick="dsToggle('q23_complaint_info','{{ $opt }}',this)">{{ $opt }}</label>
                                @endforeach
                            </div>
                            <input type="hidden" name="q23_complaint_info" id="ds-q23_complaint_info" value="">
                        </div>
                    </div>

                    {{-- ═══ SECTION E — Service Quality ═══ --}}
                    <div class="ds-section" data-section="E" style="display:none;">
                        @foreach([
                            ['q24_advice_useful', '24. Was the advice or support useful?', ['Yes, very useful','Somewhat useful','Not useful']],
                            ['q25_referral_clarity', '25. If referred, was the referral clear?', ['Clear and explained properly','Somewhat clear','Not clear']],
                            ['q26_next_steps', '26. Did staff help you understand next steps?', ['Yes, clearly','Somewhat','No']],
                            ['q27_clarity', '27. How clear are you about what to do next?', ['Very clear','Clear','Neutral','Unclear','Very unclear']],
                        ] as [$field, $label, $opts])
                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">{{ $label }}</label>
                            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                @foreach($opts as $opt)
                                <label class="ds-toggle-btn" onclick="dsToggle('{{ $field }}','{{ $opt }}',this)">{{ $opt }}</label>
                                @endforeach
                            </div>
                            <input type="hidden" name="{{ $field }}" id="ds-{{ $field }}" value="">
                        </div>
                        @endforeach
                    </div>

                    {{-- ═══ SECTION F — Satisfaction & Trust ═══ --}}
                    <div class="ds-section" data-section="F" style="display:none;">
                        <div style="font-size:11px; color:var(--ink-4); margin-bottom:16px;">
                            Scale: 1 = Very Dissatisfied, 5 = Very Satisfied
                        </div>

                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">28. Overall, how satisfied are you with Justice Hub services?</label>
                            <div style="display:flex; gap:6px;">
                                @for($r = 1; $r <= 5; $r++)
                                <label class="ds-scale-btn" onclick="dsScale('q28_satisfaction',{{ $r }},this)">{{ $r }}</label>
                                @endfor
                            </div>
                            <input type="hidden" name="q28_satisfaction" id="ds-q28_satisfaction" value="">
                        </div>

                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">29. Did visiting the Hub help resolve your problem?</label>
                            <div style="display:flex; gap:6px;">
                                @foreach(['Yes','Somewhat','No'] as $opt)
                                <label class="ds-toggle-btn" onclick="dsToggle('q29_resolution_help','{{ $opt }}',this)">{{ $opt }}</label>
                                @endforeach
                            </div>
                            <input type="hidden" name="q29_resolution_help" id="ds-q29_resolution_help" value="">
                        </div>

                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">30. Would you recommend the Justice Hub?</label>
                            <div style="display:flex; gap:6px;">
                                @foreach(['Yes','Maybe','No'] as $opt)
                                <label class="ds-toggle-btn" onclick="dsToggle('q30_recommend','{{ $opt }}',this)">{{ $opt }}</label>
                                @endforeach
                            </div>
                            <input type="hidden" name="q30_recommend" id="ds-q30_recommend" value="">
                        </div>

                        <div style="margin-bottom:14px;">
                            <label class="jh-field-label">31. How much do you trust the Justice Hub? (1-5)</label>
                            <div style="display:flex; gap:6px;">
                                @for($r = 1; $r <= 5; $r++)
                                <label class="ds-scale-btn" onclick="dsScale('q31_trust',{{ $r }},this)">{{ $r }}</label>
                                @endfor
                            </div>
                            <input type="hidden" name="q31_trust" id="ds-q31_trust" value="">
                        </div>
                    </div>

                    {{-- ═══ SECTION G — Open Feedback ═══ --}}
                    <div class="ds-section" data-section="G" style="display:none;">
                        <div style="font-size:11px; color:var(--ink-4); margin-bottom:16px;">
                            These questions are optional. Please capture the beneficiary's own words.
                        </div>

                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">32. What was the most helpful part of your visit?</label>
                            <textarea name="q32_helpful_part" rows="3" class="inp" placeholder="In their own words..." style="width:100%; resize:vertical; box-sizing:border-box;"></textarea>
                        </div>

                        <div style="margin-bottom:16px;">
                            <label class="jh-field-label">33. What can be improved in Justice Hub services?</label>
                            <textarea name="q33_improvement" rows="3" class="inp" placeholder="Suggestions for improvement..." style="width:100%; resize:vertical; box-sizing:border-box;"></textarea>
                        </div>

                        <div style="margin-bottom:14px;">
                            <label class="jh-field-label">34. Any additional comments or suggestions?</label>
                            <textarea name="q34_additional" rows="3" class="inp" placeholder="Additional feedback..." style="width:100%; resize:vertical; box-sizing:border-box;"></textarea>
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div style="padding:12px 24px; border-top:1px solid var(--rule); display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
                    <button type="button" id="ds-back-btn" class="btn-ghost" onclick="dsNav(-1)" style="display:none;">← Back</button>
                    <div id="ds-section-hint" style="font-size:11px; color:var(--ink-4);">Section 1 of 7</div>
                    <div style="display:flex; gap:8px;">
                        <button type="button" data-bs-dismiss="modal" class="btn-ghost">Cancel</button>
                        <button type="button" id="ds-next-btn" class="btn-primary" onclick="dsNav(1)" style="display:inline-flex; align-items:center; gap:6px;">
                            Continue <x-lucide-chevron-right style="width:12px;height:12px;" />
                        </button>
                        <button type="submit" id="ds-submit-btn" class="btn-primary" style="background:var(--moss); display:none;">
                            <x-lucide-check-circle-2 style="width:13px;height:13px;" /> Submit Survey
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .ds-toggle-btn {
        display:inline-flex; align-items:center; padding:8px 14px; border:2px solid var(--rule);
        cursor:pointer; font-size:12px; font-weight:500; color:var(--ink-2); transition:all 120ms;
        user-select:none;
    }
    .ds-toggle-btn:hover { border-color:var(--ink-3); }
    .ds-toggle-btn.ds-active { border-color:var(--forest); color:var(--forest); background:rgba(22,48,41,0.04); font-weight:600; }

    .ds-scale-btn {
        display:inline-flex; align-items:center; justify-content:center; width:44px; height:40px;
        border:2px solid var(--rule); cursor:pointer; font-size:14px; font-weight:600; color:var(--ink-3);
        transition:all 120ms; user-select:none;
    }
    .ds-scale-btn:hover { border-color:var(--ink-3); }
    .ds-scale-btn.ds-active { border-color:var(--forest); color:#fff; background:var(--forest); }
</style>

<script>
(function() {
    var sections = ['A','B','C','D','E','F','G'];
    var titles   = ['Basic Information','Access & Welcome','Listening & Dignity','Confidentiality & Consent','Service Quality & Referrals','Satisfaction & Trust','Open Feedback'];
    var step = 0;

    window.dsNav = function(dir) {
        step = Math.max(0, Math.min(sections.length - 1, step + dir));
        document.querySelectorAll('.ds-section').forEach(function(el) {
            el.style.display = el.dataset.section === sections[step] ? '' : 'none';
        });
        document.getElementById('ds-section-num').textContent   = sections[step];
        document.getElementById('ds-section-title').textContent = titles[step];
        document.getElementById('ds-section-hint').textContent  = 'Section ' + (step + 1) + ' of 7';
        document.getElementById('ds-back-btn').style.display    = step === 0 ? 'none' : '';
        document.getElementById('ds-next-btn').style.display    = step === sections.length - 1 ? 'none' : '';
        document.getElementById('ds-submit-btn').style.display  = step === sections.length - 1 ? '' : 'none';
        sections.forEach(function(s, i) {
            document.getElementById('ds-prog-' + s).style.background = i <= step ? 'var(--forest)' : 'var(--rule-2)';
        });
    };
    dsNav(0);

    window.dsToggle = function(field, value, btn) {
        btn.parentElement.querySelectorAll('.ds-toggle-btn').forEach(function(b) { b.classList.remove('ds-active'); });
        btn.classList.add('ds-active');
        document.getElementById('ds-' + field).value = value;
    };

    window.dsScale = function(field, rating, btn) {
        btn.parentElement.querySelectorAll('.ds-scale-btn').forEach(function(b) { b.classList.remove('ds-active'); });
        btn.classList.add('ds-active');
        document.getElementById('ds-' + field).value = rating;
    };

    window.dsAutoFill = function(sel) {
        var opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) return;

        // Auto-fill service type from pathway
        var pathway = (opt.dataset.pathway || '').trim();
        var serviceMap = {
            'Legal Advice / Consultation': 'Legal advice',
            'Court Representation':        'Legal advice',
            'Mediation':                   'Mediation / ADR',
            'ADR / Dispute Resolution Support': 'Mediation / ADR',
            'Referral':                    'Referral to another service',
            'Government Department / Public Institution': 'Referral to another service',
            'Civil Society / NGO / CSO / NPO': 'Referral to another service',
        };
        var serviceSel = document.querySelector('[name="service_type"]');
        if (serviceSel && serviceMap[pathway]) {
            serviceSel.value = serviceMap[pathway];
        }

        // Auto-fill visit date from intake_date
        var intakeDate = opt.dataset.intake || '';
        if (intakeDate) {
            document.querySelector('[name="visit_date"]').value = intakeDate;
        }

        // Auto-fill first visit (returning_client = 1 means NOT first visit)
        var returning = opt.dataset.returning;
        var firstVisitVal = returning === '1' ? 'no' : 'yes';
        document.getElementById('ds-first_visit').value = firstVisitVal;
        var firstBtns = document.getElementById('ds-first_visit').parentElement.querySelectorAll('.ds-toggle-btn');
        firstBtns.forEach(function(b) {
            b.classList.toggle('ds-active', b.textContent.trim().toLowerCase() === firstVisitVal);
        });

        // Auto-fill consent
        var consent = opt.dataset.consent;
        var consentVal = consent === '1' ? 'yes' : 'no';
        document.getElementById('ds-consent').value = consentVal;
        var consentBtns = document.getElementById('ds-consent').parentElement.querySelectorAll('.ds-toggle-btn');
        consentBtns.forEach(function(b) {
            b.classList.toggle('ds-active', b.textContent.trim().toLowerCase() === consentVal);
        });
    };
})();
</script>
@endif
</x-layouts.app>
