<?php

namespace Database\Seeders;

use App\Models\OutreachActivity;
use App\Models\PulseSurvey;
use Illuminate\Database\Seeder;

class PulseSurveySeeder extends Seeder
{
    public function run(): void
    {
        $surveys = [
            // OR-0142 · Gulshan Community Centre · Inheritance under MFLO (6 respondents)
            [
                'pulse_uid' => 'PS-2401', 'session' => 'OR-0142', 'hub' => 'JH-SAN-01',
                'date' => '2026-04-22', 'participant_ref' => 'P-1', 'gender' => 'F',
                'age_band' => '25-44', 'pre' => 1, 'post' => 4,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => 'I did not know women could claim the share. Now I will.',
            ],
            [
                'pulse_uid' => 'PS-2402', 'session' => 'OR-0142', 'hub' => 'JH-SAN-01',
                'date' => '2026-04-22', 'participant_ref' => 'P-2', 'gender' => 'F',
                'age_band' => '45-plus', 'pre' => 2, 'post' => 4,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => null,
            ],
            [
                'pulse_uid' => 'PS-2403', 'session' => 'OR-0142', 'hub' => 'JH-SAN-01',
                'date' => '2026-04-22', 'participant_ref' => 'P-3', 'gender' => 'M',
                'age_band' => '25-44', 'pre' => 3, 'post' => 4,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => null,
            ],
            [
                'pulse_uid' => 'PS-2404', 'session' => 'OR-0142', 'hub' => 'JH-SAN-01',
                'date' => '2026-04-22', 'participant_ref' => 'P-4', 'gender' => 'F',
                'age_band' => 'under-25', 'pre' => 1, 'post' => 5,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => 'Now I will speak to my mother about what is hers.',
            ],
            [
                'pulse_uid' => 'PS-2405', 'session' => 'OR-0142', 'hub' => 'JH-SAN-01',
                'date' => '2026-04-22', 'participant_ref' => 'P-5', 'gender' => 'F',
                'age_band' => '25-44', 'pre' => 2, 'post' => 2,
                'will_apply' => 'maybe', 'would_recommend' => 'yes',
                'comment' => 'Some of it I already knew.',
            ],
            [
                'pulse_uid' => 'PS-2406', 'session' => 'OR-0142', 'hub' => 'JH-SAN-01',
                'date' => '2026-04-22', 'participant_ref' => 'P-6', 'gender' => 'M',
                'age_band' => '45-plus', 'pre' => 3, 'post' => 5,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => null,
            ],

            // OR-0141 · Latifabad Union Council · CNIC & docs (4 respondents)
            [
                'pulse_uid' => 'PS-2407', 'session' => 'OR-0141', 'hub' => 'JH-HYD-01',
                'date' => '2026-04-21', 'participant_ref' => 'P-1', 'gender' => 'F',
                'age_band' => '45-plus', 'pre' => 1, 'post' => 4,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => 'I will go to NADRA next week. They told me which papers to bring.',
            ],
            [
                'pulse_uid' => 'PS-2408', 'session' => 'OR-0141', 'hub' => 'JH-HYD-01',
                'date' => '2026-04-21', 'participant_ref' => 'P-2', 'gender' => 'F',
                'age_band' => '25-44', 'pre' => 2, 'post' => 4,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => null,
            ],
            [
                'pulse_uid' => 'PS-2409', 'session' => 'OR-0141', 'hub' => 'JH-HYD-01',
                'date' => '2026-04-21', 'participant_ref' => 'P-3', 'gender' => 'M',
                'age_band' => '25-44', 'pre' => 2, 'post' => 3,
                'will_apply' => 'maybe', 'would_recommend' => 'yes',
                'comment' => null,
            ],
            [
                'pulse_uid' => 'PS-2410', 'session' => 'OR-0141', 'hub' => 'JH-HYD-01',
                'date' => '2026-04-21', 'participant_ref' => 'P-4', 'gender' => 'F',
                'age_band' => 'under-25', 'pre' => 1, 'post' => 5,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => null,
            ],

            // OR-0140 · Mirpur Sakro Village · Workers' rights (5 respondents)
            [
                'pulse_uid' => 'PS-2411', 'session' => 'OR-0140', 'hub' => 'JH-DAD-01',
                'date' => '2026-04-20', 'participant_ref' => 'P-1', 'gender' => 'M',
                'age_band' => '25-44', 'pre' => 2, 'post' => 4,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => 'The contractor took two months. Now I know I can ask.',
            ],
            [
                'pulse_uid' => 'PS-2412', 'session' => 'OR-0140', 'hub' => 'JH-DAD-01',
                'date' => '2026-04-20', 'participant_ref' => 'P-2', 'gender' => 'M',
                'age_band' => '45-plus', 'pre' => 2, 'post' => 4,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => null,
            ],
            [
                'pulse_uid' => 'PS-2413', 'session' => 'OR-0140', 'hub' => 'JH-DAD-01',
                'date' => '2026-04-20', 'participant_ref' => 'P-3', 'gender' => 'F',
                'age_band' => '25-44', 'pre' => 1, 'post' => 3,
                'will_apply' => 'maybe', 'would_recommend' => 'yes',
                'comment' => null,
            ],
            [
                'pulse_uid' => 'PS-2414', 'session' => 'OR-0140', 'hub' => 'JH-DAD-01',
                'date' => '2026-04-20', 'participant_ref' => 'P-4', 'gender' => 'M',
                'age_band' => 'under-25', 'pre' => 3, 'post' => 5,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => null,
            ],
            [
                'pulse_uid' => 'PS-2415', 'session' => 'OR-0140', 'hub' => 'JH-DAD-01',
                'date' => '2026-04-20', 'participant_ref' => 'P-5', 'gender' => 'M',
                'age_band' => '25-44', 'pre' => 4, 'post' => 4,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => 'Already knew most.',
            ],

            // OR-0139 · Dadu Girls College · Harassment protections (6 respondents)
            [
                'pulse_uid' => 'PS-2416', 'session' => 'OR-0139', 'hub' => 'JH-DAD-01',
                'date' => '2026-04-19', 'participant_ref' => 'P-1', 'gender' => 'F',
                'age_band' => 'under-25', 'pre' => 1, 'post' => 5,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => 'I will tell my friends about the helpline.',
            ],
            [
                'pulse_uid' => 'PS-2417', 'session' => 'OR-0139', 'hub' => 'JH-DAD-01',
                'date' => '2026-04-19', 'participant_ref' => 'P-2', 'gender' => 'F',
                'age_band' => 'under-25', 'pre' => 2, 'post' => 5,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => null,
            ],
            [
                'pulse_uid' => 'PS-2418', 'session' => 'OR-0139', 'hub' => 'JH-DAD-01',
                'date' => '2026-04-19', 'participant_ref' => 'P-3', 'gender' => 'F',
                'age_band' => 'under-25', 'pre' => 1, 'post' => 4,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => null,
            ],
            [
                'pulse_uid' => 'PS-2419', 'session' => 'OR-0139', 'hub' => 'JH-DAD-01',
                'date' => '2026-04-19', 'participant_ref' => 'P-4', 'gender' => 'F',
                'age_band' => 'under-25', 'pre' => 2, 'post' => 4,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => null,
            ],
            [
                'pulse_uid' => 'PS-2420', 'session' => 'OR-0139', 'hub' => 'JH-DAD-01',
                'date' => '2026-04-19', 'participant_ref' => 'P-5', 'gender' => 'F',
                'age_band' => 'under-25', 'pre' => 3, 'post' => 5,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => null,
            ],
            [
                'pulse_uid' => 'PS-2421', 'session' => 'OR-0139', 'hub' => 'JH-DAD-01',
                'date' => '2026-04-19', 'participant_ref' => 'P-6', 'gender' => 'F',
                'age_band' => 'under-25', 'pre' => 3, 'post' => 3,
                'will_apply' => 'maybe', 'would_recommend' => 'yes',
                'comment' => null,
            ],

            // OR-0138 · Rohri Town · Juvenile justice (3 respondents)
            [
                'pulse_uid' => 'PS-2422', 'session' => 'OR-0138', 'hub' => 'JH-SUK-01',
                'date' => '2026-04-18', 'participant_ref' => 'P-1', 'gender' => 'F',
                'age_band' => '25-44', 'pre' => 1, 'post' => 4,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => 'I have a 14-year-old son. Now I know what to do if he is detained.',
            ],
            [
                'pulse_uid' => 'PS-2423', 'session' => 'OR-0138', 'hub' => 'JH-SUK-01',
                'date' => '2026-04-18', 'participant_ref' => 'P-2', 'gender' => 'M',
                'age_band' => '45-plus', 'pre' => 2, 'post' => 4,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => null,
            ],
            [
                'pulse_uid' => 'PS-2424', 'session' => 'OR-0138', 'hub' => 'JH-SUK-01',
                'date' => '2026-04-18', 'participant_ref' => 'P-3', 'gender' => 'F',
                'age_band' => '25-44', 'pre' => 1, 'post' => 2,
                'will_apply' => 'no', 'would_recommend' => 'maybe',
                'comment' => 'It was a bit fast.',
            ],

            // OR-0137 · Lyari Youth Centre · Domestic violence (4 respondents)
            [
                'pulse_uid' => 'PS-2425', 'session' => 'OR-0137', 'hub' => 'JH-SBA-01',
                'date' => '2026-04-17', 'participant_ref' => 'P-1', 'gender' => 'F',
                'age_band' => '25-44', 'pre' => 1, 'post' => 5,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => 'For the first time I felt I had options.',
            ],
            [
                'pulse_uid' => 'PS-2426', 'session' => 'OR-0137', 'hub' => 'JH-SBA-01',
                'date' => '2026-04-17', 'participant_ref' => 'P-2', 'gender' => 'F',
                'age_band' => 'under-25', 'pre' => 1, 'post' => 4,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => null,
            ],
            [
                'pulse_uid' => 'PS-2427', 'session' => 'OR-0137', 'hub' => 'JH-SBA-01',
                'date' => '2026-04-17', 'participant_ref' => 'P-3', 'gender' => 'F',
                'age_band' => '45-plus', 'pre' => 2, 'post' => 4,
                'will_apply' => 'yes', 'would_recommend' => 'yes',
                'comment' => null,
            ],
            [
                'pulse_uid' => 'PS-2428', 'session' => 'OR-0137', 'hub' => 'JH-SBA-01',
                'date' => '2026-04-17', 'participant_ref' => 'P-4', 'gender' => 'M',
                'age_band' => '25-44', 'pre' => 3, 'post' => 4,
                'will_apply' => 'maybe', 'would_recommend' => 'yes',
                'comment' => null,
            ],
        ];

        foreach ($surveys as $data) {
            // Look up the outreach activity by outreach_uid to get its DB id
            $outreach = OutreachActivity::where('outreach_uid', $data['session'])->first();

            // Build demographics array
            $demographics = [
                'participant_ref' => $data['participant_ref'],
                'gender'          => $data['gender'],
                'age_band'        => $data['age_band'],
            ];

            // Determine would_recommend_pct: yes=100, maybe=50, no=0
            $recommendMap = ['yes' => 100.00, 'maybe' => 50.00, 'no' => 0.00];

            PulseSurvey::firstOrCreate(
                ['pulse_uid' => $data['pulse_uid']],
                [
                    'outreach_id'        => $outreach?->id,
                    'session'            => $data['session'],
                    'date'               => $data['date'],
                    'respondent_count'   => 1,
                    'pre_score'          => $data['pre'],
                    'post_score'         => $data['post'],
                    'will_apply'         => $data['will_apply'],
                    'would_recommend_pct' => $recommendMap[$data['would_recommend']] ?? null,
                    'demographics'       => $demographics,
                    'comment'            => $data['comment'],
                ],
            );
        }
    }
}
