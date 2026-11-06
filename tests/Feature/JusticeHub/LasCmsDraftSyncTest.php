<?php

namespace Tests\Feature\JusticeHub;

use App\Enums\UserRole;
use App\Models\CaseRecord;
use App\Models\Hub;
use App\Models\User;
use App\Services\LasCmsSyncService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LasCmsDraftSyncTest extends TestCase
{
    public function test_draft_interview_is_saved_and_later_resolved_to_program_id(): void
    {
        config([
            'services.las_cms.url' => 'https://cms.example.test/api/justice-hub',
            'services.las_cms.key' => 'integration-key',
        ]);

        $hub = Hub::firstOrFail();
        $lawyer = User::factory()->create([
            'name' => 'Simran',
            'email' => 'simran@example.test',
            'role' => UserRole::Lawyer,
            'hub_id' => $hub->id,
        ]);
        $case = CaseRecord::create([
            'case_uid' => 'LAS-Hyderabad-2041',
            'case_ref' => 'REF-HYD-2041',
            'hub_id' => $hub->id,
            'assigned_to' => $lawyer->name,
            'assigned_pathway' => 'Court Representation',
            'name' => 'Integration Client',
            'father_husband_name' => 'Integration Parent',
            'gender' => 'Female',
            'age' => 30,
            'cnic' => '4130427496798',
            'primary_contact' => '03001234567',
            'district' => 'Hyderabad',
            'intake_date' => '2026-11-01',
            'primary_issue' => 'Family Law',
            'issue_description' => 'Integration case facts',
            'urgency' => 'Medium',
            'status' => 'Active',
            'disposition' => 'litigation',
            'risk' => 'Low',
            'consent' => true,
            'sla_met' => true,
        ]);

        Http::fake([
            'https://cms.example.test/api/justice-hub/cases' => Http::response([
                'success' => true,
                'interview_id' => 1027,
                'status' => 'draft',
                'message' => 'Draft interview created. Lawyer can complete and submit.',
            ], 201),
        ]);

        $service = app(LasCmsSyncService::class);
        $this->assertNull($service->pushCase($case));
        $case->refresh();
        $this->assertSame(1027, (int) $case->jh_cms_interview_id);
        $this->assertSame('draft', $case->jh_cms_interview_status);
        $this->assertNull($case->external_case_id);
        $this->assertSame('draft', data_get($case->meta, 'las_link_status'));

        $unlinked = $case->replicate([
            'case_uid',
            'case_ref',
            'jh_cms_interview_id',
            'jh_cms_interview_status',
            'external_synced_at',
            'meta',
        ]);
        $unlinked->case_uid = 'LAS-Hyderabad-2042';
        $unlinked->case_ref = 'REF-HYD-2042';
        $unlinked->name = 'Unlinked Integration Client';
        $unlinked->save();

        $this->asHead();
        $this->get(route('cases.index', ['cms_link' => 'connected']))
            ->assertOk()
            ->assertSee('Integration Client')
            ->assertDontSee('Unlinked Integration Client');

        Http::assertSent(function ($request) use ($case, $lawyer) {
            return $request->url() === 'https://cms.example.test/api/justice-hub/cases'
                && $request['justicehub_case_uid'] === $case->case_uid
                && $request['lawyerEmail'] === $lawyer->email;
        });

        Http::fake([
            'https://cms.example.test/api/justice-hub/cases/source/LAS-Hyderabad-2041' => Http::response([
                'success' => true,
                'justicehub_case_uid' => 'LAS-Hyderabad-2041',
                'interview_id' => 1027,
                'status' => 'converted',
                'program_id' => 3712,
            ]),
        ]);

        $this->assertSame(3712, $service->resolveDraft($case));
        $case->refresh();
        $this->assertSame(3712, (int) $case->external_case_id);
        $this->assertSame('converted', $case->jh_cms_interview_status);
        $this->assertSame('verified', data_get($case->meta, 'las_link_status'));
    }
}
