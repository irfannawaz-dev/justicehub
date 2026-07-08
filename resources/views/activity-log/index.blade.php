<x-layouts.app>
<x-slot:title>Activity Log</x-slot:title>

<div style="padding: 28px 32px; max-width: 1200px; margin: 0 auto;">

    {{-- Header --}}
    <div style="margin-bottom: 24px;">
        <div class="label-cap" style="font-size: 10px; color: var(--ink-4); margin-bottom: 6px;">REPORTING</div>
        <h1 class="serif" style="font-size: 32px; font-weight: 400; margin: 0; letter-spacing: -0.02em;">
            Activity <em style="color: var(--forest); font-style: italic;">Log</em>
        </h1>
        <div style="font-size: 13px; color: var(--ink-3); margin-top: 6px;">
            Audit trail of all case changes — who changed what and when.
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" style="display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap; align-items: flex-end;">
        <div>
            <label style="font-size: 9.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--ink-4); display: block; margin-bottom: 4px;">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Case ID, name..."
                   class="inp" style="width: 200px; font-size: 12px; padding: 7px 10px;">
        </div>
        <div>
            <label style="font-size: 9.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--ink-4); display: block; margin-bottom: 4px;">Event</label>
            <select name="event" class="inp" style="width: 140px; font-size: 12px; padding: 7px 10px;">
                <option value="">All events</option>
                @foreach(['created', 'updated', 'deleted'] as $ev)
                <option value="{{ $ev }}" @selected(request('event') === $ev)>{{ ucfirst($ev) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="font-size: 9.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--ink-4); display: block; margin-bottom: 4px;">User</label>
            <select name="user" class="inp" style="width: 180px; font-size: 12px; padding: 7px 10px;">
                <option value="">All users</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" @selected(request('user') == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" style="padding: 7px 18px; background: var(--forest); color: var(--cream); border: none; font-size: 12px; font-weight: 600; cursor: pointer; font-family: inherit;">
            Filter
        </button>
        @if(request()->hasAny(['search', 'event', 'user']))
        <a href="{{ route('activity-log.index') }}" style="padding: 7px 14px; font-size: 12px; color: var(--ink-3); text-decoration: none; border: 1px solid var(--rule);">
            Clear
        </a>
        @endif
    </form>

    {{-- Activity table --}}
    <div class="card" style="overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background: var(--surface); border-bottom: 1.5px solid var(--rule);">
                    <th style="padding: 10px 14px; text-align: left; font-size: 9.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--ink-3);">When</th>
                    <th style="padding: 10px 14px; text-align: left; font-size: 9.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--ink-3);">User</th>
                    <th style="padding: 10px 14px; text-align: left; font-size: 9.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--ink-3);">Event</th>
                    <th style="padding: 10px 14px; text-align: left; font-size: 9.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--ink-3);">Case</th>
                    <th style="padding: 10px 14px; text-align: left; font-size: 9.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--ink-3);">Changes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activities as $act)
                @php
                    $props = $act->properties ?? collect();
                    $old   = $props['old'] ?? [];
                    $attrs = $props['attributes'] ?? [];
                    $eventColors = ['created' => 'var(--moss)', 'updated' => 'var(--ochre)', 'deleted' => 'var(--burgundy)'];
                    $eventColor  = $eventColors[$act->event] ?? 'var(--ink-3)';

                    // Try to get case UID
                    $caseUid = null;
                    $caseId  = $act->subject_id;
                    if ($act->subject_type === \App\Models\CaseRecord::class && $caseId) {
                        $caseUid = $attrs['case_uid'] ?? ($old['case_uid'] ?? null);
                        if (!$caseUid) {
                            $caseUid = \App\Models\CaseRecord::withTrashed()->where('id', $caseId)->value('case_uid');
                        }
                    }

                    // Build changed fields list
                    $changes = [];
                    if ($act->event === 'updated' && $old) {
                        foreach ($old as $field => $oldVal) {
                            $newVal = $attrs[$field] ?? '—';
                            if ($field === 'meta' || $field === 'last_update' || $field === 'updated_at') continue;
                            $changes[] = [
                                'field' => str_replace('_', ' ', $field),
                                'from'  => is_array($oldVal) ? json_encode($oldVal) : ($oldVal ?: '—'),
                                'to'    => is_array($newVal) ? json_encode($newVal) : ($newVal ?: '—'),
                            ];
                        }
                    }
                @endphp
                <tr style="border-bottom: 0.5px solid var(--rule-2); vertical-align: top;">
                    <td style="padding: 10px 14px; white-space: nowrap; color: var(--ink-3);">
                        <div style="font-size: 12px; font-weight: 600; color: var(--ink-2);">{{ $act->created_at->format('d M Y') }}</div>
                        <div class="mono" style="font-size: 10px; color: var(--ink-4);">{{ $act->created_at->format('H:i:s') }}</div>
                    </td>
                    <td style="padding: 10px 14px;">
                        <div style="font-size: 12px; font-weight: 600; color: var(--ink);">{{ $act->causer?->name ?? 'System' }}</div>
                    </td>
                    <td style="padding: 10px 14px;">
                        <span style="display: inline-block; padding: 2px 8px; font-size: 10px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
                            color: {{ $eventColor }}; background: {{ $eventColor }}15; border: 1px solid {{ $eventColor }}30;">
                            {{ $act->event }}
                        </span>
                    </td>
                    <td style="padding: 10px 14px;">
                        @if($caseUid && $caseId)
                        <a href="{{ route('cases.show', $caseId) }}" style="font-size: 12px; font-weight: 600; color: var(--forest); text-decoration: none;">
                            {{ $caseUid }}
                        </a>
                        @else
                        <span style="font-size: 12px; color: var(--ink-4);">{{ $act->description }}</span>
                        @endif
                    </td>
                    <td style="padding: 10px 14px; max-width: 360px;">
                        @if($act->event === 'created')
                            <span style="font-size: 11px; color: var(--moss); font-weight: 500;">Case created</span>
                        @elseif($act->event === 'deleted')
                            <span style="font-size: 11px; color: var(--burgundy); font-weight: 500;">Case deleted</span>
                        @elseif(count($changes))
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                @foreach(array_slice($changes, 0, 4) as $ch)
                                <div style="font-size: 11px; line-height: 1.4;">
                                    <span style="font-weight: 600; color: var(--ink-2); text-transform: capitalize;">{{ $ch['field'] }}</span>
                                    <span style="color: var(--ink-4);">:</span>
                                    <span style="color: var(--burgundy); text-decoration: line-through; font-size: 10px;">{{ \Illuminate\Support\Str::limit($ch['from'], 40) }}</span>
                                    <span style="color: var(--ink-4);">&rarr;</span>
                                    <span style="color: var(--moss); font-weight: 500; font-size: 10px;">{{ \Illuminate\Support\Str::limit($ch['to'], 40) }}</span>
                                </div>
                                @endforeach
                                @if(count($changes) > 4)
                                <div style="font-size: 10px; color: var(--ink-4);">+{{ count($changes) - 4 }} more fields</div>
                                @endif
                            </div>
                        @else
                            <span style="font-size: 11px; color: var(--ink-4);">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 48px 14px; text-align: center; color: var(--ink-4); font-size: 13px;">
                        No activity recorded yet. Changes to cases will appear here.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($activities->hasPages())
    <div style="margin-top: 16px; display: flex; justify-content: center;">
        {{ $activities->links() }}
    </div>
    @endif

</div>
</x-layouts.app>
