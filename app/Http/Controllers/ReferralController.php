<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\CaseRecord;
use App\Models\ServiceEncounter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    public function index(Request $request)
    {
        $partners = Partner::with('hubs')->orderBy('name')->get();

        // KPI stats now come from CaseReferral — calculated after tracker is built below
        $totalActive = $totalCompleted = $totalFailed = $closureRate = $avgResponseHrs = 0;

        // Category config — built from lookups table, with fallback colours/icons
        $defaultStyles = [
            'Shelter'         => ['color' => 'var(--burgundy)', 'tint' => 'var(--burgundy-tint)', 'icon' => 'home'],
            'Government'      => ['color' => 'var(--forest)',   'tint' => 'rgba(22,48,41,0.08)',  'icon' => 'building-2'],
            'Law Enforcement' => ['color' => 'var(--ink-1)',    'tint' => 'rgba(36,40,45,0.08)',  'icon' => 'shield'],
            'Health'          => ['color' => 'var(--moss)',     'tint' => 'var(--moss-tint)',     'icon' => 'heart-handshake'],
            'NGO'             => ['color' => 'var(--ochre)',    'tint' => 'var(--ochre-tint)',    'icon' => 'users'],
            'Legal Aid'       => ['color' => 'var(--forest)',   'tint' => 'rgba(22,48,41,0.08)',  'icon' => 'scale'],
            'Other'           => ['color' => 'var(--ink-3)',    'tint' => 'var(--rule-2)',        'icon' => 'circle-dot'],
        ];
        $dbCategories = DB::table('lookups')
            ->where('group_key', 'partner_category')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->pluck('label');
        $categoryConfig = $dbCategories->mapWithKeys(fn($cat) => [
            $cat => $defaultStyles[$cat] ?? ['color' => 'var(--ink-3)', 'tint' => 'var(--rule-2)', 'icon' => 'circle-dot'],
        ])->toArray();

        // Load all CaseReferrals grouped by referred_to
        $allReferrals = \App\Models\CaseReferral::all();

        // Per-partner stats keyed by partner name (referred_to field)
        $partnerStats = $allReferrals->groupBy('referred_to')->map(function ($refs) {
            $completed = $refs->filter(fn($r) => $r->closed_at && $r->closed_outcome === 'Successful')->count();
            $failed    = $refs->filter(fn($r) => $r->closed_at && $r->closed_outcome !== 'Successful')->count();
            $active    = $refs->filter(fn($r) => !$r->closed_at)->count();
            return [
                'active'      => $active,
                'completed'   => $completed,
                'failed'      => $failed,
                'closureRate' => ($completed + $failed) > 0 ? round(($completed / ($completed + $failed)) * 100) : 0,
            ];
        })->toArray();

        $categoryStats = $allReferrals->groupBy('referred_to')->map(function ($refs, $referredTo) {
            $completed = $refs->filter(fn($r) => $r->closed_at && $r->closed_outcome === 'Successful')->count();
            $failed    = $refs->filter(fn($r) => $r->closed_at && $r->closed_outcome !== 'Successful')->count();
            $active    = $refs->filter(fn($r) => !$r->closed_at)->count();
            $volume    = $completed + $failed + $active;
            return [
                'category'    => $referredTo,
                'color'       => 'var(--forest)',
                'tint'        => 'rgba(22,48,41,0.08)',
                'icon'        => 'share-2',
                'active'      => $active,
                'completed'   => $completed,
                'failed'      => $failed,
                'closureRate' => ($completed + $failed) > 0 ? round(($completed / ($completed + $failed)) * 100) : 0,
                'volume'      => $volume,
            ];
        })->sortByDesc('volume')->values();

        $maxVolume = $categoryStats->max('volume') ?: 1;

        // MOU attention
        $mouAttention = $partners
            ->whereIn('mou_status', ['expiring', 'expired'])
            ->sortBy('mou_expires')
            ->values();

        // Partner filter
        $partnerFilter = $request->input('category', 'all');
        $filteredPartners = $partnerFilter === 'all'
            ? $partners
            : $partners->where('category', $partnerFilter);

        // Filter counts
        $filterCounts = collect($categoryConfig)->mapWithKeys(fn ($_, $cat) => [
            $cat => $partners->where('category', $cat)->count(),
        ]);

        // Active cases for referral modal
        $activeCases = CaseRecord::query()
            ->whereNotIn('status', ['Closed', 'Settlement', 'Rejected'])
            ->orderBy('name')
            ->get(['id', 'case_uid', 'name', 'primary_issue', 'hub_id']);

        // Referral tracker — from CaseReferral records
        $caseReferrals = \App\Models\CaseReferral::with(['caseRecord:id,case_uid,name,hub_id', 'threads'])
            ->orderByDesc('referral_date')
            ->get();

        $referralTracker = $caseReferrals->map(function ($ref) {
            // Derive stage from referral state
            if ($ref->closed_at) {
                $stage = $ref->closed_outcome === 'Successful' ? 'Completed' : 'Failed';
            } elseif ($ref->threads->count() > 0) {
                $stage = 'In progress';
            } elseif ($ref->focal_person_name) {
                $stage = 'Acknowledged';
            } else {
                $stage = 'Sent';
            }

            $days = $ref->referral_date
                ? (int) now()->startOfDay()->diffInDays(Carbon::parse($ref->referral_date)->startOfDay())
                : 0;

            $followUp = $ref->threads->sortByDesc('thread_date')->first()?->created_at ?? null;

            return [
                'ref'          => 'R-' . str_pad($ref->id, 5, '0', STR_PAD_LEFT),
                'date'         => $ref->referral_date,
                'case_uid'     => $ref->caseRecord?->case_uid ?? '—',
                'client_name'  => $ref->caseRecord?->name ?? '—',
                'hub_id'       => $ref->caseRecord?->hub_id ?? '—',
                'partner_name' => $ref->referred_to,
                'partner_cat'  => $ref->caseRecord?->assigned_pathway ?? '—',
                'urgency'      => 'Med',
                'service'      => $ref->reason ?? '—',
                'stage'        => $stage,
                'days_open'    => $days,
                'follow_up'    => $followUp,
            ];
        });

        $trackerCounts = [
            'active'    => $referralTracker->whereIn('stage', ['Sent', 'Acknowledged', 'In progress'])->count(),
            'completed' => $referralTracker->where('stage', 'Completed')->count(),
            'failed'    => $referralTracker->where('stage', 'Failed')->count(),
            'all'       => $referralTracker->count(),
        ];

        // Recalculate KPI stats from CaseReferral data
        $totalActive    = $trackerCounts['active'];
        $totalCompleted = $trackerCounts['completed'];
        $totalFailed    = $trackerCounts['failed'];
        $closureRate    = ($totalCompleted + $totalFailed) > 0
            ? round(($totalCompleted / ($totalCompleted + $totalFailed)) * 100)
            : 0;
        $avgResponseHrs = 0;

        // Incoming referrals — cases registered with referral_type = 'Incoming'
        $incomingCases = CaseRecord::where('referral_type', 'Incoming')
            ->orderByDesc('intake_date')
            ->get(['id', 'case_uid', 'name', 'primary_issue', 'hub_id', 'intake_date',
                   'referral_source', 'referral_contact_person', 'status']);
        $incomingCount = $incomingCases->count();

        // Outgoing counts
        $outgoingCount  = $referralTracker->count();
        $outgoingActive = $trackerCounts['active'];
        $outgoingClosed = $trackerCounts['completed'] + $trackerCounts['failed'];

        // Pathway summary — cases grouped by assigned_pathway
        $pathwayOrder = [
            'Government Department / Public Institution',
            'Civil Society / NGO / CSO / NPO',
            'Other',
        ];
        $pathwayIcons = [
            'Court Representation'                        => 'scale',
            'Legal Advice / Consultation'                 => 'message-circle',
            'Mediation'                                   => 'handshake',
            'ADR / Dispute Resolution Support'            => 'git-merge',
            'Government Department / Public Institution'  => 'building-2',
            'Civil Society / NGO / CSO / NPO'             => 'users',
            'Other'                                       => 'circle-dot',
        ];
        $rawCounts = CaseRecord::whereNotNull('assigned_pathway')
            ->selectRaw('assigned_pathway, COUNT(*) as total,
                SUM(CASE WHEN referral_type = "Incoming" THEN 1 ELSE 0 END) as incoming,
                SUM(CASE WHEN referral_type = "Outgoing" THEN 1 ELSE 0 END) as outgoing')
            ->groupBy('assigned_pathway')
            ->get()
            ->keyBy('assigned_pathway');

        $pathwaySummary = collect($pathwayOrder)->map(fn($p) => [
            'label'    => $p,
            'icon'     => $pathwayIcons[$p] ?? 'circle-dot',
            'total'    => $rawCounts[$p]->total    ?? 0,
            'incoming' => $rawCounts[$p]->incoming ?? 0,
            'outgoing' => $rawCounts[$p]->outgoing ?? 0,
        ])->filter(fn($p) => $p['total'] > 0)->values();

        // Specific breakdown per pathway
        $govtBreakdown = CaseRecord::where('assigned_pathway', 'Government Department / Public Institution')
            ->whereNotNull('pathway_govt_dept')
            ->selectRaw('pathway_govt_dept as name,
                COUNT(*) as total,
                SUM(CASE WHEN referral_type = "Incoming" THEN 1 ELSE 0 END) as incoming,
                SUM(CASE WHEN referral_type = "Outgoing" THEN 1 ELSE 0 END) as outgoing')
            ->groupBy('pathway_govt_dept')
            ->orderByDesc('total')
            ->get();

        $ngoBreakdown = CaseRecord::where('assigned_pathway', 'Civil Society / NGO / CSO / NPO')
            ->whereNotNull('pathway_ngo_name')
            ->selectRaw('pathway_ngo_name as name,
                COUNT(*) as total,
                SUM(CASE WHEN referral_type = "Incoming" THEN 1 ELSE 0 END) as incoming,
                SUM(CASE WHEN referral_type = "Outgoing" THEN 1 ELSE 0 END) as outgoing')
            ->groupBy('pathway_ngo_name')
            ->orderByDesc('total')
            ->get();

        $otherBreakdown = CaseRecord::where('assigned_pathway', 'Other')
            ->selectRaw('COUNT(*) as total,
                SUM(CASE WHEN referral_type = "Incoming" THEN 1 ELSE 0 END) as incoming,
                SUM(CASE WHEN referral_type = "Outgoing" THEN 1 ELSE 0 END) as outgoing')
            ->first();

        // Cases grouped for expandable lists
        $govtCases = CaseRecord::where('assigned_pathway', 'Government Department / Public Institution')
            ->whereNotNull('pathway_govt_dept')
            ->orderBy('intake_date', 'desc')
            ->get(['id', 'case_uid', 'name', 'primary_issue', 'urgency', 'status',
                   'referral_type', 'referral_contact_person', 'pathway_govt_dept', 'hub_id'])
            ->groupBy('pathway_govt_dept');

        $ngoCases = CaseRecord::where('assigned_pathway', 'Civil Society / NGO / CSO / NPO')
            ->whereNotNull('pathway_ngo_name')
            ->orderBy('intake_date', 'desc')
            ->get(['id', 'case_uid', 'name', 'primary_issue', 'urgency', 'status',
                   'referral_type', 'referral_contact_person', 'pathway_ngo_name', 'hub_id'])
            ->groupBy('pathway_ngo_name');

        $otherCases = CaseRecord::where('assigned_pathway', 'Other')
            ->orderBy('intake_date', 'desc')
            ->get(['id', 'case_uid', 'name', 'primary_issue', 'urgency', 'status',
                   'referral_type', 'referral_contact_person', 'pathway_other_details', 'hub_id']);

        return view('referrals.index', compact(
            'partners', 'filteredPartners',
            'totalActive', 'totalCompleted', 'totalFailed',
            'closureRate', 'avgResponseHrs',
            'categoryStats', 'categoryConfig', 'maxVolume',
            'mouAttention', 'partnerFilter', 'filterCounts',
            'activeCases', 'referralTracker', 'trackerCounts',
            'partnerStats',
            'incomingCases', 'incomingCount',
            'outgoingCount', 'outgoingActive', 'outgoingClosed',
            'pathwaySummary', 'govtBreakdown', 'ngoBreakdown', 'otherBreakdown',
            'govtCases', 'ngoCases', 'otherCases',
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'case_id'             => 'required|exists:case_records,id',
            'partner_id'          => 'required|exists:partners,id',
            'partner_category'    => 'required|string|max:80',
            'service_description' => 'required|string|max:2000',
            'urgency'             => 'required|in:Low,Med,High',
            'expected_by'         => 'nullable|date',
            'follow_up_date'      => 'nullable|date',
            'partner_notes'       => 'nullable|string|max:2000',
            'client_consent'      => 'required|accepted',
        ]);

        $case    = CaseRecord::findOrFail($data['case_id']);
        $partner = Partner::findOrFail($data['partner_id']);

        // Log as a service encounter
        ServiceEncounter::create([
            'case_id'      => $case->id,
            'date'         => today(),
            'type'         => 'External Referral',
            'performed_by' => auth()->user()?->name ?? 'System',
            'note'         => "Referred to {$partner->name} ({$partner->category}). {$data['service_description']}",
            'meta'         => array_filter([
                'partner_id'          => $partner->id,
                'partner_name'        => $partner->name,
                'partner_category'    => $data['partner_category'],
                'urgency'             => $data['urgency'],
                'service_description' => $data['service_description'],
                'expected_by'         => $data['expected_by'] ?? null,
                'follow_up_date'      => $data['follow_up_date'] ?? null,
                'partner_notes'       => $data['partner_notes'] ?? null,
                'pipeline_stage'      => 'Sent',
            ]),
        ]);

        // Update partner's last referral date
        $partner->update(['last_referral_date' => today()]);

        // Touch case last_update
        $case->update(['last_update' => today()]);

        return back()->with('success', "Referral to {$partner->name} logged for case {$case->case_uid}.");
    }
}
