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

        return view('cases.show', compact('case', 'assignableUsers', 'pendingTransfer'));
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
