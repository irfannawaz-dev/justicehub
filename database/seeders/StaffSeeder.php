<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\Training;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $staffList = [
            // Lawyers
            ['id' => 'STF-001', 'name' => 'Adv. F. Hussain', 'initials' => 'FH', 'role' => 'Lawyer', 'hub' => 'JH-SAN-01', 'status' => 'active', 'joined' => '2022-08-14',
             'trainings' => [
                 ['code' => 'SOP-CORE',   'completedOn' => '2025-09-12', 'expires' => '2026-09-12', 'by' => 'LAS HQ'],
                 ['code' => 'SAFE-CHILD', 'completedOn' => '2025-10-04', 'expires' => '2026-10-04', 'by' => 'UNICEF Pakistan'],
                 ['code' => 'SAFE-GBV',   'completedOn' => '2025-09-25', 'expires' => '2026-09-25', 'by' => 'Aurat Foundation'],
                 ['code' => 'DATA-PROT',  'completedOn' => '2025-03-18', 'expires' => '2027-03-18', 'by' => 'LAS HQ'],
                 ['code' => 'ADR-MED',    'completedOn' => '2023-05-22', 'expires' => null,          'by' => 'LAS HQ'],
             ]],
            ['id' => 'STF-002', 'name' => 'Adv. R. Khan', 'initials' => 'RK', 'role' => 'Lawyer', 'hub' => 'JH-SBA-01', 'status' => 'active', 'joined' => '2023-02-09',
             'trainings' => [
                 ['code' => 'SOP-CORE',   'completedOn' => '2025-08-30', 'expires' => '2026-08-30', 'by' => 'LAS HQ'],
                 ['code' => 'SAFE-CHILD', 'completedOn' => '2025-08-30', 'expires' => '2026-08-30', 'by' => 'UNICEF Pakistan'],
                 ['code' => 'SAFE-GBV',   'completedOn' => '2025-09-04', 'expires' => '2026-09-04', 'by' => 'Aurat Foundation'],
                 ['code' => 'DATA-PROT',  'completedOn' => '2024-11-12', 'expires' => '2026-11-12', 'by' => 'LAS HQ'],
                 ['code' => 'ADR-MED',    'completedOn' => '2023-06-04', 'expires' => null,          'by' => 'LAS HQ'],
             ]],
            ['id' => 'STF-003', 'name' => 'Adv. S. Abbasi', 'initials' => 'SA', 'role' => 'Lawyer', 'hub' => 'JH-DAD-01', 'status' => 'active', 'joined' => '2022-11-21',
             'trainings' => [
                 ['code' => 'SOP-CORE',   'completedOn' => '2025-10-08', 'expires' => '2026-10-08', 'by' => 'LAS HQ'],
                 ['code' => 'SAFE-CHILD', 'completedOn' => '2025-09-19', 'expires' => '2026-09-19', 'by' => 'UNICEF Pakistan'],
                 ['code' => 'SAFE-GBV',   'completedOn' => '2025-10-02', 'expires' => '2026-10-02', 'by' => 'Aurat Foundation'],
                 ['code' => 'DATA-PROT',  'completedOn' => '2025-04-15', 'expires' => '2027-04-15', 'by' => 'LAS HQ'],
             ]],
            ['id' => 'STF-004', 'name' => 'Adv. P. Kumar', 'initials' => 'PK', 'role' => 'Lawyer', 'hub' => 'JH-DAD-01', 'status' => 'active', 'joined' => '2024-01-15',
             'trainings' => [
                 ['code' => 'SOP-CORE',   'completedOn' => '2024-03-22', 'expires' => '2025-03-22', 'by' => 'LAS HQ'],
                 ['code' => 'SAFE-CHILD', 'completedOn' => '2025-08-12', 'expires' => '2026-08-12', 'by' => 'UNICEF Pakistan'],
                 ['code' => 'SAFE-GBV',   'completedOn' => '2025-08-12', 'expires' => '2026-08-12', 'by' => 'Aurat Foundation'],
                 ['code' => 'DATA-PROT',  'completedOn' => '2024-04-10', 'expires' => '2026-04-10', 'by' => 'LAS HQ'],
             ]],
            ['id' => 'STF-005', 'name' => 'Adv. N. Jatoi', 'initials' => 'NJ', 'role' => 'Lawyer', 'hub' => 'JH-SUK-01', 'status' => 'active', 'joined' => '2022-05-08',
             'trainings' => [
                 ['code' => 'SOP-CORE',   'completedOn' => '2025-11-04', 'expires' => '2026-11-04', 'by' => 'LAS HQ'],
                 ['code' => 'SAFE-CHILD', 'completedOn' => '2025-10-22', 'expires' => '2026-10-22', 'by' => 'UNICEF Pakistan'],
                 ['code' => 'SAFE-GBV',   'completedOn' => '2025-11-04', 'expires' => '2026-11-04', 'by' => 'Aurat Foundation'],
                 ['code' => 'DATA-PROT',  'completedOn' => '2024-12-19', 'expires' => '2026-12-19', 'by' => 'LAS HQ'],
                 ['code' => 'JUV-JUST',   'completedOn' => '2024-08-15', 'expires' => '2026-08-15', 'by' => 'UNICEF Pakistan'],
             ]],
            ['id' => 'STF-006', 'name' => 'Adv. M. Soomro', 'initials' => 'MS', 'role' => 'Lawyer', 'hub' => 'JH-DAD-01', 'status' => 'active', 'joined' => '2023-09-12',
             'trainings' => [
                 ['code' => 'SOP-CORE',   'completedOn' => '2025-09-30', 'expires' => '2026-09-30', 'by' => 'LAS HQ'],
                 ['code' => 'SAFE-CHILD', 'completedOn' => '2025-09-30', 'expires' => '2026-09-30', 'by' => 'UNICEF Pakistan'],
                 ['code' => 'SAFE-GBV',   'completedOn' => '2025-10-15', 'expires' => '2026-10-15', 'by' => 'Aurat Foundation'],
                 ['code' => 'DATA-PROT',  'completedOn' => '2025-02-12', 'expires' => '2027-02-12', 'by' => 'LAS HQ'],
             ]],
            // Paralegals
            ['id' => 'STF-007', 'name' => 'T. Panhwar', 'initials' => 'TP', 'role' => 'Paralegal', 'hub' => 'JH-HYD-01', 'status' => 'active', 'joined' => '2023-04-18',
             'trainings' => [
                 ['code' => 'SOP-CORE',   'completedOn' => '2025-09-08', 'expires' => '2026-09-08', 'by' => 'LAS HQ'],
                 ['code' => 'SAFE-CHILD', 'completedOn' => '2025-09-08', 'expires' => '2026-09-08', 'by' => 'UNICEF Pakistan'],
                 ['code' => 'SAFE-GBV',   'completedOn' => '2025-09-22', 'expires' => '2026-09-22', 'by' => 'Aurat Foundation'],
                 ['code' => 'DATA-PROT',  'completedOn' => '2024-10-04', 'expires' => '2026-10-04', 'by' => 'LAS HQ'],
                 ['code' => 'PARA-CORE',  'completedOn' => '2023-05-19', 'expires' => null,          'by' => 'LAS HQ'],
             ]],
            ['id' => 'STF-008', 'name' => 'K. Leghari', 'initials' => 'KL', 'role' => 'Paralegal', 'hub' => 'JH-DAD-01', 'status' => 'active', 'joined' => '2024-02-05',
             'trainings' => [
                 ['code' => 'SOP-CORE',   'completedOn' => '2025-10-14', 'expires' => '2026-10-14', 'by' => 'LAS HQ'],
                 ['code' => 'SAFE-GBV',   'completedOn' => '2025-10-25', 'expires' => '2026-10-25', 'by' => 'Aurat Foundation'],
                 ['code' => 'DATA-PROT',  'completedOn' => '2024-06-08', 'expires' => '2026-06-08', 'by' => 'LAS HQ'],
                 ['code' => 'PARA-CORE',  'completedOn' => '2024-03-18', 'expires' => null,          'by' => 'LAS HQ'],
             ]],
            ['id' => 'STF-009', 'name' => 'N. Memon', 'initials' => 'NM', 'role' => 'Paralegal', 'hub' => 'JH-SAN-01', 'status' => 'active', 'joined' => '2022-07-14',
             'trainings' => [
                 ['code' => 'SOP-CORE',   'completedOn' => '2025-08-22', 'expires' => '2026-08-22', 'by' => 'LAS HQ'],
                 ['code' => 'SAFE-CHILD', 'completedOn' => '2025-08-22', 'expires' => '2026-08-22', 'by' => 'UNICEF Pakistan'],
                 ['code' => 'SAFE-GBV',   'completedOn' => '2025-08-30', 'expires' => '2026-08-30', 'by' => 'Aurat Foundation'],
                 ['code' => 'DATA-PROT',  'completedOn' => '2024-09-16', 'expires' => '2026-09-16', 'by' => 'LAS HQ'],
                 ['code' => 'PARA-CORE',  'completedOn' => '2022-08-12', 'expires' => null,          'by' => 'LAS HQ'],
                 ['code' => 'INT-COMM',   'completedOn' => '2024-11-08', 'expires' => '2026-11-08', 'by' => 'Rozan'],
             ]],
            ['id' => 'STF-010', 'name' => 'A. Mahar', 'initials' => 'AM', 'role' => 'Paralegal', 'hub' => 'JH-SUK-01', 'status' => 'active', 'joined' => '2023-08-29',
             'trainings' => [
                 ['code' => 'SOP-CORE',   'completedOn' => '2025-09-19', 'expires' => '2026-09-19', 'by' => 'LAS HQ'],
                 ['code' => 'SAFE-CHILD', 'completedOn' => '2025-09-19', 'expires' => '2026-09-19', 'by' => 'UNICEF Pakistan'],
                 ['code' => 'SAFE-GBV',   'completedOn' => '2025-10-08', 'expires' => '2026-10-08', 'by' => 'Aurat Foundation'],
                 ['code' => 'DATA-PROT',  'completedOn' => '2024-08-25', 'expires' => '2026-08-25', 'by' => 'LAS HQ'],
             ]],
            ['id' => 'STF-011', 'name' => 'S. Shah', 'initials' => 'SS', 'role' => 'Paralegal', 'hub' => 'JH-SAN-01', 'status' => 'active', 'joined' => '2024-06-10',
             'trainings' => [
                 ['code' => 'SOP-CORE',   'completedOn' => '2025-07-18', 'expires' => '2026-07-18', 'by' => 'LAS HQ'],
                 ['code' => 'SAFE-CHILD', 'completedOn' => '2025-07-25', 'expires' => '2026-07-25', 'by' => 'UNICEF Pakistan'],
                 ['code' => 'SAFE-GBV',   'completedOn' => '2025-08-02', 'expires' => '2026-08-02', 'by' => 'Aurat Foundation'],
                 ['code' => 'PARA-CORE',  'completedOn' => '2024-07-15', 'expires' => null,          'by' => 'LAS HQ'],
             ]],
            ['id' => 'STF-012', 'name' => 'H. Soomro', 'initials' => 'HS', 'role' => 'Paralegal', 'hub' => 'JH-SBA-01', 'status' => 'active', 'joined' => '2023-11-04',
             'trainings' => [
                 ['code' => 'SOP-CORE',   'completedOn' => '2025-10-28', 'expires' => '2026-10-28', 'by' => 'LAS HQ'],
                 ['code' => 'SAFE-CHILD', 'completedOn' => '2025-10-28', 'expires' => '2026-10-28', 'by' => 'UNICEF Pakistan'],
                 ['code' => 'SAFE-GBV',   'completedOn' => '2025-11-15', 'expires' => '2026-11-15', 'by' => 'Aurat Foundation'],
                 ['code' => 'DATA-PROT',  'completedOn' => '2024-12-04', 'expires' => '2026-12-04', 'by' => 'LAS HQ'],
             ]],
            // Hub Managers
            ['id' => 'STF-013', 'name' => 'Bilal Ahmed', 'initials' => 'BA', 'role' => 'Hub Manager', 'hub' => 'JH-DAD-01', 'status' => 'active', 'joined' => '2022-03-22',
             'trainings' => [
                 ['code' => 'SOP-CORE',   'completedOn' => '2025-09-04', 'expires' => '2026-09-04', 'by' => 'LAS HQ'],
                 ['code' => 'SAFE-CHILD', 'completedOn' => '2025-09-04', 'expires' => '2026-09-04', 'by' => 'UNICEF Pakistan'],
                 ['code' => 'SAFE-GBV',   'completedOn' => '2025-09-12', 'expires' => '2026-09-12', 'by' => 'Aurat Foundation'],
                 ['code' => 'DATA-PROT',  'completedOn' => '2024-10-22', 'expires' => '2026-10-22', 'by' => 'LAS HQ'],
             ]],
            ['id' => 'STF-014', 'name' => 'Irfan Nawaz', 'initials' => 'IN', 'role' => 'Hub Manager', 'hub' => 'JH-SBA-01', 'status' => 'active', 'joined' => '2022-09-08',
             'trainings' => [
                 ['code' => 'SOP-CORE',   'completedOn' => '2025-10-12', 'expires' => '2026-10-12', 'by' => 'LAS HQ'],
                 ['code' => 'SAFE-CHILD', 'completedOn' => '2025-10-12', 'expires' => '2026-10-12', 'by' => 'UNICEF Pakistan'],
                 ['code' => 'SAFE-GBV',   'completedOn' => '2025-10-25', 'expires' => '2026-10-25', 'by' => 'Aurat Foundation'],
                 ['code' => 'DATA-PROT',  'completedOn' => '2025-01-14', 'expires' => '2027-01-14', 'by' => 'LAS HQ'],
             ]],
            // M&E and Admin
            ['id' => 'STF-015', 'name' => 'A. Mahar (M&E Lead)', 'initials' => 'AM', 'role' => 'M&E', 'hub' => 'JH-HYD-01', 'status' => 'active', 'joined' => '2021-12-01',
             'trainings' => [
                 ['code' => 'SOP-CORE',   'completedOn' => '2025-08-15', 'expires' => '2026-08-15', 'by' => 'LAS HQ'],
                 ['code' => 'SAFE-CHILD', 'completedOn' => '2025-08-15', 'expires' => '2026-08-15', 'by' => 'UNICEF Pakistan'],
                 ['code' => 'DATA-PROT',  'completedOn' => '2024-09-30', 'expires' => '2026-09-30', 'by' => 'LAS HQ'],
             ]],
            ['id' => 'STF-016', 'name' => 'Zara Aslam', 'initials' => 'ZA', 'role' => 'Admin', 'hub' => 'JH-HYD-01', 'status' => 'active', 'joined' => '2023-03-20',
             'trainings' => [
                 ['code' => 'SOP-CORE',   'completedOn' => '2025-09-22', 'expires' => '2026-09-22', 'by' => 'LAS HQ'],
                 ['code' => 'DATA-PROT',  'completedOn' => '2024-08-04', 'expires' => '2026-08-04', 'by' => 'LAS HQ'],
             ]],
        ];

        // Cache training IDs
        $trainingMap = Training::pluck('id', 'code')->toArray();

        foreach ($staffList as $s) {
            $staff = Staff::firstOrCreate(
                ['staff_uid' => $s['id']],
                [
                    'name'        => $s['name'],
                    'initials'    => $s['initials'],
                    'role'        => $s['role'],
                    'hub_id'      => $s['hub'],
                    'status'      => $s['status'],
                    'joined_date' => $s['joined'],
                ]
            );

            // Sync training records (only add missing ones)
            foreach ($s['trainings'] as $t) {
                $trainingId = $trainingMap[$t['code']] ?? null;
                if (!$trainingId) continue;

                $exists = $staff->trainings()->wherePivot('training_id', $trainingId)->exists();
                if (!$exists) {
                    $staff->trainings()->attach($trainingId, [
                        'completed_on' => $t['completedOn'],
                        'expires'      => $t['expires'],
                        'delivered_by' => $t['by'],
                    ]);
                }
            }
        }
    }
}
