<?php

namespace Database\Seeders;

use App\Models\Indicator;
use Illuminate\Database\Seeder;

class IndicatorSeeder extends Seeder
{
    public function run(): void
    {
        $indicators = [
            // Goal (4) — P1 Annual
            ['code' => 'G1', 'level' => 'Goal', 'name' => 'Formal government recognition or adoption of the Justice Hub model', 'priority' => 'P1', 'cadence' => 'Annual', 'target' => 3, 'actual' => 2, 'unit' => 'records', 'type' => 'count', 'is_inverse' => false],
            ['code' => 'G2', 'level' => 'Goal', 'name' => 'Institutional integration of Justice Hubs', 'priority' => 'P1', 'cadence' => 'Annual', 'target' => 8, 'actual' => 6, 'unit' => 'mechanisms', 'type' => 'count', 'is_inverse' => false],
            ['code' => 'G3', 'level' => 'Goal', 'name' => 'Replication or scale-up of the Justice Hub model', 'priority' => 'P1', 'cadence' => 'Annual', 'target' => 2, 'actual' => 1, 'unit' => 'scale-ups', 'type' => 'count', 'is_inverse' => false],
            ['code' => 'G4', 'level' => 'Goal', 'name' => 'Use of Justice Hub evidence in policy or funding decisions', 'priority' => 'P1', 'cadence' => 'Annual', 'target' => 6, 'actual' => 5, 'unit' => 'instances', 'type' => 'count', 'is_inverse' => false],
            // Outcome 1 — Access to Justice (5)
            ['code' => 'O1.1', 'level' => 'Outcome 1', 'name' => '% of cases assessed and assigned a pathway within 48 hours', 'priority' => 'P0', 'cadence' => 'Monthly', 'target' => 90, 'actual' => 87, 'unit' => '%', 'type' => 'pct', 'is_inverse' => false],
            ['code' => 'O1.2', 'level' => 'Outcome 1', 'name' => '% of clients completing advised next steps', 'priority' => 'P0', 'cadence' => 'Quarterly', 'target' => 65, 'actual' => 58, 'unit' => '%', 'type' => 'pct', 'is_inverse' => false],
            ['code' => 'O1.3', 'level' => 'Outcome 1', 'name' => 'Total individuals accessing Justice Hub services', 'priority' => 'P0', 'cadence' => 'Quarterly', 'target' => 18000, 'actual' => 16420, 'unit' => 'people', 'type' => 'count', 'is_inverse' => false],
            ['code' => 'O1.4', 'level' => 'Outcome 1', 'name' => 'Overall client satisfaction score', 'priority' => 'P0', 'cadence' => 'Quarterly', 'target' => 85, 'actual' => 89, 'unit' => '%', 'type' => 'pct', 'is_inverse' => false],
            ['code' => 'O1.5', 'level' => 'Outcome 1', 'name' => '% of cases receiving mediation where appropriate pathway', 'priority' => 'P0', 'cadence' => 'Quarterly', 'target' => 75, 'actual' => 71, 'unit' => '%', 'type' => 'pct', 'is_inverse' => false],
            // Outcome 2 — Legal Awareness (3)
            ['code' => 'O2.1', 'level' => 'Outcome 2', 'name' => '% of clients reporting increased understanding of legal rights', 'priority' => 'P0', 'cadence' => 'Quarterly', 'target' => 80, 'actual' => 83, 'unit' => '%', 'type' => 'pct', 'is_inverse' => false],
            ['code' => 'O2.2', 'level' => 'Outcome 2', 'name' => '# of legal literacy or awareness sessions conducted', 'priority' => 'P0', 'cadence' => 'Quarterly', 'target' => 144, 'actual' => 127, 'unit' => 'sessions', 'type' => 'count', 'is_inverse' => false],
            ['code' => 'O2.3', 'level' => 'Outcome 2', 'name' => '# of participants reached', 'priority' => 'P0', 'cadence' => 'Quarterly', 'target' => 7200, 'actual' => 6840, 'unit' => 'people', 'type' => 'count', 'is_inverse' => false],
            // Outcome 3 — Institutional Coordination (3)
            ['code' => 'O3.1', 'level' => 'Outcome 3', 'name' => '% of underserved clients successfully connected to state justice', 'priority' => 'P1', 'cadence' => 'Quarterly', 'target' => 70, 'actual' => 62, 'unit' => '%', 'type' => 'pct', 'is_inverse' => false],
            ['code' => 'O3.2', 'level' => 'Outcome 3', 'name' => '% of referrals resulting in documented outcomes', 'priority' => 'P0', 'cadence' => 'Quarterly', 'target' => 60, 'actual' => 54, 'unit' => '%', 'type' => 'pct', 'is_inverse' => false],
            ['code' => 'O3.3', 'level' => 'Outcome 3', 'name' => 'Use of Justice Hub analytical products in policy processes', 'priority' => 'P1', 'cadence' => 'Annual', 'target' => 12, 'actual' => 11, 'unit' => 'instances', 'type' => 'count', 'is_inverse' => false],
            // Output 1 — Operations (5)
            ['code' => 'OP1.1', 'level' => 'Output 1', 'name' => '# of Justice Hubs operational', 'priority' => 'P0', 'cadence' => 'Quarterly', 'target' => 6, 'actual' => 6, 'unit' => 'hubs', 'type' => 'count', 'is_inverse' => false],
            ['code' => 'OP1.2', 'level' => 'Output 1', 'name' => '% of cases with complete CMS data', 'priority' => 'P0', 'cadence' => 'Monthly', 'target' => 95, 'actual' => 92, 'unit' => '%', 'type' => 'pct', 'is_inverse' => false],
            ['code' => 'OP1.3', 'level' => 'Output 1', 'name' => '% of staff trained on SOPs and safeguarding', 'priority' => 'P0', 'cadence' => 'Annual', 'target' => 100, 'actual' => 94, 'unit' => '%', 'type' => 'pct', 'is_inverse' => false],
            ['code' => 'OP1.4', 'level' => 'Output 1', 'name' => 'Average service turnaround time (SLA)', 'priority' => 'P1', 'cadence' => 'Quarterly', 'target' => 5, 'actual' => 5.8, 'unit' => 'days', 'type' => 'count', 'is_inverse' => true],
            ['code' => 'OP1.5', 'level' => 'Output 1', 'name' => 'Average cost per individual served (VfM)', 'priority' => 'P1', 'cadence' => 'Annual', 'target' => 1400, 'actual' => 1285, 'unit' => 'PKR', 'type' => 'count', 'is_inverse' => true],
            // Output 2 — Service Delivery (4)
            ['code' => 'OP2.1', 'level' => 'Output 2', 'name' => '# of individuals receiving legal advice or representation', 'priority' => 'P0', 'cadence' => 'Monthly', 'target' => 2400, 'actual' => 2186, 'unit' => 'people', 'type' => 'count', 'is_inverse' => false],
            ['code' => 'OP2.2', 'level' => 'Output 2', 'name' => '# of cases filed or represented before institutions', 'priority' => 'P0', 'cadence' => 'Quarterly', 'target' => 600, 'actual' => 548, 'unit' => 'cases', 'type' => 'count', 'is_inverse' => false],
            ['code' => 'OP2.3', 'level' => 'Output 2', 'name' => '# of mediation sessions conducted', 'priority' => 'P0', 'cadence' => 'Quarterly', 'target' => 420, 'actual' => 389, 'unit' => 'sessions', 'type' => 'count', 'is_inverse' => false],
            ['code' => 'OP2.4', 'level' => 'Output 2', 'name' => '# of documentation or entitlement applications supported', 'priority' => 'P0', 'cadence' => 'Quarterly', 'target' => 900, 'actual' => 812, 'unit' => 'applications', 'type' => 'count', 'is_inverse' => false],
            // Output 3 — Outreach (1)
            ['code' => 'OP3.1', 'level' => 'Output 3', 'name' => '# of paralegal-led community outreach activities', 'priority' => 'P0', 'cadence' => 'Monthly', 'target' => 240, 'actual' => 231, 'unit' => 'activities', 'type' => 'count', 'is_inverse' => false],
            // Output 4 — Referral, Evidence, Accountability (4)
            ['code' => 'OP4.1', 'level' => 'Output 4', 'name' => '# of referrals made', 'priority' => 'P0', 'cadence' => 'Quarterly', 'target' => 1200, 'actual' => 1089, 'unit' => 'referrals', 'type' => 'count', 'is_inverse' => false],
            ['code' => 'OP4.2', 'level' => 'Output 4', 'name' => '% of referrals accepted and actioned', 'priority' => 'P0', 'cadence' => 'Quarterly', 'target' => 75, 'actual' => 68, 'unit' => '%', 'type' => 'pct', 'is_inverse' => false],
            ['code' => 'OP4.3', 'level' => 'Output 4', 'name' => '% of complaints resolved within agreed timelines', 'priority' => 'P0', 'cadence' => 'Quarterly', 'target' => 90, 'actual' => 91, 'unit' => '%', 'type' => 'pct', 'is_inverse' => false],
            ['code' => 'OP4.4', 'level' => 'Output 4', 'name' => '# of learning or analytical products produced', 'priority' => 'P1', 'cadence' => 'Annual', 'target' => 8, 'actual' => 6, 'unit' => 'products', 'type' => 'count', 'is_inverse' => false],
        ];

        foreach ($indicators as $data) {
            Indicator::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
