<?php

namespace Tests\Feature\JusticeHub;

use App\Models\CaseRecord;
use App\Models\Complaint;
use App\Models\Hub;
use App\Models\Staff;
use Database\Seeders\LookupSeeder;
use Database\Seeders\TrainingSeeder;
use Tests\TestCase;

/**
 * Verifies validation rules on every store endpoint:
 *   - invalid / missing data → session errors (web redirect back)
 *   - whitelist violations → session errors
 *   - date constraints (before_or_equal:today, after:X) → session errors
 */
class FormValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LookupSeeder::class);
        $this->seed(TrainingSeeder::class);
        $this->asHead();
    }

    // ─── Intake ──────────────────────────────────────────────────

    public function test_intake_requires_name_gender_age_contact(): void
    {
        $this->post(route('intake.store'), [])
            ->assertSessionHasErrors(['fullName', 'gender', 'age', 'primaryContact']);
    }

    public function test_intake_rejects_age_above_max(): void
    {
        $data = array_merge($this->validIntakeData(), ['age' => 150]);
        $this->post(route('intake.store'), $data)
            ->assertSessionHasErrors('age');
    }

    public function test_intake_requires_assigned_pathway(): void
    {
        $data = $this->validIntakeData();
        unset($data['assignedPathway']);
        $this->post(route('intake.store'), $data)
            ->assertSessionHasErrors('assignedPathway');
    }

    // ─── Outreach ────────────────────────────────────────────────

    public function test_outreach_rejects_future_date(): void
    {
        $hub = Hub::first();
        $this->post(route('outreach.store'), [
            'date'               => now()->addDays(5)->toDateString(),
            'hub_id'             => $hub->id,
            'type'               => 'awareness',
            'location'           => 'Community Hall',
            'facilitator'        => 'Ali Khan',
            'total_participants' => 30,
        ])->assertSessionHasErrors('date');
    }

    public function test_outreach_requires_hub_id(): void
    {
        $this->post(route('outreach.store'), [
            'date'               => now()->toDateString(),
            'type'               => 'awareness',
            'location'           => 'Community Hall',
            'facilitator'        => 'Ali Khan',
            'total_participants' => 30,
        ])->assertSessionHasErrors('hub_id');
    }

    public function test_outreach_rejects_nonexistent_hub(): void
    {
        $this->post(route('outreach.store'), [
            'date'               => now()->toDateString(),
            'hub_id'             => 'JH-FAKE-99',
            'type'               => 'awareness',
            'location'           => 'Community Hall',
            'facilitator'        => 'Ali Khan',
            'total_participants' => 30,
        ])->assertSessionHasErrors('hub_id');
    }

    // ─── Complaint ───────────────────────────────────────────────

    public function test_complaint_requires_description(): void
    {
        $this->post(route('complaints.store'), [
            'category'     => 'process',
            'severity'     => 'medium',
            'submitted_by' => 'Test User',
        ])->assertSessionHasErrors('description');
    }

    public function test_complaint_rejects_description_over_max(): void
    {
        $this->post(route('complaints.store'), [
            'description'  => str_repeat('a', 2001),
            'category'     => 'process',
            'severity'     => 'medium',
            'submitted_by' => 'Test User',
        ])->assertSessionHasErrors('description');
    }

    public function test_complaint_rejects_invalid_severity(): void
    {
        $this->post(route('complaints.store'), [
            'description'  => 'Test complaint.',
            'category'     => 'process',
            'severity'     => 'urgent',           // not in whitelist
            'submitted_by' => 'Test User',
        ])->assertSessionHasErrors('severity');
    }

    public function test_complaint_rejects_invalid_channel(): void
    {
        $this->post(route('complaints.store'), [
            'description'  => 'Test complaint.',
            'category'     => 'process',
            'severity'     => 'medium',
            'submitted_by' => 'Test User',
            'channel'      => 'email',             // not in whitelist
        ])->assertSessionHasErrors('channel');
    }

    public function test_complaint_action_requires_note(): void
    {
        $hub       = Hub::first();
        $complaint = Complaint::create([
            'complaint_uid'  => 'CMP-VAL01',
            'submitted_date' => now()->toDateString(),
            'submitted_by'   => 'Test User',
            'category'       => 'process',
            'severity'       => 'medium',
            'sla_days'       => 14,
            'description'    => 'Complaint for validation test',
            'hub_id'         => $hub->id,
            'status'         => 'open',
        ]);

        $this->post(route('complaints.action', $complaint), [])
            ->assertSessionHasErrors('note');
    }

    public function test_complaint_action_rejects_invalid_status(): void
    {
        $hub       = Hub::first();
        $complaint = Complaint::create([
            'complaint_uid'  => 'CMP-VAL02',
            'submitted_date' => now()->toDateString(),
            'submitted_by'   => 'Test User',
            'category'       => 'process',
            'severity'       => 'medium',
            'sla_days'       => 14,
            'description'    => 'Complaint for status validation',
            'hub_id'         => $hub->id,
            'status'         => 'open',
        ]);

        $this->post(route('complaints.action', $complaint), [
            'note'       => 'Update note',
            'new_status' => 'closed',    // not in whitelist (should be 'resolved')
        ])->assertSessionHasErrors('new_status');
    }

    // ─── Evidence ────────────────────────────────────────────────

    public function test_evidence_requires_title(): void
    {
        $this->post(route('evidence.store'), [
            'type'    => 'recognition',
            'summary' => 'A summary',
            'date'    => now()->toDateString(),
            'issuer'  => 'UNDP',
        ])->assertSessionHasErrors('title');
    }

    public function test_evidence_rejects_invalid_type(): void
    {
        $this->post(route('evidence.store'), [
            'type'    => 'certificate',   // not in whitelist
            'title'   => 'Test Evidence',
            'summary' => 'A summary',
            'date'    => now()->toDateString(),
            'issuer'  => 'UNDP',
        ])->assertSessionHasErrors('type');
    }

    public function test_evidence_rejects_future_date(): void
    {
        $this->post(route('evidence.store'), [
            'type'    => 'recognition',
            'title'   => 'Test Evidence',
            'summary' => 'A summary',
            'date'    => now()->addDay()->toDateString(),
            'issuer'  => 'UNDP',
        ])->assertSessionHasErrors('date');
    }

    // ─── Feedback ────────────────────────────────────────────────

    public function test_feedback_requires_all_three_scores(): void
    {
        $this->post(route('feedback.store'), [
            'channel' => 'in-person',
        ])->assertSessionHasErrors(['score_overall', 'score_helpfulness', 'score_respect']);
    }

    public function test_feedback_rejects_score_above_5(): void
    {
        $this->post(route('feedback.store'), [
            'score_overall'     => 6,
            'score_helpfulness' => 5,
            'score_respect'     => 5,
            'channel'           => 'in-person',
        ])->assertSessionHasErrors('score_overall');
    }

    public function test_feedback_rejects_score_below_1(): void
    {
        $this->post(route('feedback.store'), [
            'score_overall'     => 0,
            'score_helpfulness' => 1,
            'score_respect'     => 1,
            'channel'           => 'in-person',
        ])->assertSessionHasErrors('score_overall');
    }

    public function test_feedback_rejects_invalid_channel(): void
    {
        $this->post(route('feedback.store'), [
            'score_overall'     => 5,
            'score_helpfulness' => 5,
            'score_respect'     => 5,
            'channel'           => 'email',   // not in whitelist
        ])->assertSessionHasErrors('channel');
    }

    // ─── Staff Training ──────────────────────────────────────────

    public function test_training_rejects_future_completed_on(): void
    {
        $staff = Staff::factory()->create(['hub_id' => Hub::first()->id, 'status' => 'active']);

        $this->post(route('staff.training', $staff), [
            'training_code' => 'SOP-CORE',
            'completed_on'  => now()->addDays(3)->toDateString(),
            'delivered_by'  => 'Trainer',
        ])->assertSessionHasErrors('completed_on');
    }

    public function test_training_rejects_expiry_before_completion(): void
    {
        $staff = Staff::factory()->create(['hub_id' => Hub::first()->id, 'status' => 'active']);

        $this->post(route('staff.training', $staff), [
            'training_code' => 'SOP-CORE',
            'completed_on'  => now()->toDateString(),
            'delivered_by'  => 'Trainer',
            'expires'       => now()->subDay()->toDateString(),   // must be after completed_on
        ])->assertSessionHasErrors('expires');
    }

    public function test_training_rejects_unknown_code(): void
    {
        $staff = Staff::factory()->create(['hub_id' => Hub::first()->id, 'status' => 'active']);

        $this->post(route('staff.training', $staff), [
            'training_code' => 'FAKE-COURSE',
            'completed_on'  => now()->toDateString(),
            'delivered_by'  => 'Trainer',
        ])->assertSessionHasErrors('training_code');
    }

    // ─── Impact Export ───────────────────────────────────────────

    public function test_impact_export_rejects_invalid_period(): void
    {
        $this->post(route('impact.export'), [
            'period'   => 'Year',            // not in whitelist
            'template' => 'program-overview',
        ])->assertSessionHasErrors('period');
    }

    public function test_impact_export_rejects_invalid_template(): void
    {
        $this->post(route('impact.export'), [
            'period'   => 'Q1',
            'template' => 'generic-report',   // not in whitelist
        ])->assertSessionHasErrors('template');
    }

    // ─── Learning ────────────────────────────────────────────────

    public function test_reflection_requires_title_description_key_learning(): void
    {
        $this->post(route('learning.reflection'), [
            'date' => now()->toDateString(),
        ])->assertSessionHasErrors(['title', 'description', 'key_learning']);
    }

    public function test_reflection_rejects_future_date(): void
    {
        $this->post(route('learning.reflection'), [
            'title'        => 'Test Reflection',
            'date'         => now()->addDays(2)->toDateString(),
            'description'  => 'Insights from the month',
            'key_learning' => 'Collaboration matters',
        ])->assertSessionHasErrors('date');
    }

    public function test_case_study_rejects_invalid_replication_potential(): void
    {
        $this->post(route('learning.case-study'), [
            'title'                 => 'Case Study',
            'narrative'             => 'The narrative text',
            'impact_statement'      => 'Positive impact',
            'replication_potential' => 'very-high',   // not in whitelist
        ])->assertSessionHasErrors('replication_potential');
    }

    public function test_case_study_requires_title_and_narrative(): void
    {
        $this->post(route('learning.case-study'), [])
            ->assertSessionHasErrors(['title', 'narrative', 'impact_statement']);
    }

    // ─── Lookup Admin ────────────────────────────────────────────

    public function test_lookup_group_rejects_invalid_key_characters(): void
    {
        $this->post(route('lookups.group.store'), [
            'group_key'   => 'My Group Key!',   // spaces/special chars not allowed
            'first_value' => 'test',
            'first_label' => 'Test',
        ])->assertSessionHasErrors('group_key');
    }

    public function test_lookup_group_rejects_duplicate_key(): void
    {
        $this->post(route('lookups.group.store'), [
            'group_key'   => 'case.urgency',   // already exists from LookupSeeder
            'first_value' => 'duplicate',
            'first_label' => 'Duplicate',
        ])->assertSessionHasErrors('group_key');
    }

    public function test_lookup_option_requires_group_key_and_value(): void
    {
        $this->post(route('lookups.option.store'), [
            'label' => 'Orphan Label',
        ])->assertSessionHasErrors(['group_key', 'value']);
    }

    public function test_lookup_reorder_requires_valid_ids(): void
    {
        $this->post(route('lookups.reorder'), [
            'group_key' => 'case.urgency',
            'order'     => [
                ['id' => 999999, 'sort_order' => 0],   // non-existent lookup id
            ],
        ])->assertSessionHasErrors('order.0.id');
    }

    // ─── Case Approve/Reject ─────────────────────────────────────

    public function test_case_reject_requires_rejection_reason(): void
    {
        $hub  = Hub::first();
        $case = CaseRecord::create([
            'case_uid'      => 'CL-VAL01',
            'case_ref'      => 'CA-VAL01',
            'hub_id'        => $hub->id,
            'name'          => 'Validation Test',
            'gender'        => 'Female',
            'age'           => 28,
            'intake_date'   => now()->toDateString(),
            'status'        => 'Pending Approval',
            'disposition'   => 'adr',
            'urgency'       => 'Medium',
            'risk'          => 'Low',
            'primary_issue' => 'Family Law',
            'consent'       => true,
            'sla_met'       => true,
        ]);

        $this->post(route('cases.reject', $case), [])
            ->assertSessionHasErrors('rejection_reason');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    private function validIntakeData(): array
    {
        $hub = Hub::first();
        return [
            'hubLocation'       => $hub->id,
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
