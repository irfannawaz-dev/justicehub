<?php

namespace Database\Seeders;

use App\Models\Reflection;
use Illuminate\Database\Seeder;

class ReflectionSeeder extends Seeder
{
    public function run(): void
    {
        $reflections = [
            [
                'date'        => '2026-03-14',
                'hub_id'      => null,
                'staff'       => '24 staff · 6 hub coordinators · 2 partners (NADRA, BISP)',
                'title'       => 'CNIC referrals are taking 60+ days. Why?',
                'description' => 'Provincial review session held in Karachi (Q1 2026). NADRA referrals were being routed through general public-counter queues, not the formal MOU pathway. Paralegals were unaware the dedicated channel existed.',
                'key_learning'=> 'The MOU-dedicated channel exists but is not being used due to lack of awareness among front-line staff.',
                'meta'        => [
                    'quarter'    => 'Q1 2026',
                    'location'   => 'Karachi (provincial review)',
                    'insight'    => 'NADRA referrals were being routed through general public-counter queues, not the formal MOU pathway. Paralegals were unaware the dedicated channel existed.',
                    'decision'   => 'Assign one paralegal per hub as dedicated NADRA liaison. Re-circulate the MOU SOP. Track CNIC referral close-time as a standing agenda item.',
                    'status'     => 'in-progress',
                    'follow_up'  => 'Review at Q2 2026 reflection',
                ],
            ],
            [
                'date'        => '2025-12-08',
                'hub_id'      => 'JH-HYD-01',
                'staff'       => '14 staff · hub coordinator · 2 GBV specialists',
                'title'       => 'GBV intake — are we catching cases early enough?',
                'description' => 'Several GBV cases first surfaced in third or fourth visits. Initial intake screening was missing indirect indicators — economic coercion, isolation, prior medical visits.',
                'key_learning'=> 'GBV identification at first contact rose from 64% to 81% after revising the safeguarding screening tool with five indirect-indicator questions.',
                'meta'        => [
                    'quarter'    => 'Q4 2025',
                    'location'   => 'Hyderabad',
                    'insight'    => 'Several GBV cases first surfaced in third or fourth visits. Initial intake screening was missing indirect indicators — economic coercion, isolation, prior medical visits.',
                    'decision'   => 'Revise the safeguarding screening tool with five indirect-indicator questions. Mandatory training for all intake officers. Add GBV-specialist consult flag to the intake form.',
                    'status'     => 'completed',
                    'outcome'    => 'New screening tool live since Jan 2026. GBV cases identified at first contact rose from 64% to 81%.',
                ],
            ],
            [
                'date'        => '2025-09-22',
                'hub_id'      => 'JH-DAD-01',
                'staff'       => '11 staff · community elders from 3 villages',
                'title'       => 'Reaching minority Hindu communities in interior Sindh',
                'description' => 'Bagri and Kolhi communities reported the Hub felt institutionally distant — staff didn\'t look like them, didn\'t know community-specific legal issues (caste-based exclusion, bonded labour).',
                'key_learning'=> 'Minority client share rose from 8% to 12% over six months after recruiting community paralegals and adding caste-discrimination modules.',
                'meta'        => [
                    'quarter'    => 'Q3 2025',
                    'location'   => 'Larkana',
                    'insight'    => 'Bagri and Kolhi communities reported the Hub felt institutionally distant — staff didn\'t look like them, didn\'t know community-specific legal issues (caste-based exclusion, bonded labour).',
                    'decision'   => 'Recruit 2 paralegals from these communities directly. Add caste-discrimination and bonded-labour modules to paralegal training. Hold monthly clinics in community spaces, not at the hub.',
                    'status'     => 'completed',
                    'outcome'    => 'Minority client share rose from 8% to 12% over six months. Three bonded-labour cases identified and resolved.',
                ],
            ],
            [
                'date'        => '2025-06-19',
                'hub_id'      => null,
                'staff'       => '22 staff · 4 partner lawyers',
                'title'       => 'Court referral fatigue — too many cases ending up in the formal system',
                'description' => 'Lawyers were defaulting to court referrals when ADR seemed difficult. Capacity for mediation training across hubs was uneven.',
                'key_learning'=> 'ADR resolution rate rose from 68% to 81% across the year after strengthening ADR-first culture with case-review committees.',
                'meta'        => [
                    'quarter'    => 'Q2 2025',
                    'location'   => 'Sukkur',
                    'insight'    => 'Lawyers were defaulting to court referrals when ADR seemed difficult. Capacity for mediation training across hubs was uneven.',
                    'decision'   => 'Strengthen ADR-first culture with case-review committees. Standardise mediation training across hubs. Track ADR-vs-court split as a hub-level metric.',
                    'status'     => 'completed',
                    'outcome'    => 'ADR resolution rate rose from 68% to 81% across the year.',
                ],
            ],
        ];

        foreach ($reflections as $data) {
            Reflection::firstOrCreate(
                ['date' => $data['date'], 'title' => $data['title']],
                $data
            );
        }
    }
}
