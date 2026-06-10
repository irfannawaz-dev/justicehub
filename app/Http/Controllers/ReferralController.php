<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\CaseRecord;
use App\Models\ServiceEncounter;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function index(Request $request)
    {
        $partners = Partner::with('hubs')->orderBy('name')->get();

        $totalActive    = $partners->sum('active_referrals');
        $totalCompleted = $partners->sum('completed_referrals');
        $totalFailed    = $partners->sum('failed_referrals');
        $closureRate    = ($totalCompleted + $totalFailed) > 0
            ? round(($totalCompleted / ($totalCompleted + $totalFailed)) * 100)
            : 0;

        // Weighted average response hours
        $weightedSum   = $partners->sum(fn ($p) => $p->avg_response_hours * ($p->active_referrals + $p->completed_referrals));
        $weightedCount = $partners->sum(fn ($p) => $p->active_referrals + $p->completed_referrals);
        $avgResponseHrs = $weightedCount > 0 ? round($weightedSum / $weightedCount) : 0;

        // Category stats for loop-closure chart
        $categoryConfig = [
            'Shelter'         => ['color' => 'var(--burgundy)', 'tint' => 'var(--burgundy-tint)', 'icon' => 'home'],
            'Government'      => ['color' => 'var(--forest)',   'tint' => 'rgba(22,48,41,0.08)',  'icon' => 'building-2'],
            'Law Enforcement' => ['color' => 'var(--ink-1)',    'tint' => 'rgba(36,40,45,0.08)',  'icon' => 'shield'],
            'Health'          => ['color' => 'var(--moss)',     'tint' => 'var(--moss-tint)',     'icon' => 'heart-handshake'],
            'NGO'             => ['color' => 'var(--ochre)',    'tint' => 'var(--ochre-tint)',    'icon' => 'users'],
        ];

        $categoryStats = collect($categoryConfig)->map(function ($cfg, $cat) use ($partners) {
            $cps = $partners->where('category', $cat);
            $completed = $cps->sum('completed_referrals');
            $failed    = $cps->sum('failed_referrals');
            $active    = $cps->sum('active_referrals');
            $volume    = $completed + $failed + $active;
            return [
                'category'    => $cat,
                'color'       => $cfg['color'],
                'tint'        => $cfg['tint'],
                'icon'        => $cfg['icon'],
                'partners'    => $cps->count(),
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
        $filterCounts = collect($categoryConfig)->mapWithKeys(fn ($cfg, $cat) => [
            $cat => $partners->where('category', $cat)->count(),
        ]);

        // Active cases for referral modal
        $activeCases = CaseRecord::query()
            ->whereNotIn('status', ['Closed', 'Settlement', 'Rejected'])
            ->orderBy('name')
            ->get(['id', 'case_uid', 'name', 'primary_issue', 'hub_id']);

        // Referral tracker – all External Referral service encounters
        $referralEncounters = ServiceEncounter::where('type', 'External Referral')
            ->with('caseRecord:id,case_uid,name,hub_id')
            ->orderByDesc('date')
            ->get();

        $referralTracker = $referralEncounters->map(function ($enc) {
            $meta  = $enc->meta ?? [];
            $stage = $meta['pipeline_stage'] ?? 'Sent';
            $days  = $enc->date
                ? (int) now()->startOfDay()->diffInDays(Carbon::parse($enc->date)->startOfDay())
                : 0;
            return [
                'ref'          => 'R-' . str_pad($enc->id, 5, '0', STR_PAD_LEFT),
                'date'         => $enc->date,
                'case_uid'     => $enc->caseRecord?->case_uid ?? '—',
                'client_name'  => $enc->caseRecord?->name ?? '—',
                'hub_id'       => $enc->caseRecord?->hub_id ?? '—',
                'partner_name' => $meta['partner_name'] ?? '—',
                'partner_cat'  => $meta['partner_category'] ?? '—',
                'urgency'      => $meta['urgency'] ?? 'Med',
                'service'      => $meta['service_description'] ?? $enc->note,
                'stage'        => $stage,
                'days_open'    => $days,
                'follow_up'    => $meta['follow_up_date'] ?? null,
            ];
        });

        $trackerCounts = [
            'active'    => $referralTracker->whereIn('stage', ['Sent', 'Acknowledged', 'In progress'])->count(),
            'completed' => $referralTracker->where('stage', 'Completed')->count(),
            'failed'    => $referralTracker->where('stage', 'Failed')->count(),
            'all'       => $referralTracker->count(),
        ];

        return view('referrals.index', compact(
            'partners', 'filteredPartners',
            'totalActive', 'totalCompleted', 'totalFailed',
            'closureRate', 'avgResponseHrs',
            'categoryStats', 'categoryConfig', 'maxVolume',
            'mouAttention', 'partnerFilter', 'filterCounts',
            'activeCases', 'referralTracker', 'trackerCounts',
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
            'performed_by' => auth()->user()->name,
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

        // Increment partner's active referrals and set last referral date
        $partner->increment('active_referrals');
        $partner->update(['last_referral_date' => today()]);

        // Touch case last_update
        $case->update(['last_update' => today()]);

        return back()->with('success', "Referral to {$partner->name} logged for case {$case->case_uid}.");
    }
}
