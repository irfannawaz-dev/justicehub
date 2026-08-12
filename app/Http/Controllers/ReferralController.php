<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\CaseRecord;
use App\Models\ServiceEncounter;
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

        // Source → Group map and reverse map
        $sourceGroupMap = [
            'Community Outreach / Awareness Session' => 'Community outreach',
            'Paralegal'                              => 'Paralegal network',
            'Word of Mouth / Friend / Family'        => 'Word of mouth',
            'Government Department'                  => 'Government',
            'NGO / CSO / NPO'                        => 'Civil society',
            'Court / Judicial Officer'               => 'Legal / Judicial',
            'Bar Association'                        => 'Legal / Judicial',
            'Community Leader / Local Representative'=> 'Community outreach',
            'District / Range Peace Committee'       => 'Civil society',
            'Shelter / Protection Service'           => 'Social services',
            'Office Staff'                           => 'Internal',
            'Phone Call / Helpline'                  => 'Digital / Media',
            'Website / Social Media'                 => 'Digital / Media',
            'SMS / WhatsApp Message'                 => 'Digital / Media',
            'Radio / TV / Newspaper'                 => 'Digital / Media',
            'Google Search / Google Maps'            => 'Digital / Media',
            'QR Code / Referral Card'                => 'Digital / Media',
            'Walk-in / Passing by the Office'        => 'Walk-in',
            'Friend / Family / Word of Mouth'        => 'Word of mouth',
            'Other - please specify'                 => 'Other',
        ];
        $groupDotColors = [
            'Community outreach' => '#163029',
            'Paralegal network'  => '#2f7a4d',
            'Word of mouth'      => '#6b6a65',
            'Government'         => '#1d6ea8',
            'Civil society'      => '#b87319',
            'Legal / Judicial'   => '#4a4078',
            'Social services'    => '#8a2e1d',
            'Digital / Media'    => '#4a7a5c',
            'Walk-in'            => '#a07830',
            'Internal'           => '#3d5a52',
            'Other'              => '#9a9a94',
        ];

        // All unique channel groups for filter dropdown
        $channelGroups = collect($sourceGroupMap)->values()->unique()->sort()->values();

        // Reverse: group → [source, source, ...]
        $groupToSources = collect($sourceGroupMap)
            ->groupBy(fn($group) => $group)
            ->map(fn($items) => $items->keys()->toArray());

        // Table/chart filters
        $filterFrom    = $request->input('from');
        $filterTo      = $request->input('to');
        $filterHub     = $request->input('hub', 'all');
        $filterChannel = $request->input('channel', 'all');
        $filterPathway = $request->input('pathway', 'all');

        // Pathway scope — narrow to specific external pathway if requested
        $pathwayLabelMap = [
            'govt'  => 'Government Department / Public Institution',
            'ngo'   => 'Civil Society / NGO / CSO / NPO',
            'other' => 'Other',
        ];
        $filteredPathways = $filterPathway !== 'all' && isset($pathwayLabelMap[$filterPathway])
            ? [$pathwayLabelMap[$filterPathway]]
            : $externalPathways;

        // Filtered base builder factory (returns a fresh query each time)
        $filteredBase = fn() => CaseRecord::whereIn('assigned_pathway', $filteredPathways)
            ->whereNotNull('referral_source')
            ->when($filterFrom, fn($q) => $q->whereDate('intake_date', '>=', $filterFrom))
            ->when($filterTo,   fn($q) => $q->whereDate('intake_date', '<=', $filterTo))
            ->when($filterHub !== 'all', fn($q) => $q->where('hub_id', $filterHub))
            ->when($filterChannel !== 'all' && $groupToSources->has($filterChannel),
                fn($q) => $q->whereIn('referral_source', $groupToSources[$filterChannel]));

        // Hubs for filter dropdown
        $hubs = \App\Models\Hub::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        // Chart data: channel ranking — how referral network clients heard about us (external pathways only)
        $channelColors = ['#163029','#4a4078','#6b6a65','#1d6ea8','#b87319','#8a2e1d','#b05080','#4a7a5c','#a07830','#2f5c3a','#6a5a3a','#3d5a52'];
        $channelTotals = $filteredBase()
            ->selectRaw('referral_source as label, COUNT(*) as total')
            ->groupBy('referral_source')
            ->orderByDesc('total')
            ->get();
        $referralNetworkTotal = $filteredBase()->count() ?: 1;
        $pathwayRanking = $channelTotals->values()->map(fn($row, $i) => [
            'label' => $row->label,
            'color' => $channelColors[$i % count($channelColors)],
            'total' => $row->total,
            'pct'   => round(($row->total / $referralNetworkTotal) * 100),
        ]);

        // All sources table
        $allSourcesTable = $filteredBase()
            ->selectRaw("
                referral_source,
                COUNT(*) as total,
                SUM(CASE WHEN assigned_pathway = 'Government Department / Public Institution' THEN 1 ELSE 0 END) as govt,
                SUM(CASE WHEN assigned_pathway = 'Civil Society / NGO / CSO / NPO' THEN 1 ELSE 0 END) as ngo,
                SUM(CASE WHEN assigned_pathway = 'Other' THEN 1 ELSE 0 END) as other_pw
            ")
            ->groupBy('referral_source')
            ->orderByDesc('total')
            ->get()
            ->map(fn($r) => [
                'source'      => $r->referral_source,
                'group'       => $sourceGroupMap[$r->referral_source] ?? 'Other',
                'dot'         => $groupDotColors[$sourceGroupMap[$r->referral_source] ?? 'Other'] ?? '#9a9a94',
                'total'       => $r->total,
                'share'       => round(($r->total / $referralNetworkTotal) * 100, 1),
                'govt'        => $r->govt,
                'ngo'         => $r->ngo,
                'other_pw'    => $r->other_pw,
            ]);

        // Chart data: named partners (right chart) — top orgs referred to
        $namedPartners = $referralTracker
            ->groupBy('partner_name')
            ->map(fn($rows, $name) => ['label' => $name, 'total' => $rows->count()])
            ->sortByDesc('total')
            ->values();

        // Named partners table (same structure as allSourcesTable)
        $outgoingTotal = $referralTracker->count() ?: 1;
        $namedPartnersTable = $referralTracker
            ->groupBy('partner_name')
            ->map(fn($rows, $name) => [
                'source'   => $name,
                'group'    => $rows->first()['partner_cat'] ?? 'Partner',
                'dot'      => '#2f5c3a',
                'total'    => $rows->count(),
                'share'    => round(($rows->count() / $outgoingTotal) * 100, 1),
                'govt'     => $rows->where('partner_cat', 'Government Department / Public Institution')->count(),
                'ngo'      => $rows->where('partner_cat', 'Civil Society / NGO / CSO / NPO')->count(),
                'other_pw' => $rows->whereNotIn('partner_cat', ['Government Department / Public Institution', 'Civil Society / NGO / CSO / NPO'])->count(),
            ])
            ->sortByDesc('total')
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
            'pathwayRanking', 'namedPartners', 'allSourcesTable', 'referralNetworkTotal',
            'namedPartnersTable', 'outgoingTotal',
            'hubs', 'channelGroups',
            'filterFrom', 'filterTo', 'filterHub', 'filterChannel', 'filterPathway',
        ));
    }

    public function report(Request $request)
    {
        $pathwayLabelMap  = [
            'govt'  => 'Government Department / Public Institution',
            'ngo'   => 'Civil Society / NGO / CSO / NPO',
            'other' => 'Other',
        ];
        $sourceGroupMap = [
            'Community Outreach / Awareness Session' => 'Community outreach',
            'Paralegal'                              => 'Paralegal network',
            'Word of Mouth / Friend / Family'        => 'Word of mouth',
            'Government Department'                  => 'Government',
            'NGO / CSO / NPO'                        => 'Civil society',
            'Court / Judicial Officer'               => 'Legal / Judicial',
            'Bar Association'                        => 'Legal / Judicial',
            'Community Leader / Local Representative'=> 'Community outreach',
            'District / Range Peace Committee'       => 'Civil society',
            'Shelter / Protection Service'           => 'Social services',
            'Office Staff'                           => 'Internal',
            'Phone Call / Helpline'                  => 'Digital / Media',
            'Website / Social Media'                 => 'Digital / Media',
            'SMS / WhatsApp Message'                 => 'Digital / Media',
            'Radio / TV / Newspaper'                 => 'Digital / Media',
            'Google Search / Google Maps'            => 'Digital / Media',
            'QR Code / Referral Card'                => 'Digital / Media',
            'Walk-in / Passing by the Office'        => 'Walk-in',
            'Friend / Family / Word of Mouth'        => 'Word of mouth',
            'Other - please specify'                 => 'Other',
        ];
        $groupToSources = collect($sourceGroupMap)
            ->groupBy(fn($g) => $g)
            ->map(fn($items) => $items->keys()->toArray());

        $filterFrom    = $request->input('from');
        $filterTo      = $request->input('to');
        $filterHub     = $request->input('hub', 'all');
        $filterChannel = $request->input('channel', 'all');
        $filterPathway = $request->input('pathway', 'all');

        // Base query factory — all cases with a referral_source, respecting filters
        $reportBase = fn() => CaseRecord::whereNotNull('referral_source')
            ->when($filterFrom, fn($q) => $q->whereDate('intake_date', '>=', $filterFrom))
            ->when($filterTo,   fn($q) => $q->whereDate('intake_date', '<=', $filterTo))
            ->when($filterHub !== 'all', fn($q) => $q->where('hub_id', $filterHub))
            ->when($filterChannel !== 'all' && $groupToSources->has($filterChannel),
                fn($q) => $q->whereIn('referral_source', $groupToSources[$filterChannel]))
            ->when($filterPathway !== 'all' && isset($pathwayLabelMap[$filterPathway]),
                fn($q) => $q->where('assigned_pathway', $pathwayLabelMap[$filterPathway]));

        // Report title
        $channelLabel = $filterChannel !== 'all' ? "$filterChannel sources" : 'All sources';
        $pwLabel      = $filterPathway !== 'all' ? ($pathwayLabelMap[$filterPathway] ?? '') : null;
        $reportTitle  = $pwLabel ? "$channelLabel (routed to: $pwLabel)" : "$channelLabel (combined)";

        // KPIs
        $total        = $reportBase()->count();
        $hubsReached  = $reportBase()->whereNotNull('hub_id')->distinct('hub_id')->count('hub_id');
        $distsReached = $reportBase()->whereNotNull('district')->distinct('district')->count('district');

        // Urgency breakdown (alias avoids enum cast)
        $urgencyMap = $reportBase()
            ->selectRaw('urgency as urg, COUNT(*) as cnt')
            ->groupBy('urgency')
            ->pluck('cnt', 'urg')
            ->toArray();
        $urgencyTotal = array_sum($urgencyMap) ?: 1;
        $urgencyData  = [
            ['label' => 'Low',       'val' => $urgencyMap['Low']       ?? 0, 'color' => '#2f7a4d'],
            ['label' => 'Medium',    'val' => $urgencyMap['Med']       ?? 0, 'color' => '#b87319'],
            ['label' => 'High',      'val' => $urgencyMap['High']      ?? 0, 'color' => '#8a2e1d'],
            ['label' => 'Immediate', 'val' => $urgencyMap['Immediate'] ?? 0, 'color' => '#5c0000'],
        ];

        // Monthly trend
        $monthlyRaw = $reportBase()
            ->whereNotNull('intake_date')
            ->selectRaw("DATE_FORMAT(intake_date,'%Y-%m') as ym, DATE_FORMAT(intake_date,'%b %y') as lbl, COUNT(*) as cnt")
            ->groupByRaw("DATE_FORMAT(intake_date,'%Y-%m'), DATE_FORMAT(intake_date,'%b %y')")
            ->orderBy('ym')
            ->get();
        $maxMonthly = $monthlyRaw->max('cnt') ?: 1;
        $n = $monthlyRaw->count();
        $cW=540; $cH=130; $pL=30; $pR=10; $pT=20; $pB=28;
        $pW=$cW-$pL-$pR; $pH=$cH-$pT-$pB;
        $chartPoints = $monthlyRaw->values()->map(fn($row,$i) => [
            'x'   => round($pL + ($n>1 ? $i/($n-1)*$pW : $pW/2), 1),
            'y'   => round($pT + $pH - ($row->cnt/$maxMonthly)*$pH, 1),
            'lbl' => $row->lbl,
            'val' => $row->cnt,
        ])->toArray();
        $polyStr  = implode(' ', array_map(fn($p) => "{$p['x']},{$p['y']}", $chartPoints));
        $baseY    = $pT + $pH;
        $areaPath = '';
        if ($chartPoints) {
            $areaPath = "M {$chartPoints[0]['x']} {$chartPoints[0]['y']}";
            foreach (array_slice($chartPoints, 1) as $p) $areaPath .= " L {$p['x']} {$p['y']}";
            $areaPath .= " L {$chartPoints[count($chartPoints)-1]['x']} $baseY L {$chartPoints[0]['x']} $baseY Z";
        }
        $chartMeta = compact('cW','cH','pL','pT','pH','pW','maxMonthly','baseY','polyStr','areaPath');

        // Routing pathway — donut chart data
        $routingColors = [
            'Court Representation'                       => '#163029',
            'Mediation'                                  => '#b87319',
            'Legal Advice / Consultation'                => '#4a4078',
            'ADR / Dispute Resolution Support'           => '#2f7a4d',
            'Government Department / Public Institution' => '#1d6ea8',
            'Civil Society / NGO / CSO / NPO'            => '#8a2e1d',
            'Other'                                      => '#9a9a94',
        ];
        $routingShort = [
            'Court Representation'                       => 'Court Representation',
            'Mediation'                                  => 'Mediation',
            'Legal Advice / Consultation'                => 'Legal Advice',
            'ADR / Dispute Resolution Support'           => 'ADR / Dispute Res.',
            'Government Department / Public Institution' => 'Government Dept',
            'Civil Society / NGO / CSO / NPO'            => 'NGO / CSO',
            'Other'                                      => 'Other',
        ];
        $routingMap   = $reportBase()
            ->whereNotNull('assigned_pathway')
            ->selectRaw('assigned_pathway as pw, COUNT(*) as cnt')
            ->groupBy('assigned_pathway')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'pw')
            ->toArray();
        $routingTotal = array_sum($routingMap) ?: 1;
        $dcx=80; $dcy=80; $dr=62; $di=40;
        $donutSegs = [];
        $angle = 0;
        foreach ($routingMap as $pw => $cnt) {
            $pct = $cnt / $routingTotal;
            $sw  = $pct * 360;
            // Handle full-circle edge case
            if ($sw >= 360) $sw = 359.9999;
            $s   = deg2rad($angle - 90);
            $e   = deg2rad($angle + $sw - 90);
            $lg  = $sw >= 180 ? 1 : 0;
            $donutSegs[] = [
                'path'  => sprintf(
                    'M %.2f %.2f A %d %d 0 %d 1 %.2f %.2f L %.2f %.2f A %d %d 0 %d 0 %.2f %.2f Z',
                    $dcx+$dr*cos($s), $dcy+$dr*sin($s),
                    $dr, $dr, $lg,
                    $dcx+$dr*cos($e), $dcy+$dr*sin($e),
                    $dcx+$di*cos($e), $dcy+$di*sin($e),
                    $di, $di, $lg,
                    $dcx+$di*cos($s), $dcy+$di*sin($s)
                ),
                'color' => $routingColors[$pw] ?? '#9a9a94',
                'label' => $routingShort[$pw]  ?? $pw,
                'full'  => $pw,
                'cnt'   => $cnt,
                'pct'   => round($pct * 100, 1),
            ];
            $angle += $sw;
        }
        $topRouting    = array_key_first($routingMap) ?? '—';
        $topRoutingCnt = reset($routingMap) ?: 0;
        $topRoutingPct = round($topRoutingCnt / $routingTotal * 100);

        // Case categories
        $catMap = $reportBase()
            ->whereNotNull('primary_issue')
            ->selectRaw('primary_issue as iss, COUNT(*) as cnt')
            ->groupBy('primary_issue')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'iss')
            ->toArray();
        $catTotal    = array_sum($catMap) ?: 1;
        $catTop      = array_slice($catMap, 0, 5, true);
        $catOther    = array_sum(array_slice($catMap, 5)) ;
        $catMaxVal   = (int) (reset($catMap) ?: 1);

        // Geographic coverage
        $geoRows = $reportBase()
            ->join('hubs', 'cases.hub_id', '=', 'hubs.id')
            ->selectRaw('hubs.id as hid, hubs.name as hname, hubs.district as hdist, COUNT(*) as cnt')
            ->groupByRaw('hubs.id, hubs.name, hubs.district')
            ->orderByDesc('cnt')
            ->get();
        $geoTotal   = $geoRows->sum('cnt') ?: 1;
        $geoHubs    = $geoRows->count();
        $geoDists   = $geoRows->pluck('hdist')->filter()->unique()->count();

        // Report metadata
        $topCat      = array_key_first($catMap) ?? '—';
        $topPathShort = $routingShort[$topRouting] ?? $topRouting;
        $hubScope    = $filterHub !== 'all' ? (\App\Models\Hub::find($filterHub)?->name ?? $filterHub) : 'All hubs';
        $hubCode     = $filterHub !== 'all' ? strtoupper(str_replace(' ', '', $filterHub)) : 'ALL';
        $refCode     = 'JH-' . $hubCode . '-' . now()->format('Ymd');
        $periodStr   = match(true) {
            (bool)$filterFrom && (bool)$filterTo => \Carbon\Carbon::parse($filterFrom)->format('j M Y') . ' and ' . \Carbon\Carbon::parse($filterTo)->format('j M Y'),
            (bool)$filterFrom                    => 'from ' . \Carbon\Carbon::parse($filterFrom)->format('j M Y'),
            (bool)$filterTo                      => 'until ' . \Carbon\Carbon::parse($filterTo)->format('j M Y'),
            default                              => 'all recorded intakes',
        };
        $preparedBy  = \Illuminate\Support\Facades\Auth::user()?->name ?? '—';
        $narrative   = "$reportTitle referred $total " . ($total === 1 ? 'person' : 'people') . " to Justice Hub services"
            . (($filterFrom || $filterTo) ? " between $periodStr" : '')
            . ($hubsReached > 0 ? ", reaching $hubsReached " . ($hubsReached === 1 ? 'hub' : 'hubs') : '')
            . ". Those referrals were most commonly recorded under {$topCat}"
            . " and were routed to {$topPathShort} in {$topRoutingPct}% of cases."
            . " Figures are counts of recorded intakes over the stated period; they describe how referrals were routed, not what followed.";

        return view('referrals.report', compact(
            'reportTitle', 'periodStr', 'hubScope', 'refCode', 'preparedBy', 'narrative',
            'total', 'hubsReached', 'distsReached',
            'urgencyData', 'urgencyTotal',
            'chartPoints', 'chartMeta',
            'donutSegs', 'routingTotal', 'topPathShort', 'topRoutingPct', 'dcx', 'dcy', 'dr',
            'catMap', 'catTop', 'catOther', 'catTotal', 'catMaxVal',
            'geoRows', 'geoTotal', 'geoHubs', 'geoDists',
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
            'performed_by' => \Illuminate\Support\Facades\Auth::user()?->name ?? 'System',
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
