<?php

namespace App\Http\Controllers;

use App\Models\OutreachActivity;
use App\Models\PulseSurvey;
use Illuminate\Http\Request;

class PulseSurveyController extends Controller
{
    public function store(Request $request, OutreachActivity $outreach)
    {
        $request->validate([
            'pre_score' => 'required|numeric|min:0|max:100',
            'post_score' => 'required|numeric|min:0|max:100',
            'will_apply' => 'required|string',
        ]);

        $lastUid = PulseSurvey::selectRaw("MAX(CAST(SUBSTRING(pulse_uid, 4) AS UNSIGNED)) as max_num")->value('max_num');
        $nextNum = $lastUid ? $lastUid + 1 : 3001;

        $wouldRecommend = $request->would_recommend ?? 'yes';
        $wouldRecommendPct = match($wouldRecommend) {
            'yes' => 100, 'maybe' => 50, default => 0,
        };

        PulseSurvey::create([
            'pulse_uid'          => 'PS-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT),
            'outreach_id'        => $outreach->id,
            'session'            => $request->session_name ?? $outreach->outreach_uid,
            'date'               => now()->toDateString(),
            'respondent_count'   => 1,
            'pre_score'          => $request->pre_score,
            'post_score'         => $request->post_score,
            'will_apply'         => $request->will_apply,
            'would_recommend_pct'=> $wouldRecommendPct,
            'demographics'       => [
                'gender'   => $request->gender,
                'age_band' => $request->age_band,
            ],
            'comment'            => $request->comment,
        ]);

        return back()->with('success', 'Pulse response logged.');
    }
}
