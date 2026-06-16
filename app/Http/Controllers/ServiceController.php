<?php

namespace App\Http\Controllers;

use App\Models\CaseRecord;
use App\Models\ServiceEncounter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function adrScorecard(Request $request)
    {
        $hubId = $request->input('_active_hub', 'all');
        $q = CaseRecord::query()->where(function ($sq) {
            $sq->where('disposition', 'adr')
               ->orWhere('assigned_pathway', 'Mediation & ADR')
               ->orWhere('assigned_pathway', 'like', '%Mediation%')
               ->orWhere('assigned_pathway', 'ADR / Dispute Resolution Support');
        });
        if ($hubId && $hubId !== 'all') $q->where('hub_id', $hubId);

        $total = (clone $q)->count();
        $settled = (clone $q)->whereIn('status', ['Settlement', 'Closed'])->count();
        $active = (clone $q)->whereIn('status', ['Active', 'Pending Approval'])->count();
        $gbv = (clone $q)->where('is_gbv', true)->count();
        $female = (clone $q)->where('gender', 'Female')->count();
        $minority = (clone $q)->where('is_minority', true)->count();
        $child = (clone $q)->where('is_child', true)->count();
        $disability = (clone $q)->where('is_disability', true)->count();
        $rate = $total > 0 ? round(($settled / $total) * 100) : 0;

        // Service encounters count for ADR cases
        $adrCaseIds = (clone $q)->pluck('id');
        $servicesDelivered = ServiceEncounter::whereIn('case_id', $adrCaseIds)->count();

        // Average days to resolution for settled ADR cases
        $resolvedAdr = (clone $q)->whereIn('status', ['Closed', 'Settlement'])
            ->get(['intake_date', 'last_update', 'created_at']);
        $adrTotalDays = $resolvedAdr->sum(fn($c) =>
            $c->intake_date && ($c->last_update ?? $c->created_at)
                ? $c->intake_date->diffInDays($c->last_update ?? $c->created_at)
                : 0
        );
        $avgDays = $resolvedAdr->count() > 0 ? round($adrTotalDays / $resolvedAdr->count()) : 0;

        // Cases with mediation encounters for pipeline
        $cases = (clone $q)->with(['serviceEncounters' => fn($sq) => $sq->orderBy('date')])
            ->latest('intake_date')->get();

        // 5-stage pipeline
        $pipeline = ['ADR Intake' => [], 'In Mediation' => [], 'Settlement Draft' => [], 'Resolved' => [], 'Escalated' => []];
        foreach ($cases as $c) {
            $lastType = $c->serviceEncounters->last()?->type ?? 'Intake';
            $daysInStage = $c->last_update ? (int) $c->last_update->diffInDays(now()) : (int) $c->intake_date->diffInDays(now());
            $c->days_in_stage = $daysInStage;
            $c->session_count = $c->serviceEncounters->count();

            // Use manual adr_stage if set, otherwise infer from encounters/status
            $manualStage = $c->adr_stage ?? null;
            if ($manualStage && array_key_exists($manualStage, $pipeline)) {
                $pipeline[$manualStage][] = $c;
            } elseif ($c->status->value === 'Rejected' || str_contains(strtolower($lastType), 'litigation') || str_contains(strtolower($lastType), 'court')) {
                $pipeline['Escalated'][] = $c;
            } elseif (in_array($c->status->value, ['Closed', 'Settlement'])) {
                $pipeline['Resolved'][] = $c;
            } elseif (str_contains(strtolower($lastType), 'settlement') || str_contains(strtolower($lastType), 'closure') || str_contains(strtolower($lastType), 'draft')) {
                $pipeline['Settlement Draft'][] = $c;
            } elseif (str_contains(strtolower($lastType), 'mediation')) {
                $pipeline['In Mediation'][] = $c;
            } else {
                $pipeline['ADR Intake'][] = $c;
            }
        }

        // Resolution outcomes for chart
        $escalated = count($pipeline['Escalated']);
        $withdrawn = 0; // No withdrawn status currently
        $ongoingAdr = $active;
        $outcomes = [
            'Settled via ADR' => $settled,
            'Ongoing ADR'     => $ongoingAdr,
            'Escalated'       => $escalated,
            'Withdrawn'       => $withdrawn,
        ];

        // Staff workload for ADR
        $staff = \App\Models\Staff::with(['hub'])->where('status', 'active')->get()->map(function ($s) {
            $allActive = CaseRecord::where('assigned_to', $s->name)->where('status', 'Active');
            $adrCount = (clone $allActive)->where(fn($sq) => $sq->where('disposition', 'adr')->orWhere('assigned_pathway', 'Mediation & ADR'))->count();
            $courtCount = (clone $allActive)->where(fn($sq) => $sq->where('disposition', 'litigation')->orWhereIn('assigned_pathway', ['Representation in Court', 'Court Representation']))->count();
            $totalActive = (clone $allActive)->count();
            $capacity = $s->role === 'Lawyer' ? 25 : 35;
            $utilization = $capacity > 0 ? round(($totalActive / $capacity) * 100) : 0;
            $slaBreach = CaseRecord::where('assigned_to', $s->name)->where('sla_met', false)->count();
            return [
                'name' => $s->name, 'initials' => $s->initials, 'role' => $s->role,
                'hub' => $s->hub?->name ?? $s->hub_id, 'hub_id' => $s->hub_id,
                'active' => $totalActive, 'adr' => $adrCount, 'court' => $courtCount,
                'capacity' => $capacity, 'utilization' => min($utilization, 100),
                'sla_breach' => $slaBreach,
            ];
        })->sortByDesc('active')->values();

        // Only ADR/Mediation pathway cases for the log service modal
        $activeCases = CaseRecord::query()
            ->when($hubId && $hubId !== 'all', fn($q) => $q->where('hub_id', $hubId))
            ->whereNotIn('status', ['Closed', 'Settlement', 'Rejected'])
            ->where(fn($q) => $q
                ->whereIn('assigned_pathway', ['Mediation', 'ADR / Dispute Resolution Support'])
                ->orWhere('disposition', 'adr')
            )
            ->orderBy('name')
            ->get(['id', 'case_uid', 'name', 'primary_issue', 'hub_id', 'disposition']);

        // Provider dropdown — real users with service-delivering roles
        $providerRoles = ['lawyer', 'hub-coordinator', 'court-clerk', 'operations-officer'];
        $providersQuery = \App\Models\User::whereIn('role', $providerRoles)->where('is_active', true);
        if ($hubId && $hubId !== 'all') {
            $providersQuery->where(fn($q) => $q->where('hub_id', $hubId)->orWhereNull('hub_id'));
        }
        $providers = $providersQuery->orderBy('name')->get(['name', 'role']);

        return view('services.adr-scorecard', compact(
            'total', 'settled', 'active', 'gbv', 'female', 'minority', 'child', 'disability',
            'rate', 'avgDays', 'pipeline', 'outcomes', 'servicesDelivered', 'staff', 'cases', 'activeCases', 'providers'
        ));
    }

    public function storeAdrReferral(Request $request)
    {
        $data = $request->validate([
            'case_id'         => 'required|exists:case_records,id',
            'dispute_type'    => 'required|string|max:120',
            'urgency'         => 'required|in:Low,Medium,High',
            'summary'         => 'nullable|string|max:1000',
            'mediator_name'   => 'nullable|string|max:255',
            'proposed_date'   => 'nullable|date',
            'session_mode'    => 'nullable|string|max:60',
            'is_gbv'          => 'nullable|boolean',
            'is_child'        => 'nullable|boolean',
            'is_minority'     => 'nullable|boolean',
            'is_disability'   => 'nullable|boolean',
            'accommodations'  => 'nullable|string|max:500',
            'mediator_notes'  => 'nullable|string|max:1000',
        ]);

        $case = CaseRecord::findOrFail($data['case_id']);

        // Update case to ADR pathway + safeguarding flags
        $case->update([
            'disposition'   => 'adr',
            'urgency'       => $data['urgency'],
            'assigned_to'   => $data['mediator_name'] ?: $case->assigned_to,
            'is_gbv'        => $request->boolean('is_gbv'),
            'is_child'      => $request->boolean('is_child'),
            'is_minority'   => $request->boolean('is_minority'),
            'is_disability' => $request->boolean('is_disability'),
            'last_update'   => now(),
        ]);

        // Schedule first mediation session if date provided
        if (!empty($data['proposed_date'])) {
            ServiceEncounter::create([
                'case_id'      => $case->id,
                'date'         => $data['proposed_date'],
                'type'         => 'Mediation Session',
                'performed_by' => $data['mediator_name'] ?? 'TBD',
                'note'         => trim(
                    ($data['summary'] ? "Dispute: {$data['dispute_type']}. {$data['summary']}" : "Dispute: {$data['dispute_type']}.") .
                    ($data['session_mode'] ? " Mode: {$data['session_mode']}." : '') .
                    ($data['mediator_notes'] ? " Notes: {$data['mediator_notes']}" : '') .
                    ($data['accommodations'] ? " Accommodations: {$data['accommodations']}" : '')
                ),
                'meta' => [
                    'session_mode'  => $data['session_mode'] ?? null,
                    'dispute_type'  => $data['dispute_type'],
                    'accommodations'=> $data['accommodations'] ?? null,
                ],
            ]);
        }

        return back()->with('success', "Case {$case->case_uid} referred to ADR mediation pathway.");
    }

    public function adrCalendar(Request $request)
    {
        $hubId = $request->input('_active_hub', 'all');

        // All ADR cases in scope
        $adrQ = CaseRecord::query()->where(function ($sq) {
                $sq->where('disposition', 'adr')
                   ->orWhere('assigned_pathway', 'Mediation & ADR')
                   ->orWhere('assigned_pathway', 'like', '%Mediation%')
                   ->orWhere('assigned_pathway', 'ADR / Dispute Resolution Support');
            })
            ->when($hubId && $hubId !== 'all', fn($q) => $q->where('hub_id', $hubId));

        $totalCases = (clone $adrQ)->count();
        $caseIds    = (clone $adrQ)->pluck('id');

        // Base session query: mediation-type sessions with case + hub loaded
        $baseQ = ServiceEncounter::whereIn('case_id', $caseIds)
            ->where(function ($sq) {
                $sq->where('type', 'like', '%Mediation%')
                   ->orWhere('type', 'like', '%ADR%')
                   ->orWhere('type', 'like', '%Settlement%')
                   ->orWhere('type', 'like', '%Agreement%')
                   ->orWhere('type', 'like', '%Session%')
                   ->orWhere('type', 'like', '%Counselling%');
            })
            ->with(['caseRecord.hub']);

        // Today's sessions
        $todaySessions = (clone $baseQ)
            ->whereDate('date', today())
            ->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.time')) ASC")
            ->orderBy('id')
            ->get();

        // Next 7 days (excluding today)
        $upcomingSessions = (clone $baseQ)
            ->whereDate('date', '>', today())
            ->whereDate('date', '<=', today()->addDays(7))
            ->orderBy('date')
            ->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.time')) ASC")
            ->get();

        // Active ADR cases without any future session scheduled
        $activeCaseIds = (clone $adrQ)
            ->whereNotIn('status', ['Closed', 'Settlement', 'Rejected'])
            ->pluck('id');

        $hasFutureSession = ServiceEncounter::whereIn('case_id', $activeCaseIds)
            ->where('type', 'like', '%Mediation%')
            ->whereDate('date', '>=', today())
            ->pluck('case_id')
            ->unique();

        $missingNextHearing = max(0, $activeCaseIds->count() - $hasFutureSession->count());

        // Actual cases missing a next session (for drill-down list)
        $missingCases = CaseRecord::whereIn('id', $activeCaseIds->diff($hasFutureSession))
            ->get(['id', 'case_uid', 'name', 'primary_issue', 'hub_id', 'assigned_to']);

        // For the "Log service" modal on this page
        $activeCases = CaseRecord::query()
            ->when($hubId && $hubId !== 'all', fn($q) => $q->where('hub_id', $hubId))
            ->whereNotIn('status', ['Closed', 'Settlement', 'Rejected'])
            ->orderBy('name')
            ->get(['id', 'case_uid', 'name', 'primary_issue', 'hub_id', 'disposition']);

        $staff = \App\Models\Staff::with('hub')
            ->where('status', 'active')
            ->get()
            ->map(fn($s) => ['name' => $s->name, 'role' => $s->role]);

        $providerRoles = ['lawyer', 'hub-coordinator', 'court-clerk', 'operations-officer'];
        $providersQuery = \App\Models\User::whereIn('role', $providerRoles)->where('is_active', true);
        if ($hubId && $hubId !== 'all') {
            $providersQuery->where(fn($q) => $q->where('hub_id', $hubId)->orWhereNull('hub_id'));
        }
        $providers = $providersQuery->orderBy('name')->get(['name', 'role']);

        return view('services.adr-calendar', compact(
            'totalCases', 'todaySessions', 'upcomingSessions',
            'missingNextHearing', 'missingCases', 'activeCases', 'staff', 'providers'
        ));
    }

    public function litigationScorecard(Request $request)
    {
        $hubId = $request->input('_active_hub', 'all');
        $q = CaseRecord::query()->where(function ($sq) {
            $sq->where('disposition', 'litigation')
               ->orWhere('assigned_pathway', 'Representation in Court')
               ->orWhere('assigned_pathway', 'Court Representation');
        });
        if ($hubId && $hubId !== 'all') $q->where('hub_id', $hubId);

        $total       = (clone $q)->count();
        $activeCount = (clone $q)->whereNotIn('status', ['Closed', 'Settlement', 'Rejected'])->count();
        $favourable  = (clone $q)->whereIn('status', ['Closed', 'Settlement'])->count();
        $favRate     = $total > 0 ? round(($favourable / $total) * 100) : 0;
        $criminal    = (clone $q)->where('primary_issue', 'like', '%Criminal%')->count();
        $juvenile    = (clone $q)->where('is_child', true)->count();
        $civil       = max(0, $total - $criminal - $juvenile);

        // Average days to disposal for resolved cases
        $resolvedCases = (clone $q)->whereIn('status', ['Closed', 'Settlement'])
            ->get(['intake_date', 'last_update', 'created_at']);
        $totalDays = $resolvedCases->sum(fn($c) =>
            $c->intake_date && ($c->last_update ?? $c->created_at)
                ? $c->intake_date->diffInDays($c->last_update ?? $c->created_at)
                : 0
        );
        $avgDays = $resolvedCases->count() > 0 ? round($totalDays / $resolvedCases->count()) : 0;

        // Hearings this quarter
        $quarterStart = now()->startOfQuarter();
        $litCaseIds   = (clone $q)->pluck('id');
        $hearingsThisQuarter = ServiceEncounter::whereIn('case_id', $litCaseIds)
            ->where(function ($sq) {
                $sq->where('type', 'like', '%Court%')
                   ->orWhere('type', 'like', '%Hearing%')
                   ->orWhere('type', 'like', '%Litigation%');
            })
            ->where('date', '>=', $quarterStart)
            ->count();

        // Cases with encounters for kanban
        $cases = (clone $q)
            ->with(['serviceEncounters' => fn($sq) => $sq->orderBy('date'), 'hub'])
            ->latest('intake_date')
            ->get();

        // Bulk-fetch CMS caseStage for all linked cases (1 query, no N+1)
        // keyed by external_case_id → caseStage
        $linkedExternalIds = $cases->whereNotNull('external_case_id')->pluck('external_case_id', 'id');
        $cmsStageMap = collect();
        if ($linkedExternalIds->isNotEmpty()) {
            try {
                $cmsStageMap = DB::connection('las_cms')
                    ->table('programs')
                    ->whereIn('id', $linkedExternalIds->values())
                    ->pluck('caseStage', 'id');
            } catch (\Exception $e) {
                \Log::warning('LAS CMS stage fetch failed: ' . $e->getMessage());
            }
        }

        // Map LAS CMS caseStage values → JusticeHub pipeline stages
        $cmsStageToJH = function (?string $stage): ?string {
            if (!$stage) return null;
            $s = strtolower(trim($stage));

            // Resolved / disposed
            if (in_array($s, ['disposed off', 'dismiss with direction', 'withdrawal of vakalatnama',
                               'compliance', 'disposed', 'challan', 'challan '])) {
                return 'Resolved';
            }
            // Awaiting Judgment
            if (str_contains($s, 'judgement') || str_contains($s, 'judgment') ||
                str_contains($s, 'final arguments') || str_contains($s, 'post trial') ||
                str_contains($s, 'order') || str_contains($s, 'orders')) {
                return 'Awaiting Judgment';
            }
            // In Hearings
            if (str_contains($s, 'hearing') || str_contains($s, 'evidence') ||
                str_contains($s, 'arguments') || str_contains($s, 'affidavit') ||
                str_contains($s, 'ex-parte') || str_contains($s, 'bail') ||
                str_contains($s, 'adjournment') || str_contains($s, 'misc') ||
                str_contains($s, 'written statement') || str_contains($s, 'objection') ||
                str_contains($s, 'framing') || str_contains($s, 'katcha') ||
                str_contains($s, 'proceedings') || str_contains($s, 'plaintiff') ||
                str_contains($s, 'defendant') || str_contains($s, 'investigation') ||
                str_contains($s, '265-k') || str_contains($s, 'stop proceedings')) {
                return 'In Hearings';
            }
            // Filed / pre-hearing stages
            if (str_contains($s, 'institution') || str_contains($s, 'pre trial') ||
                str_contains($s, 'pending') || str_contains($s, 'service') ||
                str_contains($s, 'notice') || str_contains($s, 'challan') ||
                str_contains($s, 'supply of copies') || str_contains($s, 'charge') ||
                str_contains($s, 'issue notice') || str_contains($s, '87') ||
                str_contains($s, '88')) {
                return 'Filed';
            }
            return null; // unknown — fall back to other logic
        };

        // 4-stage pipeline: Filed → In Hearings → Awaiting Judgment → Resolved
        $pipeline = ['Filed' => [], 'In Hearings' => [], 'Awaiting Judgment' => [], 'Resolved' => []];
        foreach ($cases as $c) {
            $encounters  = $c->serviceEncounters;
            $lastType    = strtolower($encounters->last()?->type ?? '');
            $daysInStage = $c->last_update
                ? (int) $c->last_update->diffInDays(now())
                : ($c->intake_date ? (int) $c->intake_date->diffInDays(now()) : 0);

            $c->days_in_stage = $daysInStage;
            $c->hearing_count = $encounters->filter(fn($e) =>
                str_contains(strtolower($e->type), 'court') ||
                str_contains(strtolower($e->type), 'hearing')
            )->count();
            $c->next_hearing = $encounters->first(fn($e) => $e->date->gte(today()));

            // Infer court type from primary_issue + flags
            $issue = strtolower($c->primary_issue ?? '');
            if ($c->is_child || str_contains($issue, 'juvenile')) {
                $c->court_type = 'Juvenile';
            } elseif (str_contains($issue, 'family') || str_contains($issue, 'domestic') ||
                      str_contains($issue, 'divorce') || str_contains($issue, 'custody')) {
                $c->court_type = 'Family';
            } elseif (str_contains($issue, 'consumer')) {
                $c->court_type = 'Consumer';
            } elseif (str_contains($issue, 'criminal') || str_contains($issue, 'assault') ||
                      str_contains($issue, 'theft') || str_contains($issue, 'session')) {
                $c->court_type = 'Sessions';
            } else {
                $c->court_type = 'Civil';
            }

            // Stage: CMS is source of truth — auto-update JH if CMS differs
            $externalId  = $c->external_case_id ?? null;
            $cmsRaw      = $externalId ? ($cmsStageMap[$externalId] ?? null) : null;
            $cmsStage    = $cmsStageToJH($cmsRaw);
            $c->cms_case_stage = $cmsRaw;

            // If CMS maps to a valid stage AND it differs from current litigation_stage → auto-update
            if ($cmsStage && $cmsStage !== ($c->litigation_stage ?? null)) {
                $fromStage = $c->litigation_stage ?? 'Filed';
                DB::table('cases')->where('id', $c->id)->update([
                    'litigation_stage'            => $cmsStage,
                    'litigation_stage_changed_by' => null,
                    'litigation_stage_changed_at' => now(),
                ]);
                DB::table('litigation_stage_logs')->insert([
                    'case_id'    => $c->id,
                    'from_stage' => $fromStage,
                    'to_stage'   => $cmsStage,
                    'changed_by' => 0, // 0 = system
                    'changed_at' => now(),
                ]);
                $c->litigation_stage = $cmsStage; // update in-memory
            }

            $effectiveStage = $c->litigation_stage ?? null;

            if ($effectiveStage && array_key_exists($effectiveStage, $pipeline)) {
                $pipeline[$effectiveStage][] = $c;
            } elseif (in_array($c->status->value, ['Closed', 'Settlement'])) {
                $pipeline['Resolved'][] = $c;
            } elseif (
                str_contains($lastType, 'judgment') || str_contains($lastType, 'verdict') ||
                str_contains($lastType, 'await') || str_contains($lastType, 'pending judgment')
            ) {
                $pipeline['Awaiting Judgment'][] = $c;
            } elseif (
                str_contains($lastType, 'court') || str_contains($lastType, 'hearing') ||
                str_contains($lastType, 'litigation') || $c->hearing_count > 0
            ) {
                $pipeline['In Hearings'][] = $c;
            } else {
                $pipeline['Filed'][] = $c;
            }
        }

        // Resolution outcomes (check meta 'outcome', fall back on status)
        $won = $partial = $lost = $withdrawn = $pending = 0;
        foreach ($pipeline['Resolved'] as $c) {
            $lastEnc = $c->serviceEncounters->last();
            $outcome = strtolower($lastEnc?->meta['outcome'] ?? '');
            if (str_contains($outcome, 'won') || str_contains($outcome, 'favour') ||
                ($outcome === '' && $c->status->value === 'Settlement')) {
                $won++;
            } elseif (str_contains($outcome, 'partial')) {
                $partial++;
            } elseif (str_contains($outcome, 'lost') || str_contains($outcome, 'adverse')) {
                $lost++;
            } elseif (str_contains($outcome, 'withdrawn') || str_contains($outcome, 'withdraw')) {
                $withdrawn++;
            } else {
                $pending++;
            }
        }
        $resolutionOutcomes = compact('won', 'partial', 'lost', 'withdrawn', 'pending');

        // Court type breakdown
        $courtTypes = ['Sessions' => 0, 'Civil' => 0, 'Family' => 0, 'Juvenile' => 0, 'Consumer' => 0];
        foreach ($cases as $c) {
            $ct = $c->court_type ?? 'Civil';
            if (array_key_exists($ct, $courtTypes)) $courtTypes[$ct]++;
        }
        $totalAppearances = ServiceEncounter::whereIn('case_id', $litCaseIds)
            ->where(function ($sq) {
                $sq->where('type', 'like', '%Court%')->orWhere('type', 'like', '%Hearing%');
            })->count();

        // Staff workload (sorted by court caseload)
        $staff = \App\Models\Staff::with(['hub'])->where('status', 'active')->get()->map(function ($s) {
            $allActive   = CaseRecord::where('assigned_to', $s->name)->whereNotIn('status', ['Closed', 'Settlement', 'Rejected']);
            $adrCount    = (clone $allActive)->where('disposition', 'adr')->count();
            $courtCount  = (clone $allActive)->where('disposition', 'litigation')->count();
            $totalActive = (clone $allActive)->count();
            $capacity    = $s->role === 'Lawyer' ? 25 : 35;
            $utilization = $capacity > 0 ? round(($totalActive / $capacity) * 100) : 0;
            $slaBreach   = CaseRecord::where('assigned_to', $s->name)->where('sla_met', false)->count();
            return [
                'name' => $s->name, 'initials' => $s->initials, 'role' => $s->role,
                'hub' => $s->hub?->name ?? $s->hub_id, 'hub_id' => $s->hub_id,
                'active' => $totalActive, 'adr' => $adrCount, 'court' => $courtCount,
                'capacity' => $capacity, 'utilization' => min($utilization, 100),
                'sla_breach' => $slaBreach,
            ];
        })->sortByDesc('court')->values();

        $staffCount  = $staff->count();
        $uniqueHubs  = $staff->pluck('hub_id')->unique()->filter()->count();

        // Recent court activity (latest 10 encounters)
        $recentActivity = ServiceEncounter::whereIn('case_id', $litCaseIds)
            ->where(function ($sq) {
                $sq->where('type', 'like', '%Court%')
                   ->orWhere('type', 'like', '%Hearing%')
                   ->orWhere('type', 'like', '%Litigation%');
            })
            ->with(['caseRecord.hub'])
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        return view('services.litigation-scorecard', compact(
            'total', 'activeCount', 'favourable', 'favRate', 'avgDays', 'hearingsThisQuarter',
            'criminal', 'juvenile', 'civil',
            'pipeline', 'resolutionOutcomes', 'courtTypes', 'totalAppearances',
            'staff', 'staffCount', 'uniqueHubs', 'recentActivity'
        ));
    }

    public function litigationCalendar(Request $request)
    {
        $hubId = $request->input('_active_hub', 'all');

        $litQ = CaseRecord::query()->where(function ($sq) {
                $sq->where('disposition', 'litigation')
                   ->orWhere('assigned_pathway', 'Representation in Court')
                   ->orWhere('assigned_pathway', 'Court Representation');
            })
            ->when($hubId && $hubId !== 'all', fn($q) => $q->where('hub_id', $hubId));

        $totalCases = (clone $litQ)->count();
        $caseIds    = (clone $litQ)->pluck('id');

        // Base query: court-type encounters with case + hub loaded
        $baseQ = ServiceEncounter::whereIn('case_id', $caseIds)
            ->where(function ($sq) {
                $sq->where('type', 'like', '%Court%')
                   ->orWhere('type', 'like', '%Hearing%')
                   ->orWhere('type', 'like', '%Litigation%')
                   ->orWhere('type', 'like', '%Judgment%')
                   ->orWhere('type', 'like', '%Verdict%')
                   ->orWhere('type', 'like', '%Representation%');
            })
            ->with(['caseRecord.hub']);

        // Today's hearings
        $todayHearings = (clone $baseQ)
            ->whereDate('date', today())
            ->orderByRaw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.time')), '23:59') ASC")
            ->orderBy('id')
            ->get();

        // Next 7 days (excluding today)
        $upcomingHearings = (clone $baseQ)
            ->whereDate('date', '>', today())
            ->whereDate('date', '<=', today()->addDays(7))
            ->orderBy('date')
            ->orderByRaw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.time')), '23:59') ASC")
            ->get();

        // Active cases without a future hearing
        $activeCaseIds = (clone $litQ)
            ->whereNotIn('status', ['Closed', 'Settlement', 'Rejected'])
            ->pluck('id');

        $hasFutureHearing = ServiceEncounter::whereIn('case_id', $activeCaseIds)
            ->where(function ($sq) {
                $sq->where('type', 'like', '%Court%')
                   ->orWhere('type', 'like', '%Hearing%');
            })
            ->whereDate('date', '>=', today())
            ->pluck('case_id')
            ->unique();

        $missingNextHearing = max(0, $activeCaseIds->count() - $hasFutureHearing->count());

        // Active cases for log modal
        $activeCases = CaseRecord::query()
            ->when($hubId && $hubId !== 'all', fn($q) => $q->where('hub_id', $hubId))
            ->whereNotIn('status', ['Closed', 'Settlement', 'Rejected'])
            ->orderBy('name')
            ->get(['id', 'case_uid', 'name', 'primary_issue', 'hub_id', 'disposition']);

        $staff = \App\Models\Staff::with('hub')
            ->where('status', 'active')
            ->get()
            ->map(fn($s) => ['name' => $s->name, 'role' => $s->role]);

        $providerRoles = ['lawyer', 'hub-coordinator', 'court-clerk', 'operations-officer'];
        $providersQuery = \App\Models\User::whereIn('role', $providerRoles)->where('is_active', true);
        if ($hubId && $hubId !== 'all') {
            $providersQuery->where(fn($q) => $q->where('hub_id', $hubId)->orWhereNull('hub_id'));
        }
        $providers = $providersQuery->orderBy('name')->get(['name', 'role']);

        return view('services.litigation-calendar', compact(
            'totalCases', 'todayHearings', 'upcomingHearings',
            'missingNextHearing', 'activeCases', 'staff', 'providers'
        ));
    }

    public function updateAdrStage(Request $request, CaseRecord $case)
    {
        $stages = ['ADR Intake', 'In Mediation', 'Settlement Draft', 'Resolved', 'Escalated'];
        $request->validate(['stage' => 'required|in:' . implode(',', $stages)]);

        $fromStage = $case->adr_stage ?? 'ADR Intake';
        $toStage   = $request->stage;

        if ($fromStage === $toStage) {
            return response()->json(['success' => false, 'message' => 'No change']);
        }

        DB::table('cases')->where('id', $case->id)->update([
            'adr_stage'            => $toStage,
            'adr_stage_changed_by' => $request->user()->id,
            'adr_stage_changed_at' => now(),
        ]);

        DB::table('adr_stage_logs')->insert([
            'case_id'    => $case->id,
            'from_stage' => $fromStage,
            'to_stage'   => $toStage,
            'changed_by' => $request->user()->id,
            'changed_at' => now(),
        ]);

        return response()->json([
            'success'    => true,
            'from'       => $fromStage,
            'to'         => $toStage,
            'changed_by' => $request->user()->name,
            'changed_at' => now()->format('d M Y, H:i'),
        ]);
    }

    public function updateLitigationStage(Request $request, CaseRecord $case)
    {
        $stages = ['Filed', 'In Hearings', 'Awaiting Judgment', 'Resolved'];
        $request->validate(['stage' => 'required|in:' . implode(',', $stages)]);

        $fromStage = $case->litigation_stage ?? 'Filed';
        $toStage   = $request->stage;

        if ($fromStage === $toStage) {
            return response()->json(['success' => false, 'message' => 'No change']);
        }

        // Update case
        DB::table('cases')->where('id', $case->id)->update([
            'litigation_stage'            => $toStage,
            'litigation_stage_changed_by' => $request->user()->id,
            'litigation_stage_changed_at' => now(),
        ]);

        // Log the change
        DB::table('litigation_stage_logs')->insert([
            'case_id'    => $case->id,
            'from_stage' => $fromStage,
            'to_stage'   => $toStage,
            'changed_by' => $request->user()->id,
            'changed_at' => now(),
        ]);

        return response()->json([
            'success'    => true,
            'from'       => $fromStage,
            'to'         => $toStage,
            'changed_by' => $request->user()->name,
            'changed_at' => now()->format('d M Y, H:i'),
        ]);
    }
}
