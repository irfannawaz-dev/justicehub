<?php

namespace App\Http\Controllers;

use App\Models\CaseRecord;
use App\Models\Evidence;
use App\Models\Feedback;
use App\Models\HubCost;
use App\Models\Indicator;
use App\Models\OutreachActivity;
use App\Models\Staff;
use App\Models\Training;
use App\Services\IndicatorDerivationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ImpactController extends Controller
{
    public function index()
    {
        $service = new IndicatorDerivationService();
        $actuals  = $service->derive();

        $indicators = Indicator::all()->map(function ($ind) use ($actuals) {
            if (isset($actuals[$ind->code])) {
                $ind->actual = $actuals[$ind->code];
            }
            $ind->rag = $ind->ragStatus();
            return $ind;
        });

        $cases    = CaseRecord::forAuthUser()->get();
        $feedback = Feedback::forAuthUser()->get();

        // Staff compliance (simplified aggregate)
        $today             = now();
        $allStaff          = Staff::forAuthUser()->active()->with('trainings')->get();
        $mandatoryTrainings = Training::where('mandatory', true)->get();
        $compliantStaff = $allStaff->filter(function ($s) use ($mandatoryTrainings, $today) {
            $required = $mandatoryTrainings->filter(fn($t) => in_array($s->role, $t->audience ?? []));
            return $required->every(function ($req) use ($s, $today) {
                $pivot = $s->trainings->where('code', $req->code)->first();
                if (!$pivot) return false;
                if ($req->refresh === 'one-off' || !$pivot->pivot->expires) return true;
                return Carbon::parse($pivot->pivot->expires)->gte($today);
            });
        });

        $metrics = [
            'total_cases'          => $cases->count(),
            'active_cases'         => $cases->filter(fn($c) => $c->status?->value !== 'closed')->count(),
            'resolved_cases'       => $cases->filter(fn($c) => $c->status?->value === 'closed')->count(),
            'sla_met_pct'          => $cases->count() > 0 ? round(($cases->where('sla_met', true)->count() / $cases->count()) * 100) : 0,
            'underserved_pct'      => $cases->count() > 0 ? round(($cases->where('is_underserved', true)->count() / $cases->count()) * 100) : 0,
            'avg_feedback'         => $feedback->count() > 0 ? round($feedback->avg('score_overall'), 1) : 0,
            'positive_feedback'    => $feedback->where('score_overall', '>=', 4)->count(),
            'outreach_sessions'    => OutreachActivity::forAuthUser()->count(),
            'outreach_participants'=> OutreachActivity::forAuthUser()->sum('total_participants'),
            'evidence_total'       => Evidence::count(),
            'evidence_verified'    => Evidence::where('verified', true)->count(),
            'indicators_green'     => $indicators->where('rag', 'green')->count(),
            'indicators_amber'     => $indicators->where('rag', 'amber')->count(),
            'indicators_red'       => $indicators->where('rag', 'red')->count(),
            'indicators_total'     => $indicators->count(),
            'staff_total'          => $allStaff->count(),
            'staff_compliant'      => $compliantStaff->count(),
            'staff_compliance_pct' => $allStaff->count() > 0 ? round(($compliantStaff->count() / $allStaff->count()) * 100) : 0,
        ];

        $ragCounts = [
            'green' => $metrics['indicators_green'],
            'amber' => $metrics['indicators_amber'],
            'red'   => $metrics['indicators_red'],
        ];

        // Hub costs
        $hubCosts   = HubCost::with('hub')->orderBy('hub_id')->get();
        $totalCost  = $hubCosts->sum('total_operational_cost') * 4; // annualise
        $costPerCase = $hubCosts->avg('cost_per_case') ?: 8500;

        // Top P0 indicators for display
        $p0Indicators = $indicators->where('priority', 'P0')->sortBy('code')->values();

        return view('impact.index', compact(
            'metrics', 'ragCounts', 'p0Indicators', 'totalCost', 'costPerCase', 'hubCosts'
        ));
    }

    public function export(Request $request)
    {
        abort_unless($request->user()->can('reports.export'), 403, 'You do not have permission to export impact reports.');

        $request->validate([
            'period'   => ['required', 'string', 'max:20'],
            'template' => ['required', Rule::in(['quarterly', 'annual', 'donor', 'me-update'])],
            'scope'    => ['nullable', Rule::in(['all', 'hub'])],
        ]);

        $data = $this->gatherReportData($request);

        $pdf = Pdf::loadView('impact.pdf', $data)
            ->setPaper('a4', 'portrait');

        // Sanitize filename components (values already whitelisted by validation above)
        $filename = 'justice-hub-impact-' . $request->period . '-' . $request->template . '.pdf';

        return $pdf->download($filename);
    }

    private function gatherReportData(Request $request): array
    {
        $service  = new IndicatorDerivationService();
        $actuals  = $service->derive();

        $indicators = Indicator::all()->map(function ($ind) use ($actuals) {
            if (isset($actuals[$ind->code])) $ind->actual = $actuals[$ind->code];
            $ind->rag = $ind->ragStatus();
            return $ind;
        })->sortBy('code');

        $cases    = CaseRecord::forAuthUser()->get();
        $feedback = Feedback::forAuthUser()->get();

        return [
            'period'      => $request->period,
            'template'    => $request->template,
            'generated'   => now()->format('d M Y'),
            'indicators'  => $indicators,
            'cases'       => $cases,
            'feedback'    => $feedback,
            'hubCosts'    => HubCost::with('hub')->get(),
            'outreach'    => OutreachActivity::forAuthUser()->get(),
            'evidence'    => Evidence::where('verified', true)->get(),
            'ragCounts'   => [
                'green' => $indicators->where('rag', 'green')->count(),
                'amber' => $indicators->where('rag', 'amber')->count(),
                'red'   => $indicators->where('rag', 'red')->count(),
            ],
        ];
    }
}
