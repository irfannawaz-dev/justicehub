<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Services\DashboardMetricsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function commandCenter(Request $request)
    {
        $role = $request->user()->role;
        $userRole = $role instanceof UserRole ? $role : UserRole::tryFrom((string) $role);
        if ($userRole && in_array($userRole, [UserRole::Lawyer, UserRole::CourtClerk])) {
            return redirect()->route('cases.index');
        }

        $period   = $request->input('period', 'All time');
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $hubNames = $request->input('hub', []);
        $services = $request->input('service', []);
        $districts = $request->input('district', []);

        // Normalise to arrays
        if (is_string($hubNames)) $hubNames = $hubNames === 'All Hubs' ? [] : [$hubNames];
        if (is_string($services)) $services = $services === 'All Services' ? [] : [$services];
        if (is_string($districts)) $districts = $districts === 'All Districts' ? [] : [$districts];

        // Resolve hub names to hub_ids
        $hubIds = [];
        if (!empty($hubNames)) {
            $hubIds = \App\Models\Hub::whereIn('name', $hubNames)->pluck('id')->toArray();
        }

        // Hub-scoped users: force to their hub, ignore filter params
        $user = $request->user();
        if (! $user->canSeeAllHubs()) {
            $hubIds = [$user->hub_id];
        }

        $metrics = new DashboardMetricsService(
            count($hubIds) === 1 ? $hubIds[0] : 'all',
            $period,
            count($services) === 1 ? $services[0] : null
        );
        $metrics->setMultiFilters($hubIds, $services);
        $metrics->setDistricts($districts);
        if ($dateFrom || $dateTo) {
            $metrics->setDateRange($dateFrom, $dateTo);
        }

        // Role-based case filtering
        if ($user->isLawyer()) {
            $metrics->setAssignedTo($user->name);
        }
        if ($user->isCourtClerk()) {
            $metrics->setPathwayFilter(['Representation in Court', 'Court Representation']);
        }

        $m = $metrics->all();

        // Active filter state for view
        $activeFilters = [
            'period'   => $period,
            'hub'      => $hubNames,
            'service'  => $services,
            'district' => $districts,
        ];

        // Time-based greeting
        $hour = now()->hour;
        $greeting = match(true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default    => 'Good evening',
        };

        // ── Cached inline metrics (highRisk, referralSources, hubDist, etc.) ──
        // These ~12 queries are wrapped in a single cache entry per filter combo.
        $version  = (int) Cache::get('jh.cache.version', 0);
        $extraKey = "dashboard.extras.v{$version}." . implode('.', $hubIds ?: ['all'])
                  . '.' . ($period ?? 'all')
                  . '.' . implode('.', $services ?: ['all'])
                  . '.' . implode('.', $districts ?: ['all'])
                  . '.' . ($user->isLawyer() ? $user->name : '_')
                  . '.' . ($dateFrom ?? '') . '_' . ($dateTo ?? '');

        $extras = DashboardMetricsService::cacheEnabled()
            ? Cache::remember($extraKey, DashboardMetricsService::cacheTtl(), fn() => $this->computeDashboardExtras($hubIds, $user, $period, $dateFrom, $dateTo, $services, $districts))
            : $this->computeDashboardExtras($hubIds, $user, $period, $dateFrom, $dateTo, $services, $districts);

        return view('dashboards.command-center', array_merge(
            compact('m', 'greeting', 'activeFilters', 'dateFrom', 'dateTo'),
            $extras
        ));
    }

    public function litigationAdr(Request $request)
    {
        $hubId = $request->input('_active_hub', 'all');
        $user = $request->user();

        // Hub-scoped users: force their hub
        if (! $user->canSeeAllHubs()) {
            $hubId = $user->hub_id;
        }

        $caseQ = function () use ($hubId, $user) {
            $q = \App\Models\CaseRecord::query()->forHub($hubId === 'all' ? null : $hubId);
            // Lawyer: only their assigned cases
            if ($user->isLawyer()) {
                $q->where('assigned_to', $user->name);
            }
            // Court Clerk: only litigation cases
            if ($user->isCourtClerk()) {
                $q->where(fn($sq) => $sq->where('disposition', 'litigation')
                    ->orWhereIn('assigned_pathway', ['Representation in Court', 'Court Representation']));
            }
            return $q;
        };

        // ADR metrics (Mediation pathway only — ADR Complaints tracked separately)
        $adrCases = (clone $caseQ())
            ->where(function($q) {
                $q->where('disposition', 'adr')
                  ->orWhere('assigned_pathway', 'Mediation')
                  ->orWhereExists(function($sq) {
                      $sq->select(DB::raw(1))->from('case_pathway')
                         ->whereColumn('case_pathway.case_id', 'cases.id')
                         ->where('pathway_value', 'like', '%Mediation%');
                  });
            });
        $adrTotal = (clone $adrCases)->count();
        $adrSettled = (clone $adrCases)->whereIn('status', ['Settlement', 'Closed'])->count();
        $adrActive = (clone $adrCases)->whereIn('status', ['Active', 'Pending Approval'])->count();
        $adrGbv = (clone $adrCases)->where('is_gbv', true)->count();
        $adrRate = $adrTotal > 0 ? round(($adrSettled / $adrTotal) * 100) : 0;
        $adrResolved = (clone $adrCases)->whereIn('status', ['Settlement', 'Closed'])->get(['intake_date', 'last_update', 'created_at']);
        $adrAvgDays = $adrResolved->count() > 0 ? round($adrResolved->avg(fn($c) =>
            $c->intake_date && ($c->last_update ?? $c->created_at) ? $c->intake_date->diffInDays($c->last_update ?? $c->created_at) : 0
        )) : 0;

        // Litigation metrics
        $litCases = (clone $caseQ())->where(function($q) {
            $q->where('disposition', 'litigation')
              ->orWhereIn('assigned_pathway', ['Court Representation', 'Representation in Court'])
              ->orWhereExists(function($sq) {
                  $sq->select(DB::raw(1))->from('case_pathway')
                     ->whereColumn('case_pathway.case_id', 'cases.id')
                     ->where(fn($pq) => $pq->where('pathway_value', 'like', '%Litigation%')->orWhere('pathway_value', 'like', '%Court%'));
              });
        });
        $litTotal = (clone $litCases)->count();
        $litActive = (clone $litCases)->where('status', 'Active')->count();
        $litFavourable = (clone $litCases)->whereIn('status', ['Closed', 'Settlement'])->count();
        $litFavRate = $litTotal > 0 ? round(($litFavourable / $litTotal) * 100) : 0;
        $litCriminal = (clone $litCases)->where('primary_issue', 'like', '%Criminal%')->count();
        $litCivil = $litTotal - $litCriminal;
        $litResolved = (clone $litCases)->whereIn('status', ['Closed', 'Settlement'])->get(['intake_date', 'last_update', 'created_at']);
        $litAvgDays = $litResolved->count() > 0 ? round($litResolved->avg(fn($c) =>
            $c->intake_date && ($c->last_update ?? $c->created_at) ? $c->intake_date->diffInDays($c->last_update ?? $c->created_at) : 0
        )) : 0;

        // Staff workload — anyone with active Court Representation or Mediation cases
        $courtPathways    = ['Court Representation', 'Representation in Court'];
        $mediationPathway = 'Mediation';

        // Get names that actually appear on these cases
        $activeNames = \App\Models\CaseRecord::where('status', 'Active')
            ->where(function($q) use ($courtPathways, $mediationPathway) {
                $q->whereIn('assigned_pathway', $courtPathways)
                  ->orWhere('assigned_pathway', $mediationPathway);
            })
            ->when(! $user->canSeeAllHubs(), fn($q) => $q->where('hub_id', $user->hub_id))
            ->when($user->isLawyer(), fn($q) => $q->where('assigned_to', $user->name))
            ->whereNotNull('assigned_to')
            ->pluck('assigned_to')
            ->unique();

        $staff = $activeNames->map(function($name) use ($courtPathways, $mediationPathway) {
            $s    = \App\Models\Staff::where('name', $name)->first();
            $base = \App\Models\CaseRecord::where('assigned_to', $name)->where('status', 'Active');
            $court     = (clone $base)->whereIn('assigned_pathway', $courtPathways)->count();
            $mediation = (clone $base)->where('assigned_pathway', $mediationPathway)->count();
            $load      = $court + $mediation;

            $initials = collect(explode(' ', $name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
            $role     = $s?->role ?? 'Staff';
            $capacity = str_contains(strtolower($role), 'lawyer') ? 25 : 35;

            return [
                'name'        => $name,
                'initials'    => $s?->initials ?? $initials,
                'role'        => $role,
                'designation' => $s?->user?->designation ?: $role,
                'hub'         => $s?->hub?->name ?? ($s?->hub_id ?? '—'),
                'court'       => $court,
                'mediation'   => $mediation,
                'active'      => $load,
                'capacity'    => $capacity,
                'utilization' => min($capacity > 0 ? round(($load / $capacity) * 100) : 0, 100),
            ];
        })->sortByDesc('active')->values();

        return view('dashboards.litigation-adr', compact(
            'adrTotal', 'adrSettled', 'adrActive', 'adrGbv', 'adrRate', 'adrAvgDays',
            'litTotal', 'litActive', 'litFavourable', 'litFavRate', 'litCriminal', 'litCivil', 'litAvgDays',
            'staff'
        ));
    }

    public function lcd(Request $request)
    {
        $hubId = $request->input('hub', 'all');
        $metrics = new DashboardMetricsService($hubId);

        $q = \App\Models\CaseRecord::query();
        if ($hubId && $hubId !== 'all') $q->where('hub_id', $hubId);

        $lcd = [
            'total'       => (clone $q)->count(),
            'active'      => (clone $q)->where('status', 'Active')->count(),
            'pending'     => (clone $q)->where('status', 'Pending Approval')->count(),
            'closed'      => (clone $q)->whereIn('status', ['Closed', 'Settlement'])->count(),
            'high_urgency'=> (clone $q)->whereIn('urgency', ['High', 'Immediate'])->count(),
            'safeguarding'=> (clone $q)->where(fn($sq) => $sq->where('is_gbv', true)->orWhere('is_child', true))->where('status', 'Active')->count(),
            'sla_pct'     => $metrics->slaCompliancePct(),
            'sla_breach'  => (clone $q)->where('sla_met', false)->count(),
        ];

        $sources = (clone $q)->select('referral_source', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('referral_source')->where('referral_source', '!=', '')
            ->groupBy('referral_source')->orderByDesc('cnt')->limit(10)->pluck('cnt', 'referral_source')->toArray();
        $categories = (clone $q)->select('primary_issue', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('primary_issue')->where('primary_issue', '!=', '')
            ->groupBy('primary_issue')->orderByDesc('cnt')->limit(10)->pluck('cnt', 'primary_issue')->toArray();
        $statusBreakdown = $metrics->statusBreakdown();
        $advisors = (clone $q)->where('status', 'Active')
            ->select('assigned_to', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('assigned_to')->where('assigned_to', '!=', '')
            ->groupBy('assigned_to')->orderByDesc('cnt')->pluck('cnt', 'assigned_to')->toArray();
        $pathways = DB::table('case_pathway')
            ->join('cases', 'cases.id', '=', 'case_pathway.case_id')
            ->when($hubId && $hubId !== 'all', fn($pq) => $pq->where('cases.hub_id', $hubId))
            ->select('case_pathway.pathway_value', DB::raw('COUNT(*) as cnt'))
            ->groupBy('case_pathway.pathway_value')->orderByDesc('cnt')->pluck('cnt', 'pathway_value')->toArray();

        // Hub-wise breakdown
        $hubBreakdown = \App\Models\CaseRecord::select('hub_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('hub_id')->orderByDesc('cnt')->pluck('cnt', 'hub_id')->toArray();
        $hubNames = \App\Models\Hub::pluck('name', 'id')->toArray();

        $hubs = \App\Models\Hub::where('is_active', true)->orderBy('name')->pluck('name', 'id');

        // Today's registrations
        $todayCases = (clone $q)->whereDate('intake_date', today())->count();

        // Monthly intake trend (last 6 months)
        $monthlyTrend = (clone $q)
            ->select(DB::raw("DATE_FORMAT(intake_date, '%Y-%m') as month"), DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('intake_date')
            ->where('intake_date', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('cnt', 'month')
            ->toArray();

        return view('dashboards.lcd', compact(
            'lcd', 'sources', 'categories', 'statusBreakdown', 'advisors',
            'pathways', 'hubBreakdown', 'hubNames', 'hubs', 'hubId', 'todayCases', 'monthlyTrend'
        ));
    }

    /**
     * Compute the dashboard "extras" (highRisk, referralSources, hubDist, etc.)
     * Extracted so it can be wrapped in Cache::remember() by commandCenter().
     */
    private function computeDashboardExtras(array $hubIds, $user, ?string $period, ?string $dateFrom, ?string $dateTo, array $services, array $districts): array
    {
        $q = \App\Models\CaseRecord::query();
        if (!empty($hubIds)) {
            $q->whereIn('hub_id', $hubIds);
        }
        if ($user->isLawyer()) {
            $q->where('assigned_to', $user->name);
        }
        if ($user->isCourtClerk()) {
            $q->where(fn($sq) => $sq->where('disposition', 'litigation')
                ->orWhereIn('assigned_pathway', ['Representation in Court', 'Court Representation']));
        }
        if ($dateFrom || $dateTo) {
            if ($dateFrom) $q->where('intake_date', '>=', $dateFrom);
            if ($dateTo)   $q->where('intake_date', '<=', $dateTo);
        } elseif ($period && $period !== 'All time') {
            $from = match ($period) {
                'Today'        => now()->startOfDay(),
                'Last 7 days'  => now()->subDays(7)->startOfDay(),
                'Last 30 days' => now()->subDays(30)->startOfDay(),
                'Last 90 days' => now()->subDays(90)->startOfDay(),
                'Year to date' => now()->startOfYear(),
                default        => null,
            };
            if ($from) $q->where('intake_date', '>=', $from->toDateString());
        }
        if (!empty($services)) {
            $q->where(function ($sq) use ($services) {
                foreach ($services as $svc) {
                    $sq->orWhere('assigned_pathway', 'like', '%' . $svc . '%');
                }
            });
        }
        if (!empty($districts)) {
            $q->whereIn('district', $districts);
        }

        $highRisk      = (clone $q)->where('risk', 'High')->count();
        $casesLast7    = (clone $q)->where('intake_date', '>=', now()->subDays(7)->toDateString())->count();
        $resolvedLast7 = (clone $q)->whereIn('status', ['Closed', 'Settlement'])
                            ->where('updated_at', '>=', now()->subDays(7))->count();

        $referralSources = (clone $q)->select('referral_source', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('referral_source')->where('referral_source', '!=', '')
            ->groupBy('referral_source')->orderByDesc('cnt')->limit(8)
            ->pluck('cnt', 'referral_source')->toArray();

        $primaryIssues = (clone $q)->select('primary_issue', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('primary_issue')->where('primary_issue', '!=', '')
            ->groupBy('primary_issue')->orderByDesc('cnt')->limit(8)
            ->pluck('cnt', 'primary_issue')->toArray();

        $hubColorPalette = ['#4a7a5c', '#163029', '#b87319', '#7e57c2', '#6b6a65', '#8a2e1d'];
        $hubQuery = \App\Models\Hub::where('is_active', true);
        if (!empty($hubIds)) {
            $hubQuery->whereIn('id', $hubIds);
        }
        $hubDist = $hubQuery->get()
            ->map(function ($h) use ($q) {
                $cnt = (clone $q)->where('hub_id', $h->id)->count();
                return ['id' => $h->id, 'name' => $h->name, 'count' => $cnt];
            })
            ->sortByDesc('count')
            ->values();
        $hubTotal = $hubDist->sum('count') ?: 1;
        $hubDist = $hubDist->map(function ($h, $i) use ($hubColorPalette, $hubTotal) {
            $h['pct']   = round(($h['count'] / $hubTotal) * 100, 1);
            $h['color'] = $hubColorPalette[$i % count($hubColorPalette)];
            return $h;
        });

        $lookupDistricts = \App\Models\Lookup::where('group_key', 'intake.district')
            ->where('is_active', true)->orderBy('sort_order')->pluck('value')->toArray();
        $caseDistricts = \App\Models\CaseRecord::query()
            ->whereNotNull('district')->where('district', '!=', '')
            ->distinct()->pluck('district')->toArray();
        $availableDistricts = collect(array_unique(array_merge($lookupDistricts, $caseDistricts)))
            ->sort()->values()->toArray();

        return compact('highRisk', 'casesLast7', 'resolvedLast7', 'hubDist',
            'referralSources', 'primaryIssues', 'availableDistricts');
    }

    public function downloadReport(Request $request)
    {
        $role = $request->user()->role;
        $userRole = $role instanceof UserRole ? $role : UserRole::tryFrom((string) $role);

        $period   = $request->input('period', 'All time');
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $hubNames = $request->input('hub', []);
        $services = $request->input('service', []);
        $districts = $request->input('district', []);

        if (is_string($hubNames)) $hubNames = $hubNames === 'All Hubs' ? [] : [$hubNames];
        if (is_string($services)) $services = $services === 'All Services' ? [] : [$services];
        if (is_string($districts)) $districts = $districts === 'All Districts' ? [] : [$districts];

        $hubIds = [];
        if (!empty($hubNames)) {
            $hubIds = \App\Models\Hub::whereIn('name', $hubNames)->pluck('id')->toArray();
        }

        $user = $request->user();
        if (! $user->canSeeAllHubs()) {
            $hubIds = [$user->hub_id];
        }

        $metrics = new DashboardMetricsService(
            count($hubIds) === 1 ? $hubIds[0] : 'all',
            $period,
            count($services) === 1 ? $services[0] : null
        );
        $metrics->setMultiFilters($hubIds, $services);
        $metrics->setDistricts($districts);
        if ($dateFrom || $dateTo) {
            $metrics->setDateRange($dateFrom, $dateTo);
        }
        if ($user->isLawyer()) {
            $metrics->setAssignedTo($user->name);
        }

        $m = $metrics->all();
        $extras = $this->computeDashboardExtras($hubIds, $user, $period, $dateFrom, $dateTo, $services, $districts);

        $filterLabel = 'All Hubs · All Services · ' . $period;
        if (!empty($hubNames)) $filterLabel = implode(', ', $hubNames) . ' · ' . $period;

        $data = array_merge(compact('m', 'filterLabel', 'user'), $extras);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.dashboard-pdf', $data)
            ->setPaper('a4', 'landscape');

        $filename = 'JusticeHub_Dashboard_' . now()->format('Y-m-d_His') . '.pdf';
        return $pdf->download($filename);
    }
}
