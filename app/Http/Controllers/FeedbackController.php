<?php

namespace App\Http\Controllers;

use App\Models\CaseRecord;
use App\Models\Feedback;
use App\Models\FeedbackSurvey;
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

        $cases = CaseRecord::forAuthUser()->active()->orderBy('name')->get(['id', 'case_uid', 'name', 'hub_id', 'assigned_pathway', 'intake_date', 'returning_client', 'consent']);

        $trendJson   = json_encode(['labels' => $trendData->pluck('month')->values(), 'values' => $trendData->map(fn($r) => round((float)$r->avg_score, 1))->values()]);
        $serviceJson = json_encode(['labels' => $byService->pluck('service')->values(), 'values' => $byService->map(fn($r) => round((float)$r->avg_score, 1))->values()]);

        // Detailed surveys
        $hubId = session('active_hub');
        $surveyQuery = FeedbackSurvey::query()
            ->when($hubId && $hubId !== 'all', fn($q) => $q->where('hub_id', $hubId))
            ->latest();
        $surveys = $surveyQuery->paginate(15, ['*'], 'survey_page')->withQueryString();

        return view('feedback.index', compact('feedback', 'avgScore', 'counts', 'cases', 'byService', 'trendJson', 'serviceJson', 'surveys'));
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
            'service'           => $request->service ?? $case?->assigned_pathway ?? 'General',
            'lawyer'            => $request->lawyer ?? $case?->assigned_to,
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

    public function storeSurvey(Request $request)
    {
        $data = $request->validate([
            'case_id'           => 'nullable|exists:cases,id',
            'consent'           => 'required|in:yes,no',
            'visit_date'        => 'nullable|date',
            'service_date'      => 'nullable|date',
            'service_type'      => 'nullable|string|max:60',
            'first_visit'       => 'nullable|in:yes,no',
            'q11_access'        => 'nullable|integer|min:1|max:5',
            'q12_reception'     => 'nullable|integer|min:1|max:5',
            'q13_explanation'   => 'nullable|integer|min:1|max:5',
            'q14_waiting'       => 'nullable|integer|min:1|max:5',
            'q15_difficulty'    => 'nullable|string|max:120',
            'q16_listened'      => 'nullable|string|max:30',
            'q17_comfortable'   => 'nullable|string|max:30',
            'q18_understood'    => 'nullable|integer|min:1|max:5',
            'q19_fair_treatment'=> 'nullable|string|max:30',
            'q20_info_safety'   => 'nullable|string|max:30',
            'q21_data_explained'=> 'nullable|string|max:30',
            'q22_confidence'    => 'nullable|integer|min:1|max:5',
            'q23_complaint_info'=> 'nullable|string|max:30',
            'q24_advice_useful' => 'nullable|string|max:30',
            'q25_referral_clarity' => 'nullable|string|max:30',
            'q26_next_steps'    => 'nullable|string|max:30',
            'q27_clarity'       => 'nullable|string|max:30',
            'q28_satisfaction'  => 'nullable|integer|min:1|max:5',
            'q29_resolution_help'=> 'nullable|string|max:30',
            'q30_recommend'     => 'nullable|string|max:30',
            'q31_trust'         => 'nullable|integer|min:1|max:5',
            'q32_helpful_part'  => 'nullable|string|max:2000',
            'q33_improvement'   => 'nullable|string|max:2000',
            'q34_additional'    => 'nullable|string|max:2000',
        ]);

        if ($data['consent'] === 'no') {
            return back()->with('error', 'Survey cannot be submitted without consent.');
        }

        $case = $data['case_id'] ? CaseRecord::forAuthUser()->find($data['case_id']) : null;

        $lastNum = FeedbackSurvey::selectRaw("MAX(CAST(SUBSTRING(survey_uid, 4) AS UNSIGNED)) as max_num")->value('max_num');
        $nextNum = ($lastNum ?? 0) + 1;

        $data['survey_uid']      = 'FS-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        $data['case_id']         = $case?->id;
        $data['hub_id']          = $case?->hub_id ?? session('active_hub', 'unknown');
        $data['enumerator_name'] = auth()->user()->name;
        $data['consent']         = true;
        $data['first_visit']     = isset($data['first_visit']) ? ($data['first_visit'] === 'yes') : null;

        FeedbackSurvey::create($data);

        return back()->with('success', 'Detailed feedback survey submitted successfully.');
    }
}
