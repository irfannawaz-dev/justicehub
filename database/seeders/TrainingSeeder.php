<?php

namespace Database\Seeders;

use App\Models\Training;
use Illuminate\Database\Seeder;

class TrainingSeeder extends Seeder
{
    public function run(): void
    {
        $trainings = [
            ['code' => 'SOP-CORE',  'name' => 'Justice Hub SOPs · core operations',       'category' => 'sops',            'mandatory' => true,  'refresh' => 'annual',
             'audience' => ['Lawyer', 'Paralegal', 'Hub Manager', 'M&E', 'Admin']],
            ['code' => 'SAFE-CHILD','name' => 'Child safeguarding & protection',            'category' => 'safeguarding',    'mandatory' => true,  'refresh' => 'annual',
             'audience' => ['Lawyer', 'Paralegal', 'Hub Manager', 'M&E']],
            ['code' => 'SAFE-GBV',  'name' => 'GBV-sensitive intake & referral',           'category' => 'safeguarding',    'mandatory' => true,  'refresh' => 'annual',
             'audience' => ['Lawyer', 'Paralegal', 'Hub Manager']],
            ['code' => 'DATA-PROT', 'name' => 'Data protection & client confidentiality', 'category' => 'data-protection', 'mandatory' => true,  'refresh' => 'biennial',
             'audience' => ['Lawyer', 'Paralegal', 'Hub Manager', 'M&E', 'Admin']],
            ['code' => 'ADR-MED',   'name' => 'Mediation & ADR skills',                   'category' => 'legal-skills',    'mandatory' => false, 'refresh' => 'one-off',
             'audience' => ['Lawyer']],
            ['code' => 'PARA-CORE', 'name' => 'Paralegal foundations',                    'category' => 'legal-skills',    'mandatory' => false, 'refresh' => 'one-off',
             'audience' => ['Paralegal']],
            ['code' => 'JUV-JUST',  'name' => 'Juvenile justice procedures',              'category' => 'legal-skills',    'mandatory' => false, 'refresh' => 'biennial',
             'audience' => ['Lawyer']],
            ['code' => 'INT-COMM',  'name' => 'Trauma-informed client communication',     'category' => 'safeguarding',    'mandatory' => false, 'refresh' => 'biennial',
             'audience' => ['Lawyer', 'Paralegal']],
        ];

        foreach ($trainings as $t) {
            Training::firstOrCreate(['code' => $t['code']], $t);
        }
    }
}
