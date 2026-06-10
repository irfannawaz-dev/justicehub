<?php

namespace Tests\Feature\JusticeHub;

use App\Models\CaseRecord;
use App\Models\Complaint;
use App\Models\Evidence;
use App\Models\Hub;
use App\Models\Staff;
use App\Models\Training;
use Database\Seeders\LookupSeeder;
use Tests\TestCase;

/**
 * Verifies role-based access control:
 *
 *   Head          → full access everywhere
 *   Hub Admin     → own hub only, can approve, cannot manage lookups
 *   Data Entry    → own hub only, no settings, no approval
 *   M&E Lead      → all hubs read, can verify evidence, cannot approve cases
 *   Comp. Invest. → complaints only write, cannot approve/verify
 *   Viewer        → read-only everywhere, all POST/mutation routes → 403
 */
class RoleAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LookupSeeder::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function makeCase(string $hubId): CaseRecord
    {
        return CaseRecord::create([
            'case_uid'      => 'CL-' . rand(10000, 99999),
            'case_ref'      => 'CA-' . rand(10000, 99999),
            'hub_id'        => $hubId,
            'name'          => 'Test Client',
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
    }

    private function makeComplaint(string $hubId): Complaint
    {
        return Complaint::create([
            'complaint_uid'  => 'CMP-' . rand(100, 999),
            'submitted_date' => now()->toDateString(),
            'submitted_by'   => 'Test User',
            'category'       => 'process',
            'severity'       => 'medium',
            'sla_days'       => 14,
            'description'    => 'Test complaint',
            'hub_id'         => $hubId,
            'status'         => 'open',
        ]);
    }

    private function makeEvidence(): Evidence
    {
        return Evidence::create([
            'evidence_uid' => 'EV-' . rand(100, 999),
            'type'         => 'recognition',
            'title'        => 'Test evidence',
            'summary'      => 'Summary',
            'date'         => now()->toDateString(),
            'issuer'       => 'UNDP',
            'verified'     => false,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // HEAD — full access
    // ─────────────────────────────────────────────────────────────

    public function test_head_can_access_all_GET_routes(): void
    {
        $this->asHead();
        $hub  = Hub::first();
        $case = $this->makeCase($hub->id);

        $routes = [
            'dashboard', 'dashboard.litigation-adr', 'dashboard.lcd',
            'cases.index', 'intake.create',
            'services.adr', 'services.adr-calendar',
            'services.litigation', 'services.litigation-calendar',
            'referrals.index', 'outreach.index', 'complaints.index',
            'indicators.index', 'evidence.index', 'feedback.index',
            'staff.index', 'learning.index', 'impact.index',
            'settings.index',
        ];

        foreach ($routes as $route) {
            $this->get(route($route))->assertOk("Route [{$route}] should return 200 for Head");
        }

        $this->get(route('cases.show', $case))->assertOk();
    }

    public function test_head_can_approve_cases(): void
    {
        $this->asHead();
        $hub  = Hub::first();
        $case = $this->makeCase($hub->id);

        $this->post(route('cases.approve', $case))->assertRedirect();
        $this->assertDatabaseHas('cases', ['id' => $case->id, 'approval_decision' => 'approved']);
    }

    public function test_head_can_see_lookup_management(): void
    {
        $this->asHead();
        $this->get(route('settings.index'))->assertOk()->assertSee('Lookup Management');
    }

    // ─────────────────────────────────────────────────────────────
    // HUB ADMIN
    // ─────────────────────────────────────────────────────────────

    public function test_hub_admin_can_access_standard_views(): void
    {
        $this->asHubAdmin();

        $routes = [
            'dashboard', 'cases.index', 'complaints.index',
            'outreach.index', 'feedback.index', 'settings.index',
        ];
        foreach ($routes as $route) {
            $this->get(route($route))->assertOk("Route [{$route}] failed for Hub Admin");
        }
    }

    public function test_hub_admin_can_approve_cases(): void
    {
        $hub  = Hub::first();
        $this->asHubAdmin($hub->id);
        $case = $this->makeCase($hub->id);

        $this->post(route('cases.approve', $case))->assertRedirect();
    }

    public function test_hub_admin_cannot_manage_lookups(): void
    {
        $hub = Hub::first();
        $this->asHubAdmin($hub->id);

        $this->get(route('settings.index'))->assertOk()->assertDontSee('Lookup Management');
    }

    // ─────────────────────────────────────────────────────────────
    // DATA ENTRY
    // ─────────────────────────────────────────────────────────────

    public function test_data_entry_can_view_cases_and_create_intake(): void
    {
        $this->asDataEntry();

        $this->get(route('cases.index'))->assertOk();
        $this->get(route('intake.create'))->assertOk();
        $this->get(route('outreach.index'))->assertOk();
        $this->get(route('feedback.index'))->assertOk();
    }

    public function test_data_entry_cannot_approve_cases(): void
    {
        $hub  = Hub::first();
        $this->asDataEntry($hub->id);
        $case = $this->makeCase($hub->id);

        $this->post(route('cases.approve', $case))->assertForbidden();
    }

    public function test_data_entry_cannot_verify_evidence(): void
    {
        $this->asDataEntry();
        $evidence = $this->makeEvidence();

        $this->post(route('evidence.verify', $evidence))->assertForbidden();
    }

    // ─────────────────────────────────────────────────────────────
    // M&E LEAD
    // ─────────────────────────────────────────────────────────────

    public function test_me_lead_can_access_measurement_views(): void
    {
        $this->asMELead();

        $routes = ['indicators.index', 'evidence.index', 'feedback.index', 'staff.index', 'impact.index'];
        foreach ($routes as $route) {
            $this->get(route($route))->assertOk("Route [{$route}] failed for M&E Lead");
        }
    }

    public function test_me_lead_can_verify_evidence(): void
    {
        $this->asMELead();
        $evidence = $this->makeEvidence();

        $this->post(route('evidence.verify', $evidence))->assertRedirect();
        $this->assertDatabaseHas('evidence', ['id' => $evidence->id, 'verified' => 1]);
    }

    public function test_me_lead_cannot_approve_cases(): void
    {
        $hub  = Hub::first();
        $this->asMELead();
        $case = $this->makeCase($hub->id);

        $this->post(route('cases.approve', $case))->assertForbidden();
    }

    // ─────────────────────────────────────────────────────────────
    // COMPLAINT INVESTIGATOR
    // ─────────────────────────────────────────────────────────────

    public function test_complaint_investigator_can_add_complaint_action(): void
    {
        $hub       = Hub::first();
        $this->asComplaintInvestigator($hub->id);
        $complaint = $this->makeComplaint($hub->id);

        $this->post(route('complaints.action', $complaint), [
            'note'       => 'Initial investigation started.',
            'new_status' => 'in-progress',
        ])->assertRedirect();

        $this->assertDatabaseHas('complaint_actions', [
            'complaint_id' => $complaint->id,
            'note'         => 'Initial investigation started.',
        ]);
    }

    public function test_complaint_investigator_cannot_approve_cases(): void
    {
        $hub  = Hub::first();
        $this->asComplaintInvestigator($hub->id);
        $case = $this->makeCase($hub->id);

        $this->post(route('cases.approve', $case))->assertForbidden();
    }

    public function test_complaint_investigator_cannot_verify_evidence(): void
    {
        $this->asComplaintInvestigator();
        $evidence = $this->makeEvidence();

        $this->post(route('evidence.verify', $evidence))->assertForbidden();
    }

    // ─────────────────────────────────────────────────────────────
    // VIEWER — read-only everywhere
    // ─────────────────────────────────────────────────────────────

    public function test_viewer_can_read_all_views(): void
    {
        $this->asViewer();
        $hub = Hub::first();

        $routes = [
            'cases.index', 'complaints.index', 'outreach.index',
            'feedback.index', 'indicators.index', 'evidence.index',
            'staff.index', 'impact.index',
        ];
        foreach ($routes as $route) {
            $this->get(route($route))->assertOk("Viewer should be able to GET [{$route}]");
        }
    }

    public function test_viewer_cannot_approve_cases(): void
    {
        $hub  = Hub::first();
        $this->asViewer();
        $case = $this->makeCase($hub->id);

        $this->post(route('cases.approve', $case))->assertForbidden();
    }

    public function test_viewer_cannot_log_training(): void
    {
        $this->asViewer();
        $staff = Staff::factory()->create(['hub_id' => Hub::first()->id, 'status' => 'active']);

        $this->post(route('staff.training', $staff), [
            'training_code' => 'SOP-CORE',
            'completed_on'  => now()->toDateString(),
            'delivered_by'  => 'Trainer',
        ])->assertForbidden();
    }

    public function test_viewer_cannot_add_complaint_action(): void
    {
        $hub       = Hub::first();
        $this->asViewer();
        $complaint = $this->makeComplaint($hub->id);

        $this->post(route('complaints.action', $complaint), ['note' => 'test'])->assertForbidden();
    }

    public function test_viewer_cannot_verify_evidence(): void
    {
        $this->asViewer();
        $evidence = $this->makeEvidence();

        $this->post(route('evidence.verify', $evidence))->assertForbidden();
    }

    public function test_viewer_cannot_export_impact_report(): void
    {
        $this->asViewer();

        $this->post(route('impact.export'), [
            'period'   => 'Q1',
            'template' => 'program-overview',
        ])->assertForbidden();
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('cases.index'))->assertRedirect(route('login'));
    }
}
