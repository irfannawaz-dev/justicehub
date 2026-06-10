<?php

namespace Tests\Feature\JusticeHub;

use App\Models\CaseRecord;
use App\Models\Complaint;
use App\Models\Hub;
use App\Models\Staff;
use Database\Seeders\IndicatorSeeder;
use Database\Seeders\LookupSeeder;
use Database\Seeders\TrainingSeeder;
use Tests\TestCase;

/**
 * Verifies that every store/mutation endpoint:
 *   - Returns a redirect (302) on success
 *   - Persists the correct record to the database
 */
class StoreEndpointsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LookupSeeder::class);
        $this->seed(TrainingSeeder::class);
        $this->seed(IndicatorSeeder::class);
        $this->asHead();
    }

    // ─── Intake ──────────────────────────────────────────────────

    public function test_intake_creates_case_record(): void
    {
        $hub = Hub::first();

        $this->post(route('intake.store'), $this->validIntakeData($hub->id))
            ->assertRedirect();

        $this->assertDatabaseHas('cases', ['name' => 'Fatima Bibi', 'hub_id' => $hub->id]);
    }

    // ─── Outreach ────────────────────────────────────────────────

    public function test_outreach_creates_activity(): void
    {
        $hub = Hub::first();

        $this->post(route('outreach.store'), [
            'date'               => now()->toDateString(),
            'hub_id'             => $hub->id,
            'type'               => 'awareness',
            'location'           => 'District Community Hall',
            'facilitator'        => 'Rashida Khan',
            'total_participants' => 45,
        ])->assertRedirect();

        $this->assertDatabaseHas('outreach_activities', ['facilitator' => 'Rashida Khan']);
    }

    // ─── Complaint ───────────────────────────────────────────────

    public function test_complaint_creates_record(): void
    {
        $hub = Hub::first();

        $this->withSession(['active_hub' => $hub->id])
            ->post(route('complaints.store'), [
                'description'  => 'Staff was dismissive during intake process.',
                'category'     => 'conduct',
                'severity'     => 'high',
                'submitted_by' => 'Anonymous Client',
            ])->assertRedirect();

        $this->assertDatabaseHas('complaints', [
            'submitted_by' => 'Anonymous Client',
            'severity'     => 'high',
        ]);
    }

    public function test_complaint_action_creates_record(): void
    {
        $hub       = Hub::first();
        $complaint = Complaint::create([
            'complaint_uid'  => 'CMP-STORE01',
            'submitted_date' => now()->toDateString(),
            'submitted_by'   => 'Test Client',
            'category'       => 'process',
            'severity'       => 'medium',
            'sla_days'       => 14,
            'description'    => 'Complaint for store test',
            'hub_id'         => $hub->id,
            'status'         => 'open',
        ]);

        $this->post(route('complaints.action', $complaint), [
            'note'       => 'Reviewed case files and scheduled follow-up.',
            'new_status' => 'in-progress',
        ])->assertRedirect();

        $this->assertDatabaseHas('complaint_actions', [
            'complaint_id' => $complaint->id,
            'note'         => 'Reviewed case files and scheduled follow-up.',
        ]);
        $this->assertDatabaseHas('complaints', [
            'id'     => $complaint->id,
            'status' => 'in-progress',
        ]);
    }

    // ─── Evidence ────────────────────────────────────────────────

    public function test_evidence_creates_record(): void
    {
        $this->post(route('evidence.store'), [
            'type'    => 'recognition',
            'title'   => 'UNDP Best Practice Award 2025',
            'summary' => 'Recognized as best practice in access to justice.',
            'date'    => now()->subDays(10)->toDateString(),
            'issuer'  => 'UNDP Pakistan',
        ])->assertRedirect();

        $this->assertDatabaseHas('evidence', ['title' => 'UNDP Best Practice Award 2025']);
    }

    public function test_evidence_verify_marks_as_verified(): void
    {
        $evidence = \App\Models\Evidence::create([
            'evidence_uid' => 'EV-STORE01',
            'type'         => 'recognition',
            'title'        => 'Evidence to verify',
            'summary'      => 'Summary',
            'date'         => now()->toDateString(),
            'issuer'       => 'UNDP',
            'verified'     => false,
        ]);

        $this->post(route('evidence.verify', $evidence))->assertRedirect();

        $this->assertDatabaseHas('evidence', ['id' => $evidence->id, 'verified' => 1]);
    }

    // ─── Feedback ────────────────────────────────────────────────

    public function test_feedback_creates_record(): void
    {
        $hub = Hub::first();

        $this->withSession(['active_hub' => $hub->id])
            ->post(route('feedback.store'), [
                'score_overall'     => 4,
                'score_helpfulness' => 5,
                'score_respect'     => 5,
                'channel'           => 'in-person',
            ])->assertRedirect();

        $this->assertDatabaseHas('feedback', [
            'score_overall' => 4,
            'channel'       => 'in-person',
        ]);
    }

    // ─── Staff Training ──────────────────────────────────────────

    public function test_staff_training_log_creates_record(): void
    {
        $staff = Staff::factory()->create(['hub_id' => Hub::first()->id, 'status' => 'active']);

        $this->post(route('staff.training', $staff), [
            'training_code' => 'SOP-CORE',
            'completed_on'  => now()->subDays(5)->toDateString(),
            'delivered_by'  => 'UNDP Facilitator',
        ])->assertRedirect();

        $this->assertDatabaseHas('staff_trainings', [
            'staff_id'    => $staff->id,
            'delivered_by' => 'UNDP Facilitator',
        ]);
    }

    // ─── Learning ────────────────────────────────────────────────

    public function test_reflection_creates_record(): void
    {
        $this->post(route('learning.reflection'), [
            'title'        => 'Monthly Team Reflection — April',
            'date'         => now()->toDateString(),
            'description'  => 'We discussed key challenges in case intake.',
            'key_learning' => 'Early risk screening reduces SLA breaches.',
        ])->assertRedirect();

        $this->assertDatabaseHas('reflections', ['title' => 'Monthly Team Reflection — April']);
    }

    public function test_case_study_creates_record(): void
    {
        $this->post(route('learning.case-study'), [
            'title'            => 'GBV Survivor — ADR Success',
            'narrative'        => 'Detailed narrative of how mediation resolved the dispute.',
            'impact_statement' => 'Client regained custody and financial support.',
        ])->assertRedirect();

        $this->assertDatabaseHas('case_studies', ['title' => 'GBV Survivor — ADR Success']);
    }

    // ─── Lookup Admin ────────────────────────────────────────────

    public function test_lookup_group_store_creates_group(): void
    {
        $this->post(route('lookups.group.store'), [
            'group_key'   => 'custom.test-category',
            'first_value' => 'option-a',
            'first_label' => 'Option A',
        ])->assertRedirect();

        $this->assertDatabaseHas('lookups', [
            'group_key' => 'custom.test-category',
            'value'     => 'option-a',
        ]);
    }

    public function test_lookup_option_store_adds_to_group(): void
    {
        $this->post(route('lookups.option.store'), [
            'group_key' => 'case.urgency',
            'value'     => 'extreme',
            'label'     => 'Extreme',
        ])->assertRedirect();

        $this->assertDatabaseHas('lookups', [
            'group_key' => 'case.urgency',
            'value'     => 'extreme',
            'label'     => 'Extreme',
        ]);
    }

    public function test_lookup_option_toggle_flips_active_state(): void
    {
        $lookup = \App\Models\Lookup::where('group_key', 'case.urgency')->first();
        $originalState = $lookup->is_active;

        $this->post(route('lookups.option.toggle', $lookup))->assertRedirect();

        $this->assertDatabaseHas('lookups', [
            'id'        => $lookup->id,
            'is_active' => !$originalState,
        ]);
    }

    // ─── Case Mutations ──────────────────────────────────────────

    public function test_case_approve_sets_decision_and_status(): void
    {
        $hub  = Hub::first();
        $case = CaseRecord::create([
            'case_uid'      => 'CL-STORE01',
            'case_ref'      => 'CA-STORE01',
            'hub_id'        => $hub->id,
            'name'          => 'Store Test Client',
            'gender'        => 'Male',
            'age'           => 35,
            'intake_date'   => now()->toDateString(),
            'status'        => 'Pending Approval',
            'disposition'   => 'adr',
            'urgency'       => 'Medium',
            'risk'          => 'Low',
            'primary_issue' => 'Family Law',
            'consent'       => true,
            'sla_met'       => true,
        ]);

        $this->post(route('cases.approve', $case))->assertRedirect();

        $this->assertDatabaseHas('cases', [
            'id'                => $case->id,
            'approval_decision' => 'approved',
            'status'            => 'Active',
        ]);
    }

    public function test_case_reject_stores_reason(): void
    {
        $hub  = Hub::first();
        $case = CaseRecord::create([
            'case_uid'      => 'CL-STORE02',
            'case_ref'      => 'CA-STORE02',
            'hub_id'        => $hub->id,
            'name'          => 'Rejection Test Client',
            'gender'        => 'Female',
            'age'           => 25,
            'intake_date'   => now()->toDateString(),
            'status'        => 'Pending Approval',
            'disposition'   => 'litigation',
            'urgency'       => 'High',
            'risk'          => 'Medium',
            'primary_issue' => 'Labour',
            'consent'       => true,
            'sla_met'       => false,
        ]);

        $this->post(route('cases.reject', $case), [
            'rejection_reason' => 'Insufficient documentation provided.',
        ])->assertRedirect();

        $this->assertDatabaseHas('cases', [
            'id'                => $case->id,
            'approval_decision' => 'rejected',
            'rejection_reason'  => 'Insufficient documentation provided.',
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    private function validIntakeData(string $hubId): array
    {
        return [
            'hubLocation'       => $hubId,
            'staffReceiving'    => 'Ali Khan',
            'consent'           => 'yes',
            'heardAboutUs'      => 'community',
            'fullName'          => 'Fatima Bibi',
            'fatherHusbandName' => 'Abdul Rehman',
            'gender'            => 'Female',
            'age'               => 32,
            'maritalStatus'     => 'married',
            'religion'          => 'Islam',
            'educationLevel'    => 'primary',
            'monthlyIncome'     => '5000-10000',
            'disabilityStatus'  => 'none',
            'primaryContact'    => '03001234567',
            'tehsil'            => 'Hyderabad',
            'district'          => 'Hyderabad',
            'preferredLanguage' => 'Sindhi',
            'category'          => 'Family Law',
            'urgencyLevel'      => 'Medium',
            'assignedPathway'   => 'advice-only',
        ];
    }
}
