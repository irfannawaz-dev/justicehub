<?php

namespace App\Services;

use App\Models\CaseRecord;
use App\Models\Complaint;
use App\Models\Feedback;
use App\Models\Hub;
use App\Models\OutreachActivity;
use App\Models\ServiceEncounter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardMetricsService
{
    protected ?string $hubId;
    protected ?string $period;
    protected ?string $service;
    protected array $hubIds = [];
    protected array $services = [];
    protected array $districts = [];
    protected ?string $assignedTo = null;
    protected ?array $pathwayFilter = null;
    protected ?string $dateFrom = null;
    protected ?string $dateTo = null;

    public function __construct(?string $hubId = null, ?string $period = null, ?string $service = null)
    {
        $this->hubId   = ($hubId && $hubId !== 'all') ? $hubId : null;
        $this->period  = ($period && $period !== 'All time') ? $period : null;
        $this->service = ($service && $service !== 'All Services') ? $service : null;
    }

    public function setMultiFilters(array $hubIds = [], array $services = []): self
    {
        $this->hubIds   = $hubIds;
        $this->services = $services;
        return $this;
    }

    public function setDistricts(array $districts): self
    {
        $this->districts = $districts;
        return $this;
    }

    /** Filter cases to only those assigned to a specific user (for Lawyer role). */
    public function setAssignedTo(?string $name): self
    {
        $this->assignedTo = $name;
        return $this;
    }

    /** Filter cases to specific pathways (for Court Clerk role). */
    public function setPathwayFilter(?array $pathways): self
    {
        $this->pathwayFilter = $pathways;
        return $this;
    }

    /** Apply a custom date range filter (overrides period). */
    public function setDateRange(?string $from, ?string $to): self
    {
        $this->dateFrom = $from;
        $this->dateTo   = $to;
        return $this;
    }

    // ─────────────────────────────────────────────────────────────
    // CASE COUNTS
    // ─────────────────────────────────────────────────────────────

    public function totalCases(): int
    {
        return $this->caseQuery()->count();
    }

    public function activeCases(): int
    {
        return $this->caseQuery()->where('status', 'Active')->count();
    }

    public function closedCases(): int
    {
        return $this->caseQuery()->whereIn('status', ['Closed', 'Settlement'])->count();
    }

    public function casesThisMonth(): int
    {
        return $this->caseQuery()->where('intake_date', '>=', now()->startOfMonth())->count();
    }

    public function casesThisQuarter(): int
    {
        return $this->caseQuery()->where('intake_date', '>=', now()->startOfQuarter())->count();
    }

    public function pendingApproval(): int
    {
        return $this->caseQuery()->where('status', 'Pending Approval')->count();
    }

    // ─────────────────────────────────────────────────────────────
    // SLA
    // ─────────────────────────────────────────────────────────────

    public function slaCompliancePct(): float
    {
        $total = $this->caseQuery()->count();
        if ($total === 0) return 100;
        $met = $this->caseQuery()->where('sla_met', true)->count();
        return round(($met / $total) * 100, 1);
    }

    public function slaBreach(): int
    {
        return $this->caseQuery()->where('sla_met', false)->count();
    }

    // ─────────────────────────────────────────────────────────────
    // DEMOGRAPHICS
    // ─────────────────────────────────────────────────────────────

    public function genderSplit(): array
    {
        $counts = $this->caseQuery()
            ->select('gender', DB::raw('COUNT(*) as cnt'))
            ->groupBy('gender')
            ->pluck('cnt', 'gender')
            ->toArray();
        $total = array_sum($counts);
        return [
            'counts' => $counts,
            'pct' => $total > 0 ? array_map(fn($c) => round(($c / $total) * 100, 1), $counts) : [],
        ];
    }

    public function ageDistribution(): array
    {
        $bins = ['0-17' => 0, '18-25' => 0, '26-35' => 0, '36-50' => 0, '51-65' => 0, '65+' => 0];
        $cases = $this->caseQuery()->select('age')->get();
        foreach ($cases as $c) {
            $a = $c->age;
            if ($a <= 17) $bins['0-17']++;
            elseif ($a <= 25) $bins['18-25']++;
            elseif ($a <= 35) $bins['26-35']++;
            elseif ($a <= 50) $bins['36-50']++;
            elseif ($a <= 65) $bins['51-65']++;
            else $bins['65+']++;
        }
        return ['labels' => array_keys($bins), 'values' => array_values($bins)];
    }

    public function religionDistribution(): array
    {
        return $this->caseQuery()
            ->select('religion', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('religion')
            ->groupBy('religion')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'religion')
            ->toArray();
    }

    // ─────────────────────────────────────────────────────────────
    // VULNERABILITY FLAGS
    // ─────────────────────────────────────────────────────────────

    public function vulnerabilityFlags(): array
    {
        return [
            'gbv' => $this->caseQuery()->where(fn($q) => $q
                ->where('is_gbv', true)
                ->orWhere('primary_issue', 'like', '%GBV%')
                ->orWhere('secondary_issue', 'like', '%GBV%')
            )->count(),

            'child' => $this->caseQuery()->where(fn($q) => $q
                ->where('is_child', true)
                ->orWhere('primary_issue', 'like', '%Juvenile%')
                ->orWhere('primary_issue', 'like', '%Child%')
                ->orWhere('secondary_issue', 'like', '%Juvenile%')
                ->orWhere('secondary_issue', 'like', '%Child%')
            )->count(),

            'minority'    => $this->caseQuery()->where('is_minority', true)->count(),
            'disability'  => $this->caseQuery()->where('is_disability', true)->count(),
            'underserved' => $this->caseQuery()->where('is_underserved', true)->count(),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // PATHWAY / DISPOSITION
    // ─────────────────────────────────────────────────────────────

    public function dispositionBreakdown(): array
    {
        return $this->caseQuery()
            ->select('disposition', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('disposition')
            ->groupBy('disposition')
            ->pluck('cnt', 'disposition')
            ->toArray();
    }

    public function statusBreakdown(): array
    {
        return $this->caseQuery()
            ->select('status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->toArray();
    }

    // ─────────────────────────────────────────────────────────────
    // SERVICE MIX
    // ─────────────────────────────────────────────────────────────

    public function serviceMix(): array
    {
        return $this->caseQuery()
            ->whereNotNull('assigned_pathway')
            ->where('assigned_pathway', '!=', '')
            ->select('assigned_pathway', DB::raw('COUNT(*) as cnt'))
            ->groupBy('assigned_pathway')
            ->orderByDesc('cnt')
            ->pluck('cnt', 'assigned_pathway')
            ->toArray();
    }

    // ─────────────────────────────────────────────────────────────
    // DAILY ACTIVITY TRENDS (last 30 days)
    // ─────────────────────────────────────────────────────────────

    public function dailyActivityTrend(int $days = 30): array
    {
        $startDate = now()->subDays($days)->startOfDay();
        $caseIds = $this->caseQuery()->pluck('id');
        $query = ServiceEncounter::where('date', '>=', $startDate)
            ->whereIn('case_id', $caseIds);
        $counts = $query
            ->select(DB::raw('DATE(date) as d'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('cnt', 'd')
            ->toArray();

        $labels = [];
        $values = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($date)->format('M d');
            $values[] = $counts[$date] ?? 0;
        }
        return ['labels' => $labels, 'values' => $values];
    }

    // ─────────────────────────────────────────────────────────────
    // HUB PERFORMANCE
    // ─────────────────────────────────────────────────────────────

    public function hubPerformance(): array
    {
        $hubQuery = Hub::where('is_active', true);
        if (!empty($this->hubIds)) {
            $hubQuery->whereIn('id', $this->hubIds);
        } elseif ($this->hubId) {
            $hubQuery->where('id', $this->hubId);
        }
        $hubs = $hubQuery->get()->keyBy('id');

        $baseQ = $this->caseQuery();
        $stats = (clone $baseQ)->select(
                'hub_id',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active"),
                DB::raw("SUM(CASE WHEN status IN ('Closed','Settlement') THEN 1 ELSE 0 END) as closed"),
                DB::raw('SUM(sla_met) as sla_met')
            )
            ->groupBy('hub_id')
            ->get()
            ->keyBy('hub_id');

        return $hubs->map(function ($hub) use ($stats) {
            $s = $stats[$hub->id] ?? null;
            $total = $s?->total ?? 0;
            return [
                'id'      => $hub->id,
                'name'    => $hub->name,
                'total'   => $total,
                'active'  => $s?->active ?? 0,
                'closed'  => $s?->closed ?? 0,
                'sla_pct' => $total > 0 ? round(($s->sla_met / $total) * 100, 1) : 100,
            ];
        })->values()->toArray();
    }

    // ─────────────────────────────────────────────────────────────
    // SATISFACTION (from feedback)
    // ─────────────────────────────────────────────────────────────

    public function satisfactionAvg(): float
    {
        $query = Feedback::query();
        $this->applyHubFilter($query);
        return round($query->avg('score_overall') ?? 0, 1);
    }

    public function satisfactionTrend(): array
    {
        $query = Feedback::query();
        $this->applyHubFilter($query);
        $monthly = $query
            ->select(DB::raw("DATE_FORMAT(date, '%Y-%m') as month"), DB::raw('AVG(score_overall) as avg_score'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'labels' => $monthly->pluck('month')->map(fn($m) => Carbon::parse($m . '-01')->format('M Y'))->toArray(),
            'values' => $monthly->pluck('avg_score')->map(fn($v) => round($v, 1))->toArray(),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // COMPLAINTS SUMMARY
    // ─────────────────────────────────────────────────────────────

    public function complaintsOpen(): int
    {
        $query = Complaint::where('status', '!=', 'resolved');
        $this->applyHubFilter($query);
        return $query->count();
    }

    // ─────────────────────────────────────────────────────────────
    // OUTREACH SUMMARY
    // ─────────────────────────────────────────────────────────────

    public function outreachSummary(): array
    {
        $query = OutreachActivity::query();
        $this->applyHubFilter($query);
        return [
            'sessions'     => $query->count(),
            'participants' => (clone $query)->sum('total_participants'),
            'female'       => (clone $query)->sum('female_participants'),
            'minority'     => (clone $query)->sum('minority_participants'),
            'disability'   => (clone $query)->sum('disability_participants'),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // COST PER CASE (VfM)
    // ─────────────────────────────────────────────────────────────

    public function costPerCase(): float
    {
        $total = $this->totalCases();
        if ($total === 0) return 0;
        $annualCost = \App\Models\FinanceConfig::getValue('default_annual_operational', config('justice_hub.finance.default_annual_operational'));
        return round($annualCost / $total, 0);
    }

    // ─────────────────────────────────────────────────────────────
    // AGGREGATE — returns all metrics at once for the dashboard
    // ─────────────────────────────────────────────────────────────

    public function all(): array
    {
        // Check if caching is enabled via settings
        $cacheEnabled = self::cacheEnabled();
        if (! $cacheEnabled) {
            return $this->compute();
        }

        $version   = (int) Cache::get('jh.cache.version', 0);
        $hubPart   = !empty($this->hubIds) ? implode(',', $this->hubIds) : ($this->hubId ?? 'all');
        $svcPart   = !empty($this->services) ? implode(',', $this->services) : ($this->service ?? 'all');
        $userPart  = $this->assignedTo ?? ($this->pathwayFilter ? implode(',', $this->pathwayFilter) : 'all');
        $datePart  = ($this->dateFrom ?? '') . '_' . ($this->dateTo ?? '');
        $cacheKey  = "dashboard.metrics.v{$version}.{$hubPart}." . ($this->period ?? 'all') . ".{$svcPart}.{$userPart}.{$datePart}";
        $ttl       = self::cacheTtl();

        return Cache::remember($cacheKey, $ttl, fn() => $this->compute());
    }

    /**
     * Flush ALL JusticeHub caches by incrementing the version counter.
     * Old cache entries expire naturally at their TTL (no need to delete them).
     */
    public static function flush(): void
    {
        $current = (int) Cache::get('jh.cache.version', 0);
        Cache::forever('jh.cache.version', $current + 1);
        IndicatorDerivationService::flush();
    }

    /** Check if caching is enabled (cached for 60s to avoid DB hit every request). */
    public static function cacheEnabled(): bool
    {
        return Cache::remember('jh.settings.cache_enabled', 60, function () {
            return DB::table('settings')->where('key', 'cache_enabled')->value('value') ?? 'on';
        }) !== 'off';
    }

    /** Get cache TTL in seconds (cached for 60s). */
    public static function cacheTtl(): int
    {
        return (int) Cache::remember('jh.settings.cache_ttl', 60, function () {
            return DB::table('settings')->where('key', 'cache_ttl')->value('value') ?? 300;
        });
    }

    private function compute(): array
    {
        return [
            'total_cases'       => $this->totalCases(),
            'active_cases'      => $this->activeCases(),
            'closed_cases'      => $this->closedCases(),
            'cases_this_month'  => $this->casesThisMonth(),
            'cases_this_quarter'=> $this->casesThisQuarter(),
            'pending_approval'  => $this->pendingApproval(),
            'sla_compliance'    => $this->slaCompliancePct(),
            'sla_breach'        => $this->slaBreach(),
            'gender_split'      => $this->genderSplit(),
            'age_distribution'  => $this->ageDistribution(),
            'religion'          => $this->religionDistribution(),
            'vulnerability'     => $this->vulnerabilityFlags(),
            'disposition'       => $this->dispositionBreakdown(),
            'status'            => $this->statusBreakdown(),
            'service_mix'       => $this->serviceMix(),
            'daily_activity'    => $this->dailyActivityTrend(),
            'hub_performance'   => $this->hubPerformance(),
            'satisfaction_avg'  => $this->satisfactionAvg(),
            'satisfaction_trend'=> $this->satisfactionTrend(),
            'complaints_open'   => $this->complaintsOpen(),
            'outreach'          => $this->outreachSummary(),
            'cost_per_case'     => $this->costPerCase(),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────

    /** Apply hub filter to non-case models (Feedback, Complaint, Outreach) that have hub_id. */
    protected function applyHubFilter($query): void
    {
        if (!empty($this->hubIds)) {
            $query->whereIn('hub_id', $this->hubIds);
        } elseif ($this->hubId) {
            $query->where('hub_id', $this->hubId);
        }
    }

    protected function caseQuery()
    {
        $q = CaseRecord::query();

        // Hub filter — multi takes precedence
        if (!empty($this->hubIds)) {
            $q->whereIn('hub_id', $this->hubIds);
        } elseif ($this->hubId) {
            $q->where('hub_id', $this->hubId);
        }

        // Custom date range takes precedence over period
        if ($this->dateFrom || $this->dateTo) {
            if ($this->dateFrom) $q->where('intake_date', '>=', $this->dateFrom);
            if ($this->dateTo)   $q->where('intake_date', '<=', $this->dateTo);
        }

        // Period filter
        if (!$this->dateFrom && !$this->dateTo && $this->period) {
            $from = match ($this->period) {
                'Today'        => now()->startOfDay(),
                'Last 7 days'  => now()->subDays(7)->startOfDay(),
                'Last 30 days' => now()->subDays(30)->startOfDay(),
                'Last 90 days' => now()->subDays(90)->startOfDay(),
                'Year to date' => now()->startOfYear(),
                default        => null,
            };
            if ($from) $q->where('intake_date', '>=', $from->toDateString());
        }

        // Service / pathway filter — multi takes precedence
        if (!empty($this->services)) {
            $q->where(function ($sq) {
                foreach ($this->services as $svc) {
                    $sq->orWhere('assigned_pathway', 'like', '%' . $svc . '%');
                }
            });
        } elseif ($this->service) {
            $q->where('assigned_pathway', 'like', '%' . $this->service . '%');
        }

        // District filter
        if (!empty($this->districts)) {
            $q->whereIn('district', $this->districts);
        }

        // Lawyer: only their assigned cases
        if ($this->assignedTo) {
            $q->where('assigned_to', $this->assignedTo);
        }

        // Court Clerk: only litigation cases
        if ($this->pathwayFilter) {
            $q->where(fn($sq) => $sq->where('disposition', 'litigation')
                ->orWhereIn('assigned_pathway', $this->pathwayFilter));
        }

        return $q;
    }
}
