<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Services\DashboardMetricsService;
use Illuminate\Http\Request;
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

        $hubId    = $request->input('_active_hub', 'all');
        $period   = $request->input('period', 'All time');
        $hubNames = $request->input('hub', []);
        $services = $request->input('service', []);

        // Normalise to arrays
        if (is_string($hubNames)) $hubNames = $hubNames === 'All Hubs' ? [] : [$hubNames];
        if (is_string($services)) $services = $services === 'All Services' ? [] : [$services];

        // Resolve hub names to hub_ids
        $hubIds = [];
        if (!empty($hubNames)) {
            $hubIds = \App\Models\Hub::whereIn('name', $hubNames)->pluck('id')->toArray();
        }

        // Hub-scoped users: force to their hub, ignore filter params
        $user = $request->user();
        if (! $user->canSeeAllHubs()) {
            $hubIds = [$user->hub_id];
            $hubId  = $user->hub_id;
        }

        $metrics = new DashboardMetricsService(
            count($hubIds) === 1 ? $hubIds[0] : 'all',
            $period,
            count($services) === 1 ? $services[0] : null
        );
        // Pass multi-hub and multi-service for caseQuery override
        $metrics->setMultiFilters($hubIds, $services);

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
            'period'  => $period,
            'hub'     => $hubNames,
            'service' => $services,
        ];

        // Time-based greeting
        $hour = now()->hour;
        $greeting = match(true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default    => 'Good evening',
        };

        // Additional inline metrics — apply same filters
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
        if ($period && $period !== 'All time') {
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

        $highRisk    = (clone $q)->where('risk', 'High')->count();
        $casesLast7  = (clone $q)->where('intake_date', '>=', now()->subDays(7)->toDateString())->count();
        $resolvedLast7 = (clone $q)->whereIn('status', ['Closed', 'Settlement'])
                            ->where('updated_at', '>=', now()->subDays(7))->count();

        // Referral sources for command-center
        $referralSources = (clone $q)->select('referral_source', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('referral_source')->where('referral_source', '!=', '')
            ->groupBy('referral_source')->orderByDesc('cnt')->limit(8)
            ->pluck('cnt', 'referral_source')->toArray();

        // Primary issue distribution
        $primaryIssues = (clone $q)->select('primary_issue', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('primary_issue')->where('primary_issue', '!=', '')
            ->groupBy('primary_issue')->orderByDesc('cnt')->limit(8)
            ->pluck('cnt', 'primary_issue')->toArray();

        // Hub distribution for geographic section — respects all active filters
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

        return view('dashboards.command-center', compact(
            'm', 'greeting', 'highRisk', 'casesLast7', 'resolvedLast7', 'hubDist',
            'referralSources', 'primaryIssues', 'activeFilters'
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

        // ADR metrics
        $adrCases = (clone $caseQ())->whereHas('serviceEncounters', fn($q) => $q) // all cases
            ->where(function($q) {
                $q->where('disposition', 'adr')
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

        // Staff workload — scoped by role
        $staffQ = \App\Models\Staff::with(['hub'])->where('status', 'active');
        if ($user->isLawyer()) {
            // Lawyer sees only themselves
            $staffQ->where('name', $user->name);
        } elseif (! $user->canSeeAllHubs()) {
            // Hub-scoped users see only their hub's staff
            $staffQ->where('hub_id', $user->hub_id);
        }
        $staff = $staffQ->get()->map(function($s) {
            $load = \App\Models\CaseRecord::where('assigned_to', $s->name)->where('status', 'Active')->count();
            $capacity = $s->role === 'Lawyer' ? 25 : 35;
            $utilization = $capacity > 0 ? round(($load / $capacity) * 100) : 0;
            return [
                'name' => $s->name, 'initials' => $s->initials, 'role' => $s->role,
                'hub' => $s->hub?->name ?? $s->hub_id, 'active' => $load,
                'capacity' => $capacity, 'utilization' => min($utilization, 100),
            ];
        });

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
}
