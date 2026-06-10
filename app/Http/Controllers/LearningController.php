<?php

namespace App\Http\Controllers;

use App\Models\CaseRecord;
use App\Models\CaseStudy;
use App\Models\Complaint;
use App\Models\Feedback;
use App\Models\FinanceConfig;
use App\Models\Hub;
use App\Models\HubCost;
use App\Models\OutreachActivity;
use App\Models\Reflection;
use App\Models\ServiceEncounter;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class LearningController extends Controller
{
    public function index()
    {
        $reflections   = Reflection::orderByDesc('date')->get();
        $caseStudies   = CaseStudy::orderByDesc('id')->get();
        $financeConfig = FinanceConfig::current();
        $hubCosts      = HubCost::with('hub')->orderBy('hub_id')->get();

        // Cost base — only from real data, no hardcoded fallbacks
        $totalCost         = $hubCosts->sum('total_operational_cost') * 4;
        $costPerCase       = $hubCosts->avg('cost_per_case') ?: null;
        $hasHubCosts       = $hubCosts->isNotEmpty() && $totalCost > 0;

        $configTarget      = $financeConfig ? ($financeConfig->config['targets']['costPerCase']   ?? null) : null;
        $overheadTarget    = $financeConfig ? ($financeConfig->config['targets']['overheadCeiling'] ?? null) : null;
        $outreachTarget    = $financeConfig ? ($financeConfig->config['targets']['costPerOutreach'] ?? null) : null;
        $overheadPct       = $financeConfig ? ($financeConfig->config['overheadPct']               ?? null) : null;
        $outreachAllocPct  = $financeConfig ? ($financeConfig->config['outreachAllocationPct']      ?? 8)    : 8;

        // Cost trend chart
        $history     = $financeConfig ? ($financeConfig->getValue('history', []) ?? []) : [];
        $historyJson = json_encode([
            'labels' => array_column($history, 'period'),
            'values' => array_column($history, 'costPerCase'),
        ]);

        // Raw data for metrics
        $cases       = CaseRecord::forAuthUser()->get();
        $n           = max($cases->count(), 1);
        $feedbacks   = Feedback::forAuthUser()->get();
        $complaints  = Complaint::get();
        $outreach    = OutreachActivity::forAuthUser()->get();
        $encounters  = ServiceEncounter::count();
        $activeHubs  = Hub::where('is_active', true)->count();
        $totalHubs   = max(Hub::count(), 1);
        $lawyerCount = max(Staff::forAuthUser()->where('role', 'like', '%awyer%')->count(), 1);

        // ── Economy metrics ──────────────────────────────────────────
        $costPerOutreach = ($hasHubCosts && $outreach->count() > 0)
            ? round($totalCost * ($outreachAllocPct / 100) / $outreach->count())
            : null;

        $economyMetrics = [
            $this->metric(
                'Cost per case (all-in)',
                $costPerCase !== null ? 'PKR ' . number_format((int)$costPerCase) : '—',
                $configTarget !== null ? 'target PKR ' . number_format((int)$configTarget) : 'no target set',
                $costPerCase ?? 0, $configTarget ?? 0, 'higher_is_worse',
                'HUB_COSTS ÷ cases',
                $costPerCase === null || $configTarget === null
            ),
            $this->metric(
                'Operating overhead share',
                $overheadPct !== null ? $overheadPct . '%' : '—',
                $overheadTarget !== null ? 'target < ' . $overheadTarget . '%' : 'no target set',
                $overheadPct ?? 0, $overheadTarget ?? 0, 'higher_is_worse',
                'Finance config · overhead allocation',
                $overheadPct === null || $overheadTarget === null
            ),
            $this->metric(
                'Total programme cost (annualised)',
                $hasHubCosts ? 'PKR ' . number_format((int)$totalCost) : '—',
                $configTarget !== null && $cases->count() > 0
                    ? 'target PKR ' . number_format((int)($configTarget * $cases->count()))
                    : 'no target set',
                $hasHubCosts ? (int)$totalCost : 0,
                ($configTarget ?? 0) * max($cases->count(), 1),
                'higher_is_worse',
                'HUB_COSTS × 4 quarters',
                ! $hasHubCosts || $configTarget === null
            ),
            $this->metric(
                'Cost per outreach session',
                $costPerOutreach !== null ? 'PKR ' . number_format($costPerOutreach) : '—',
                $outreachTarget !== null ? 'target PKR ' . number_format((int)$outreachTarget) : 'no target set',
                $costPerOutreach ?? 0, $outreachTarget ?? 0, 'higher_is_worse',
                ($outreachAllocPct) . '% of budget ÷ ' . $outreach->count() . ' outreach sessions',
                $costPerOutreach === null || $outreachTarget === null
            ),
        ];

        // ── Efficiency metrics ────────────────────────────────────────
        $slaMet      = $cases->filter(fn($c) => (bool)$c->sla_met)->count();
        $slaRate     = round($slaMet / $n * 100);
        $avgEncounters = $cases->count() > 0 ? round($encounters / $n, 1) : 0;
        $casesPerLawyerMonth = round($cases->count() / ($lawyerCount * 3), 1); // Q = 3 months

        $efficiencyMetrics = [
            $this->metric(
                'SLA compliance rate',
                $slaRate . '%',
                'target 90%',
                $slaRate, 90, 'higher_is_better',
                $slaMet . ' of ' . $cases->count() . ' cases within SLA'
            ),
            $this->metric(
                'Cases per lawyer-month',
                (string)$casesPerLawyerMonth,
                'target 10',
                $casesPerLawyerMonth, 10, 'higher_is_better',
                $lawyerCount . ' active lawyers · CASES ÷ 3 months'
            ),
            $this->metric(
                'Service encounters per case',
                (string)$avgEncounters,
                'target 3 – 5',
                $avgEncounters, 3, 'band',
                'Mean services per case across all CASES'
            ),
            $this->metric(
                'Outreach sessions this quarter',
                (string)$outreach->count(),
                'target 30',
                $outreach->count(), 30, 'higher_is_better',
                'OUTREACH activity log'
            ),
        ];

        // ── Effectiveness metrics ─────────────────────────────────────
        $avgScore       = $feedbacks->count() > 0 ? round($feedbacks->avg('score_overall'), 1) : 0;
        $positiveRate   = $feedbacks->count() > 0
            ? round($feedbacks->where('score_overall', '>=', 4)->count() / $feedbacks->count() * 100)
            : 0;
        $adrCases       = $cases->filter(fn($c) => in_array($c->disposition, ['adr', 'mediation', 'ADR']));
        $adrResolved    = $adrCases->filter(fn($c) => $c->status === 'resolved')->count();
        $adrRate        = $adrCases->count() > 0 ? round($adrResolved / $adrCases->count() * 100) : 0;
        $resolvedComp   = $complaints->where('status', 'resolved')->count();
        $compSlaRate    = $complaints->count() > 0 ? round($resolvedComp / $complaints->count() * 100) : 0;

        $effectivenessMetrics = [
            $this->metric(
                'Client satisfaction (avg score)',
                $avgScore . ' / 5',
                'target 4.5 / 5',
                (float)$avgScore, 4.5, 'higher_is_better',
                'Mean overall rating across ' . $feedbacks->count() . ' responses'
            ),
            $this->metric(
                'Positive feedback rate',
                $positiveRate . '%',
                'target 85%',
                $positiveRate, 85, 'higher_is_better',
                $feedbacks->where('score_overall', '>=', 4)->count() . ' of ' . $feedbacks->count() . ' rated ≥ 4'
            ),
            $this->metric(
                'ADR resolution rate',
                $adrRate . '%',
                'target 70%',
                $adrRate, 70, 'higher_is_better',
                $adrResolved . ' resolved of ' . $adrCases->count() . ' ADR cases'
            ),
            $this->metric(
                'Complaints closed',
                $compSlaRate . '%',
                'target 90%',
                $compSlaRate, 90, 'higher_is_better',
                $resolvedComp . ' of ' . $complaints->count() . ' resolved complaints'
            ),
        ];

        // ── Equity metrics ────────────────────────────────────────────
        $femaleCount      = $cases->where('gender', 'female')->count();
        $minorityCount    = $cases->filter(fn($c) => (bool)$c->is_minority)->count();
        $underservedCount = $cases->filter(fn($c) => (bool)$c->is_underserved)->count();
        $femaleRate      = round($femaleCount / $n * 100);
        $minorityRate    = round($minorityCount / $n * 100);
        $underservedRate = round($underservedCount / $n * 100);

        $equityMetrics = [
            $this->metric(
                'Female client share',
                $femaleRate . '%',
                'target ≥ 50%',
                $femaleRate, 50, 'higher_is_better',
                $femaleCount . ' of ' . $cases->count() . ' cases'
            ),
            $this->metric(
                'Underserved client share',
                $underservedRate . '%',
                'target ≥ 80%',
                $underservedRate, 80, 'higher_is_better',
                $underservedCount . ' of ' . $cases->count() . ' cases · CASES.underserved flag'
            ),
            $this->metric(
                'Religious minority share',
                $minorityRate . '%',
                'target ≥ 10%',
                $minorityRate, 10, 'higher_is_better',
                $minorityCount . ' of ' . $cases->count() . ' cases · CASES.minority flag'
            ),
            $this->metric(
                'Hub geographic coverage',
                $activeHubs . ' of ' . $totalHubs,
                'target ' . $totalHubs . ' of ' . $totalHubs,
                $activeHubs, $totalHubs, 'higher_is_better',
                'OP1.1 · HUBS register'
            ),
        ];

        // ── Legacy pillar summary (for tab badges) ────────────────────
        $pillars = [
            'economy'       => ['label' => 'Economy',       'value' => $costPerCase !== null ? 'PKR ' . number_format((int)$costPerCase) : '—', 'met' => $costPerCase !== null && $configTarget !== null && $costPerCase <= $configTarget],
            'efficiency'    => ['label' => 'Efficiency',    'value' => $slaRate . '% SLA',                 'met' => $slaRate >= 90],
            'effectiveness' => ['label' => 'Effectiveness', 'value' => $avgScore . '/5 satisfaction',      'met' => $avgScore >= 4.5],
            'equity'        => ['label' => 'Equity',        'value' => $underservedRate . '% underserved', 'met' => $underservedRate >= 80],
        ];

        $casesForSelect = CaseRecord::forAuthUser()->orderBy('name')->get(['id', 'case_uid', 'name']);
        $hubs = Hub::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('learning.index', compact(
            'reflections', 'caseStudies', 'financeConfig', 'hubCosts',
            'totalCost', 'costPerCase', 'pillars', 'historyJson',
            'casesForSelect', 'hubs',
            'economyMetrics', 'efficiencyMetrics', 'effectivenessMetrics', 'equityMetrics'
        ));
    }

    private function metric(string $label, string $value, string $targetLabel, float $actual, float $target, string $mode, string $source, bool $unconfigured = false): array
    {
        if ($unconfigured || $value === '—') {
            return ['label' => $label, 'value' => '—', 'targetLabel' => $targetLabel, 'source' => $source, 'status' => 'no_data', 'delta' => '—'];
        }

        if ($mode === 'higher_is_better') {
            if ($actual >= $target * 1.05)       $status = 'exceeds';
            elseif ($actual >= $target * 0.90)   $status = 'on';
            else                                  $status = 'below';
        } elseif ($mode === 'higher_is_worse') {
            if ($actual <= $target * 0.95)       $status = 'exceeds';
            elseif ($actual <= $target * 1.10)   $status = 'on';
            else                                  $status = 'below';
        } else { // band
            $status = ($actual >= 3 && $actual <= 5) ? 'on' : ($actual > 5 ? 'below' : 'exceeds');
        }

        $diff = abs(round($actual - $target, 1));
        if ($mode === 'higher_is_better') {
            $delta = $actual >= $target ? '+' . $diff . ' above' : $diff . ' below';
        } elseif ($mode === 'higher_is_worse') {
            $delta = $actual <= $target ? $diff . ' under' : $diff . ' over';
        } else {
            $delta = 'within band';
        }

        return compact('label', 'value', 'targetLabel', 'source', 'status', 'delta');
    }

    public function storeReflection(Request $request)
    {
        abort_unless($request->user()->can('cases.edit'), 403, 'You do not have permission to add reflections.');

        $request->validate([
            'title'        => 'required|string|max:500',
            'date'         => 'required|date',
            'description'  => 'required|string|max:5000',
            'key_learning' => 'required|string|max:3000',
            'follow_up'    => 'required|string|max:500',
            'status'       => 'required|in:in-progress,completed',
            'outcome'      => 'nullable|string|max:3000',
            'hub_scope'    => 'nullable|string',
            'location'     => 'nullable|string|max:200',
            'quarter'      => 'nullable|string|max:20',
        ]);

        $hubId = ($request->hub_scope && $request->hub_scope !== 'all') ? $request->hub_scope : null;

        Reflection::create([
            'date'         => $request->date,
            'hub_id'       => $hubId,
            'staff'        => $request->attendees ?? auth()->user()->name,
            'title'        => $request->title,
            'description'  => $request->description,
            'key_learning' => $request->key_learning,
            'meta'         => [
                'quarter'   => $request->quarter,
                'location'  => $request->location,
                'status'    => $request->status,
                'follow_up' => $request->follow_up,
                'outcome'   => $request->outcome,
            ],
        ]);

        return back()->with('success', 'Reflection saved.');
    }

    public function storeCaseStudy(Request $request)
    {
        abort_unless($request->user()->can('cases.edit'), 403, 'You do not have permission to add case studies.');

        $request->validate([
            'title'                 => 'required|string|max:255',
            'narrative'             => 'required|string|max:5000',
            'impact_statement'      => 'required|string|max:3000',
            'case_id'               => 'nullable|exists:cases,id',
            'replication_potential' => ['nullable', Rule::in(['low', 'medium', 'high'])],
        ]);

        CaseStudy::create([
            'case_id'               => $request->case_id ?: null,
            'title'                 => $request->title,
            'narrative'             => $request->narrative,
            'impact_statement'      => $request->impact_statement,
            'lessons_learned'       => $request->lessons_learned,
            'replication_potential' => $request->replication_potential ?? 'medium',
            'meta'                  => ['kind' => 'Case study', 'hub' => session('active_hub'), 'year' => now()->year],
        ]);

        return back()->with('success', 'Case study saved.');
    }

    public function updateFinanceInputs(Request $request)
    {
        abort_unless($request->user()->can('settings.view'), 403);

        $request->validate([
            'hub_cost'              => 'nullable|array',
            'hub_cost.*'            => 'nullable|numeric|min:0',
            'overhead_pct'          => 'required|numeric|min:0|max:100',
            'outreach_allocation'   => 'required|numeric|min:0|max:100',
            'target_cost_individual'=> 'required|numeric|min:0',
            'target_cost_case'      => 'required|numeric|min:0',
            'target_overhead_ceiling'=> 'required|numeric|min:0|max:100',
            'target_cost_outreach'  => 'required|numeric|min:0',
            'reach_per_case'        => 'required|numeric|min:1',
            'annual_cases'          => 'required|numeric|min:1',
            'annual_sessions'       => 'required|numeric|min:1',
            'history_period'        => 'nullable|array',
            'history_cost'          => 'nullable|array',
            'submitted_by'          => 'required|string|max:200',
        ]);

        // 1. Update hub_costs table
        $quarter = 'Q' . ceil(now()->month / 3) . ' ' . now()->year;
        foreach ($request->hub_cost ?? [] as $hubId => $cost) {
            if ($cost === null || $cost === '') continue;
            $totalCost = (float) $cost;
            $caseCount = \App\Models\CaseRecord::where('hub_id', $hubId)->count() ?: 1;
            HubCost::updateOrCreate(
                ['hub_id' => $hubId, 'quarter' => $quarter],
                [
                    'total_operational_cost' => $totalCost,
                    'cost_per_case'          => round($totalCost / $caseCount, 2),
                ]
            );
        }

        // 2. Save everything else to finance_config JSON
        $config   = FinanceConfig::current() ?? new FinanceConfig();
        $existing = $config->config ?? [];

        $existing['overheadPct']           = (float) $request->overhead_pct;
        $existing['outreachAllocationPct'] = (float) $request->outreach_allocation;

        $existing['targets'] = [
            'costPerIndividual' => (float) $request->target_cost_individual,
            'costPerCase'       => (float) $request->target_cost_case,
            'overheadCeiling'   => (float) $request->target_overhead_ceiling,
            'costPerOutreach'   => (float) $request->target_cost_outreach,
        ];

        $existing['projections'] = [
            'reachPerCase'   => (int) $request->reach_per_case,
            'annualCases'    => (int) $request->annual_cases,
            'annualSessions' => (int) $request->annual_sessions,
        ];

        // Historical periods
        $periods = $request->history_period ?? [];
        $costs   = $request->history_cost ?? [];
        $history = [];
        foreach ($periods as $i => $period) {
            if ($period && isset($costs[$i]) && $costs[$i] !== '') {
                $history[] = ['period' => $period, 'costPerCase' => (float) $costs[$i]];
            }
        }
        $existing['history'] = $history;

        $existing['asOf']        = now()->toDateString();
        $existing['submittedBy'] = $request->submitted_by;

        $config->config     = $existing;
        $config->updated_by = $request->submitted_by;
        $config->notes      = 'Updated via Finance Inputs modal';
        $config->save();

        return back()->with('success', 'Finance inputs saved successfully.');
    }

    public function updateFinance(Request $request)
    {
        $request->validate([
            'cost_per_case' => 'required|numeric|min:0',
            'overhead_pct'  => 'required|numeric|min:0|max:100',
        ]);

        $config   = FinanceConfig::current() ?? new FinanceConfig();
        $existing = $config->config ?? [];

        $existing['targets']['costPerCase'] = (float) $request->cost_per_case;
        $existing['overheadPct']            = (float) $request->overhead_pct;
        $existing['asOf']                   = now()->toDateString();
        $existing['submittedBy']            = auth()->user()->name;

        $config->config     = $existing;
        $config->updated_by = auth()->user()->name;
        $config->notes      = $request->notes ?? 'Updated via UI';
        $config->save();

        return back()->with('success', 'Finance configuration updated.');
    }
}
