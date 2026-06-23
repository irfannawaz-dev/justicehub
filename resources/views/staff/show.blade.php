<x-layouts.app>
@php
    $statusColor = match($staff->status) {
        'active'   => 'var(--moss)',
        'on-leave' => 'var(--ochre)',
        default    => 'var(--ink-3)',
    };
    $compColor = $staff->compliance_pct >= 100 ? 'var(--moss)'
               : ($staff->compliance_pct >= 60  ? 'var(--ochre)' : 'var(--burgundy)');
@endphp

<div style="padding: 28px 36px 64px; max-width: 1320px; margin: 0 auto;">

    {{-- Breadcrumb --}}
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 22px; font-size: 12px; color: var(--ink-3);">
        <a href="{{ route('staff.index') }}" style="color: var(--ink-3); text-decoration: none; display: flex; align-items: center; gap: 4px;">
            <x-lucide-arrow-left style="width:12px;height:12px;" /> Staff &amp; Training
        </a>
        <span style="color: var(--rule);">/</span>
        <span>{{ $staff->name }}</span>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div style="background: rgba(74,122,92,0.1); border: 1px solid rgba(74,122,92,0.3); padding: 10px 16px; margin-bottom: 16px; font-size: 13px; color: var(--moss);">
        {{ session('success') }}
    </div>
    @endif

    {{-- ═══ Profile Hero ═══ --}}
    <div style="display: grid; grid-template-columns: auto 1fr; gap: 24px; align-items: start; margin-bottom: 28px; padding-bottom: 24px; border-bottom: 1px solid var(--rule);">

        {{-- Avatar --}}
        <div style="width: 72px; height: 72px; background: var(--forest); color: var(--cream); display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 600; font-family: inherit; flex-shrink: 0;">
            {{ $staff->initials }}
        </div>

        {{-- Info --}}
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <h1 class="serif" style="font-size: 30px; font-weight: 400; letter-spacing: -0.015em; margin: 0;">{{ $staff->name }}</h1>
                <span style="font-size: 10px; padding: 3px 8px; background: {{ $statusColor }}; color: var(--cream); font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;">{{ $staff->status }}</span>
            </div>
            <div style="font-size: 14px; color: var(--ink-2); margin-bottom: 6px;">{{ $staff->role }}</div>
            <div style="display: flex; align-items: center; gap: 20px; font-size: 12px; color: var(--ink-3);">
                <span style="display: flex; align-items: center; gap: 5px;">
                    <x-lucide-map-pin style="width:11px;height:11px;" />
                    {{ $staff->hub?->name ?? $staff->hub_id }}
                </span>
                <span class="mono" style="font-size: 10px;">{{ $staff->staff_uid }}</span>
                @if($staff->joined_date)
                <span style="display: flex; align-items: center; gap: 5px;">
                    <x-lucide-calendar style="width:11px;height:11px;" />
                    Joined {{ $staff->joined_date->format('M Y') }}
                </span>
                @endif
                @if($staff->user?->email)
                <span style="display: flex; align-items: center; gap: 5px;">
                    <x-lucide-mail style="width:11px;height:11px;" />
                    {{ $staff->user->email }}
                </span>
                @endif
            </div>
        </div>

        {{-- Compliance summary (top-right) --}}
        <div></div>
        <div style="display: flex; gap: 14px; align-items: stretch;">
            <div class="card" style="padding: 14px 20px; border-top: 3px solid {{ $compColor }}; display: flex; flex-direction: column; justify-content: space-between; min-width: 130px;">
                <div class="label-cap" style="font-size: 9px; color: {{ $compColor }};">Compliance</div>
                <div class="serif" style="font-size: 34px; font-weight: 500; color: {{ $compColor }}; margin-top: 6px;">{{ $staff->compliance_pct }}<span style="font-size: 16px;">%</span></div>
                <div style="height: 4px; background: var(--rule); border-radius: 2px; margin-top: 8px; overflow: hidden;">
                    <div style="height: 100%; width: {{ $staff->compliance_pct }}%; background: {{ $compColor }};"></div>
                </div>
            </div>
            <div class="card" style="padding: 14px 20px; min-width: 120px;">
                <div class="label-cap" style="font-size: 9px;">Mandatory done</div>
                <div class="serif" style="font-size: 34px; font-weight: 500; margin-top: 6px;">
                    {{ $staff->trainings->whereIn('code', $required->pluck('code'))->count() }}<span style="font-size: 16px; color: var(--ink-3);"> / {{ $required->count() }}</span>
                </div>
            </div>
            <div class="card" style="padding: 14px 20px; min-width: 120px;">
                <div class="label-cap" style="font-size: 9px;">Total completions</div>
                <div class="serif" style="font-size: 34px; font-weight: 500; margin-top: 6px;">{{ $staff->trainings->count() }}</div>
            </div>
        </div>
    </div>

    {{-- ═══ Two-column layout ═══ --}}
    <div style="display: grid; grid-template-columns: 1fr 320px; gap: 24px; align-items: start;">

        {{-- Training matrix --}}
        <div>
            {{-- Mandatory trainings --}}
            <div style="margin-bottom: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <div>
                        <div class="label-cap" style="font-size: 9px; margin-bottom: 2px;">Mandatory trainings for {{ $staff->role }}</div>
                        <div style="font-size: 12px; color: var(--ink-3);">{{ $required->count() }} required</div>
                    </div>
                    @if($canWrite)
                    <button class="btn-primary" style="font-size: 11.5px;" onclick="jhOpenModal('log-training-{{ $staff->id }}')">
                        <x-lucide-plus style="width:12px;height:12px;" /> Log training
                    </button>
                    @endif
                </div>

                @if($required->count() === 0)
                <div class="card" style="padding: 20px; text-align: center; color: var(--ink-3); font-size: 13px;">
                    No mandatory trainings defined for role "{{ $staff->role }}".
                </div>
                @else
                <div class="card" style="padding: 0; overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="background: var(--paper); border-bottom: 2px solid var(--rule);">
                                <th style="text-align: left; padding: 10px 16px; color: var(--ink-3); font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em;">Training</th>
                                <th style="text-align: center; padding: 10px 16px; color: var(--ink-3); font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em;">Completed</th>
                                <th style="text-align: center; padding: 10px 16px; color: var(--ink-3); font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em;">Expires</th>
                                <th style="text-align: center; padding: 10px 16px; color: var(--ink-3); font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em;">Status</th>
                                <th style="text-align: left; padding: 10px 16px; color: var(--ink-3); font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em;">Delivered by</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($required as $req)
                            @php
                                $done  = $staff->trainings->firstWhere('code', $req->code);
                                $pivot = $done?->pivot;
                                $status = $trainingStatus[$req->code] ?? null;
                                [$badgeBg, $badgeText, $badgeLabel] = match(true) {
                                    !$done                  => ['var(--burgundy-tint)', 'var(--burgundy)', 'Missing'],
                                    $status === 'expired'   => ['var(--burgundy-tint)', 'var(--burgundy)', 'Expired'],
                                    $status === 'expiring'  => ['var(--ochre-tint)',    'var(--ochre)',    'Expiring'],
                                    default                 => ['var(--moss-tint)',     'var(--moss)',     'Current'],
                                };
                            @endphp
                            <tr style="border-bottom: 1px solid var(--rule-2);">
                                <td style="padding: 12px 16px;">
                                    <div style="font-weight: 500; line-height: 1.3;">{{ $req->name }}</div>
                                    <div style="font-size: 10.5px; color: var(--ink-3); margin-top: 2px;">
                                        <span class="mono">{{ $req->code }}</span>
                                        @if($req->mandatory) · <span style="color: var(--burgundy); font-weight: 600;">mandatory</span> @endif
                                    </div>
                                </td>
                                <td style="padding: 12px 16px; text-align: center; font-size: 12px; color: var(--ink-2);">
                                    {{ $pivot?->completed_on ? \Carbon\Carbon::parse($pivot->completed_on)->format('d M Y') : '—' }}
                                </td>
                                <td style="padding: 12px 16px; text-align: center; font-size: 12px; color: var(--ink-2);">
                                    {{ $pivot?->expires ? \Carbon\Carbon::parse($pivot->expires)->format('d M Y') : ($req->refresh === 'one-off' ? 'One-off' : '—') }}
                                </td>
                                <td style="padding: 12px 16px; text-align: center;">
                                    <span style="font-size: 10px; padding: 2px 8px; background: {{ $badgeBg }}; color: {{ $badgeText }}; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase;">{{ $badgeLabel }}</span>
                                </td>
                                <td style="padding: 12px 16px; font-size: 12px; color: var(--ink-3);">
                                    {{ $pivot?->delivered_by ?? '—' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Optional / additional trainings --}}
            @php
                $extra = $staff->trainings->filter(fn($t) => !$required->contains('code', $t->code));
            @endphp
            @if($extra->count())
            <div>
                <div class="label-cap" style="font-size: 9px; margin-bottom: 12px;">Additional completions ({{ $extra->count() }})</div>
                <div class="card" style="padding: 0; overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="background: var(--paper); border-bottom: 2px solid var(--rule);">
                                <th style="text-align: left; padding: 10px 16px; color: var(--ink-3); font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em;">Training</th>
                                <th style="text-align: center; padding: 10px 16px; color: var(--ink-3); font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em;">Completed</th>
                                <th style="text-align: left; padding: 10px 16px; color: var(--ink-3); font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em;">Delivered by</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($extra as $t)
                            <tr style="border-bottom: 1px solid var(--rule-2);">
                                <td style="padding: 11px 16px;">
                                    <div style="font-weight: 500;">{{ $t->name }}</div>
                                    <span class="mono" style="font-size: 10px; color: var(--ink-3);">{{ $t->code }}</span>
                                </td>
                                <td style="padding: 11px 16px; text-align: center; font-size: 12px; color: var(--ink-2);">
                                    {{ $t->pivot->completed_on ? \Carbon\Carbon::parse($t->pivot->completed_on)->format('d M Y') : '—' }}
                                </td>
                                <td style="padding: 11px 16px; font-size: 12px; color: var(--ink-3);">{{ $t->pivot->delivered_by ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- Right sidebar --}}
        <div style="display: flex; flex-direction: column; gap: 16px;">

            {{-- Staff details card --}}
            <div class="card" style="padding: 18px 20px;">
                <div class="label-cap" style="font-size: 9px; margin-bottom: 12px;">Staff details</div>
                <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                    @foreach([
                        ['Hub', $staff->hub?->name ?? $staff->hub_id],
                        ['Role', $staff->role],
                        ['UID', $staff->staff_uid],
                        ['Status', ucfirst($staff->status)],
                        ['Joined', $staff->joined_date?->format('d M Y') ?? '—'],
                    ] as [$label, $val])
                    <tr style="border-bottom: 1px solid var(--rule-2);">
                        <td style="padding: 7px 0; color: var(--ink-3); font-weight: 600; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em; width: 45%;">{{ $label }}</td>
                        <td style="padding: 7px 0; font-weight: 500;">{{ $val }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>

        </div>
    </div>
</div>

{{-- Log training modal --}}
@if($canWrite)
<div id="modal-log-training-{{ $staff->id }}" style="display:none; position:fixed; inset:0; z-index:200; background:rgba(0,0,0,0.45); align-items:center; justify-content:center;">
    <div class="card" style="width:440px; padding:24px; position:relative;">
        <button onclick="jhCloseModal('log-training-{{ $staff->id }}')" style="position:absolute; top:14px; right:14px; background:none; border:none; cursor:pointer; color:var(--ink-3);">
            <x-lucide-x style="width:16px;height:16px;" />
        </button>
        <h3 class="serif" style="font-size:20px; font-weight:400; margin:0 0 18px 0;">Log training — {{ $staff->name }}</h3>
        <form method="POST" action="{{ route('staff.training', $staff) }}">
            @csrf
            <div style="display:flex; flex-direction:column; gap:14px;">
                <div>
                    <label class="label-cap" style="font-size:9.5px; display:block; margin-bottom:5px;">Training</label>
                    <select name="training_code" required style="width:100%; padding:8px 10px; border:1px solid var(--rule); background:var(--paper); font-size:13px; font-family:inherit;">
                        <option value="">— select —</option>
                        @foreach($trainings as $t)
                        <option value="{{ $t->code }}">{{ $t->name }} ({{ $t->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label class="label-cap" style="font-size:9.5px; display:block; margin-bottom:5px;">Completed on</label>
                        <input type="date" name="completed_on" required max="{{ now()->toDateString() }}"
                               style="width:100%; padding:8px 10px; border:1px solid var(--rule); background:var(--paper); font-size:13px; font-family:inherit;">
                    </div>
                    <div>
                        <label class="label-cap" style="font-size:9.5px; display:block; margin-bottom:5px;">Expires (optional)</label>
                        <input type="date" name="expires"
                               style="width:100%; padding:8px 10px; border:1px solid var(--rule); background:var(--paper); font-size:13px; font-family:inherit;">
                    </div>
                </div>
                <div>
                    <label class="label-cap" style="font-size:9.5px; display:block; margin-bottom:5px;">Delivered by</label>
                    <input type="text" name="delivered_by" required placeholder="Trainer / organisation"
                           style="width:100%; padding:8px 10px; border:1px solid var(--rule); background:var(--paper); font-size:13px; font-family:inherit;">
                </div>
                <button type="submit" class="btn-primary" style="width:100%; justify-content:center;">Save training record</button>
            </div>
        </form>
    </div>
</div>
@endif
</x-layouts.app>
