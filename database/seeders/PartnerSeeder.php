<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $allHubs = ['JH-HYD-01', 'JH-SUK-01', 'JH-SAN-01', 'JH-SBA-01', 'JH-DAD-01'];

        $partners = [
            // Shelters
            [
                'id' => 'P-001', 'name' => 'Panah Shelter', 'category' => 'Shelter',
                'type' => 'NGO-run safe house', 'focal_person' => 'Nighat K.',
                'active_referrals' => 4, 'completed_referrals' => 78, 'failed_referrals' => 6,
                'avg_response_hours' => 4, 'last_referral_date' => '2026-04-20',
                'mou_expires' => '2027-03-14', 'mou_status' => 'active',
                'hubs' => ['JH-SAN-01', 'JH-SBA-01'],
            ],
            [
                'id' => 'P-002', 'name' => 'Dar-ul-Aman Karachi', 'category' => 'Shelter',
                'type' => 'Govt. women\'s shelter', 'focal_person' => 'Asma S.',
                'active_referrals' => 6, 'completed_referrals' => 124, 'failed_referrals' => 11,
                'avg_response_hours' => 6, 'last_referral_date' => '2026-04-22',
                'mou_expires' => '2026-07-31', 'mou_status' => 'expiring',
                'hubs' => ['JH-SAN-01', 'JH-SBA-01'],
            ],
            [
                'id' => 'P-003', 'name' => 'Dar-ul-Aman Hyderabad', 'category' => 'Shelter',
                'type' => 'Govt. women\'s shelter', 'focal_person' => 'Rehana S.',
                'active_referrals' => 2, 'completed_referrals' => 56, 'failed_referrals' => 3,
                'avg_response_hours' => 8, 'last_referral_date' => '2026-04-18',
                'mou_expires' => '2027-09-09', 'mou_status' => 'active',
                'hubs' => ['JH-HYD-01', 'JH-DAD-01'],
            ],
            [
                'id' => 'P-004', 'name' => 'Dar-ul-Aman Sukkur', 'category' => 'Shelter',
                'type' => 'Govt. women\'s shelter', 'focal_person' => 'Zehra M.',
                'active_referrals' => 1, 'completed_referrals' => 34, 'failed_referrals' => 4,
                'avg_response_hours' => 10, 'last_referral_date' => '2026-04-14',
                'mou_expires' => '2026-05-31', 'mou_status' => 'expiring',
                'hubs' => ['JH-SUK-01', 'JH-DAD-01'],
            ],

            // Government / Documentation
            [
                'id' => 'P-005', 'name' => 'NADRA', 'category' => 'Government',
                'type' => 'National registration', 'focal_person' => 'Ali J.',
                'active_referrals' => 18, 'completed_referrals' => 412, 'failed_referrals' => 22,
                'avg_response_hours' => 48, 'last_referral_date' => '2026-04-22',
                'mou_expires' => '2027-01-14', 'mou_status' => 'active',
                'hubs' => ['all'],
            ],
            [
                'id' => 'P-006', 'name' => 'BISP', 'category' => 'Government',
                'type' => 'Income support programme', 'focal_person' => 'Saima R.',
                'active_referrals' => 8, 'completed_referrals' => 189, 'failed_referrals' => 15,
                'avg_response_hours' => 72, 'last_referral_date' => '2026-04-19',
                'mou_expires' => '2027-05-19', 'mou_status' => 'active',
                'hubs' => ['all'],
            ],
            [
                'id' => 'P-007', 'name' => 'Union Council Network', 'category' => 'Government',
                'type' => 'Local documentation', 'focal_person' => 'Hub coordinators',
                'active_referrals' => 14, 'completed_referrals' => 298, 'failed_referrals' => 31,
                'avg_response_hours' => 24, 'last_referral_date' => '2026-04-22',
                'mou_expires' => '2026-10-31', 'mou_status' => 'expiring',
                'hubs' => ['all'],
            ],
            [
                'id' => 'P-008', 'name' => 'Sindh Social Welfare Dept.', 'category' => 'Government',
                'type' => 'Welfare services', 'focal_person' => 'Tahir M.',
                'active_referrals' => 5, 'completed_referrals' => 87, 'failed_referrals' => 12,
                'avg_response_hours' => 96, 'last_referral_date' => '2026-04-17',
                'mou_expires' => '2027-02-11', 'mou_status' => 'active',
                'hubs' => ['all'],
            ],

            // Law Enforcement
            [
                'id' => 'P-009', 'name' => 'Sindh Police · Women\'s Desk', 'category' => 'Law Enforcement',
                'type' => 'GBV response', 'focal_person' => 'SP Fatima M.',
                'active_referrals' => 7, 'completed_referrals' => 142, 'failed_referrals' => 19,
                'avg_response_hours' => 12, 'last_referral_date' => '2026-04-22',
                'mou_expires' => '2027-04-21', 'mou_status' => 'active',
                'hubs' => ['all'],
            ],
            [
                'id' => 'P-010', 'name' => 'Sukkur Child Protection Unit', 'category' => 'Law Enforcement',
                'type' => 'Juvenile protection', 'focal_person' => 'DSP Imran S.',
                'active_referrals' => 2, 'completed_referrals' => 45, 'failed_referrals' => 3,
                'avg_response_hours' => 6, 'last_referral_date' => '2026-04-22',
                'mou_expires' => '2027-06-30', 'mou_status' => 'active',
                'hubs' => ['JH-SUK-01', 'JH-DAD-01'],
            ],

            // Health / Psychosocial
            [
                'id' => 'P-011', 'name' => 'Civil Hospital Karachi', 'category' => 'Health',
                'type' => 'Medical + MLC', 'focal_person' => 'Dr. Rabia A.',
                'active_referrals' => 3, 'completed_referrals' => 68, 'failed_referrals' => 5,
                'avg_response_hours' => 4, 'last_referral_date' => '2026-04-21',
                'mou_expires' => '2027-06-14', 'mou_status' => 'active',
                'hubs' => ['JH-SAN-01', 'JH-SBA-01'],
            ],
            [
                'id' => 'P-012', 'name' => 'Rozan', 'category' => 'Health',
                'type' => 'Counselling & psychosocial', 'focal_person' => 'Dr. Maryam B.',
                'active_referrals' => 4, 'completed_referrals' => 56, 'failed_referrals' => 2,
                'avg_response_hours' => 24, 'last_referral_date' => '2026-04-20',
                'mou_expires' => '2027-09-30', 'mou_status' => 'active',
                'hubs' => ['JH-SAN-01', 'JH-SBA-01', 'JH-HYD-01'],
            ],

            // NGO
            [
                'id' => 'P-013', 'name' => 'Aurat Foundation', 'category' => 'NGO',
                'type' => 'Women\'s rights advocacy', 'focal_person' => 'Nausheen Q.',
                'active_referrals' => 3, 'completed_referrals' => 42, 'failed_referrals' => 2,
                'avg_response_hours' => 24, 'last_referral_date' => '2026-04-18',
                'mou_expires' => '2027-08-14', 'mou_status' => 'active',
                'hubs' => ['all'],
            ],
            [
                'id' => 'P-014', 'name' => 'Shirkat Gah', 'category' => 'NGO',
                'type' => 'Gender & policy', 'focal_person' => 'Kiran K.',
                'active_referrals' => 2, 'completed_referrals' => 28, 'failed_referrals' => 1,
                'avg_response_hours' => 36, 'last_referral_date' => '2026-04-15',
                'mou_expires' => '2026-02-28', 'mou_status' => 'expired',
                'hubs' => ['JH-SAN-01', 'JH-SBA-01', 'JH-HYD-01'],
            ],
        ];

        foreach ($partners as $data) {
            $hubs = $data['hubs'];
            unset($data['hubs']);

            Partner::firstOrCreate(['id' => $data['id']], $data);

            // Resolve hub IDs: ['all'] expands to all 5 hubs
            $hubIds = ($hubs === ['all']) ? $allHubs : $hubs;

            $pivotRows = array_map(fn (string $hubId) => [
                'hub_id'     => $hubId,
                'partner_id' => $data['id'],
            ], $hubIds);

            DB::table('hub_partner')->insertOrIgnore($pivotRows);
        }
    }
}
