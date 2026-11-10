<x-layouts.app>
<div class="page-header">
    <div>
        <h1 class="page-title">LAS CMS — Pending Links</h1>
        <p class="page-subtitle">Review and link ambiguous or unmatched Court Representation cases to LAS CMS records.</p>
    </div>
    <div class="flex gap-2">
        <span class="badge badge-ochre">{{ $cases->total() }} pending</span>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger mb-4">{{ $errors->first() }}</div>
@endif

@forelse($cases as $case)
@php
    $meta       = $case->meta ?? [];
    $status     = $meta['las_link_status'] ?? 'unknown';
    $candidates = collect($meta['las_link_candidates'] ?? $meta['las_candidates'] ?? [])->filter(fn ($candidate) => \App\Support\LasCmsIdentity::matches($case, $candidate))->all();
@endphp
<div class="card mb-4" style="border-left: 3px solid {{ $status === 'name_ambiguous' ? 'var(--ochre)' : 'var(--ink-3)' }}">
    <div class="card-body">
        {{-- Case header --}}
        <div class="flex items-start justify-between gap-4 mb-3">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-semibold text-sm">{{ $case->case_uid }}</span>
                    <span class="badge {{ $status === 'name_ambiguous' ? 'badge-ochre' : 'badge-muted' }} text-xs">
                        {{ str_replace('_', ' ', $status) }}
                    </span>
                </div>
                <div class="text-base font-semibold">{{ $case->name }}</div>
                <div class="text-xs text-muted mt-1">
                    {{ $case->district }} &middot; {{ $case->intake_date?->format('d M Y') }} &middot; CNIC: {{ $case->cnic ?: '—' }}
                </div>
            </div>
            <a href="{{ route('cases.show', $case) }}" class="btn btn-sm btn-outline" target="_blank">View Case</a>
        </div>

        @if(count($candidates) > 0)
        {{-- Candidate selection --}}
        <p class="text-xs text-muted mb-2">Multiple LAS CMS records found — select the correct one:</p>
        <div class="grid gap-2" style="grid-template-columns: repeat(auto-fill, minmax(260px, 1fr))">
            @foreach($candidates as $candidate)
            <form method="POST" action="{{ route('cases.link-las', $case) }}">
                @csrf
                <input type="hidden" name="program_id" value="{{ $candidate['program_id'] ?? $candidate['id'] ?? '' }}">
                <button type="submit" class="w-full text-left p-3 rounded border border-ink-2 hover:border-forest hover:bg-green-50 transition-colors" style="background: var(--surface)">
                    <div class="font-medium text-sm">{{ $candidate['clientName'] ?? $candidate['name'] ?? '—' }}</div>
                    <div class="text-xs text-muted mt-1">
                        LAS #{{ $candidate['program_id'] ?? $candidate['id'] ?? '?' }}
                        @if(!empty($candidate['districtName'])) &middot; {{ $candidate['districtName'] }} @endif
                        @if(!empty($candidate['interviewDate'])) &middot; {{ \Carbon\Carbon::parse($candidate['interviewDate'])->format('d M Y') }} @endif
                    </div>
                </button>
            </form>
            @endforeach
        </div>

        @else
        {{-- Manual program_id entry --}}
        <form method="POST" action="{{ route('cases.link-las', $case) }}" class="flex gap-2 items-end mt-2">
            @csrf
            <div class="flex-1">
                <label class="label text-xs">Enter LAS CMS Program ID (CNIC must match)</label>
                <input type="number" name="program_id" class="input input-sm" placeholder="e.g. 1234" required>
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Link</button>
        </form>
        @endif

    </div>
</div>
@empty
<div class="card">
    <div class="card-body text-center py-12">
        <div class="text-muted">All cases are linked. Nothing to review.</div>
    </div>
</div>
@endforelse

{{ $cases->links() }}
</x-layouts.app>
