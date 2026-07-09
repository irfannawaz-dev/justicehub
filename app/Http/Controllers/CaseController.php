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
        if ($user->isLawyer()) {
            $base->where('assigned_to', $user->name)
                 ->whereNotIn('assigned_pathway', [
                     'Mediation',
                     'ADR / Dispute Resolution Support',
                 ]);
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
        if ($request->filled('district') && $request->district !== 'all') {
            $base->where('district', $request->district);
        }
        if ($request->filled('pathway') && $request->pathway !== 'all') {
            $pw = $request->pathway;
            $base->where(function ($q) use ($pw) {
                if ($pw === 'mediation') {
                    $q->where('assigned_pathway', 'Mediation');
                } elseif ($pw === 'adr') {
                    $q->where('assigned_pathway', 'ADR / Dispute Resolution Support');
                } elseif ($pw === 'court') {
                    $q->whereIn('assigned_pathway', ['Court Representation', 'Representation in Court']);
                } elseif ($pw === 'referred') {
                    $q->whereIn('assigned_pathway', ['Referral', 'Government Department / Public Institution', 'Civil Society / NGO / CSO / NPO']);
                } elseif ($pw === 'legal_advice') {
                    $q->where('assigned_pathway', 'Legal Advice / Consultation');
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
        if ($user->isLawyer()) {
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

        // Disposition counts — derived from assigned_pathway when disposition is null
        $totalAll = (clone $hubBase)->count();

        $litigationPathways = ['Court Representation', 'Representation in Court'];
        $adrPathways        = ['Mediation', 'ADR / Dispute Resolution Support'];
        $referredPathways   = ['Government Department / Public Institution', 'Civil Society / NGO / CSO / NPO', 'Referral', 'Other'];
        $advicePathways     = ['Legal Advice / Consultation', 'Information & Awareness'];

        $dispositionCounts = [
            'all'         => $totalAll,
            'advice-only' => (clone $hubBase)->where(fn($q) => $q->where('disposition', 'advice-only')->orWhere(fn($q2) => $q2->whereNull('disposition')->whereIn('assigned_pathway', $advicePathways)))->count(),
            'litigation'  => (clone $hubBase)->where(fn($q) => $q->where('disposition', 'litigation')->orWhere(fn($q2) => $q2->whereNull('disposition')->whereIn('assigned_pathway', $litigationPathways)))->count(),
            'adr'         => (clone $hubBase)->where(fn($q) => $q->where('disposition', 'adr')->orWhere(fn($q2) => $q2->whereNull('disposition')->whereIn('assigned_pathway', $adrPathways)))->count(),
            'referred'    => (clone $hubBase)->where(fn($q) => $q->where('disposition', 'referred')->orWhere(fn($q2) => $q2->whereNull('disposition')->whereIn('assigned_pathway', $referredPathways)))->count(),
            'pending'     => (clone $hubBase)->where(fn($q) => $q->whereNull('disposition')->orWhere('disposition', ''))->whereNotIn('assigned_pathway', array_merge($litigationPathways, $adrPathways, $referredPathways, $advicePathways))->count(),
        ];

        // Service pathway counts — matching actual lookup values stored in DB
        $hubCaseIds = (clone $hubBase)->pluck('id');
        $pathwayCounts = [
            'legal_advice'  => (clone $hubBase)->where('assigned_pathway', 'Legal Advice / Consultation')->count(),
            'mediation'     => (clone $hubBase)->where('assigned_pathway', 'Mediation')->count(),
            'adr'           => (clone $hubBase)->where('assigned_pathway', 'ADR / Dispute Resolution Support')->count(),
            'court'         => (clone $hubBase)->whereIn('assigned_pathway', ['Court Representation', 'Representation in Court'])->count(),
            'referred'      => (clone $hubBase)->whereIn('assigned_pathway', ['Government Department / Public Institution', 'Civil Society / NGO / CSO / NPO', 'Referral', 'Other'])->count(),
            'info_awareness'=> (clone $hubBase)->where('assigned_pathway', 'Information & Awareness')->count(),
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
            $d = $request->disposition;
            $query->where(function($q) use ($d, $litigationPathways, $adrPathways, $referredPathways, $advicePathways) {
                $q->where('disposition', $d);
                // Also include cases where disposition is null but pathway implies this disposition
                $pathwayMap = [
                    'litigation'  => $litigationPathways,
                    'adr'         => $adrPathways,
                    'referred'    => $referredPathways,
                    'advice-only' => $advicePathways,
                ];
                if (isset($pathwayMap[$d])) {
                    $q->orWhere(fn($q2) => $q2->whereNull('disposition')->whereIn('assigned_pathway', $pathwayMap[$d]));
                }
            });
        }
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'active') $query->where('status', 'Active');
            elseif ($request->status === 'closed') $query->whereIn('status', ['Closed', 'Settlement']);
            elseif ($request->status === 'safeguarding') $query->where(fn($q) => $q->where('is_gbv', true)->orWhere('is_child', true));
            elseif ($request->status === 'sla') $query->where('sla_met', false);
        }

        $cases = $query->orderByDesc('id')->paginate(config('justice_hub.per_page.cases', 25));
        $hubs = Hub::where('is_active', true)->get();

        $lookupDistricts = \App\Models\Lookup::where('group_key', 'intake.district')
            ->where('is_active', true)->orderBy('sort_order')->pluck('value')->toArray();
        $caseDistricts = CaseRecord::whereNotNull('district')->where('district', '!=', '')
            ->distinct()->pluck('district')->toArray();
        $availableDistricts = collect(array_unique(array_merge($lookupDistricts, $caseDistricts)))
            ->sort()->values()->toArray();

        return view('cases.index', compact('cases', 'counts', 'dispositionCounts', 'pathwayCounts', 'hubs', 'availableDistricts'));
    }

    public function slip(CaseRecord $case)
    {
        $case->load('hub');
        $lawyerPhone = $case->getAssignedUser()?->contact_number;
        return view('cases.slip', compact('case', 'lawyerPhone'));
    }

    public function show(CaseRecord $case)
    {
        // Lawyers cannot access mediation / ADR cases
        if (auth()->user()->isLawyer() && in_array($case->assigned_pathway, [
            'Mediation',
            'ADR / Dispute Resolution Support',
        ])) {
            abort(403, 'Lawyers are not permitted to view mediation cases.');
        }

        // Hub scope enforced via Route::bind() in AppServiceProvider
        $case->load(['serviceEncounters', 'documents', 'complaints', 'feedback', 'hub', 'transfers.transferredBy', 'transfers.approvedBy', 'mediationParties', 'mediationDiary', 'caseReferrals.letters', 'caseReferrals.threads', 'messages.sender']);

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
            ->get(['id', 'name', 'role', 'hub_id', 'designation']);

        // All active staff across hubs (for pathway transfers to different hubs)
        $allStaff = \App\Models\User::where('is_active', true)
            ->whereNotIn('role', ['viewer', 'me-lead', 'complaint-investigator'])
            ->orderBy('hub_id')->orderBy('name')
            ->get(['id', 'name', 'role', 'hub_id', 'designation']);

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
                        'programName', 'caseReferred', 'caseReferredSubCategory',
                        'interviewEligibleForZakat', 'complainantName', 'partyName',
                        'caseApprovalStatus', 'approvalDate',
                        'vakalatnamaSubmissionDate', 'caseFileDate',
                        'lawyer1', 'reasonOfChange',
                        'courtName', 'levelOfCourt', 'caseNumber',
                        'firNumber', 'policeStation', 'natureOfCase', 'typeOfCase',
                        'mainCaseCategory', 'caseFiledUnderAct', 'caseFiledOther',
                        'nextHearing', 'currentCaseStatus',
                        'caseDecision', 'caseDisposalDate', 'ctcStatus',
                        'caseStage', 'caseStageOther', 'additionalComment',
                        'UniqueNumber2',
                    ])
                    ->first();

                // Change history from programs_detail
                $cmsHistory = $cmsDb->table('programs_detail')
                    ->where('programsid', $case->external_case_id)
                    ->orderBy('created_at')
                    ->get([
                        'change_type', 'created_at', 'edited_by', 'username',
                        'currentCaseStatus', 'caseStage', 'nextHearing',
                        'lawyer1', 'courtName', 'levelOfCourt',
                        'caseNumber', 'firNumber', 'policeStation',
                        'natureOfCase', 'mainCaseCategory', 'typeOfCase',
                        'caseFiledUnderAct', 'caseDecision', 'caseDisposalDate',
                        'ctcStatus', 'approvalDate', 'caseApprovalStatus',
                        'vakalatnamaSubmissionDate', 'caseFileDate',
                        'reasonOfChange', 'additionalComment',
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
        $cmsCreateSeen = false;
        foreach ($cmsHistory as $h) {
            $rawCreate = ($h->change_type ?? '') === 'create';
            $isCreate  = $rawCreate && !$cmsCreateSeen;
            if ($rawCreate) $cmsCreateSeen = true;
            $by       = $h->edited_by ?: $h->username ?: 'LAS CMS';
            $parts    = [];
            if ($h->currentCaseStatus)         $parts[] = "Status: {$h->currentCaseStatus}";
            if ($h->caseApprovalStatus)        $parts[] = "Approval: {$h->caseApprovalStatus}";
            if ($h->caseStage)                 $parts[] = "Stage: {$h->caseStage}";
            if ($h->caseDecision)              $parts[] = "Decision: {$h->caseDecision}";
            if ($h->nextHearing)               $parts[] = "Next hearing: {$h->nextHearing}";
            if ($h->lawyer1)                   $parts[] = "Lawyer: {$h->lawyer1}";
            if ($h->courtName)                 $parts[] = "Court: {$h->courtName}";
            if ($h->levelOfCourt)              $parts[] = "Level: {$h->levelOfCourt}";
            if ($h->caseNumber)                $parts[] = "Case no: {$h->caseNumber}";
            if ($h->firNumber)                 $parts[] = "FIR: {$h->firNumber}";
            if ($h->policeStation)             $parts[] = "Police station: {$h->policeStation}";
            if ($h->natureOfCase)              $parts[] = "Nature: {$h->natureOfCase}";
            if ($h->mainCaseCategory)          $parts[] = "Category: {$h->mainCaseCategory}";
            if ($h->typeOfCase)                $parts[] = "Type: {$h->typeOfCase}";
            if ($h->caseFiledUnderAct)         $parts[] = "Filed under: {$h->caseFiledUnderAct}";
            if ($h->ctcStatus)                 $parts[] = "CTC: {$h->ctcStatus}";
            if ($h->approvalDate)              $parts[] = "Approved: {$h->approvalDate}";
            if ($h->vakalatnamaSubmissionDate) $parts[] = "Vakalatnama: {$h->vakalatnamaSubmissionDate}";
            if ($h->caseFileDate)              $parts[] = "Case filed: {$h->caseFileDate}";
            if ($h->caseDisposalDate)          $parts[] = "Disposed: {$h->caseDisposalDate}";
            if ($h->reasonOfChange)            $parts[] = "Reason: {$h->reasonOfChange}";
            if ($h->additionalComment)         $parts[] = $h->additionalComment;

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

        // Mediation stepper step (restored from flash)
        $mstep = session('flash_mstep', 1);
        if ($case->mediationParties->where('consent_status', 'agreed')->count() > 0) {
            $mstep = max($mstep, 3);
        } elseif ($case->mediationParties->count() > 0) {
            $mstep = max($mstep, 1);
        }

        $caseReferrals = $case->caseReferrals;

        return view('cases.show', compact('case', 'assignableUsers', 'allStaff', 'pendingTransfer', 'timeline', 'cmsData', 'mstep', 'caseReferrals'));
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

        if ($case->external_case_id) {
            (new \App\Services\LasCmsSyncService())->updateStatus($case->fresh());
        }

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

        if ($case->external_case_id) {
            (new \App\Services\LasCmsSyncService())->updateStatus($case->fresh());
        }

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

        $isAdr = $case->assigned_pathway === 'ADR / Dispute Resolution Support';
        $data = $request->validate([
            'outcome'         => 'required|in:Won,Partial,Lost,Withdrawn,Settlement,In Favour,Against',
            'resolution_type' => $isAdr ? 'nullable|in:Closed,Settlement' : 'required|in:Closed,Settlement',
            'disposed_date'   => 'required|date',
            'resolution_note' => 'nullable|string|max:2000',
        ]);
        if ($isAdr) {
            $data['resolution_type'] = 'Closed';
        }

        $case->update([
            'status'      => $data['resolution_type'],
            'last_update' => now(),
            'meta'        => array_merge($case->meta ?? [], [
                'outcome'         => $data['outcome'],
                'disposed_date'   => $data['disposed_date'],
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
        try {
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
        } catch (\Exception $e) {
            \Log::warning("Case resolve notification failed for {$case->case_uid}: " . $e->getMessage());
        }

        return back()->with('success', "Case resolved as {$data['outcome']}.");
    }

    public function reassign(Request $request, CaseRecord $case)
    {
        abort_unless($request->user()->can('cases.edit'), 403);

        $isPathway = $request->input('transfer_type') === 'pathway';

        $data = $request->validate([
            'transfer_type'       => 'required|in:staff,pathway',
            'to_assignee'         => 'required|string|max:150',
            'to_pathway'          => $isPathway ? 'required|string|max:255' : 'nullable',
            'to_pathway_specific' => 'nullable|string|max:255',
            'transfer_date'       => 'required|date',
            'reason'              => 'required|string|min:10|max:1000',
        ]);

        // Block if a pending transfer already exists
        if ($case->transfers()->where('status', 'pending')->exists()) {
            return back()->with('error', 'A transfer request is already pending approval for this case.');
        }

        $transfer = \App\Models\CaseTransfer::create([
            'case_id'              => $case->id,
            'transfer_type'        => $data['transfer_type'],
            'from_assignee'        => $case->assigned_to,
            'to_assignee'          => $data['to_assignee'],
            'from_pathway'         => $case->assigned_pathway,
            'to_pathway'           => $data['to_pathway'] ?? null,
            'to_pathway_specific'  => $data['to_pathway_specific'] ?? null,
            'transferred_by'       => $request->user()->id,
            'transfer_date'        => $data['transfer_date'],
            'reason'               => $data['reason'],
            'status'               => 'pending',
        ]);

        // Log on timeline
        $logNote = $isPathway
            ? "Pathway transfer requested: {$case->assigned_pathway} → {$data['to_pathway']}, staff {$transfer->from_assignee} → {$data['to_assignee']}. Reason: {$data['reason']}"
            : "Staff reassignment requested: {$transfer->from_assignee} → {$data['to_assignee']}. Reason: {$data['reason']}";

        \App\Models\ServiceEncounter::create([
            'case_id'      => $case->id,
            'date'         => now()->toDateString(),
            'type'         => 'Transfer Request',
            'performed_by' => $request->user()->name,
            'note'         => $logNote,
        ]);

        // Notify Head / approvers
        $approvers = \App\Models\User::where('hub_id', $case->hub_id)
            ->whereIn('role', ['head', 'provincial-lead', 'hub-coordinator'])
            ->where('id', '!=', $request->user()->id)
            ->get();

        $notifMsg = $isPathway
            ? "{$request->user()->name} requested pathway transfer for {$case->case_uid}: {$case->assigned_pathway} → {$data['to_pathway']}, assign to {$data['to_assignee']}. Reason: {$data['reason']}"
            : "{$request->user()->name} requested reassignment of {$case->case_uid} from {$transfer->from_assignee} to {$data['to_assignee']}. Reason: {$data['reason']}";

        foreach ($approvers as $approver) {
            $approver->notify(new \App\Notifications\CaseNotification(
                title:      "Transfer request — {$case->case_uid}",
                message:    $notifMsg,
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
            'approval_note' => $data['approval_note'] ?? null,
        ]);

        // Actually reassign the case
        $updateData = [
            'assigned_to' => $transfer->to_assignee,
            'last_update' => now()->toDateString(),
        ];

        // If pathway transfer, also update the pathway
        if ($transfer->transfer_type === 'pathway' && $transfer->to_pathway) {
            $updateData['assigned_pathway']   = $transfer->to_pathway;
            $updateData['pathway_specific']   = $transfer->to_pathway_specific;
        }

        $case->update($updateData);

        // Log on timeline
        $logNote = $transfer->transfer_type === 'pathway'
            ? "Pathway transfer approved. {$transfer->from_pathway} → {$transfer->to_pathway}, staff {$transfer->from_assignee} → {$transfer->to_assignee}."
            : "Transfer approved. Case reassigned from {$transfer->from_assignee} to {$transfer->to_assignee}.";

        \App\Models\ServiceEncounter::create([
            'case_id'      => $case->id,
            'date'         => now()->toDateString(),
            'type'         => 'Transfer Approved',
            'performed_by' => $request->user()->name,
            'note'         => $logNote . (!empty($data['approval_note']) ? " Note: {$data['approval_note']}" : ''),
        ]);

        // Notify old and new assignee
        $notifMsg = $transfer->transfer_type === 'pathway'
            ? "Case {$case->case_uid} transferred: {$transfer->from_pathway} → {$transfer->to_pathway}, assigned to {$transfer->to_assignee}."
            : "Case {$case->case_uid} reassigned from {$transfer->from_assignee} to {$transfer->to_assignee}.";

        foreach ([$transfer->from_assignee, $transfer->to_assignee] as $name) {
            $target = \App\Models\User::where('name', $name)->first();
            if ($target) {
                $target->notify(new \App\Notifications\CaseNotification(
                    title:      "Case transfer approved — {$case->case_uid}",
                    message:    $notifMsg,
                    actionText: 'View Case',
                    actionUrl:  route('cases.show', $case),
                    type:       'assigned',
                ));
            }
        }

        $successMsg = $transfer->transfer_type === 'pathway'
            ? "Transfer approved. Case moved to {$transfer->to_pathway} and assigned to {$transfer->to_assignee}."
            : "Transfer approved. Case reassigned to {$transfer->to_assignee}.";

        return back()->with('success', $successMsg);
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

    // ── Referral: create ─────────────────────────────────────────────────────
    public function storeReferral(Request $request, CaseRecord $case)
    {
        $data = $request->validate([
            'referred_to'          => 'required|string|max:255',
            'referral_date'        => 'required|date',
            'reason'               => 'nullable|string|max:2000',
            'referred_by'          => 'nullable|string|max:255',
            'filing_status'        => 'nullable|in:Filed,Not Filed',
            'tracking_number'      => 'nullable|string|max:255',
            'filing_justification' => 'nullable|string|max:2000',
        ]);

        $data['case_id']     = $case->id;
        $data['status']      = 'Active';
        $data['referred_by'] = $data['referred_by'] ?? auth()->user()->name;

        // Clear irrelevant field based on filing status
        if (($data['filing_status'] ?? null) === 'Filed') {
            $data['filing_justification'] = null;
        } elseif (($data['filing_status'] ?? null) === 'Not Filed') {
            $data['tracking_number'] = null;
        }

        \App\Models\CaseReferral::create($data);

        return redirect()->route('cases.show', $case)
            ->with('success', 'Referral created.')
            ->with('activeTab', 'referrals');
    }

    // ── Referral: update focal person ────────────────────────────────────────
    public function updateReferralFocal(Request $request, CaseRecord $case, \App\Models\CaseReferral $referral)
    {
        abort_unless($referral->case_id === $case->id, 404);
        abort_if($referral->isClosed(), 403, 'Referral is closed.');

        $data = $request->validate([
            'focal_person_name'        => 'nullable|string|max:255',
            'focal_person_designation' => 'nullable|string|max:255',
            'focal_person_phone'       => 'nullable|string|max:50',
            'focal_person_email'       => 'nullable|email|max:255',
            'follow_up_date'           => 'nullable|date',
            'partner_tracking_ref'     => 'nullable|string|max:255',
        ]);

        $referral->update($data);

        return redirect()->route('cases.show', $case)
            ->with('success', 'Focal person updated.')
            ->with('activeTab', 'referrals');
    }

    // ── Referral: log letter ─────────────────────────────────────────────────
    public function storeReferralLetter(Request $request, CaseRecord $case, \App\Models\CaseReferral $referral)
    {
        abort_unless($referral->case_id === $case->id, 404);
        abort_if($referral->isClosed(), 403, 'Referral is closed.');

        $data = $request->validate([
            'our_ref'     => 'nullable|string|max:255',
            'note'        => 'nullable|string|max:2000',
            'letter_date' => 'required|date',
            'letter_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $data['case_referral_id'] = $referral->id;
        $data['logged_by']        = auth()->user()->name;

        if ($request->hasFile('letter_file')) {
            $file              = $request->file('letter_file');
            $data['file_path'] = $file->store('referral-letters/' . $case->id, 'public');
            $data['file_name'] = $file->getClientOriginalName();
        }

        unset($data['letter_file']);
        \App\Models\CaseReferralLetter::create($data);

        return redirect()->route('cases.show', $case)
            ->with('success', 'Letter logged.')
            ->with('activeTab', 'referrals');
    }

    // ── Referral: add thread entry ───────────────────────────────────────────
    public function storeReferralThread(Request $request, CaseRecord $case, \App\Models\CaseReferral $referral)
    {
        abort_unless($referral->case_id === $case->id, 404);
        abort_if($referral->isClosed(), 403, 'Referral is closed.');

        $data = $request->validate([
            'direction'            => 'required|in:from_partner,from_us',
            'type'                 => 'required|in:Email,Phone,Meeting,Letter',
            'thread_date'          => 'required|date',
            'note'                 => 'nullable|string|max:4000',
            'follow_up_date'       => 'nullable|date',
            'partner_tracking_ref' => 'nullable|string|max:255',
        ]);

        // Update referral-level fields if provided
        $referralUpdate = array_filter([
            'follow_up_date'       => $data['follow_up_date'] ?? null,
            'partner_tracking_ref' => $data['partner_tracking_ref'] ?? null,
        ]);
        if ($referralUpdate) {
            $referral->update($referralUpdate);
        }

        \App\Models\CaseReferralThread::create([
            'case_referral_id' => $referral->id,
            'direction'        => $data['direction'],
            'type'             => $data['type'],
            'thread_date'      => $data['thread_date'],
            'note'             => $data['note'] ?? null,
            'logged_by'        => auth()->user()->name,
        ]);

        return redirect()->route('cases.show', $case)
            ->with('success', 'Follow-up entry added.')
            ->with('activeTab', 'referrals');
    }

    // ── Referral: close ──────────────────────────────────────────────────────
    public function closeReferral(Request $request, CaseRecord $case, \App\Models\CaseReferral $referral)
    {
        abort_unless($referral->case_id === $case->id, 404);
        abort_if($referral->isClosed(), 403, 'Already closed.');

        $data = $request->validate([
            'closed_outcome' => 'required|string|max:255',
            'closed_note'    => 'nullable|string|max:4000',
            'closed_at'      => 'required|date',
        ]);

        $referral->update([
            'status'         => 'Closed',
            'closed_at'      => $data['closed_at'],
            'closed_outcome' => $data['closed_outcome'],
            'closed_note'    => $data['closed_note'],
        ]);

        return redirect()->route('cases.show', $case)
            ->with('success', 'Referral closed.')
            ->with('activeTab', 'referrals');
    }

    // ── Referral: delete ─────────────────────────────────────────────────────
    public function destroyReferral(CaseRecord $case, \App\Models\CaseReferral $referral)
    {
        abort_unless($referral->case_id === $case->id, 404);

        $referral->delete();

        return redirect()->route('cases.show', $case)
            ->with('success', 'Referral deleted.')
            ->with('activeTab', 'referrals');
    }

    // ── Edit Intake ─────────────────────────────────────────────────────────────
    public function updateIntake(Request $request, CaseRecord $case)
    {
        $user = $request->user();
        abort_unless($user->can('cases.edit') || ($user->isHubCoordinator() && $case->assigned_to === $user->name), 403);

        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'father_husband_name' => 'nullable|string|max:255',
            'gender'              => 'nullable|string|max:50',
            'age'                 => 'nullable|integer|min:0|max:120',
            'cnic'                => 'nullable|string|max:15',
            'primary_contact'     => 'nullable|string|max:30',
            'alternative_contact' => 'nullable|string|max:30',
            'marital_status'      => 'nullable|string|max:50',
            'religion'            => 'nullable|string|max:50',
            'education_level'     => 'nullable|string|max:100',
            'occupation'          => 'nullable|string|max:100',
            'income_bracket'      => 'nullable|string|max:100',
            'disability_status'   => 'nullable|string|max:100',
            'district'            => 'nullable|string|max:100',
            'tehsil'              => 'nullable|string|max:100',
            'union_council'       => 'nullable|string|max:100',
            'language'            => 'nullable|string|max:50',
            'referral_source'     => 'nullable|string|max:255',
            'primary_issue'       => 'nullable|string|max:100',
            'secondary_issue'     => 'nullable|string|max:100',
            'urgency'             => 'nullable|string|max:50',
            'issue_description'   => 'nullable|string|max:5000',
        ]);

        $case->update(array_merge($data, ['last_update' => now()]));

        return redirect()->route('cases.show', $case)
            ->with('activeTab', 'overview')
            ->with('success', 'Intake information updated.');
    }

    // ── Case Messages (Coordinator ↔ Lawyer/Mediator) ─────────────────────────
    public function storeMessage(Request $request, CaseRecord $case)
    {
        $user = $request->user();

        // Only Hub Coordinator, assigned Lawyer/Mediator, or Head may post
        $isCoordinator = $user->isHubCoordinator();
        $isAssigned    = $case->assigned_to && $user->name === $case->assigned_to;
        $isHead        = $user->isHead();

        abort_unless($isCoordinator || $isAssigned || $isHead, 403);

        $request->validate(['body' => 'required|string|max:3000']);

        \App\Models\CaseMessage::create([
            'case_id'   => $case->id,
            'sender_id' => $user->id,
            'body'      => $request->body,
        ]);

        // Notify all other parties who should see this thread
        try {
            $parties = collect();

            // 1. Always notify the assigned person (if set and not the sender)
            if ($case->assigned_to) {
                $assignee = \App\Models\User::where('name', $case->assigned_to)->first();
                if ($assignee) $parties->push($assignee);
            }

            // 2. Always notify Hub Coordinator(s) at this hub
            $coords = \App\Models\User::where('hub_id', $case->hub_id)
                ->where('role', \App\Enums\UserRole::HubCoordinator->value)
                ->get();
            $parties = $parties->merge($coords);

            // 3. Notify Head users globally
            $heads = \App\Models\User::where('role', \App\Enums\UserRole::Head->value)->get();
            $parties = $parties->merge($heads);

            // Exclude the sender, deduplicate
            $recipients = $parties->unique('id')->where('id', '!=', $user->id)->values();

            $preview = mb_strlen($request->body) > 80
                ? mb_substr($request->body, 0, 80) . '…'
                : $request->body;

            foreach ($recipients as $recipient) {
                $recipient->notify(new \App\Notifications\CaseNotification(
                    title:      "New case note — {$case->case_uid}",
                    message:    "{$user->name}: {$preview}",
                    actionText: 'View Case Notes',
                    actionUrl:  route('cases.show', $case) . '#messages',
                    type:       'message',
                ));
            }
        } catch (\Exception $e) {
            \Log::warning("Case note notification failed for {$case->case_uid}: " . $e->getMessage());
        }

        return redirect()->route('cases.show', $case)
            ->with('activeTab', 'messages')
            ->with('success', 'Message sent.');
    }

    public function exportExcel(Request $request)
    {
        abort_unless($request->user()->can('reports.export'), 403);

        $hubId   = $request->input('hub', 'all');
        $status  = $request->input('status', 'all');
        $pathway = $request->input('pathway', 'all');

        $filename = 'justice-hub-cases-' . now()->format('Y-m-d') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CasesExport($hubId, $status, $pathway),
            $filename
        );
    }
}
