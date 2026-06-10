<?php

namespace App\Http\Controllers;

use App\Models\CaseRecord;
use App\Models\Hub;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    public function index(Request $request)
    {
        $hubId = $request->input('_active_hub');

        // ── Base query with ALL active filters applied ──
        $user = $request->user();
        $base = CaseRecord::query()->forHub($hubId);

        // Role-based case filtering
        if ($user->isLawyer() || $user->isHubCoordinator()) {
            $base->where('assigned_to', $user->name);
        }
        if ($user->isCourtClerk()) {
            $base->where(fn($q) => $q
                ->where('disposition', 'litigation')
                ->orWhereIn('assigned_pathway', ['Representation in Court', 'Court Representation'])
            );
        }

        if ($request->filled('hub') && $request->hub !== 'all') {
            $base->where('hub_id', $request->hub);
        }
        if ($request->filled('pathway') && $request->pathway !== 'all') {
            $pw = $request->pathway;
            $base->where(function ($q) use ($pw) {
                if ($pw === 'mediation_adr') {
                    $q->whereIn('assigned_pathway', ['Mediation', 'ADR / Dispute Resolution Support']);
                } elseif ($pw === 'court') {
                    $q->whereIn('assigned_pathway', ['Court Representation', 'Representation in Court']);
                } elseif ($pw === 'referred') {
                    $q->whereIn('assigned_pathway', ['Referral', 'Government Department / Public Institution', 'Civil Society / NGO / CSO / NPO']);
                } elseif ($pw === 'legal_advice') {
                    $q->where('assigned_pathway', 'Legal Advice / Consultation');
                } elseif ($pw === 'documentation') {
                    $q->where('assigned_pathway', 'NADRA & Documentation');
                } elseif ($pw === 'info_awareness') {
                    $q->where('assigned_pathway', 'Information & Awareness');
                } else {
                    $q->where('assigned_pathway', $pw);
                }
            });
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $base->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('case_uid', 'like', "%{$s}%")
                  ->orWhere('primary_issue', 'like', "%{$s}%")
                  ->orWhere('assigned_pathway', 'like', "%{$s}%")
                  ->orWhere('district', 'like', "%{$s}%");
            });
        }

        // ── Unfiltered base (only hub scope) for pathway + disposition counts ──
        $hubBase = CaseRecord::query()->forHub($hubId);
        if ($user->isLawyer() || $user->isHubCoordinator()) {
            $hubBase->where('assigned_to', $user->name);
        }
        if ($user->isCourtClerk()) {
            $hubBase->where(fn($q) => $q
                ->where('disposition', 'litigation')
                ->orWhereIn('assigned_pathway', ['Representation in Court', 'Court Representation'])
            );
        }
        if ($request->filled('hub') && $request->hub !== 'all') {
            $hubBase->where('hub_id', $request->hub);
        }

        // Disposition counts (always show full counts, not search-filtered)
        $totalAll = (clone $hubBase)->count();
        $dispositionCounts = [
            'all'         => $totalAll,
            'advice-only' => (clone $hubBase)->where('disposition', 'advice-only')->count(),
            'litigation'  => (clone $hubBase)->where('disposition', 'litigation')->count(),
            'adr'         => (clone $hubBase)->where('disposition', 'adr')->count(),
            'referred'    => (clone $hubBase)->where('disposition', 'referred')->count(),
            'pending'     => (clone $hubBase)->where(fn($q) => $q->whereNull('disposition')->orWhere('disposition', ''))->count(),
        ];

        // Service pathway counts — matching actual lookup values stored in DB
        $pathwayCounts = [
            'legal_advice'    => (clone $hubBase)->where('assigned_pathway', 'Legal Advice / Consultation')->count(),
            'mediation_adr'   => (clone $hubBase)->whereIn('assigned_pathway', ['Mediation', 'ADR / Dispute Resolution Support'])->count(),
            'documentation'   => (clone $hubBase)->where('assigned_pathway', 'NADRA & Documentation')->count(),
            'court'           => (clone $hubBase)->whereIn('assigned_pathway', ['Court Representation', 'Representation in Court'])->count(),
            'referred'        => (clone $hubBase)->whereIn('assigned_pathway', ['Referral', 'Government Department / Public Institution', 'Civil Society / NGO / CSO / NPO'])->count(),
            'info_awareness'  => (clone $hubBase)->where('assigned_pathway', 'Information & Awareness')->count(),
        ];

        // ── Cohort & status counts from filtered base ──
        $totalFiltered = (clone $base)->count();
        $counts = [
            'total'      => $totalFiltered,
            'pending'    => (clone $base)->where('status', 'Pending Approval')->count(),
            'female'     => (clone $base)->where('gender', 'Female')->count(),
            'male'       => (clone $base)->where('gender', 'Male')->count(),
            'minority'   => (clone $base)->where('is_minority', true)->count(),
            'disability' => (clone $base)->where('is_disability', true)->count(),
            'gbv'        => (clone $base)->where('is_gbv', true)->count(),
            'child'      => (clone $base)->where('is_child', true)->count(),
            'high_risk'  => (clone $base)->whereIn('urgency', ['High', 'Immediate'])->count(),
            'sla_breach' => (clone $base)->where('sla_met', false)->count(),
            'underserved'=> (clone $base)->where('is_underserved', true)->count(),
            'active'     => (clone $base)->where('status', 'Active')->count(),
            'closed'     => (clone $base)->whereIn('status', ['Closed', 'Settlement'])->count(),
            'safeguarding' => (clone $base)->where(function($q) {
                $q->where('is_gbv', true)->orWhere('is_child', true);
            })->count(),
        ];

        // ── Final paginated query (adds disposition + status on top of base) ──
        $query = clone $base;
        if ($request->filled('disposition') && $request->disposition !== 'all') {
            $query->where('disposition', $request->disposition);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'active') $query->where('status', 'Active');
            elseif ($request->status === 'closed') $query->whereIn('status', ['Closed', 'Settlement']);
            elseif ($request->status === 'safeguarding') $query->where(fn($q) => $q->where('is_gbv', true)->orWhere('is_child', true));
            elseif ($request->status === 'sla') $query->where('sla_met', false);
        }

        $cases = $query->latest('intake_date')->paginate(config('justice_hub.per_page.cases', 25));
        $hubs = Hub::where('is_active', true)->get();

        return view('cases.index', compact('cases', 'counts', 'dispositionCounts', 'pathwayCounts', 'hubs'));
    }

    public function show(CaseRecord $case)
    {
        // Hub scope enforced via Route::bind() in AppServiceProvider
        $case->load(['serviceEncounters', 'documents', 'complaints', 'feedback', 'hub', 'transfers.transferredBy', 'transfers.approvedBy']);

        // Auto-fetch hearings from LAS CMS if case is linked
        if ($case->external_case_id) {
            try {
                $sync = new \App\Services\LasCmsSyncService();
                $sync->pullHearings($case);
                $case->load('serviceEncounters'); // reload to include new hearings
            } catch (\Exception $e) {
                \Log::warning('LAS CMS auto-sync failed for ' . $case->case_uid . ': ' . $e->getMessage());
            }
        }

        // Staff eligible for reassignment (same hub, active, can write)
        $assignableUsers = \App\Models\User::where('hub_id', $case->hub_id)
            ->where('is_active', true)
            ->whereNotIn('role', ['viewer', 'me-lead', 'complaint-investigator'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        $pendingTransfer = $case->transfers->where('status', 'pending')->first();

        // ── LAS CMS Program Data ─────────────────────────────────────────────
        $cmsData     = null;
        $cmsHistory  = collect();
        $cmsHearings = collect();

        if ($case->external_case_id) {
            try {
                $cmsDb = \Illuminate\Support\Facades\DB::connection('las_cms');

                // Current record
                $cmsData = $cmsDb->table('programs')
                    ->where('id', $case->external_case_id)
                    ->select([
                        'approvalDate', 'vakalatnamaSubmissionDate', 'caseFileDate',
                        'lawyer1', 'courtName', 'levelOfCourt', 'caseNumber',
                        'firNumber', 'policeStation', 'natureOfCase', 'typeOfCase',
                        'mainCaseCategory', 'caseFiledUnderAct', 'nextHearing',
                        'currentCaseStatus', 'caseDecision', 'caseDisposalDate',
                        'caseStage', 'UniqueNumber2',
                    ])
                    ->first();

                // Change history from programs_detail
                $cmsHistory = $cmsDb->table('programs_detail')
                    ->where('programsid', $case->external_case_id)
                    ->orderBy('created_at')
                    ->get([
                        'change_type', 'created_at', 'edited_by', 'username',
                        'currentCaseStatus', 'caseStage', 'nextHearing',
                        'lawyer1', 'courtName', 'caseNumber', 'reasonOfChange',
                        'additionalComment',
                    ]);

                // Hearings from hearings table
                $cmsHearings = $cmsDb->table('hearings')
                    ->where('programsID', $case->external_case_id)
                    ->orderBy('created_at')
                    ->get([
                        'id', 'date', 'nextHearing', 'hearingUpdate', 'caseNumber', 'created_at',
                    ]);

            } catch (\Exception $e) {
                \Log::warning('LAS CMS data fetch failed for ' . $case->case_uid . ': ' . $e->getMessage());
            }
        }

        // ── Unified Activity Timeline ─────────────────────────────────────────
        $timeline = collect();

        // 1. Intake
        $timeline->push([
            'type'  => 'intake',
            'icon'  => 'clipboard',
            'label' => 'Intake',
            'text'  => 'Client registered via intake form.' . ($case->referral_source ? ' Referral source: ' . $case->referral_source . '.' : ''),
            'by'    => $case->staff_receiving,
            'at'    => \Carbon\Carbon::parse($case->intake_date->toDateString() . ' ' . ($case->intake_time ?? '09:00:00')),
            'color' => 'var(--forest)',
        ]);

        // 2. Service encounters (skip "Intake" type — already shown from case fields above)
        foreach ($case->serviceEncounters->reject(fn($e) => strtolower($e->type) === 'intake') as $enc) {
            $timeline->push([
                'type'  => 'encounter',
                'icon'  => 'message-square',
                'label' => $enc->type,
                'text'  => $enc->note,
                'by'    => $enc->performed_by,
                'at'    => $enc->date->startOfDay(),
                'color' => 'var(--forest)',
            ]);
        }

        // 3. Litigation stage changes
        $litLogs = \Illuminate\Support\Facades\DB::table('litigation_stage_logs')
            ->where('case_id', $case->id)->orderBy('changed_at')->get();
        foreach ($litLogs as $log) {
            $changer = $log->changed_by ? \App\Models\User::find($log->changed_by) : null;
            $byLabel = $changer?->name ?? ($log->changed_by == 0 ? 'System · CMS Sync' : '—');
            $timeline->push([
                'type'  => 'lit_stage',
                'icon'  => 'gavel',
                'label' => 'Litigation Stage Changed',
                'text'  => "Stage moved from «{$log->from_stage}» → «{$log->to_stage}».",
                'by'    => $byLabel,
                'at'    => \Carbon\Carbon::parse($log->changed_at),
                'color' => 'var(--burgundy)',
            ]);
        }

        // 4. ADR stage changes
        $adrLogs = \Illuminate\Support\Facades\DB::table('adr_stage_logs')
            ->where('case_id', $case->id)->orderBy('changed_at')->get();
        foreach ($adrLogs as $log) {
            $changer = \App\Models\User::find($log->changed_by);
            $timeline->push([
                'type'  => 'adr_stage',
                'icon'  => 'heart-handshake',
                'label' => 'ADR Stage Changed',
                'text'  => "Stage moved from «{$log->from_stage}» → «{$log->to_stage}».",
                'by'    => $changer?->name ?? '—',
                'at'    => \Carbon\Carbon::parse($log->changed_at),
                'color' => 'var(--ochre)',
            ]);
        }

        // 5. Documents
        foreach ($case->documents as $doc) {
            $timeline->push([
                'type'  => 'document',
                'icon'  => 'file-text',
                'label' => 'Document Uploaded',
                'text'  => "{$doc->name} ({$doc->type}) — {$doc->confidentiality}",
                'by'    => $doc->added_by,
                'at'    => $doc->added_date->startOfDay(),
                'color' => 'var(--ochre)',
            ]);
        }

        // 6. Complaints
        foreach ($case->complaints as $complaint) {
            $timeline->push([
                'type'  => 'complaint',
                'icon'  => 'alert-triangle',
                'label' => 'Complaint Filed',
                'text'  => $complaint->description ?? $complaint->category ?? 'Complaint recorded.',
                'by'    => $complaint->is_anonymous ? 'Anonymous' : ($complaint->submitted_by ?? '—'),
                'at'    => $complaint->submitted_date->startOfDay(),
                'color' => 'var(--burgundy)',
            ]);
        }

        // 7. Feedback
        foreach ($case->feedback as $fb) {
            $score = $fb->score_overall ? "Overall: {$fb->score_overall}/5." : '';
            $timeline->push([
                'type'  => 'feedback',
                'icon'  => 'star',
                'label' => 'Feedback Received',
                'text'  => trim($score . ' ' . ($fb->comment ?? '')),
                'by'    => $fb->is_anonymous ? 'Anonymous' : ($fb->client_name ?? '—'),
                'at'    => $fb->date->startOfDay(),
                'color' => 'var(--moss)',
            ]);
        }

        // 8. Pathway approval request
        if ($case->requested_at) {
            $timeline->push([
                'type'  => 'approval_request',
                'icon'  => 'send',
                'label' => 'Pathway Approval Requested',
                'text'  => "Pathway «{$case->assigned_pathway}» submitted for manager approval.",
                'by'    => $case->pathway_manager ?? '—',
                'at'    => $case->requested_at,
                'color' => 'var(--ochre)',
            ]);
        }

        // 9. Pathway approved
        if ($case->approval_decision === 'approved' && $case->requested_at) {
            $timeline->push([
                'type'  => 'approved',
                'icon'  => 'check-circle-2',
                'label' => 'Pathway Approved',
                'text'  => "Pathway «{$case->assigned_pathway}» approved. Case set to Active.",
                'by'    => $case->pathway_manager ?? '—',
                'at'    => $case->requested_at->addMinute(),
                'color' => 'var(--moss)',
            ]);
        }

        // 10. Pathway rejected
        if ($case->rejected_at) {
            $timeline->push([
                'type'  => 'rejected',
                'icon'  => 'x-circle',
                'label' => 'Pathway Rejected',
                'text'  => $case->rejection_reason ?? 'No reason provided.',
                'by'    => $case->rejected_by ?? '—',
                'at'    => $case->rejected_at,
                'color' => 'var(--burgundy)',
            ]);
        }

        // 11. Transfers
        foreach ($case->transfers as $transfer) {
            $timeline->push([
                'type'  => 'transfer',
                'icon'  => 'arrow-right-left',
                'label' => 'Case Transfer ' . ucfirst($transfer->status),
                'text'  => "Transfer requested from hub {$transfer->from_hub_id} to {$transfer->to_hub_id}." . ($transfer->reason ? " Reason: {$transfer->reason}." : ''),
                'by'    => $transfer->transferredBy?->name ?? '—',
                'at'    => \Carbon\Carbon::parse($transfer->created_at),
                'color' => 'var(--ink-3)',
            ]);
        }

        // 12. LAS CMS — programs_detail change history
        foreach ($cmsHistory as $h) {
            $isCreate = ($h->change_type ?? '') === 'create';
            $by       = $h->edited_by ?: $h->username ?: 'LAS CMS';
            $parts    = [];
            if ($h->currentCaseStatus) $parts[] = "Status: {$h->currentCaseStatus}";
            if ($h->caseStage)         $parts[] = "Stage: {$h->caseStage}";
            if ($h->nextHearing)       $parts[] = "Next hearing: {$h->nextHearing}";
            if ($h->lawyer1)           $parts[] = "Lawyer: {$h->lawyer1}";
            if ($h->courtName)         $parts[] = "Court: {$h->courtName}";
            if ($h->caseNumber)        $parts[] = "Case no: {$h->caseNumber}";
            if ($h->reasonOfChange)    $parts[] = "Reason: {$h->reasonOfChange}";
            if ($h->additionalComment) $parts[] = $h->additionalComment;

            $timeline->push([
                'type'  => $isCreate ? 'cms_create' : 'cms_update',
                'icon'  => $isCreate ? 'database' : 'refresh-cw',
                'label' => $isCreate ? 'LAS CMS — Case Created' : 'LAS CMS — Case Updated',
                'text'  => implode(' · ', $parts) ?: 'Record saved in LAS CMS.',
                'by'    => $by,
                'at'    => \Carbon\Carbon::parse($h->created_at),
                'color' => 'var(--ink-3)',
            ]);
        }

        // 13. LAS CMS — court hearings
        foreach ($cmsHearings as $h) {
            $text = $h->hearingUpdate ?? '';
            if ($h->nextHearing) $text .= ($text ? ' · ' : '') . "Next hearing: {$h->nextHearing}";
            $timeline->push([
                'type'  => 'cms_hearing',
                'icon'  => 'scale',
                'label' => 'LAS CMS — Court Hearing',
                'text'  => $text ?: 'Hearing logged in LAS CMS.',
                'by'    => 'LAS CMS',
                'at'    => \Carbon\Carbon::parse($h->date ?: $h->created_at),
                'color' => 'var(--burgundy)',
            ]);
        }

        // Sort newest first
        $timeline = $timeline->sortByDesc('at')->values();

        return view('cases.show', compact('case', 'assignableUsers', 'pendingTransfer', 'timeline', 'cmsData'));
    }

    public function verifyDocument(Request $request, \App\Models\Document $document)
    {
        $document->update([
            'status' => 'Verified',
        ]);

        return back()->with('success', "Document {$document->document_uid} verified.");
    }

    public function setOutcome(Request $request, CaseRecord $case)
    {
        $data = $request->validate([
            'outcome' => 'required|in:Won,Partial,Lost,Withdrawn,Settlement',
        ]);

        $case->update([
            'meta' => array_merge($case->meta ?? [], [
                'outcome'     => $data['outcome'],
                'outcome_set_by' => auth()->user()->name,
                'outcome_set_at' => now()->toDateTimeString(),
            ]),
        ]);

        return back()->with('success', "Outcome set to {$data['outcome']} for {$case->case_uid}.");
    }

    public function storeDocument(Request $request, CaseRecord $case)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'type'            => 'required|string',
            'confidentiality' => 'required|string',
            'added_by'        => 'required|string|max:255',
            'description'     => 'nullable|string',
            'file'            => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/' . $case->id, 'public');

        $maxNum = \App\Models\Document::selectRaw("MAX(CAST(SUBSTRING(document_uid, 5) AS UNSIGNED)) as max_num")->value('max_num');
        $nextNum = $maxNum ? $maxNum + 1 : 1001;
        $docUid = 'DOC-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

        \App\Models\Document::create([
            'document_uid'   => $docUid,
            'case_id'        => $case->id,
            'type'           => $request->type,
            'name'           => $request->name,
            'added_date'     => now()->toDateString(),
            'added_by'       => $request->added_by,
            'source'         => 'Upload',
            'status'         => 'Pending Review',
            'confidentiality'=> $request->confidentiality,
            'document_ref'   => $case->case_ref,
            'file_path'      => $path,
            'meta'           => $request->description ? ['description' => $request->description] : null,
        ]);

        return back()->with('success', "Document uploaded: {$docUid}");
    }

    public function approve(Request $request, CaseRecord $case)
    {
        abort_unless($request->user()->can('cases.approve'), 403, 'You do not have permission to approve cases.');

        $case->update([
            'approval_decision' => 'approved',
            'status' => 'Active',
        ]);

        if ($assignedUser = $case->getAssignedUser()) {
            $assignedUser->notify(new \App\Notifications\CaseNotification(
                title:      "Case pathway approved — {$case->case_uid}",
                message:    "The pathway for case {$case->case_uid} ({$case->name}) has been approved.",
                actionText: 'View Case',
                actionUrl:  route('cases.show', $case),
                type:       'approved',
            ));
        }

        return back()->with('success', 'Case pathway approved.');
    }

    public function reject(Request $request, CaseRecord $case)
    {
        abort_unless($request->user()->can('cases.approve'), 403, 'You do not have permission to reject cases.');

        $request->validate(['rejection_reason' => 'required|string|max:1000']);
        $case->update([
            'approval_decision' => 'rejected',
            'rejection_reason' => $request->input('rejection_reason'),
            'rejected_by' => auth()->user()->name,
            'rejected_at' => now(),
            'status' => 'Rejected',
        ]);

        if ($assignedUser = $case->getAssignedUser()) {
            $assignedUser->notify(new \App\Notifications\CaseNotification(
                title:      "Case pathway rejected — {$case->case_uid}",
                message:    "The pathway for case {$case->case_uid} ({$case->name}) was rejected. Reason: {$request->input('rejection_reason')}",
                actionText: 'View Case',
                actionUrl:  route('cases.show', $case),
                type:       'rejected',
            ));
        }

        return back()->with('success', 'Case pathway rejected.');
    }

    public function resolve(Request $request, CaseRecord $case)
    {
        $user = $request->user();
        $canResolve = $user->isHubCoordinator()
            ? ($case->assigned_to === $user->name)
            : $user->can('cases.approve');
        abort_unless($canResolve, 403, 'Only the assigned Hub Coordinator can resolve this case.');

        $data = $request->validate([
            'outcome'         => 'required|in:Won,Partial,Lost,Withdrawn,Settlement',
            'resolution_type' => 'required|in:Closed,Settlement',
            'resolution_note' => 'nullable|string|max:2000',
        ]);

        $case->update([
            'status'      => $data['resolution_type'],
            'last_update' => now(),
            'meta'        => array_merge($case->meta ?? [], [
                'outcome'         => $data['outcome'],
                'resolution_note' => $data['resolution_note'],
                'resolved_at'     => now()->toDateTimeString(),
                'resolved_by'     => auth()->user()->name,
            ]),
        ]);

        // Log as service encounter
        \App\Models\ServiceEncounter::create([
            'case_id'      => $case->id,
            'date'         => now()->toDateString(),
            'type'         => 'Case Closure',
            'performed_by' => auth()->user()->name,
            'note'         => "Case resolved: {$data['outcome']}." . ($data['resolution_note'] ? " {$data['resolution_note']}" : ''),
            'meta'         => ['outcome' => $data['outcome']],
        ]);

        // Notify assigned user and (if different) staff who received the client
        $notified = [];
        foreach (array_unique([$case->assigned_to, $case->staff_receiving]) as $name) {
            if (! $name || in_array($name, $notified)) continue;
            $targetUser = \App\Models\User::where('name', $name)->where('hub_id', $case->hub_id)->first();
            if ($targetUser) {
                $targetUser->notify(new \App\Notifications\CaseNotification(
                    title:      "Case resolved — {$case->case_uid}",
                    message:    "Case {$case->case_uid} ({$case->name}) has been resolved as {$data['outcome']}.",
                    actionText: 'View Case',
                    actionUrl:  route('cases.show', $case),
                    type:       'resolved',
                ));
                $notified[] = $name;
            }
        }

        return back()->with('success', "Case resolved as {$data['outcome']}.");
    }

    public function reassign(Request $request, CaseRecord $case)
    {
        abort_unless($request->user()->can('cases.edit'), 403);

        $data = $request->validate([
            'to_assignee'   => 'required|string|max:150',
            'transfer_date' => 'required|date',
            'reason'        => 'required|string|min:10|max:1000',
        ]);

        // Block if a pending transfer already exists
        if ($case->transfers()->where('status', 'pending')->exists()) {
            return back()->with('error', 'A transfer request is already pending approval for this case.');
        }

        $transfer = \App\Models\CaseTransfer::create([
            'case_id'         => $case->id,
            'from_assignee'   => $case->assigned_to,
            'to_assignee'     => $data['to_assignee'],
            'transferred_by'  => $request->user()->id,
            'transfer_date'   => $data['transfer_date'],
            'reason'          => $data['reason'],
            'status'          => 'pending',
        ]);

        // Log on timeline
        \App\Models\ServiceEncounter::create([
            'case_id'      => $case->id,
            'date'         => now()->toDateString(),
            'type'         => 'Transfer Request',
            'performed_by' => $request->user()->name,
            'note'         => "Reassignment requested from {$transfer->from_assignee} to {$data['to_assignee']}. Reason: {$data['reason']}",
        ]);

        // Notify Head / approvers
        $approvers = \App\Models\User::where('hub_id', $case->hub_id)
            ->whereIn('role', ['head', 'provincial-lead', 'hub-coordinator'])
            ->where('id', '!=', $request->user()->id)
            ->get();

        foreach ($approvers as $approver) {
            $approver->notify(new \App\Notifications\CaseNotification(
                title:      "Transfer request — {$case->case_uid}",
                message:    "{$request->user()->name} requested reassignment of {$case->case_uid} from {$transfer->from_assignee} to {$data['to_assignee']}. Reason: {$data['reason']}",
                actionText: 'Review Case',
                actionUrl:  route('cases.show', $case),
                type:       'updated',
            ));
        }

        return back()->with('success', 'Transfer request submitted. Awaiting approval.');
    }

    public function approveTransfer(Request $request, CaseRecord $case, \App\Models\CaseTransfer $transfer)
    {
        abort_unless($request->user()->can('cases.approve'), 403);

        $data = $request->validate([
            'approval_note' => 'nullable|string|max:500',
        ]);

        $transfer->update([
            'status'        => 'approved',
            'approved_by'   => $request->user()->id,
            'decided_at'    => now(),
            'approval_note' => $data['approval_note'],
        ]);

        // Actually reassign the case
        $case->update([
            'assigned_to' => $transfer->to_assignee,
            'last_update' => now()->toDateString(),
        ]);

        // Log on timeline
        \App\Models\ServiceEncounter::create([
            'case_id'      => $case->id,
            'date'         => now()->toDateString(),
            'type'         => 'Transfer Approved',
            'performed_by' => $request->user()->name,
            'note'         => "Transfer approved. Case reassigned from {$transfer->from_assignee} to {$transfer->to_assignee}." . ($data['approval_note'] ? " Note: {$data['approval_note']}" : ''),
        ]);

        // Notify old and new assignee
        foreach ([$transfer->from_assignee, $transfer->to_assignee] as $name) {
            $target = \App\Models\User::where('name', $name)->where('hub_id', $case->hub_id)->first();
            if ($target) {
                $target->notify(new \App\Notifications\CaseNotification(
                    title:      "Case transfer approved — {$case->case_uid}",
                    message:    "Case {$case->case_uid} ({$case->name}) has been reassigned from {$transfer->from_assignee} to {$transfer->to_assignee}.",
                    actionText: 'View Case',
                    actionUrl:  route('cases.show', $case),
                    type:       'assigned',
                ));
            }
        }

        return back()->with('success', "Transfer approved. Case reassigned to {$transfer->to_assignee}.");
    }

    public function rejectTransfer(Request $request, CaseRecord $case, \App\Models\CaseTransfer $transfer)
    {
        abort_unless($request->user()->can('cases.approve'), 403);

        $data = $request->validate([
            'approval_note' => 'required|string|min:5|max:500',
        ]);

        $transfer->update([
            'status'        => 'rejected',
            'approved_by'   => $request->user()->id,
            'decided_at'    => now(),
            'approval_note' => $data['approval_note'],
        ]);

        // Log on timeline
        \App\Models\ServiceEncounter::create([
            'case_id'      => $case->id,
            'date'         => now()->toDateString(),
            'type'         => 'Transfer Rejected',
            'performed_by' => $request->user()->name,
            'note'         => "Transfer request rejected. Reason: {$data['approval_note']}",
        ]);

        // Notify requester
        $requester = $transfer->transferredBy;
        if ($requester) {
            $requester->notify(new \App\Notifications\CaseNotification(
                title:      "Transfer request rejected — {$case->case_uid}",
                message:    "Your transfer request for {$case->case_uid} was rejected. Reason: {$data['approval_note']}",
                actionText: 'View Case',
                actionUrl:  route('cases.show', $case),
                type:       'rejected',
            ));
        }

        return back()->with('error', 'Transfer request rejected.');
    }
}
