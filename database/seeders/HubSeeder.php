<?php

namespace Database\Seeders;

use App\Models\Hub;
use Illuminate\Database\Seeder;

class HubSeeder extends Seeder
{
    public function run(): void
    {
        $hubs = [
            ['id' => 'JH-KHI-01', 'name' => 'Karachi',             'district' => 'Karachi',            'province' => 'Sindh',            'tier' => 1, 'staff_count' => 0],
            ['id' => 'JH-HYD-01', 'name' => 'Hyderabad',           'district' => 'Hyderabad',          'province' => 'Sindh',            'tier' => 1, 'staff_count' => 7],
            ['id' => 'JH-SAN-01', 'name' => 'Sanghar',             'district' => 'Sanghar',            'province' => 'Sindh',            'tier' => 1, 'staff_count' => 7],
            ['id' => 'JH-SBA-01', 'name' => 'Shaheed Benazirabad', 'district' => 'Shaheed Benazirabad','province' => 'Sindh',            'tier' => 2, 'staff_count' => 5],
            ['id' => 'JH-DAD-01', 'name' => 'Dadu',                'district' => 'Dadu',               'province' => 'Sindh',            'tier' => 2, 'staff_count' => 6],
            ['id' => 'JH-SUK-01', 'name' => 'Sukkur',              'district' => 'Sukkur',             'province' => 'Sindh',            'tier' => 2, 'staff_count' => 5],
            ['id' => 'JH-LAR-01', 'name' => 'Larkana',             'district' => 'Larkana',            'province' => 'Sindh',            'tier' => 3, 'staff_count' => 4],
            ['id' => 'JH-ISB-01', 'name' => 'Islamabad',           'district' => 'Islamabad',          'province' => 'Islamabad Capital','tier' => 1, 'staff_count' => 0],
        ];

        foreach ($hubs as $hub) {
            Hub::firstOrCreate(['id' => $hub['id']], $hub);
        }
    }
}
