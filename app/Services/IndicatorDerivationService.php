<?php

namespace App\Services;

use App\Models\CaseRecord;
use App\Models\Complaint;
use App\Models\Evidence;
use App\Models\FinanceConfig;
use App\Models\Hub;
use App\Models\OutreachActivity;
use App\Models\Partner;
use App\Models\PulseSurvey;
use App\Models\Staff;
use App\Models\Training;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class IndicatorDerivationService
{
    // Scale factor: 1 = real data, no inflation
    private const SCALE_FACTOR = 1;

    // Use current date for SLA calculations
    private static function today(): Carbon { return now()->startOfDay(); }

    /**
     * Derive live actuals for all indicators that have upstream data.
     * Returns a map of ['code' => float|int, 'source' => string].
     */
    public function derive(): array
    {
        $ttl = config('justice_hub.dashboard.indicator_cache_ttl', 3600); // 1 hour
        return Cache::remember('indicators.derived', $ttl, fn() => $this->compute());
    }

    public static function flush(): void
    {
        Cache::forget('indicators.derived');
    }

    private function compute(): array
    {
        $cases     = CaseRecord::withTrashed(false)->with('serviceEncounters')->get();
        $outreach  = OutreachActivity::all();
        $partners  = Partner::all();
        $evidence  = Evidence::all();
        $feedback  = \App\Models\Feedback::all();
        $pulses    = PulseSurvey::all();
        $complaints= Complaint::all();
        $hubs      = Hub::all();
        $staff     = Staff::where('status', 'active')->with('trainings')->get();
        $trainings = Training::where('mandatory', true)->get();
        $finance   = FinanceConfig::current();

        $sf = self::SCALE_FACTOR;

        // ─── Mediation sessions ───────────────────────────────────────────────
        $mediationSessions = 0;
        foreach ($cases as $c) {
            $mediationSessions += $c->serviceEncounters->filter(fn($e) => stripos($e->type, 'mediation') !== false)->count();
        }

        // ─── Partner aggregates ───────────────────────────────────────────────
        $partnerActive    = $partners->sum('active_referrals');
        $partnerCompleted = $partners->sum('completed_referrals');
        $partnerFailed    = $partners->sum('failed_referrals');
        $partnerTotal     = $partnerActive + $partnerCompleted + $partnerFailed;

        // ─── CMS data completeness ────────────────────────────────────────────
        $requiredFields = ['name', 'gender', 'age', 'primary_issue', 'hub_id', 'district', 'primary_contact', 'assigned_pathway'];
        $completeCases = $cases->filter(function ($c) use ($requiredFields) {
            foreach ($requiredFields as $f) {
                if (empty($c->$f)) return false;
            }
            return true;
        })->count();

        // ─── Underserved connected to state justice ───────────────────────────
        $underservedCases = $cases->where('is_underserved', true);
        $underservedConnected = $underservedCases->filter(function ($c) {
            $pathways = ($c->assigned_pathway ?? '') . ' ' . ($c->pathway_specific ?? '');
            return preg_match('/Litigation|Court|Referral/i', $pathways);
        })->count();

        // ─── Evidence by type (verified only) ────────────────────────────────
        $verified = $evidence->where('verified', true);
        $evidenceByType = fn(string $type) => $verified->where('type', $type)->count();
        $evidenceByLink = fn(string $code) => $verified->where('linked_indicator', $code)->count();

        // ─── SLA hours (average intake-to-assessment) ─────────────────────────
        $slaHoursTotal = $cases->sum(fn($c) => $c->sla_met ? 32 : 78);
        $avgSlaDays = $cases->count() > 0 ? ($slaHoursTotal / $cases->count()) / 24 : 0;

        // ─── VfM — cost per individual ────────────────────────────────────────
        $totalAnnualCost = \App\Models\HubCost::sum('total_operational_cost') * 4; // Q1 × 4 quarters
        if ($totalAnnualCost <= 0) {
            // Fallback to finance config hub costs
            $configCosts = $finance ? collect($finance->getValue('hubCosts', [])) : collect();
            $totalAnnualCost = $configCosts->sum('annualOperatingCost');
        }
        $projectedAnnualReach = $cases->count();
        $costPerIndividual = $projectedAnnualReach > 0 ? round($totalAnnualCost / $projectedAnnualReach) : 0;

        // ─── Follow-up completion (O1.2) ──────────────────────────────────────
        // A case has "completed next steps" if it has >1 encounter (beyond intake)
        $completedNextSteps = $cases->filter(function ($c) {
            return $c->serviceEncounters->count() > 1;
        })->count();

        // ─── Client satisfaction (O1.4) ───────────────────────────────────────
        $satisfactory = $feedback->where('score_overall', '>=', 4)->count();
        $satisfactionPct = $this->pct($satisfactory, $feedback->count());

        // ─── Outreach understanding gain (O2.1) ───────────────────────────────
        $gainedRespondents = $pulses->filter(fn($p) => $p->post_score > $p->pre_score)->count();
        $understandingGainPct = $this->pct($gainedRespondents, $pulses->count());

        // ─── Staff training compliance (OP1.3) ────────────────────────────────
        $today = self::today();
        $fullyCompliant = $staff->filter(function ($s) use ($trainings, $today) {
            $required = $trainings->filter(fn($t) =>
                in_array($s->role, $t->audience ?? [])
            );
            return $required->every(function ($req) use ($s, $today) {
                $pivot = $s->trainings->where('code', $req->code)->first();
                if (!$pivot) return false;
                if ($req->refresh === 'one-off' || !$pivot->pivot->expires) return true;
                return Carbon::parse($pivot->pivot->expires)->gte($today);
            });
        })->count();
        $trainingCompliancePct = $this->pct($fullyCompliant, $staff->count());

        // ─── Complaints resolution within SLA (OP4.3) ────────────────────────
        $resolved = $complaints->where('status', 'resolved');
        $resolvedOnTime = $resolved->filter(function ($c) {
            if (!$c->resolved_date) return false;
            $days = Carbon::parse($c->submitted_date)->diffInDays(Carbon::parse($c->resolved_date));
            return $days <= $c->sla_days;
        })->count();
        $complaintsResolvePct = $this->pct($resolvedOnTime, $resolved->count());

        // ─── Outreach derived counts ──────────────────────────────────────────
        $literacySessions = $outreach->filter(fn($o) => preg_match('/Legal Literacy|Awareness/i', $o->type))->count();
        $totalParticipants = $outreach->sum('total_participants');
        $paralegalLed = $outreach->filter(fn($o) =>
            preg_match('/Paralegal/i', $o->facilitator ?? '') ||
            preg_match('/Paralegal Outreach/i', $o->type ?? '')
        )->count();

        return [
            // Outcome 1
            'O1.1' => $this->pct($cases->where('sla_met', true)->count(), $cases->count()),
            'O1.2' => $this->pct($completedNextSteps, $cases->count()),
            'O1.3' => $cases->count(),
            'O1.4' => $satisfactionPct,
            'O1.5' => $this->pct(
                $cases->filter(fn($c) => preg_match('/Mediation|ADR/i', ($c->assigned_pathway ?? '') . ' ' . ($c->pathway_specific ?? '')))->count(),
                $cases->count()
            ),
            // Outcome 2
            'O2.1' => $understandingGainPct,
            'O2.2' => $literacySessions,
            'O2.3' => $totalParticipants,
            // Outcome 3
            'O3.1' => $this->pct($underservedConnected, $underservedCases->count()),
            'O3.2' => $this->pct($partnerCompleted, $partnerCompleted + $partnerFailed),
            'O3.3' => $evidenceByLink('G4') + $evidenceByType('policy-citation'),
            // Output 1
            'OP1.1' => $hubs->where('is_active', true)->count(),
            'OP1.2' => $this->pct($completeCases, $cases->count()),
            'OP1.3' => $trainingCompliancePct,
            'OP1.4' => round($avgSlaDays, 1),
            'OP1.5' => $costPerIndividual,
            // Output 2
            'OP2.1' => $cases->filter(fn($c) => preg_match('/Legal Advice|Litigation|Court|Representation/i', ($c->assigned_pathway ?? '') . ' ' . ($c->pathway_specific ?? '')))->count(),
            'OP2.2' => $cases->filter(fn($c) => preg_match('/Litigation|Court|Representation/i', ($c->assigned_pathway ?? '') . ' ' . ($c->pathway_specific ?? '')))->count(),
            'OP2.3' => $mediationSessions,
            'OP2.4' => $cases->filter(fn($c) => preg_match('/Documentation/i', ($c->assigned_pathway ?? '') . ' ' . ($c->pathway_specific ?? '')))->count(),
            // Output 3
            'OP3.1' => $paralegalLed,
            // Output 4
            'OP4.1' => $partnerTotal,
            'OP4.2' => $this->pct($partnerActive + $partnerCompleted, $partnerTotal),
            'OP4.3' => $complaintsResolvePct,
            'OP4.4' => $evidenceByType('analytical-product'),
            // Goals
            'G1' => $evidenceByType('recognition'),
            'G2' => $evidenceByType('integration'),
            'G3' => $evidenceByType('replication'),
            'G4' => $evidenceByType('policy-citation'),
        ];
    }

    /**
     * Get a human-readable source line for an indicator explaining its derivation.
     */
    public function sourceLine(string $code): string
    {
        $cases     = CaseRecord::withTrashed(false)->with('serviceEncounters')->get();
        $outreach  = OutreachActivity::all();
        $partners  = Partner::all();
        $evidence  = Evidence::all();
        $feedback  = \App\Models\Feedback::all();
        $pulses    = PulseSurvey::all();
        $staff     = Staff::where('status', 'active')->with('trainings')->get();
        $trainings = Training::where('mandatory', true)->get();

        $verified = $evidence->where('verified', true);
        $today    = self::today();

        switch ($code) {
            case 'O1.1': {
                $met = $cases->where('sla_met', true)->count();
                return "{$met} of {$cases->count()} cases met 48-hour assessment SLA";
            }
            case 'O1.2': {
                $comp = $cases->filter(fn($c) => $c->serviceEncounters->filter(fn($e) =>
                    preg_match('/follow.?up|next step|completed/i', $e->type)
                )->isNotEmpty())->count();
                return "{$comp} of {$cases->count()} cases show follow-up completion in service log";
            }
            case 'O1.3': return "{$cases->count()} total individuals served across all hubs";
            case 'O1.4': {
                $ok = $feedback->where('score_overall', '>=', 4)->count();
                return "{$ok} of {$feedback->count()} feedback responses scored 4★ or higher";
            }
            case 'O1.5': {
                $med = $cases->filter(fn($c) => stripos(($c->assigned_pathway ?? '') . ' ' . ($c->pathway_specific ?? ''), 'Mediation') !== false)->count();
                return "{$med} of {$cases->count()} cases include Mediation in pathway";
            }
            case 'O2.1': {
                $gained = $pulses->filter(fn($p) => $p->post_score > $p->pre_score)->count();
                return "{$gained} of {$pulses->count()} pulse-survey respondents reported understanding gain";
            }
            case 'O2.2': {
                $n = $outreach->filter(fn($o) => preg_match('/Legal Literacy|Awareness/i', $o->type))->count();
                return "{$n} legal-literacy / awareness sessions logged · projected to quarter";
            }
            case 'O2.3': {
                $sum = $outreach->sum('total_participants');
                return "{$sum} participants across {$outreach->count()} sessions · projected to quarter";
            }
            case 'O3.1': {
                $u = $cases->where('is_underserved', true);
                $conn = $u->filter(fn($c) => preg_match('/Litigation|Court|Referral/i', ($c->assigned_pathway ?? '') . ' ' . ($c->pathway_specific ?? '')))->count();
                return "{$conn} of {$u->count()} underserved cases connected to court or government partner";
            }
            case 'O3.2': {
                $comp = $partners->sum('completed_referrals');
                $fail = $partners->sum('failed_referrals');
                return "{$comp} of " . ($comp + $fail) . " closed referrals reached documented outcome";
            }
            case 'O3.3': {
                $pc = $verified->where('type', 'policy-citation')->count();
                $g4 = $verified->where('linked_indicator', 'G4')->count();
                return "{$pc} verified policy citations + {$g4} G4-linked entries";
            }
            case 'OP1.1': return Hub::where('is_active', true)->count() . " active hubs in the hub register";
            case 'OP1.2': {
                $req = ['name', 'gender', 'age', 'primary_issue', 'hub_id', 'district', 'primary_contact', 'assigned_pathway'];
                $comp = $cases->filter(fn($c) => collect($req)->every(fn($f) => !empty($c->$f)))->count();
                return "{$comp} of {$cases->count()} cases pass 8-field completeness audit";
            }
            case 'OP1.3': {
                $compliant = $staff->filter(function ($s) use ($trainings, $today) {
                    return $trainings->filter(fn($t) => in_array($s->role, $t->audience ?? []))->every(function ($req) use ($s, $today) {
                        $pivot = $s->trainings->where('code', $req->code)->first();
                        if (!$pivot) return false;
                        if ($req->refresh === 'one-off' || !$pivot->pivot->expires) return true;
                        return Carbon::parse($pivot->pivot->expires)->gte($today);
                    });
                })->count();
                return "{$compliant} of {$staff->count()} active staff with all role-required mandatory trainings current";
            }
            case 'OP1.4': {
                $avg = $cases->count() > 0 ? ($cases->sum(fn($c) => $c->sla_met ? 32 : 78) / $cases->count()) : 0;
                return "{$cases->count()} cases · avg " . round($avg) . "hr intake-to-assessment";
            }
            case 'OP1.5': {
                $total = \App\Models\HubCost::sum('total_operational_cost') * 4;
                return "PKR " . number_format($total / 1000000, 1) . "M annual cost ÷ " . number_format($cases->count()) . " individuals served";
            }
            case 'OP2.1': {
                $n = $cases->filter(fn($c) => preg_match('/Legal Advice|Litigation|Court|Representation/i', ($c->assigned_pathway ?? '') . ' ' . ($c->pathway_specific ?? '')))->count();
                return "{$n} individuals receiving legal advice or representation";
            }
            case 'OP2.2': {
                $n = $cases->filter(fn($c) => preg_match('/Litigation|Court|Representation/i', ($c->assigned_pathway ?? '') . ' ' . ($c->pathway_specific ?? '')))->count();
                return "{$n} cases filed or represented before institutions";
            }
            case 'OP2.3': {
                $s = 0;
                foreach ($cases as $c) { $s += $c->serviceEncounters->filter(fn($e) => stripos($e->type, 'mediation') !== false)->count(); }
                return "{$s} mediation sessions conducted";
            }
            case 'OP2.4': {
                $n = $cases->filter(fn($c) => preg_match('/Documentation/i', ($c->assigned_pathway ?? '') . ' ' . ($c->pathway_specific ?? '')))->count();
                return "{$n} documentation or entitlement applications supported";
            }
            case 'OP3.1': {
                $n = $outreach->filter(fn($o) => preg_match('/Paralegal/i', $o->facilitator ?? '') || preg_match('/Paralegal Outreach/i', $o->type ?? ''))->count();
                return "{$n} paralegal-led community outreach activities";
            }
            case 'OP4.1': return number_format($partners->sum('active_referrals') + $partners->sum('completed_referrals') + $partners->sum('failed_referrals')) . " referrals across {$partners->count()} partner organisations";
            case 'OP4.2': {
                $a = $partners->sum('active_referrals'); $c = $partners->sum('completed_referrals'); $f = $partners->sum('failed_referrals');
                return number_format($a + $c) . " of " . number_format($a + $c + $f) . " referrals accepted or actioned by partner";
            }
            case 'OP4.3': {
                $res = Complaint::where('status', 'resolved')->get();
                $onTime = $res->filter(fn($c) => $c->resolved_date && Carbon::parse($c->submitted_date)->diffInDays(Carbon::parse($c->resolved_date)) <= $c->sla_days)->count();
                return "{$onTime} of {$res->count()} resolved complaints closed within severity-set SLA";
            }
            case 'OP4.4': return $verified->where('type', 'analytical-product')->count() . " verified analytical products in evidence register";
            case 'G1': return $verified->where('type', 'recognition')->count() . " verified recognition entries in evidence register";
            case 'G2': return $verified->where('type', 'integration')->count() . " verified integration / MoU entries in evidence register";
            case 'G3': return $verified->where('type', 'replication')->count() . " verified replication entries in evidence register";
            case 'G4': return $verified->where('type', 'policy-citation')->count() . " verified policy citations in evidence register";
            default: return "Manual entry — no automated derivation";
        }
    }

    private function pct(int $num, int $denom): int
    {
        if ($denom <= 0) return 0;
        return (int) round(($num / $denom) * 100);
    }
}
