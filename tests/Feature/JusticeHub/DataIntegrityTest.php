<?php

namespace Tests\Feature\JusticeHub;

use App\Models\CaseRecord;
use App\Models\Complaint;
use App\Models\Evidence;
use App\Models\Feedback;
use App\Models\Hub;
use App\Models\Indicator;
use App\Models\Lookup;
use App\Models\Staff;
use Database\Seeders\CaseSeeder;
use Database\Seeders\ComplaintSeeder;
use Database\Seeders\EvidenceSeeder;
use Database\Seeders\FeedbackSeeder;
use Database\Seeders\IndicatorSeeder;
use Database\Seeders\LookupSeeder;
use Database\Seeders\OutreachSeeder;
use Database\Seeders\PartnerSeeder;
use Database\Seeders\ServiceEncounterSeeder;
use Database\Seeders\StaffSeeder;
use Database\Seeders\TrainingSeeder;
use Tests\TestCase;

/**
 * Verifies that all seeders produce the expected record counts
 * matching the original JSX mock data arrays.
 *
 * Expected counts (source: seeder arrays):
 *   Hubs: 6  |  Cases: 31  |  Partners: 14  |  Outreach: 6
 *   Complaints: 21  |  Indicators: 28  |  Evidence: 20
 *   Feedback: 16  |  Staff: 16  |  Trainings (catalog): 8
 */
class DataIntegrityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LookupSeeder::class);
        $this->seed(TrainingSeeder::class);
        $this->seed(IndicatorSeeder::class);
        $this->seed(CaseSeeder::class);
        $this->seed(ServiceEncounterSeeder::class);
        $this->seed(PartnerSeeder::class);
        $this->seed(StaffSeeder::class);
        $this->seed(OutreachSeeder::class);
        $this->seed(ComplaintSeeder::class);
        $this->seed(EvidenceSeeder::class);
        $this->seed(FeedbackSeeder::class);
    }

    // ─── Record Counts ───────────────────────────────────────────

    public function test_hubs_seeded_correctly(): void
    {
        $this->assertEquals(6, Hub::count());
    }

    public function test_cases_seeded_correctly(): void
    {
        $this->assertEquals(31, CaseRecord::count());
    }

    public function test_partners_seeded_correctly(): void
    {
        $this->assertDatabaseCount('partners', 14);
    }

    public function test_complaints_seeded_correctly(): void
    {
        $this->assertEquals(21, Complaint::count());
    }

    public function test_indicators_seeded_correctly(): void
    {
        $this->assertEquals(28, Indicator::count());
    }

    public function test_evidence_seeded_correctly(): void
    {
        $this->assertEquals(20, Evidence::count());
    }

    public function test_feedback_seeded_correctly(): void
    {
        $this->assertEquals(16, Feedback::count());
    }

    public function test_staff_seeded_correctly(): void
    {
        $this->assertEquals(16, Staff::count());
    }

    public function test_outreach_seeded_correctly(): void
    {
        $this->assertDatabaseCount('outreach_activities', 6);
    }

    public function test_training_catalog_seeded_correctly(): void
    {
        $this->assertDatabaseCount('trainings', 8);
    }

    // ─── Relational Integrity ─────────────────────────────────────

    public function test_service_encounters_seeded_for_all_cases(): void
    {
        $this->assertGreaterThan(0, \Illuminate\Support\Facades\DB::table('service_encounters')->count());

        $casesWithEncounters = CaseRecord::has('encounters')->count();
        $this->assertEquals(
            CaseRecord::count(),
            $casesWithEncounters,
            'Every seeded case should have at least one service encounter'
        );
    }

    public function test_complaint_actions_seeded_for_all_complaints(): void
    {
        $complaintsWithActions = Complaint::has('actions')->count();
        $this->assertEquals(
            Complaint::count(),
            $complaintsWithActions,
            'Every seeded complaint should have at least one complaint action'
        );
    }

    public function test_all_cases_belong_to_valid_hubs(): void
    {
        $orphans = CaseRecord::whereNotIn('hub_id', Hub::pluck('id'))->count();
        $this->assertEquals(0, $orphans, 'Some cases reference non-existent hubs');
    }

    public function test_all_staff_belong_to_valid_hubs(): void
    {
        $orphans = Staff::whereNotIn('hub_id', Hub::pluck('id'))->count();
        $this->assertEquals(0, $orphans, 'Some staff records reference non-existent hubs');
    }

    public function test_all_complaints_belong_to_valid_hubs(): void
    {
        $orphans = Complaint::whereNotNull('hub_id')
            ->whereNotIn('hub_id', Hub::pluck('id'))
            ->count();
        $this->assertEquals(0, $orphans, 'Some complaints reference non-existent hubs');
    }

    // ─── Lookup Groups ────────────────────────────────────────────

    public function test_lookups_seeded_with_required_groups(): void
    {
        $seededGroups = Lookup::distinct()->pluck('group_key');

        $required = [
            'case.primary_issue',
            'case.urgency',
            'case.status',
            'case.disposition',
            'case.risk',
            'complaint.category',
            'feedback.channel',
            'evidence.type',
        ];

        foreach ($required as $group) {
            $this->assertContains($group, $seededGroups, "Lookup group [{$group}] was not seeded");
        }
    }

    public function test_each_lookup_group_has_at_least_one_active_option(): void
    {
        $groups = Lookup::distinct()->pluck('group_key');

        foreach ($groups as $group) {
            $activeCount = Lookup::where('group_key', $group)->where('is_active', true)->count();
            $this->assertGreaterThan(
                0,
                $activeCount,
                "Lookup group [{$group}] has no active options"
            );
        }
    }

    // ─── Data Quality ─────────────────────────────────────────────

    public function test_cases_have_unique_case_uids(): void
    {
        $total  = CaseRecord::count();
        $unique = CaseRecord::distinct('case_uid')->count('case_uid');
        $this->assertEquals($total, $unique, 'Duplicate case_uid values found in cases table');
    }

    public function test_complaints_have_unique_complaint_uids(): void
    {
        $total  = Complaint::count();
        $unique = Complaint::distinct('complaint_uid')->count('complaint_uid');
        $this->assertEquals($total, $unique, 'Duplicate complaint_uid values found in complaints table');
    }

    public function test_evidence_has_unique_evidence_uids(): void
    {
        $total  = Evidence::count();
        $unique = Evidence::distinct('evidence_uid')->count('evidence_uid');
        $this->assertEquals($total, $unique, 'Duplicate evidence_uid values found in evidence table');
    }

    public function test_indicators_have_unique_codes(): void
    {
        $total  = Indicator::count();
        $unique = Indicator::distinct('code')->count('code');
        $this->assertEquals($total, $unique, 'Duplicate indicator codes found');
    }

    public function test_staff_have_training_records(): void
    {
        // StaffSeeder attaches 4-6 training completions per staff member
        $this->assertGreaterThan(0, \Illuminate\Support\Facades\DB::table('staff_trainings')->count());
    }
}
