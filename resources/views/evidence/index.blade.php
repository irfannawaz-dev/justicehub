<x-layouts.app>
@php $canWrite = auth()->user()->canWrite(); @endphp

<div style="padding: 24px 34px 64px; max-width: 1600px; margin: 0 auto;">

    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 22px;">
        <div>
            <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">Measurement & Evaluation</div>
            <h1 class="serif" style="font-size: 34px; font-weight: 400; letter-spacing: -0.02em; margin: 0;">
                Evidence Register
            </h1>
            <p style="margin: 6px 0 0 0; font-size: 13px; color: var(--ink-3);">
                {{ $counts['total'] }} records · {{ $counts['verified'] }} verified · {{ $counts['pending'] }} pending
            </p>
        </div>
        @if($canWrite)
        <button class="btn-primary" onclick="jhOpenModal('register-evidence')">
            <x-lucide-plus style="width: 12px; height: 12px;" /> Register evidence
        </button>
        @endif
    </div>

    {{-- ═══ KPI Strip ═══ --}}
    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 22px;">
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--forest);">
            <div class="label-cap" style="font-size: 9px;">Total Records</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px;">{{ $counts['total'] }}</div>
        </div>
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--moss);">
            <div class="label-cap" style="font-size: 9px;">Verified</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px; color: var(--moss);">{{ $counts['verified'] }}</div>
        </div>
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--ochre);">
            <div class="label-cap" style="font-size: 9px;">Pending Review</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px; color: var(--ochre);">{{ $counts['pending'] }}</div>
        </div>
        @php
            $recognitionCount = $counts['by_type']['recognition'] ?? 0;
            $integrationCount = $counts['by_type']['integration'] ?? 0;
        @endphp
        <div class="card" style="padding: 16px 18px; border-top: 3px solid #7e57c2;">
            <div class="label-cap" style="font-size: 9px;">Recognition</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px;">{{ $recognitionCount }}</div>
        </div>
        <div class="card" style="padding: 16px 18px; border-top: 3px solid var(--forest);">
            <div class="label-cap" style="font-size: 9px;">Integration</div>
            <div class="serif" style="font-size: 32px; margin-top: 6px;">{{ $integrationCount }}</div>
        </div>
    </div>

    {{-- ═══ Filter Bar ═══ --}}
    <form method="GET" action="{{ route('evidence.index') }}" style="display: flex; gap: 10px; align-items: center; margin-bottom: 16px;">
        <select name="type" onchange="this.form.submit()" style="padding: 7px 10px; border: 1px solid var(--rule); border-radius: 6px; font-size: 12px; background: var(--surface-2); color: var(--ink-1);">
            <option value="">All Types</option>
            @foreach($types as $type)
            <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ ucwords(str_replace('-', ' ', $type)) }}</option>
            @endforeach
        </select>
        <select name="verified" onchange="this.form.submit()" style="padding: 7px 10px; border: 1px solid var(--rule); border-radius: 6px; font-size: 12px; background: var(--surface-2); color: var(--ink-1);">
            <option value="">All Status</option>
            <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>Verified</option>
            <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>Pending</option>
        </select>
        @if(request('type') || request('verified'))
        <a href="{{ route('evidence.index') }}" style="font-size: 12px; color: var(--ink-3); text-decoration: none;">
            <x-lucide-x style="width:12px;height:12px;display:inline;vertical-align:-1px;" /> Clear
        </a>
        @endif
    </form>

    {{-- ═══ Evidence Table ═══ --}}
    <div class="card" style="padding: 0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 1px solid var(--rule);">
                    <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">ID</th>
                    <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Date</th>
                    <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Type</th>
                    <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Title</th>
                    <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Issuer</th>
                    <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Linked</th>
                    <th style="text-align: left; padding: 10px 14px; font-size: 10px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--ink-3);">Status</th>
                    <th style="padding: 10px 14px; width: 80px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($evidence as $ev)
                @php
                    $typeVal = $ev->type instanceof \BackedEnum ? $ev->type->value : (string) $ev->type;
                    $typeColor = match($typeVal) {
                        'recognition'        => '#7e57c2',
                        'integration'        => 'var(--forest)',
                        'replication'        => 'var(--moss)',
                        'policy-citation'    => 'var(--ochre)',
                        'analytical-product' => 'var(--burgundy)',
                        default              => 'var(--ink-3)',
                    };
                @endphp
                <tr style="border-bottom: 1px solid var(--rule-2);">
                    <td style="padding: 12px 14px;">
                        <span class="mono" style="font-size: 11px; color: var(--forest); font-weight: 500;">{{ $ev->evidence_uid }}</span>
                    </td>
                    <td style="padding: 12px 14px; white-space: nowrap;" class="mono">{{ $ev->date->format('M d, Y') }}</td>
                    <td style="padding: 12px 14px;">
                        <span style="font-size: 10px; padding: 2px 7px; border-radius: 10px; font-weight: 600; letter-spacing: 0.03em; text-transform: uppercase; background: rgba(0,0,0,0.05); color: {{ $typeColor }};">
                            {{ ucwords(str_replace('-', ' ', $ev->type instanceof \BackedEnum ? $ev->type->value : $ev->type)) }}
                        </span>
                    </td>
                    <td style="padding: 12px 14px; color: var(--ink-1); max-width: 280px;">
                        <div style="font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $ev->title }}</div>
                        @if($ev->summary)
                        <div style="font-size: 11px; color: var(--ink-3); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ Str::limit($ev->summary, 80) }}</div>
                        @endif
                    </td>
                    <td style="padding: 12px 14px; font-size: 12px; color: var(--ink-2);">{{ $ev->issuer }}</td>
                    <td style="padding: 12px 14px;">
                        @if($ev->linked_indicator)
                        <span class="mono" style="font-size: 11px; color: var(--forest);">{{ $ev->linked_indicator }}</span>
                        @else
                        <span style="color: var(--ink-4); font-size: 11px;">—</span>
                        @endif
                    </td>
                    <td style="padding: 12px 14px;">
                        @if($ev->verified)
                        <span style="display: flex; align-items: center; gap: 4px; font-size: 11px; color: var(--moss); font-weight: 600;">
                            <x-lucide-check-circle-2 style="width:12px;height:12px;" /> Verified
                        </span>
                        <div style="font-size: 10px; color: var(--ink-4); margin-top: 1px;">{{ $ev->verified_by }}</div>
                        @else
                        <span style="font-size: 11px; color: var(--ochre); font-weight: 500;">Pending</span>
                        @endif
                    </td>
                    <td style="padding: 12px 14px; text-align: right;">
                        @if(!$ev->verified && $canWrite)
                        <form method="POST" action="{{ route('evidence.verify', $ev) }}" style="display: inline;">
                            @csrf
                            <button type="submit" style="font-size: 11px; padding: 4px 10px; border: 1px solid var(--moss); background: transparent; color: var(--moss); border-radius: 5px; cursor: pointer;">
                                Verify
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8"><x-empty-state icon="file-check" message="No evidence records found." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($evidence->hasPages())
    <div style="margin-top: 16px; display: flex; justify-content: center;">
        {{ $evidence->links() }}
    </div>
    @endif
</div>

{{-- ═══ Register Evidence Modal ═══ --}}
@if($canWrite)
@php
    $typeConfig = [
        ['value' => 'recognition',        'label' => 'Recognition',        'icon' => 'award',       'indicator' => 'G1'],
        ['value' => 'integration',        'label' => 'Integration / MoU',  'icon' => 'heart-handshake','indicator' => 'G2'],
        ['value' => 'replication',        'label' => 'Replication',        'icon' => 'copy',        'indicator' => 'G3'],
        ['value' => 'policy-citation',    'label' => 'Policy citation',    'icon' => 'flag',        'indicator' => 'G4'],
        ['value' => 'analytical-product', 'label' => 'Analytical product', 'icon' => 'bar-chart-2', 'indicator' => 'OP4.4'],
    ];
    $hubs = \App\Models\Hub::where('is_active', true)->get(['id', 'name']);
@endphp
<div class="modal fade" id="modal-register-evidence" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 580px; margin: 1.75rem auto;">
        <div class="modal-content" style="border: 1px solid var(--rule); border-radius: 4px; background: var(--parchment); box-shadow: 0 16px 48px rgba(0,0,0,.18); display: flex; flex-direction: column; max-height: 92vh;">

            {{-- Header --}}
            <div style="padding: 22px 24px 16px; border-bottom: 1px solid var(--rule); flex-shrink: 0;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="label-cap" style="font-size: 9.5px; color: var(--ink-3); margin-bottom: 6px;">New Evidence Entry</div>
                        <h2 class="serif" style="font-size: 26px; font-weight: 400; margin: 0; line-height: 1.1;">
                            Register institutional <em style="color: var(--forest);">evidence</em>
                        </h2>
                    </div>
                    <button type="button" data-bs-dismiss="modal" style="background:none; border:1px solid var(--rule); cursor:pointer; padding:6px 8px; color:var(--ink-3); border-radius:3px;">
                        <x-lucide-x style="width:15px;height:15px;" />
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div style="flex: 1; overflow-y: auto; padding: 0;">
                <form method="POST" action="{{ route('evidence.store') }}" id="evForm" enctype="multipart/form-data">
                    @csrf

                    {{-- Type selector --}}
                    <div style="padding: 20px 24px; border-bottom: 1px solid var(--rule);">
                        <div class="label-cap" style="font-size: 9.5px; color: var(--ink-2); margin-bottom: 12px;">Type</div>
                        <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px;">
                            @foreach($typeConfig as $tc)
                            <label style="display:flex; flex-direction:column; align-items:center; gap:4px; padding:12px 4px; border:2px solid var(--rule); cursor:pointer; transition:all 120ms; text-align:center;"
                                   onclick="this.querySelector('input').checked=true;
                                            document.querySelectorAll('#evForm [name=type]').forEach(function(r){r.closest('label').style.background='transparent';r.closest('label').style.borderColor='var(--rule)';r.closest('label').style.color='var(--ink-2)'});
                                            this.style.background='var(--forest)';this.style.borderColor='var(--forest)';this.style.color='var(--cream)';
                                            document.getElementById('evIndicatorHint').textContent='Once verified, this entry will count toward indicator {{ $tc['indicator'] }}.';
                                            document.getElementById('evIndicatorHint').style.display='';">
                                <input type="radio" name="type" value="{{ $tc['value'] }}" required style="display:none;">
                                <x-dynamic-component :component="'lucide-' . $tc['icon']" style="width:16px;height:16px;" />
                                <span style="font-size:10.5px; font-weight:600; letter-spacing:0.01em;">{{ $tc['label'] }}</span>
                            </label>
                            @endforeach
                        </div>
                        <div id="evIndicatorHint" style="display:none; margin-top:10px; padding:8px 12px; background:var(--ochre-tint); border:1px solid rgba(184,115,25,0.2); font-size:11.5px; color:var(--ochre);">
                            <x-lucide-info style="width:12px;height:12px;display:inline;vertical-align:-2px;" />
                        </div>
                    </div>

                    {{-- Title + Summary --}}
                    <div style="padding: 20px 24px; border-bottom: 1px solid var(--rule);">
                        <div style="margin-bottom: 14px;">
                            <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Title <span style="color:var(--burgundy);">*</span></label>
                            <input type="text" name="title" required placeholder="e.g. Sindh Govt. Notification — ADR provider recognition"
                                   style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Summary</label>
                            <textarea name="summary" rows="3" required placeholder="What this evidence is, what it establishes, and why it counts."
                                      style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; resize:vertical; box-sizing:border-box; border-radius:2px; line-height:1.5;"></textarea>
                        </div>
                    </div>

                    {{-- Issuer + Date --}}
                    <div style="padding: 20px 24px; border-bottom: 1px solid var(--rule);">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Issuer / Source <span style="color:var(--burgundy);">*</span></label>
                                <input type="text" name="issuer" required placeholder="Govt. body, agency, or publisher"
                                       style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                            </div>
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Date Issued <span style="color:var(--burgundy);">*</span></label>
                                <input type="date" name="date" required value="{{ now()->format('Y-m-d') }}"
                                       style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; box-sizing:border-box; border-radius:2px;">
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Hub Scope</label>
                                <select name="hub_id"
                                        style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px; appearance:auto;">
                                    <option value="">All hubs (programme-wide)</option>
                                    @foreach($hubs as $hub)
                                    <option value="{{ $hub->id }}">{{ $hub->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label style="display:block; font-size:9.5px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-3); margin-bottom:6px;">Document Reference (optional)</label>
                                <input type="text" name="document_ref" placeholder="Notification no. / URL / file ref"
                                       style="width:100%; padding:10px 12px; border:1px solid var(--rule); background:var(--parchment); color:var(--ink); font-size:13px; font-family:inherit; box-sizing:border-box; border-radius:2px;">
                            </div>
                        </div>
                    </div>

                    {{-- File upload --}}
                    <div style="padding: 20px 24px; border-bottom: 1px solid var(--rule);">
                        <div class="label-cap" style="font-size: 9.5px; color: var(--ink-2); margin-bottom: 4px;">Supporting Documents</div>
                        <div style="font-size: 11px; color: var(--ink-4); margin-bottom: 10px;">PDFs, images, video / audio testimonials</div>
                        <label style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 28px 20px; border: 2px dashed var(--rule); cursor: pointer; transition: border-color 150ms, background 150ms; text-align: center;"
                               onmouseenter="this.style.borderColor='var(--forest)';this.style.background='rgba(22,48,41,0.02)'"
                               onmouseleave="this.style.borderColor='var(--rule)';this.style.background='transparent'">
                            <x-lucide-upload style="width:22px;height:22px;color:var(--ink-3);" />
                            <div>
                                <span style="font-size: 13px; font-weight: 600; color: var(--forest); text-decoration: underline;">Click to choose files</span>
                                <span style="font-size: 13px; color: var(--ink-3);"> or drag and drop</span>
                            </div>
                            <div style="font-size: 10.5px; color: var(--ink-4);">PDF, JPG, PNG &middot; MP4, MOV, MP3 &middot; up to 50 MB per file</div>
                            <input type="file" name="files[]" multiple accept=".pdf,.jpg,.jpeg,.png,.mp4,.mov,.mp3,.doc,.docx" style="display:none;" onchange="evShowFiles(this)">
                        </label>
                        <div id="evFileList" style="margin-top: 8px;"></div>
                    </div>

                    {{-- Info note --}}
                    <div style="padding: 16px 24px;">
                        <div style="padding: 10px 14px; background: var(--ochre-tint); border: 1px solid rgba(184,115,25,0.2); font-size: 11.5px; color: var(--ink-2); line-height: 1.55;">
                            <x-lucide-info style="width:12px;height:12px;display:inline;vertical-align:-2px;color:var(--ochre);" />
                            New entries are saved as <strong>Pending verification</strong>. They will not count toward indicators until an M&amp;E Lead reviews and verifies the entry.
                        </div>
                    </div>

                </form>
            </div>

            {{-- Footer --}}
            <div style="flex-shrink:0; padding:14px 24px; border-top:1px solid var(--rule); display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" data-bs-dismiss="modal" class="btn-ghost">Cancel</button>
                <button type="submit" form="evForm" class="btn-primary" style="display:inline-flex; align-items:center; gap:7px;">
                    <x-lucide-plus style="width:13px;height:13px;" /> Register evidence
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
function evShowFiles(input) {
    var list = document.getElementById('evFileList');
    if (!input.files.length) { list.innerHTML = ''; return; }
    var html = '';
    for (var i = 0; i < input.files.length; i++) {
        var f = input.files[i];
        var size = f.size < 1024*1024 ? Math.round(f.size/1024) + ' KB' : (f.size/1024/1024).toFixed(1) + ' MB';
        html += '<div style="display:flex;align-items:center;gap:8px;padding:6px 0;font-size:12px;color:var(--ink-2);">';
        html += '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--forest);flex-shrink:0;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>';
        html += '<span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + f.name + '</span>';
        html += '<span style="font-size:10px;color:var(--ink-4);flex-shrink:0;">' + size + '</span>';
        html += '</div>';
    }
    list.innerHTML = html;
}
</script>
</x-layouts.app>
