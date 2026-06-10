<?php

namespace Tests\Feature\JusticeHub;

use App\Models\CaseRecord;
use App\Models\Complaint;
use App\Models\Evidence;
use App\Models\Feedback;
use App\Models\Hub;
use Database\Seeders\LookupSeeder;
use Tests\TestCase;

/**
 * Verifies that filter/search parameters on index routes
 * correctly scope the results shown in the response.
 *
 * Strategy: create two records with distinct, identifiable
 * values; apply a filter that matches only one; assert
 * the matching record's UID is visible and the other is not.
 */
class FilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LookupSeeder::class);
        $this->asHead();
    }

    // ─── Cases ───────────────────────────────────────────────────

    public function test_cases_filter_by_disposition_litigation(): void
    {
        $hub   = Hub::first();
        $caseA = $this->makeCase($hub->id, ['case_uid' => 'CL-FILT01', 'disposition' => 'litigation']);
        $caseB = $this->makeCase($hub->id, ['case_uid' => 'CL-FILT02', 'disposition' => 'adr']);

        $this->get(route('cases.index', ['disposition' => 'litigation']))
            ->assertOk()
            ->assertSee('CL-FILT01')
            ->assertDontSee('CL-FILT02');
    }

    public function test_cases_filter_by_disposition_adr(): void
    {
        $hub   = Hub::first();
        $caseA = $this->makeCase($hub->id, ['case_uid' => 'CL-FILT03', 'disposition' => 'advice-only']);
        $caseB = $this->makeCase($hub->id, ['case_uid' => 'CL-FILT04', 'disposition' => 'adr']);

        $this->get(route('cases.index', ['disposition' => 'adr']))
            ->assertOk()
            ->assertSee('CL-FILT04')
            ->assertDontSee('CL-FILT03');
    }

    public function test_cases_filter_by_status_active(): void
    {
        $hub   = Hub::first();
        $caseA = $this->makeCase($hub->id, ['case_uid' => 'CL-FILT05', 'status' => 'Active']);
        $caseB = $this->makeCase($hub->id, ['case_uid' => 'CL-FILT06', 'status' => 'Closed']);

        $this->get(route('cases.index', ['status' => 'active']))
            ->assertOk()
            ->assertSee('CL-FILT05')
            ->assertDontSee('CL-FILT06');
    }

    public function test_cases_search_by_name(): void
    {
        $hub = Hub::first();
        $this->makeCase($hub->id, ['case_uid' => 'CL-FILT07', 'name' => 'Zubaida Sultana Alpha']);
        $this->makeCase($hub->id, ['case_uid' => 'CL-FILT08', 'name' => 'Rahim Beta Client']);

        $this->get(route('cases.index', ['search' => 'Zubaida Sultana Alpha']))
            ->assertOk()
            ->assertSee('CL-FILT07')
            ->assertDontSee('CL-FILT08');
    }

    public function test_cases_search_by_case_uid(): void
    {
        $hub = Hub::first();
        $this->makeCase($hub->id, ['case_uid' => 'CL-SRCH01']);
        $this->makeCase($hub->id, ['case_uid' => 'CL-SRCH02']);

        $this->get(route('cases.index', ['search' => 'CL-SRCH01']))
            ->assertOk()
            ->assertSee('CL-SRCH01')
            ->assertDontSee('CL-SRCH02');
    }

    public function test_cases_no_filter_returns_all(): void
    {
        $hub = Hub::first();
        $this->makeCase($hub->id, ['case_uid' => 'CL-ALL01']);
        $this->makeCase($hub->id, ['case_uid' => 'CL-ALL02']);

        $this->get(route('cases.index'))
            ->assertOk()
            ->assertSee('CL-ALL01')
            ->assertSee('CL-ALL02');
    }

    // ─── Evidence ────────────────────────────────────────────────

    public function test_evidence_filter_by_type_recognition(): void
    {
        Evidence::create([
            'evidence_uid' => 'EV-FILT01',
            'type'         => 'recognition',
            'title'        => 'Award Alpha Recognition',
            'summary'      => 'Summary A',
            'date'         => now()->toDateString(),
            'issuer'       => 'UNDP',
            'verified'     => false,
        ]);
        Evidence::create([
            'evidence_uid' => 'EV-FILT02',
            'type'         => 'replication',
            'title'        => 'Replication Beta Initiative',
            'summary'      => 'Summary B',
            'date'         => now()->toDateString(),
            'issuer'       => 'UNICEF',
            'verified'     => false,
        ]);

        $this->get(route('evidence.index', ['type' => 'recognition']))
            ->assertOk()
            ->assertSee('Award Alpha Recognition')
            ->assertDontSee('Replication Beta Initiative');
    }

    public function test_evidence_filter_by_verified_shows_only_verified(): void
    {
        Evidence::create([
            'evidence_uid' => 'EV-FILT03',
            'type'         => 'recognition',
            'title'        => 'Verified Evidence Title',
            'summary'      => 'Summary',
            'date'         => now()->toDateString(),
            'issuer'       => 'UNDP',
            'verified'     => true,
        ]);
        Evidence::create([
            'evidence_uid' => 'EV-FILT04',
            'type'         => 'recognition',
            'title'        => 'Unverified Evidence Title',
            'summary'      => 'Summary',
            'date'         => now()->toDateString(),
            'issuer'       => 'UNDP',
            'verified'     => false,
        ]);

        $this->get(route('evidence.index', ['verified' => '1']))
            ->assertOk()
            ->assertSee('Verified Evidence Title')
            ->assertDontSee('Unverified Evidence Title');
    }

    public function test_evidence_no_filter_returns_all_types(): void
    {
        Evidence::create([
            'evidence_uid' => 'EV-FILT05',
            'type'         => 'policy-citation',
            'title'        => 'Policy Citation Alpha',
            'summary'      => 'Summary',
            'date'         => now()->toDateString(),
            'issuer'       => 'UNDP',
            'verified'     => false,
        ]);
        Evidence::create([
            'evidence_uid' => 'EV-FILT06',
            'type'         => 'integration',
            'title'        => 'Integration Beta',
            'summary'      => 'Summary',
            'date'         => now()->toDateString(),
            'issuer'       => 'UNICEF',
            'verified'     => false,
        ]);

        $this->get(route('evidence.index'))
            ->assertOk()
            ->assertSee('Policy Citation Alpha')
            ->assertSee('Integration Beta');
    }

    // ─── Feedback ────────────────────────────────────────────────

    public function test_feedback_filter_by_channel_phone(): void
    {
        $hub = Hub::first();

        Feedback::create([
            'feedback_uid'      => 'FB-FILT01',
            'channel'           => 'phone',
            'score_overall'     => 4,
            'score_helpfulness' => 4,
            'score_respect'     => 4,
            'hub_id'            => $hub->id,
            'date'              => now()->toDateString(),
        ]);
        Feedback::create([
            'feedback_uid'      => 'FB-FILT02',
            'channel'           => 'sms',
            'score_overall'     => 3,
            'score_helpfulness' => 3,
            'score_respect'     => 3,
            'hub_id'            => $hub->id,
            'date'              => now()->toDateString(),
        ]);

        $this->get(route('feedback.index', ['channel' => 'phone']))
            ->assertOk()
            ->assertSee('FB-FILT01')
            ->assertDontSee('FB-FILT02');
    }

    public function test_feedback_filter_by_channel_digital(): void
    {
        $hub = Hub::first();

        Feedback::create([
            'feedback_uid'      => 'FB-FILT03',
            'channel'           => 'digital',
            'score_overall'     => 5,
            'score_helpfulness' => 5,
            'score_respect'     => 5,
            'hub_id'            => $hub->id,
            'date'              => now()->toDateString(),
        ]);
        Feedback::create([
            'feedback_uid'      => 'FB-FILT04',
            'channel'           => 'in-person',
            'score_overall'     => 4,
            'score_helpfulness' => 4,
            'score_respect'     => 4,
            'hub_id'            => $hub->id,
            'date'              => now()->toDateString(),
        ]);

        $this->get(route('feedback.index', ['channel' => 'digital']))
            ->assertOk()
            ->assertSee('FB-FILT03')
            ->assertDontSee('FB-FILT04');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    private function makeCase(string $hubId, array $overrides = []): CaseRecord
    {
        return CaseRecord::create(array_merge([
            'case_uid'      => 'CL-' . rand(10000, 99999),
            'case_ref'      => 'CA-' . rand(10000, 99999),
            'hub_id'        => $hubId,
            'name'          => 'Filter Test Client',
            'gender'        => 'Female',
            'age'           => 28,
            'intake_date'   => now()->toDateString(),
            'status'        => 'Active',
            'disposition'   => 'adr',
            'urgency'       => 'Medium',
            'risk'          => 'Low',
            'primary_issue' => 'Family Law',
            'consent'       => true,
            'sla_met'       => true,
        ], $overrides));
    }
}
