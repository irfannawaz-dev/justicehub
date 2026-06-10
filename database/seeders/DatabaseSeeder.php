<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            HubSeeder::class,
            LookupSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            CaseSeeder::class,
            ServiceEncounterSeeder::class,
            DocumentSeeder::class,
            PartnerSeeder::class,
            OutreachSeeder::class,
            ComplaintSeeder::class,
            PulseSurveySeeder::class,
            // Phase 5 seeders
            TrainingSeeder::class,
            StaffSeeder::class,
            FeedbackSeeder::class,
            IndicatorSeeder::class,
            IndicatorSnapshotSeeder::class,
            EvidenceSeeder::class,
            ReflectionSeeder::class,
            CaseStudySeeder::class,
            FinanceConfigSeeder::class,
            HubCostSeeder::class,
        ]);
    }
}
