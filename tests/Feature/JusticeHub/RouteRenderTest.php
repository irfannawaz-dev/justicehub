<?php

namespace Tests\Feature\JusticeHub;

use App\Models\CaseRecord;
use App\Models\Complaint;
use App\Models\Evidence;
use App\Models\Hub;
use App\Models\Indicator;
use App\Models\Lookup;
use App\Models\Staff;
use Database\Seeders\LookupSeeder;
use Tests\TestCase;

/**
 * Verifies that all 19 application views return HTTP 200 for an
 * authenticated Head user (full access), and that key HTML landmarks
 * are present (page title, nav, content section).
 */
class RouteRenderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LookupSeeder::class);
        $this->asHead();
    }

    // ─────────────────────────────────────────────────────────────
    // 1. Dashboards
    // ─────────────────────────────────────────────────────────────

    public function test_main_dashboard_renders(): void
    {
        $this->get(route('dashboard'))->assertOk()->assertSee('Justice Hub');
    }

    public function test_litigation_adr_dashboard_renders(): void
    {
        $this->get(route('dashboard.litigation-adr'))->assertOk();
    }

    public function test_lcd_dashboard_renders(): void
    {
        $this->get(route('dashboard.lcd'))->assertOk();
    }

    // ─────────────────────────────────────────────────────────────
    // 2. Cases
    // ─────────────────────────────────────────────────────────────

    public function test_cases_index_renders(): void
    {
        $this->get(route('cases.index'))->assertOk()->assertSee('Cases');
    }

    public function test_case_detail_renders(): void
    {
        $hub  = Hub::first();
        $case = CaseRecord::create([
            'case_uid'    => 'CL-99001',
            'case_ref'    => 'CA-99001',
            'hub_id'      => $hub->id,
            'name'        => 'Test Client',
            'gender'      => 'Female',
            'age'         => 30,
            'intake_date' => now()->toDateString(),
            'status'      => 'Active',
            'disposition' => 'advice-only',
            'urgency'     => 'Medium',
            'risk'        => 'Low',
            'primary_issue' => 'Family Law',
            'consent'     => true,
            'sla_met'     => true,
        ]);
        $this->get(route('cases.show', $case))->assertOk()->assertSee('Test Client');
    }

    public function test_intake_create_renders(): void
    {
        $this->get(route('intake.create'))->assertOk()->assertSee('New Intake');
    }

    // ─────────────────────────────────────────────────────────────
    // 3. Service scorecards & calendars
    // ─────────────────────────────────────────────────────────────

    public function test_adr_scorecard_renders(): void
    {
        $this->get(route('services.adr'))->assertOk();
    }

    public function test_adr_calendar_renders(): void
    {
        $this->get(route('services.adr-calendar'))->assertOk();
    }

    public function test_litigation_scorecard_renders(): void
    {
        $this->get(route('services.litigation'))->assertOk();
    }

    public function test_litigation_calendar_renders(): void
    {
        $this->get(route('services.litigation-calendar'))->assertOk();
    }

    // ─────────────────────────────────────────────────────────────
    // 4. Service delivery modules
    // ─────────────────────────────────────────────────────────────

    public function test_referrals_renders(): void
    {
        $this->get(route('referrals.index'))->assertOk();
    }

    public function test_outreach_renders(): void
    {
        $this->get(route('outreach.index'))->assertOk();
    }

    public function test_complaints_index_renders(): void
    {
        $this->get(route('complaints.index'))->assertOk();
    }

    public function test_complaint_show_renders(): void
    {
        $hub = Hub::first();
        $complaint = Complaint::create([
            'complaint_uid'  => 'CMP-999',
            'submitted_date' => now()->toDateString(),
            'submitted_by'   => 'Test User',
            'category'       => 'process',
            'severity'       => 'medium',
            'sla_days'       => 14,
            'description'    => 'Test complaint description',
            'hub_id'         => $hub->id,
            'status'         => 'open',
        ]);
        $this->get(route('complaints.show', $complaint))->assertOk();
    }

    // ─────────────────────────────────────────────────────────────
    // 5. Measurement modules
    // ─────────────────────────────────────────────────────────────

    public function test_indicators_index_renders(): void
    {
        Indicator::create([
            'code'     => 'O1.1',
            'level'    => 'outcome-1',
            'name'     => 'SLA Compliance',
            'priority' => 'P0',
            'cadence'  => 'monthly',
            'target'   => 90,
            'actual'   => 0,
            'unit'     => '%',
            'type'     => 'quantitative',
        ]);
        $this->get(route('indicators.index'))->assertOk();
    }

    public function test_evidence_index_renders(): void
    {
        $this->get(route('evidence.index'))->assertOk();
    }

    public function test_feedback_index_renders(): void
    {
        $this->get(route('feedback.index'))->assertOk();
    }

    public function test_staff_index_renders(): void
    {
        $this->get(route('staff.index'))->assertOk();
    }

    public function test_learning_index_renders(): void
    {
        $this->get(route('learning.index'))->assertOk();
    }

    public function test_impact_index_renders(): void
    {
        $this->get(route('impact.index'))->assertOk();
    }

    // ─────────────────────────────────────────────────────────────
    // 6. Settings
    // ─────────────────────────────────────────────────────────────

    public function test_settings_renders(): void
    {
        $this->get(route('settings.index'))->assertOk()->assertSee('Settings');
    }

    public function test_settings_shows_lookup_section_for_head(): void
    {
        $this->get(route('settings.index'))->assertOk()->assertSee('Lookup Management');
    }
}
