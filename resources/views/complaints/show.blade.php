<x-layouts.app>
@php
    $canWrite = auth()->user()->canWrite();
    $canResolve = auth()->user()->can('complaints.resolve');
@endphp

<div style="padding: 24px 34px 64px; max-width: 1100px; margin: 0 auto;">

    {{-- Back --}}
    <a href="{{ route('complaints.index') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: var(--ink-3); margin-bottom: 18px; text-decoration: none;">
        <x-lucide-chevron-left style="width: 13px; height: 13px;" /> Back to complaints
    </a>

    {{-- Header --}}
    <div class="card" style="padding: 24px 28px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <span class="mono" style="font-size: 12px; color: var(--ink-3);">{{ $complaint->complaint_uid }}</span>
                    <x-pill :color="$complaint->severity->color()" :bg="$complaint->severity->tint()">{{ $complaint->severity->label() }}</x-pill>
                    <x-pill :color="$complaint->status->color()">{{ $complaint->status->label() }}</x-pill>
                </div>
                <h1 class="serif" style="font-size: 26px; font-weight: 400; margin: 0 0 8px 0;">
                    {{ ucfirst(str_replace('-', ' ', $complaint->category)) }}
                </h1>
                <p style="font-size: 14px; color: var(--ink-2); margin: 0; line-height: 1.55; max-width: 640px;">
                    {{ $complaint->description }}
                </p>
            </div>
            <div style="text-align: right;">
                <div class="label-cap" style="font-size: 9px; margin-bottom: 4px;">SLA</div>
                @if($complaint->status->value === 'resolved')
                <div style="font-size: 14px; color: var(--moss); font-weight: 500;">
                    <x-lucide-check-circle-2 style="width:14px;height:14px;display:inline;vertical-align:-2px;" /> Resolved
                </div>
                @elseif($complaint->isOverdue())
                <div style="font-size: 14px; color: var(--burgundy); font-weight: 600;">
                    {{ abs($complaint->daysRemaining()) }}d overdue
                </div>
                @else
                <div style="font-size: 14px; color: var(--ink-2);">{{ $complaint->daysRemaining() }}d remaining</div>
                @endif
                <div style="font-size: 11px; color: var(--ink-4); margin-top: 2px;">{{ $complaint->sla_days }}-day SLA</div>
            </div>
        </div>

        {{-- Meta --}}
        <div style="display: flex; gap: 24px; font-size: 12px; color: var(--ink-3); margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--rule-2); flex-wrap: wrap;">
            <span>Submitted: {{ $complaint->submitted_date->format('M d, Y') }}</span>
            <span>By: {{ $complaint->is_anonymous ? 'Anonymous' : $complaint->submitted_by }}</span>
            <span>Channel: {{ $complaint->channel }}</span>
            <span>Hub: {{ $complaint->hub_id }}</span>
            @if($complaint->assigned_to)<span>Assigned: {{ $complaint->assigned_to }}</span>@endif
            @if($complaint->caseRecord)<span>Case: <a href="{{ route('cases.show', $complaint->caseRecord) }}" style="color: var(--forest);" class="mono">{{ $complaint->caseRecord->case_uid }}</a></span>@endif
        </div>
    </div>

    {{-- Resolution --}}
    @if($complaint->resolution)
    <div class="card-accent" style="padding: 16px 20px; margin-bottom: 20px; border-left-color: var(--moss);">
        <div class="label-cap" style="font-size: 9px; margin-bottom: 6px;">Resolution</div>
        <div style="font-size: 13px; color: var(--ink-2); line-height: 1.55;">{{ $complaint->resolution }}</div>
        @if($complaint->resolved_date)
        <div style="font-size: 11px; color: var(--ink-4); margin-top: 6px;">Resolved on {{ $complaint->resolved_date->format('M d, Y') }}</div>
        @endif
    </div>
    @endif

    {{-- Action Timeline --}}
    <div style="margin-bottom: 22px;">
        <div class="label-cap" style="font-size: 9.5px; margin-bottom: 12px;">Action Timeline</div>
        @forelse($complaint->actions as $action)
        <div style="display: flex; gap: 16px; position: relative; padding-left: 24px; margin-bottom: 0;">
            <div style="position: absolute; left: 7px; top: 0; bottom: 0; width: 2px; background: var(--rule);"></div>
            <div style="position: absolute; left: 2px; top: 6px; width: 12px; height: 12px; border-radius: 50%; background: var(--paper); border: 2px solid var(--forest); z-index: 1;"></div>
            <div class="card" style="padding: 12px 16px; margin-bottom: 10px; flex: 1;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                    <span class="mono" style="font-size: 11px; color: var(--ink-3);">{{ $action->date->format('M d, Y') }}</span>
                    <span style="font-size: 12px; color: var(--ink-3);">{{ $action->performed_by }}</span>
                </div>
                <div style="font-size: 13px; color: var(--ink-2); line-height: 1.55;">{{ $action->note }}</div>
            </div>
        </div>
        @empty
        <div style="padding: 20px; text-align: center; color: var(--ink-4); font-size: 13px;">No actions logged yet.</div>
        @endforelse
    </div>

    {{-- Add Action Form --}}
    @if($canWrite && $complaint->status->value !== 'resolved')
    <div class="card" style="padding: 20px 24px;">
        <div class="label-cap" style="font-size: 9.5px; margin-bottom: 12px;">Add Action</div>
        <form method="POST" action="{{ route('complaints.action', $complaint) }}">
            @csrf
            <x-form-input name="note" label="Action Note" type="textarea" required placeholder="Describe the action taken..." />
            @if($canResolve)
            <div style="margin-top: 14px;">
                <x-form-select name="new_status" label="Update Status (optional)" :options="['in-progress' => 'In Progress', 'resolved' => 'Resolved', 'escalated' => 'Escalated']" />
            </div>
            @endif
            <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
                <button type="submit" class="btn-primary"><x-lucide-plus style="width:12px;height:12px;" /> Log Action</button>
            </div>
        </form>
    </div>
    @endif
</div>
</x-layouts.app>
