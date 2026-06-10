<x-layouts.app>
@php $canWrite = auth()->user()->canWrite(); @endphp

<div style="padding: 24px 34px 64px; max-width: 1600px; margin: 0 auto;">

    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 22px;">
        <div>
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">People & Capability</div>
            <h1 class="serif" style="font-size: 34px; font-weight: 400; letter-spacing: -0.02em; margin: 0;">
                Staff &amp; Training
            </h1>
            <p style="margin: 6px 0 0 0; font-size: 13px; color: var(--ink-3);">
                {{ $staff->count() }} staff · {{ $compliantCount }} fully compliant · {{ $compliancePct }}% compliance rate
            </p>
        </div>
        @if($canWrite)
        <button class="btn-primary" onclick="jhOpenModal('log-training')">
            <x-lucide-plus style="width:14px;height:14px;" /> Log training completion
        </button>
        @endif
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div style="background: rgba(74,122,92,0.1); border: 1px solid rgba(74,122,92,0.3); border-radius: 6px; padding: 10px 16px; margin-bottom: 16px; font-size: 13px; color: var(--moss);">
        {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div style="background: rgba(139,30,30,0.08); border: 1px solid rgba(139,30,30,0.25); border-radius: 6px; padding: 10px 16px; margin-bottom: 16px; font-size: 13px; color: var(--burgundy);">
        <strong>Please fix these errors:</strong>
        <ul style="margin: 4px 0 0 16px; padding: 0;">
            @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ═══ Compliance Strip ═══ --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 22px;">
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--moss);">
            <div class="label-cap" style="font-size: 9px;">Compliance Rate</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px; color: var(--moss);">{{ $compliancePct }}<span style="font-size: 16px;">%</span></div>
            <div style="height: 4px; background: var(--rule); border-radius: 2px; margin-top: 8px; overflow: hidden;">
                <div style="height: 100%; width: {{ $compliancePct }}%; background: var(--moss); border-radius: 2px;"></div>
            </div>
        </div>
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--forest);">
            <div class="label-cap" style="font-size: 9px;">Fully Compliant</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px;">{{ $compliantCount }}</div>
            <div style="font-size: 11px; color: var(--ink-3);">of {{ $staff->count() }} active staff</div>
        </div>
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--ochre);">
            <div class="label-cap" style="font-size: 9px;">Expiring Soon</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px; color: {{ $expiring->count() > 0 ? 'var(--ochre)' : 'inherit' }};">{{ $expiring->count() }}</div>
            <div style="font-size: 11px; color: var(--ink-3);">within 30 days</div>
        </div>
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--burgundy);">
            <div class="label-cap" style="font-size: 9px;">Mandatory Trainings</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px;">{{ $mandatoryTrainings->count() }}</div>
            <div style="font-size: 11px; color: var(--ink-3);">in training catalog</div>
        </div>
    </div>

    {{-- Expiring soon alert --}}
    @if($expiring->count() > 0)
    <div style="background: rgba(184,115,25,0.08); border: 1px solid rgba(184,115,25,0.3); border-radius: 8px; padding: 12px 16px; margin-bottom: 18px; display: flex; align-items: flex-start; gap: 10px;">
        <x-lucide-alert-triangle style="width:14px;height:14px;color:var(--ochre);flex-shrink:0;margin-top:1px;" />
        <div style="font-size: 12px; color: var(--ink-2);">
            <strong>Trainings expiring within 30 days:</strong>
            {{ $expiring->map(fn($e) => $e['staff'] . ' (' . $e['code'] . ', exp ' . \Carbon\Carbon::parse($e['expires'])->format('M d') . ')')->implode(' · ') }}
        </div>
    </div>
    @endif

    {{-- ═══ Training Compliance Matrix ═══ --}}
    @foreach($grouped as $hubId => $hubStaff)
    <div style="margin-bottom: 28px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
            <h2 style="font-size: 12px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: var(--ink-3); margin: 0;">
                {{ $hubStaff->first()->hub?->name ?? 'Unassigned' }}
            </h2>
            <div style="flex: 1; height: 1px; background: var(--rule);"></div>
        </div>

        <div class="card" style="padding: 0; overflow: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; min-width: 700px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--rule);">
                        <th style="text-align: left; padding: 9px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3); min-width: 160px; position: sticky; left: 0; background: var(--surface);">Staff</th>
                        <th style="text-align: left; padding: 9px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3); width: 60px;">Role</th>
                        <th style="text-align: center; padding: 9px 8px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3); width: 50px;">%</th>
                        @foreach($trainings as $training)
                        <th style="text-align: center; padding: 9px 6px; font-size: 9px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: {{ $training->mandatory ? 'var(--ink-2)' : 'var(--ink-4)' }}; max-width: 60px;">
                            <span title="{{ $training->name }}">{{ $training->code }}</span>
                            @if($training->mandatory)<span style="color: var(--burgundy);">*</span>@endif
                        </th>
                        @endforeach
                        <th style="padding: 9px 14px; width: 60px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hubStaff as $s)
                    <tr style="border-bottom: 1px solid var(--rule-2);">
                        <td style="padding: 11px 14px; position: sticky; left: 0; background: var(--surface); z-index: 1;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: var(--forest); color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0;">
                                    {{ $s->initials }}
                                </div>
                                <div>
                                    <div style="font-size: 12px; font-weight: 500; color: var(--ink-1);">{{ $s->name }}</div>
                                    @if(!$s->is_compliant)
                                    <div style="font-size: 9px; color: var(--burgundy); font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; margin-top: 1px;">Non-compliant</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="padding: 11px 14px; font-size: 11px; color: var(--ink-3);">{{ $s->role }}</td>
                        <td style="padding: 11px 8px; text-align: center;">
                            <span style="font-size: 11px; font-weight: 700; color: {{ $s->compliance_pct >= 100 ? 'var(--moss)' : ($s->compliance_pct >= 70 ? 'var(--ochre)' : 'var(--burgundy)') }};">
                                {{ $s->compliance_pct }}%
                            </span>
                        </td>
                        @foreach($trainings as $training)
                        @php
                            $status = $s->training_status[$training->code] ?? null;
                            $isRequired = $s->required_trainings->contains('code', $training->code);
                            $cellBg = match($status) {
                                'current'  => 'rgba(74,122,92,0.12)',
                                'expiring' => 'rgba(184,115,25,0.12)',
                                'expired'  => 'rgba(139,30,30,0.12)',
                                default    => $isRequired ? 'rgba(139,30,30,0.06)' : 'transparent',
                            };
                            $cellColor = match($status) {
                                'current'  => 'var(--moss)',
                                'expiring' => 'var(--ochre)',
                                'expired'  => 'var(--burgundy)',
                                default    => $isRequired ? 'var(--burgundy)' : 'var(--ink-4)',
                            };
                            $cellIcon = match($status) {
                                'current'  => '✓',
                                'expiring' => '⚠',
                                'expired'  => '✗',
                                default    => $isRequired ? '—' : '·',
                            };
                        @endphp
                        <td style="padding: 11px 6px; text-align: center; background: {{ $cellBg }};">
                            <span style="font-size: 13px; color: {{ $cellColor }}; font-weight: 600;" title="{{ $status ?? 'not completed' }}">
                                {{ $cellIcon }}
                            </span>
                        </td>
                        @endforeach
                        <td style="padding: 11px 14px; text-align: right;">
                            @if($canWrite)
                            <button class="btn-ghost" style="font-size: 11px; padding: 3px 8px;"
                                @click="$dispatch('open-modal-log-training', { staffId: {{ $s->id }}, staffName: '{{ addslashes($s->name) }}' })">
                                Log
                            </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    <div style="font-size: 11px; color: var(--ink-4); margin-top: 8px;">
        ✓ Current &nbsp;&nbsp; ⚠ Expiring (within 3 months) &nbsp;&nbsp; ✗ Expired &nbsp;&nbsp; — Required but not completed &nbsp;&nbsp; * Mandatory training
    </div>
</div>

{{-- ═══ Log Training Modal ═══ --}}
@if($canWrite)
<div class="modal fade" id="modal-log-training" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 520px; margin: 1.75rem auto;">
        <div class="modal-content" style="border: 1px solid var(--rule); border-radius: 4px; background: var(--parchment); box-shadow: 0 16px 48px rgba(0,0,0,.18);">

            <div style="padding: 22px 24px 16px; border-bottom: 1px solid var(--rule);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 6px;">New Training Record</div>
                        <h2 class="serif" style="font-size: 26px; font-weight: 400; margin: 0;">
                            Log <em style="color: var(--ochre);">training</em> completion
                        </h2>
                    </div>
                    <button type="button" data-bs-dismiss="modal" style="background:none; border:1px solid var(--rule); cursor:pointer; padding:6px 8px; color:var(--ink-3); border-radius:3px;">
                        <x-lucide-x style="width:15px;height:15px;" />
                    </button>
                </div>
            </div>

            <form method="POST" action="" id="trainingForm">
                @csrf
                <div style="padding: 22px 24px;">

                    <div style="margin-bottom: 16px;">
                        <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Staff Member <span style="color:var(--burgundy);">*</span></label>
                        @php
                            $staffUsers = \App\Models\User::where('is_active', true)
                                ->where('is_ghost', false)
                                ->orderBy('name')
                                ->get(['id', 'name', 'role', 'hub_id']);
                        @endphp
                        <select name="staff_id_select" id="trainingStaffSelect" required onchange="trainingSetAction(this.value)"
                                style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px; appearance:auto;">
                            <option value="">Select staff member…</option>
                            @foreach($staffUsers as $su)
                            <option value="{{ $su->id }}">{{ $su->name }} &middot; {{ $su->role->label() }} &middot; {{ $su->hub_id ?: 'Global' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Training Course <span style="color:var(--burgundy);">*</span></label>
                        <select name="training_code" required onchange="trainingCalcExpiry(this)"
                                style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px; appearance:auto;">
                            <option value="">Select training…</option>
                            @foreach($trainings as $t)
                            <option value="{{ $t->code }}" data-refresh="{{ $t->refresh ?? '' }}" data-mandatory="{{ $t->mandatory ? 'yes' : 'no' }}">
                                {{ $t->code }} &middot; {{ $t->name }}{{ $t->mandatory ? ' (mandatory)' : '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Date Completed <span style="color:var(--burgundy);">*</span></label>
                            <input type="date" name="completed_on" id="trainingDate" required value="{{ now()->format('Y-m-d') }}" onchange="trainingCalcExpiry(document.querySelector('[name=training_code]'))"
                                   style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; box-sizing:border-box; border-radius:2px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Delivered By <span style="color:var(--burgundy);">*</span></label>
                            <input type="text" name="delivered_by" required value="LAS HQ"
                                   style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                        </div>
                    </div>

                    <div id="trainingExpiryHint" style="display:none; padding: 10px 14px; background: var(--ochre-tint); border: 1px solid rgba(184,115,25,0.2); font-size: 12px; color: var(--ochre); display: flex; align-items: center; gap: 6px;">
                        <x-lucide-calendar style="width:13px;height:13px;flex-shrink:0;" />
                        <span id="trainingExpiryText"></span>
                    </div>
                    <input type="hidden" name="expires" id="trainingExpiryVal">

                </div>

                <div style="padding: 14px 24px; border-top: 1px solid var(--rule); display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" data-bs-dismiss="modal" class="btn-ghost">Cancel</button>
                    <button type="submit" class="btn-primary" style="display:inline-flex; align-items:center; gap:7px;">
                        <x-lucide-plus style="width:13px;height:13px;" /> Save record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function trainingSetAction(userId) {
    document.getElementById('trainingForm').action = '/staff/user/' + userId + '/training';
}

document.getElementById('trainingForm').addEventListener('submit', function(e) {
    var sel = document.getElementById('trainingStaffSelect');
    if (!sel.value) {
        e.preventDefault();
        alert('Please select a staff member first.');
        return;
    }
});

// Re-open modal if there are validation errors
@if($errors->any())
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('modal-log-training');
    if (el && typeof bootstrap !== 'undefined') {
        new bootstrap.Modal(el).show();
    } else if (el) {
        el.classList.add('show');
        el.style.display = 'block';
    }
});
@endif

function trainingCalcExpiry(sel) {
    var opt = sel.options[sel.selectedIndex];
    var refresh = opt ? opt.dataset.refresh : '';
    var hint = document.getElementById('trainingExpiryHint');
    var text = document.getElementById('trainingExpiryText');
    var val = document.getElementById('trainingExpiryVal');
    var date = document.getElementById('trainingDate').value;

    if (!refresh || refresh === 'one-off' || !date) {
        hint.style.display = 'none';
        val.value = '';
        return;
    }

    var months = parseInt(refresh.replace('mo','').replace('m','')) || 12;
    var d = new Date(date);
    d.setMonth(d.getMonth() + months);
    var expiry = d.toISOString().split('T')[0];
    val.value = expiry;
    text.textContent = 'Expires ' + d.toLocaleDateString('en-US', {year:'numeric', month:'long', day:'numeric'});
    hint.style.display = 'flex';
}
</script>
@endif

</x-layouts.app>
