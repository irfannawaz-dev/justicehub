<?php

namespace Database\Seeders;

use App\Models\HubCost;
use Illuminate\Database\Seeder;

class HubCostSeeder extends Seeder
{
    public function run(): void
    {
        $costs = [
            ['hub_id' => 'JH-HYD-01', 'quarter' => '2026-Q1', 'total_operational_cost' => 2450000, 'cost_per_case' => 27222],
            ['hub_id' => 'JH-SUK-01', 'quarter' => '2026-Q1', 'total_operational_cost' => 2150000, 'cost_per_case' => 28947],
            ['hub_id' => 'JH-SAN-01', 'quarter' => '2026-Q1', 'total_operational_cost' => 2050000, 'cost_per_case' => 25641],
            ['hub_id' => 'JH-SBA-01', 'quarter' => '2026-Q1', 'total_operational_cost' => 1975000, 'cost_per_case' => 28214],
            ['hub_id' => 'JH-DAD-01', 'quarter' => '2026-Q1', 'total_operational_cost' => 2100000, 'cost_per_case' => 26250],
        ];

        foreach ($costs as $data) {
            HubCost::firstOrCreate(
                ['hub_id' => $data['hub_id'], 'quarter' => $data['quarter']],
                $data
            );
        }
    }
}
