<?php

namespace Database\Seeders;

use App\Models\OutreachActivity;
use Illuminate\Database\Seeder;

class OutreachSeeder extends Seeder
{
    public function run(): void
    {
        $activities = [
            [
                'outreach_uid' => 'OR-0142', 'date' => '2026-04-22', 'hub_id' => 'JH-SAN-01',
                'type' => 'Legal Literacy', 'location' => 'Gulshan Community Centre',
                'facilitator' => 'S. Shah (Paralegal)', 'total_participants' => 38,
                'female_participants' => 28, 'minority_participants' => 4, 'disability_participants' => 1,
                'naz_promoted' => true, 'slacc' => false,
                'topic' => 'Inheritance rights under Muslim Family Law',
            ],
            [
                'outreach_uid' => 'OR-0141', 'date' => '2026-04-21', 'hub_id' => 'JH-HYD-01',
                'type' => 'Paralegal Outreach', 'location' => 'Latifabad Union Council',
                'facilitator' => 'T. Panhwar', 'total_participants' => 24,
                'female_participants' => 15, 'minority_participants' => 2, 'disability_participants' => 0,
                'naz_promoted' => true, 'slacc' => true,
                'topic' => 'CNIC & documentation door-to-door campaign',
            ],
            [
                'outreach_uid' => 'OR-0140', 'date' => '2026-04-20', 'hub_id' => 'JH-DAD-01',
                'type' => 'Awareness', 'location' => 'Mirpur Sakro Village',
                'facilitator' => 'M. Soomro', 'total_participants' => 52,
                'female_participants' => 31, 'minority_participants' => 9, 'disability_participants' => 0,
                'naz_promoted' => false, 'slacc' => false,
                'topic' => 'Workers\' rights & wage recovery',
            ],
            [
                'outreach_uid' => 'OR-0139', 'date' => '2026-04-19', 'hub_id' => 'JH-DAD-01',
                'type' => 'Legal Literacy', 'location' => 'Dadu Girls College',
                'facilitator' => 'K. Leghari', 'total_participants' => 67,
                'female_participants' => 67, 'minority_participants' => 3, 'disability_participants' => 2,
                'naz_promoted' => true, 'slacc' => true,
                'topic' => 'Protection from harassment & reporting mechanisms',
            ],
            [
                'outreach_uid' => 'OR-0138', 'date' => '2026-04-18', 'hub_id' => 'JH-SUK-01',
                'type' => 'Paralegal Outreach', 'location' => 'Rohri Town',
                'facilitator' => 'A. Mahar', 'total_participants' => 29,
                'female_participants' => 14, 'minority_participants' => 1, 'disability_participants' => 1,
                'naz_promoted' => false, 'slacc' => true,
                'topic' => 'Juvenile justice & child protection',
            ],
            [
                'outreach_uid' => 'OR-0137', 'date' => '2026-04-17', 'hub_id' => 'JH-SBA-01',
                'type' => 'Awareness', 'location' => 'Lyari Youth Centre',
                'facilitator' => 'H. Soomro', 'total_participants' => 44,
                'female_participants' => 19, 'minority_participants' => 0, 'disability_participants' => 0,
                'naz_promoted' => true, 'slacc' => false,
                'topic' => 'Domestic violence: legal options & support',
            ],
        ];

        foreach ($activities as $data) {
            OutreachActivity::firstOrCreate(
                ['outreach_uid' => $data['outreach_uid']],
                $data,
            );
        }
    }
}
