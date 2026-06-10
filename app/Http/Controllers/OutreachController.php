<?php

namespace App\Http\Controllers;

use App\Models\Hub;
use App\Models\OutreachActivity;
use Illuminate\Http\Request;

class OutreachController extends Controller
{
    public function index(Request $request)
    {
        // Load everything — hub filter is client-side (no page reload)
        $activities = OutreachActivity::with('pulseSurveys')
            ->orderByDesc('date')
            ->get();

        $totals = [
            'sessions'    => $activities->count(),
            'participants'=> $activities->sum('total_participants'),
            'female'      => $activities->sum('female_participants'),
            'minority'    => $activities->sum('minority_participants'),
            'disability'  => $activities->sum('disability_participants'),
        ];

        $femalePct    = $totals['participants'] > 0 ? round(($totals['female'] / $totals['participants']) * 100) : 0;
        $marginalised = $totals['minority'] + $totals['disability'];

        // Paralegal-led (OP3.1)
        $paralegalLed = $activities->where('type', 'Paralegal Outreach')->count();
        $paralegalPct = $totals['sessions'] > 0 ? round(($paralegalLed / $totals['sessions']) * 100) : 0;

        // Pulse survey stats (all activities)
        $allPulse          = $activities->flatMap->pulseSurveys;
        $gainDist          = [4 => 0, 3 => 0, 2 => 0, 1 => 0, 0 => 0];
        $totalGainWeighted = 0;
        $pulseRespondents  = 0;
        $recommendWeighted = 0;

        foreach ($allPulse as $ps) {
            $raw  = (float) $ps->post_score - (float) $ps->pre_score;
            $gain = max(0, min(4, (int) round($raw)));
            $cnt  = $ps->respondent_count ?? 1;
            $gainDist[$gain]    += $cnt;
            $totalGainWeighted  += $raw * $cnt;
            $pulseRespondents   += $cnt;
            $recommendWeighted  += ((float) ($ps->would_recommend_pct ?? 0)) * $cnt;
        }

        $maxGainDist          = max(1, max($gainDist));
        $avgGain              = $pulseRespondents > 0 ? round($totalGainWeighted / $pulseRespondents, 1) : 0;
        $wouldRecommend       = $pulseRespondents > 0 ? round($recommendWeighted / $pulseRespondents) : 0;
        $gainedCount          = $gainDist[1] + $gainDist[2] + $gainDist[3] + $gainDist[4];
        $understandingGainPct = $pulseRespondents > 0 ? round(($gainedCount / $pulseRespondents) * 100) : 0;
        $responseRate         = $totals['participants'] > 0 ? round(($pulseRespondents / $totals['participants']) * 100) : 0;

        // Comments for "What participants said"
        $recentComments = $activities->flatMap(function ($a) {
            return $a->pulseSurveys
                ->filter(fn ($ps) => !empty($ps->comment))
                ->map(fn ($ps) => [
                    'comment'  => $ps->comment,
                    'hub_id'   => $a->hub_id,
                    'demo'     => $ps->demographics ?? [],
                    'gain'     => (int) round((float) $ps->post_score - (float) $ps->pre_score),
                    'location' => $a->location,
                    'date'     => $a->date,
                ]);
        })->filter()->take(3)->values();

        // Hubs for filter pills
        $user = $request->user();
        $hubQuery = Hub::where('is_active', true)->orderBy('name');
        if (! $user->canSeeAllHubs()) {
            $hubQuery->where('id', $user->hub_id);
        }
        $hubs = $hubQuery->get(['id', 'name']);

        return view('outreach.index', compact(
            'activities', 'totals', 'femalePct', 'marginalised',
            'paralegalLed', 'paralegalPct',
            'gainDist', 'maxGainDist', 'avgGain', 'wouldRecommend',
            'understandingGainPct', 'responseRate', 'pulseRespondents',
            'recentComments', 'hubs',
        ));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('outreach.create'), 403, 'You do not have permission to log outreach activities.');

        $request->validate([
            'date'               => 'required|date|before_or_equal:today',
            'hub_id'             => 'required|string|exists:hubs,id',
            'type'               => 'required|string',
            'topic'              => 'required|string|max:255',
            'location'           => 'required|string|max:255',
            'facilitator'        => 'required|string|max:150',
            'total_participants'      => 'required|integer|min:0|max:10000',
            'female_participants'     => 'nullable|integer|min:0|max:10000',
            'male_participants'       => 'nullable|integer|min:0|max:10000',
            'minority_participants'   => 'nullable|integer|min:0|max:10000',
            'disability_participants' => 'nullable|integer|min:0|max:10000',
            'notes'              => 'nullable|string|max:2000',
        ]);

        $lastUid = OutreachActivity::selectRaw("MAX(CAST(SUBSTRING(outreach_uid, 4) AS UNSIGNED)) as max_num")->value('max_num');
        $nextNum = $lastUid ? $lastUid + 1 : 1001;

        OutreachActivity::create([
            'outreach_uid'            => 'OR-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT),
            'date'                    => $request->date,
            'hub_id'                  => $request->hub_id,
            'type'                    => $request->type,
            'topic'                   => $request->topic,
            'location'                => $request->location,
            'facilitator'             => $request->facilitator,
            'total_participants'      => $request->total_participants,
            'female_participants'     => $request->female_participants ?? 0,
            'minority_participants'   => $request->minority_participants ?? 0,
            'disability_participants' => $request->disability_participants ?? 0,
            'naz_promoted'            => $request->has('naz_promoted'),
            'slacc'                   => $request->has('slacc'),
            'meta'                    => array_filter([
                'male_participants' => $request->male_participants ?? null,
                'notes'             => $request->notes ?? null,
            ]),
        ]);

        return back()->with('success', 'Outreach activity logged.');
    }
}
