<?php

namespace Database\Seeders;

use App\Models\FinanceConfig;
use Illuminate\Database\Seeder;

class FinanceConfigSeeder extends Seeder
{
    public function run(): void
    {
        if (FinanceConfig::count() === 0) {
            FinanceConfig::create([
                'config' => [
                    'overheadPct'        => 17,
                    'outreachAllocPct'   => 8,
                    'targets' => [
                        'costPerIndividual'       => 1400,
                        'costPerCase'             => 30000,
                        'overheadCeiling'         => 20,
                        'costPerOutreachSession'  => 10000,
                    ],
                    'projection' => [
                        'casesToReachMultiplier'      => 200,
                        'casesToAnnualMultiplier'     => 30,
                        'sessionsToAnnualMultiplier'  => 30,
                    ],
                    'history' => [
                        ['period' => '2020', 'costPerCase' => 138000],
                        ['period' => '2021', 'costPerCase' => 124000],
                        ['period' => '2022', 'costPerCase' => 112000],
                        ['period' => '2023', 'costPerCase' => 102000],
                        ['period' => '2024', 'costPerCase' =>  95000],
                        ['period' => '2025', 'costPerCase' =>  89000],
                    ],
                    'asOf'        => '2026-04-01',
                    'submittedBy' => 'Finance · seeded baseline',
                ],
                'updated_by' => 'System Seeder',
                'notes'      => 'Initial baseline configuration from programme finance data.',
            ]);
        }
    }
}
