<?php

namespace App\Http\Controllers;

use App\Models\CaseRecord;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $query = Feedback::with('caseRecord')->forAuthUser();

        if ($request->filled('service')) {
            $query->where('service', $request->service);
        }
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }
        if ($request->filled('hub')) {
            $query->where('hub_id', $request->hub);
        }

        $feedback = $query->orderByDesc('date')->paginate(25)->withQueryString();

        $all = Feedback::forAuthUser()->get();
        $avgScore = $all->count() > 0 ? round($all->avg('score_overall'), 1) : 0;

        // Monthly satisfaction trend (last 6 months)
        $trendData = Feedback::forAuthUser()
            ->selectRaw("DATE_FORMAT(date, '%b') as month, DATE_FORMAT(date, '%Y-%m') as month_iso, AVG(score_overall) as avg_score, COUNT(*) as cnt")
            ->where('date', '>=', now()->subMonths(6)->startOfMonth())
            ->groupByRaw("DATE_FORMAT(date, '%Y-%m'), DATE_FORMAT(date, '%b')")
            ->orderBy('month_iso')
            ->get();

        // Service breakdown
        $byService = Feedback::forAuthUser()
            ->selectRaw("service, AVG(score_overall) as avg_score, COUNT(*) as cnt")
            ->groupBy('service')
            ->orderByDesc('cnt')
            ->get();

        $counts = [
            'total'           => $all->count(),
            'positive'        => $all->where('score_overall', '>=', 4)->count(),
            'would_recommend' => $all->where('would_recommend', 'yes')->count(),
            'understood'      => $all->where('understood_rights', 'yes')->count(),
        ];

        $cases = CaseRecord::forAuthUser()->active()->orderBy('name')->get(['id', 'case_uid', 'name']);

        $trendJson   = json_encode(['labels' => $trendData->pluck('month')->values(), 'values' => $trendData->map(fn($r) => round((float)$r->avg_score, 1))->values()]);
        $serviceJson = json_encode(['labels' => $byService->pluck('service')->values(), 'values' => $byService->map(fn($r) => round((float)$r->avg_score, 1))->values()]);

        return view('feedback.index', compact('feedback', 'avgScore', 'counts', 'cases', 'byService', 'trendJson', 'serviceJson'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'score_overall'    => 'required|integer|min:1|max:5',
            'score_helpfulness'=> 'required|integer|min:1|max:5',
            'score_respect'    => 'required|integer|min:1|max:5',
            'channel'          => ['required', Rule::in(['in-person', 'phone', 'sms', 'digital'])],
            // case_id must belong to a case visible to this user (hub scoped)
            'case_id'          => 'nullable|exists:cases,id',
            'comment'          => 'nullable|string|max:2000',
        ]);

        $lastNum = Feedback::selectRaw("MAX(CAST(SUBSTRING(feedback_uid, 4) AS UNSIGNED)) as max_num")->value('max_num');
        $nextNum = $lastNum ? $lastNum + 1 : 17;

        // Verify the case belongs to user's accessible scope
        $case = $request->case_id
            ? CaseRecord::forAuthUser()->find($request->case_id)
            : null;

        Feedback::create([
            'feedback_uid'      => 'FB-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT),
            'case_id'           => $case?->id,
            'hub_id'            => $case?->hub_id ?? session('active_hub'),
            'client_name'       => $request->boolean('is_anonymous') ? 'Anonymous' : ($request->client_name ?? 'Unknown'),
            'is_anonymous'      => $request->boolean('is_anonymous'),
            'service'           => $request->service,
            'lawyer'            => $request->lawyer,
            'date'              => now()->toDateString(),
            'channel'           => $request->channel,
            'score_overall'     => $request->score_overall,
            'score_helpfulness' => $request->score_helpfulness,
            'score_respect'     => $request->score_respect,
            'understood_rights' => $request->understood_rights ?? 'yes',
            'would_recommend'   => $request->would_recommend ?? 'yes',
            'comment'           => $request->comment,
            'consent_to_share'  => $request->boolean('consent_to_share'),
        ]);

        return back()->with('success', 'Feedback captured.');
    }
}
