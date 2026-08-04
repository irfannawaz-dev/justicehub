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

        // Only referrals from external-partner pathways (not ADR / Mediation / Legal Advice / Litigation)
        $externalPathways = ['Government Department / Public Institution', 'Civil Society / NGO / CSO / NPO', 'Other'];

        // Load all CaseReferrals grouped by referred_to — external pathways only
        $allReferrals = \App\Models\CaseReferral::whereHas('caseRecord', fn($q) => $q->whereIn('assigned_pathway', $externalPathways))->get();

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

        // Referral tracker — built from CaseRecord (external pathways only)
        $trackerCases = CaseRecord::whereIn('assigned_pathway', $externalPathways)
            ->orderByDesc('intake_date')
            ->get(['id', 'case_uid', 'name', 'hub_id', 'assigned_pathway', 'pathway_govt_dept',
                   'pathway_ngo_name', 'pathway_other_details', 'status', 'intake_date',
                   'referral_type', 'urgency', 'primary_issue']);

        $referralTracker = $trackerCases->map(function ($c) {
            // Stage from case status
            $stage = match (true) {
                in_array($c->status, [\App\Enums\CaseStatus::Closed, \App\Enums\CaseStatus::Settlement]) => 'Completed',
                $c->status === \App\Enums\CaseStatus::Rejected => 'Failed',
                default => 'In progress',
            };

            // Referred-to name from pathway-specific field
            $referredTo = match ($c->assigned_pathway) {
                'Government Department / Public Institution' => $c->pathway_govt_dept ?? 'Government Dept',
                'Civil Society / NGO / CSO / NPO'           => $c->pathway_ngo_name  ?? 'NGO / CSO',
                default                                      => $c->pathway_other_details ?? 'Other',
            };

            $days = $c->intake_date
                ? (int) $c->intake_date->startOfDay()->diffInDays(now()->startOfDay())
                : 0;

            return [
                'case_id'      => $c->id,
                'ref'          => $c->case_uid,
                'date'         => $c->intake_date?->toDateString(),
                'case_uid'     => $c->case_uid,
                'client_name'  => $c->name,
                'hub_id'       => $c->hub_id,
                'partner_name' => $referredTo,
                'partner_cat'  => $c->assigned_pathway,
                'urgency'      => $c->urgency ?? '—',
                'service'      => $c->primary_issue ?? '—',
                'stage'        => $stage,
                'days_open'    => $days,
                'follow_up'    => null,
            ];
        });

        $trackerCounts = [
            'active'    => $referralTracker->where('stage', 'In progress')->count(),
            'completed' => $referralTracker->where('stage', 'Completed')->count(),
            'failed'    => $referralTracker->where('stage', 'Failed')->count(),
            'all'       => $referralTracker->count(),
        ];

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

        // Referral Network KPI summary (Govt + NGO + Other pathways only)
        $referralPathways = $externalPathways;
        $referralKpi = \App\Models\CaseRecord::whereIn('assigned_pathway', $referralPathways)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status IN ("Closed","Settlement") THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN status NOT IN ("Closed","Settlement") THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN referral_type = "Incoming" THEN 1 ELSE 0 END) as incoming,
                SUM(CASE WHEN referral_type = "Outgoing" THEN 1 ELSE 0 END) as outgoing
            ')->first();

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

        // Chart data: pathway ranking (left chart)
        $pathwayRanking = collect([
            'Government Department / Public Institution' => ['label' => 'Government / Public Institution', 'color' => '#2f5c3a'],
            'Civil Society / NGO / CSO / NPO'           => ['label' => 'Civil Society / NGO / CSO',       'color' => '#b87319'],
            'Other'                                      => ['label' => 'Other Pathway',                   'color' => '#6b6a65'],
        ])->map(fn($meta, $pw) => [
            'label' => $meta['label'],
            'color' => $meta['color'],
            'total' => $rawCounts[$pw]->total    ?? 0,
            'pct'   => ($referralKpi->total ?? 0) > 0
                        ? round((($rawCounts[$pw]->total ?? 0) / $referralKpi->total) * 100)
                        : 0,
        ])->sortByDesc('total')->values();

        // Chart data: named partners (right chart) — top orgs referred to
        $namedPartners = $referralTracker
            ->groupBy('partner_name')
            ->map(fn($rows, $name) => ['label' => $name, 'total' => $rows->count()])
            ->sortByDesc('total')
            ->take(12)
            ->values();

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
            'govtCases', 'ngoCases', 'otherCases', 'referralKpi',
            'pathwayRanking', 'namedPartners',
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
