<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use Illuminate\Database\Seeder;

class CaseStudySeeder extends Seeder
{
    public function run(): void
    {
        $studies = [
            [
                'title'                 => 'When mediation works: a six-week inheritance dispute',
                'narrative'             => 'How the Hub resolved a contested ancestral-land case across three mediation sessions — and what it would have cost the family in court.',
                'impact_statement'      => 'Siblings who had not spoken for years reached a binding settlement. Estimated legal costs saved: PKR 180,000.',
                'lessons_learned'       => 'Early intake and genuine consent from all parties is the decisive factor in successful mediation outcomes.',
                'replication_potential' => 'high',
                'meta'                  => ['kind' => 'Case study', 'hub' => 'JH-SAN-01', 'year' => 2025, 'tags' => ['ADR', 'Inheritance', 'Cost analysis']],
            ],
            [
                'title'                 => 'A bonded-labour family, recognised by the state at last',
                'narrative'             => "For three generations the family had no CNICs, no bank accounts, no legal employment. The Hub's paralegal-led intervention changed that in eight months.",
                'impact_statement'      => 'All five family members received CNICs. Two children enrolled in school. Family accessed BISP income support for first time.',
                'lessons_learned'       => 'Documentation cases require sustained paralegal accompaniment through every agency step — a single visit is rarely enough.',
                'replication_potential' => 'high',
                'meta'                  => ['kind' => 'MSC story', 'hub' => 'JH-DAD-01', 'year' => 2025, 'tags' => ['Documentation', 'Minority', 'Bonded labour']],
            ],
            [
                'title'                 => 'The cost of delay: why early intake matters in GBV cases',
                'narrative'             => 'A retrospective look at fifteen cases where late screening led to escalation. The findings drove the Q4 2025 screening-tool revision.',
                'impact_statement'      => 'GBV identification at first contact rose from 64% to 81% following the new screening tool introduced in January 2026.',
                'lessons_learned'       => 'Indirect GBV indicators (economic coercion, isolation, prior medical visits) are more predictive than direct disclosure questions for rural clients.',
                'replication_potential' => 'high',
                'meta'                  => ['kind' => 'Case study', 'hub' => 'all', 'year' => 2026, 'tags' => ['GBV', 'Safeguarding', 'Methodology']],
            ],
            [
                'title'                 => '"My daughter goes to school because I have a paper now"',
                'narrative'             => "A widow describes how a death-certificate referral unlocked her late husband's pension, school admission, and a future for her daughter.",
                'impact_statement'      => 'One CNIC unlocked pension access, school admission, and a bank account for a family of three.',
                'lessons_learned'       => 'The cascade effect of documentation cases is consistently underestimated in programme reporting.',
                'replication_potential' => 'high',
                'meta'                  => ['kind' => 'MSC story', 'hub' => 'JH-DAD-01', 'year' => 2024, 'tags' => ['Documentation', 'Women', 'Outcomes']],
            ],
            [
                'title'                 => "Year three: what changed, what didn't, what we still don't know",
                'narrative'             => "The 2025 reflection report — surfacing what worked, what plateaued, and three open questions the team is still living with.",
                'impact_statement'      => 'ADR resolution rate up from 68% to 81%. GBV early identification up from 64% to 81%. Minority client share up from 8% to 12%.',
                'lessons_learned'       => 'Adaptive management requires not just collecting learning but creating structured space to act on it — the quarterly reflection format has been critical.',
                'replication_potential' => 'medium',
                'meta'                  => ['kind' => 'Annual reflection', 'hub' => 'all', 'year' => 2025, 'tags' => ['Annual review', 'Adaptive learning']],
            ],
            [
                'title'                 => 'Independent evaluation: Justice Hub access for women in Sindh',
                'narrative'             => 'A commissioned independent study by IDS, examining whether the Hub model has shifted access-to-justice outcomes for women clients.',
                'impact_statement'      => 'Independent evaluation found statistically significant improvement in legal knowledge and access outcomes among women clients vs. control group.',
                'lessons_learned'       => 'External evaluation validates the paralegal model but identifies a gap in post-case follow-up for sustained behaviour change.',
                'replication_potential' => 'medium',
                'meta'                  => ['kind' => 'External research', 'hub' => 'External', 'year' => 2025, 'tags' => ['External', 'Evaluation', 'Gender']],
            ],
        ];

        foreach ($studies as $data) {
            CaseStudy::firstOrCreate(
                ['title' => $data['title']],
                $data
            );
        }
    }
}
