<?php

namespace Tests\Feature\JusticeHub;

use App\Exports\CasesExport;
use App\Models\CaseRecord;
use App\Models\Hub;
use App\Models\ServiceEncounter;
use App\Services\LasCmsSyncService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LasCmsSyncTest extends TestCase
{
    private string $url = 'https://cms.example.test/api/justice-hub';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.las_cms.url' => $this->url, 'services.las_cms.key' => 'test-key']);
        Http::preventStrayRequests();
    }

    private function makeCase(array $attributes = []): CaseRecord
    {
        return CaseRecord::create(array_merge([
            'case_uid' => 'CMS-' . fake()->unique()->numerify('######'),
            'case_ref' => 'REF-' . fake()->unique()->numerify('######'),
            'hub_id' => Hub::firstOrFail()->id,
            'name' => 'Local Client Name',
            'gender' => 'Female',
            'age' => 30,
            'cnic' => '4220112345671',
            'assigned_pathway' => 'Representation in Court',
            'district' => 'Dadu',
            'intake_date' => '2026-11-01',
            'status' => 'Active',
            'urgency' => 'Medium',
            'risk' => 'Low',
            'primary_issue' => 'Family Law',
            'consent' => true,
            'sla_met' => true,
        ], $attributes));
    }

    public function test_existing_case_matches_by_cnic_even_when_name_differs(): void
    {
        $case = $this->makeCase();
        Http::fake([$this->url . '/cases/lookup*' => Http::response([
            'program_id' => 51, 'cnic' => '42201-1234567-1', 'clientName' => 'Different CMS Name',
        ])]);

        $this->assertSame(51, app(LasCmsSyncService::class)->linkByCnic($case));
        $this->assertTrue($case->fresh()->hasVerifiedLasLink());
        Http::assertSent(fn ($request) => $request->method() === 'GET'
            && $request['cnic'] === '42201-1234567-1'
            && $request->hasHeader('X-JusticeHub-Key', 'test-key'));
        Http::assertSentCount(1);
    }

    public function test_same_name_with_different_cnic_never_links_and_clears_old_candidates(): void
    {
        $case = $this->makeCase(['external_case_id' => 5, 'meta' => [
            'las_link_status' => 'name_matched', 'las_candidates' => [['id' => 5]],
        ]]);
        Http::fake([$this->url . '/cases/lookup*' => Http::response([
            'program_id' => 5, 'cnic' => '42201-7654321-1', 'clientName' => $case->name,
        ])]);

        $this->assertNull(app(LasCmsSyncService::class)->linkByName($case));
        $this->assertNull($case->fresh()->external_case_id);
        $this->assertArrayNotHasKey('las_candidates', $case->meta);
        $this->assertSame('cnic_mismatch', $case->meta['las_link_status']);
        Http::assertSentCount(1);
    }

    public function test_missing_or_invalid_cnic_does_not_fall_back_to_name(): void
    {
        Http::fake();
        foreach ([null, '', '888'] as $cnic) {
            $case = $this->makeCase(['cnic' => $cnic]);
            $this->assertNull(app(LasCmsSyncService::class)->linkByName($case));
            $this->assertSame('invalid_cnic', $case->fresh()->meta['las_link_status']);
        }
        Http::assertNothingSent();
    }

    public function test_duplicate_cnic_matches_require_selection_and_exclude_other_cnics(): void
    {
        $case = $this->makeCase();
        Http::fake([$this->url . '/cases/lookup*' => Http::response([
            'match_count' => 3,
            'candidates' => [
                ['program_id' => 51, 'cnic' => $case->cnic],
                ['program_id' => 52, 'cnic' => $case->cnic],
                ['program_id' => 53, 'cnic' => '42201-7654321-1'],
            ],
        ], 409)]);

        $this->assertNull(app(LasCmsSyncService::class)->linkByCnic($case));
        $this->assertNull($case->external_case_id);
        $this->assertSame('ambiguous', $case->meta['las_link_status']);
        $this->assertSame([51, 52], array_column($case->meta['las_link_candidates'], 'program_id'));
    }

    public function test_incomplete_duplicate_response_cannot_choose_one_program(): void
    {
        $case = $this->makeCase();
        Http::fake([$this->url . '/cases/lookup*' => Http::response([
            'match_count' => 2, 'candidates' => [['program_id' => 51, 'cnic' => $case->cnic]],
        ], 409)]);
        $this->assertNull(app(LasCmsSyncService::class)->linkByCnic($case));
        $this->assertSame('ambiguous', $case->meta['las_link_status']);
    }

    public function test_lookup_fetches_cnic_from_program_when_search_response_omits_it(): void
    {
        $case = $this->makeCase();
        Http::fake([
            $this->url . '/cases/lookup*' => Http::response(['data' => ['program_id' => 51]]),
            $this->url . '/cases/51' => Http::response(['data' => ['id' => 51, 'cnic' => $case->cnic]]),
        ]);
        $this->assertSame(51, app(LasCmsSyncService::class)->linkByCnic($case));
        Http::assertSentCount(2);
    }

    public function test_all_api_entry_points_skip_non_court_cases_even_with_old_ids(): void
    {
        Http::fake();
        $service = app(LasCmsSyncService::class);
        foreach (['Mediation', 'Legal Advice / Consultation', 'ADR / Dispute Resolution Support'] as $pathway) {
            $case = $this->makeCase(['assigned_pathway' => $pathway, 'external_case_id' => 51, 'jh_cms_interview_id' => 61]);
            $this->assertNull($service->pushCase($case));
            $this->assertNull($service->linkByCnic($case));
            $this->assertNull($service->linkProgram($case, 51));
            $this->assertNull($service->resolveDraft($case));
            $this->assertNull($service->fetchCaseWithHearings($case));
            $this->assertSame([], $service->fetchTimeline($case));
            $this->assertSame(0, $service->pullHearings($case));
            $this->assertFalse($service->updateStatus($case));
            $service->syncCaseInfo($case);
        }
        Http::assertNothingSent();
    }

    public function test_wrong_link_never_reads_hearings_or_timeline_or_updates_status(): void
    {
        $case = $this->makeCase(['external_case_id' => 51, 'meta' => ['las_link_status' => 'name_matched']]);
        Http::fake([$this->url . '/cases/51' => Http::response([
            'id' => 51, 'cnic' => '42201-7654321-1', 'courtName' => 'Wrong Court',
        ])]);
        $service = app(LasCmsSyncService::class);
        $this->assertNull($service->fetchCaseWithHearings($case));
        $this->assertSame([], $service->fetchTimeline($case));
        $this->assertFalse($service->updateStatus($case));
        $this->assertSame(0, $service->pullHearings($case));
        $this->assertNull($case->fresh()->external_case_id);
        Http::assertSentCount(1);
    }

    public function test_manual_selection_requires_cnic_and_preserves_existing_link_on_failure(): void
    {
        $this->asHead();
        $case = $this->makeCase(['external_case_id' => 51, 'meta' => ['las_link_status' => 'verified']]);
        Http::fake([$this->url . '/cases/52' => Http::response(['id' => 52, 'cnic' => '42201-7654321-1'])]);
        $this->post(route('cases.link-las', $case), ['program_id' => 52])->assertSessionHasErrors('program_id');
        $this->assertSame(51, (int) $case->fresh()->external_case_id);
    }

    public function test_draft_id_is_never_used_as_program_and_repeat_push_only_resolves_source(): void
    {
        $case = $this->makeCase();
        Http::fake([
            $this->url . '/cases' => Http::response(['id' => 61, 'interview_id' => 61, 'status' => 'draft'], 201),
            $this->url . '/cases/source/*' => Http::response(['interview_id' => 61, 'status' => 'draft']),
        ]);
        $service = app(LasCmsSyncService::class);
        $this->assertNull($service->pushCase($case));
        $this->assertNull($case->external_case_id);
        $this->assertSame(61, (int) $case->jh_cms_interview_id);
        $this->assertNull($service->pushCase($case));
        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request->method() === 'GET' && str_contains($request->url(), '/source/'));
    }

    public function test_converted_draft_reads_program_and_hearings_by_resolved_id(): void
    {
        $case = $this->makeCase(['cnic' => null, 'jh_cms_interview_id' => 61, 'meta' => ['las_link_status' => 'draft']]);
        Http::fake([
            $this->url . '/cases/source/*' => Http::response([
                'justicehub_case_uid' => $case->case_uid, 'interview_id' => 61, 'program_id' => 91, 'status' => 'converted',
            ]),
            $this->url . '/cases/91' => Http::response(['data' => ['id' => 91, 'courtName' => 'Family Court']]),
            $this->url . '/cases/91/hearings' => Http::response(['hearings' => [['id' => 3, 'date' => '2026-11-05']]]),
        ]);
        $service = app(LasCmsSyncService::class);
        $this->assertSame(91, $service->resolveDraft($case));
        $this->assertSame('source', $case->meta['las_match_method']);
        $data = $service->fetchCaseWithHearings($case);
        $this->assertSame('Family Court', $data['courtName']);
        $this->assertSame(3, $data['hearings'][0]['id']);
        Http::assertSentCount(3);
    }

    public function test_draft_cannot_link_to_a_different_source_uid(): void
    {
        $case = $this->makeCase(['jh_cms_interview_id' => 61]);
        Http::fake([$this->url . '/cases/source/*' => Http::response([
            'justicehub_case_uid' => 'ANOTHER-CASE', 'interview_id' => 61, 'program_id' => 91,
        ])]);
        $this->assertNull(app(LasCmsSyncService::class)->resolveDraft($case));
        $this->assertNull($case->external_case_id);
        $this->assertSame('source_mismatch', $case->meta['las_link_status']);
    }

    public function test_cnic_change_invalidates_old_match_but_name_change_does_not(): void
    {
        $case = $this->makeCase(['external_case_id' => 51, 'meta' => ['las_link_status' => 'verified', 'las_match_method' => 'cnic']]);
        $case->update(['name' => 'Corrected Name']);
        $this->assertTrue($case->hasVerifiedLasLink());
        $case->update(['cnic' => '42201-1234567-1']);
        $this->assertTrue($case->hasVerifiedLasLink());
        $case->update(['cnic' => '4220176543211']);
        $this->assertFalse($case->hasVerifiedLasLink());
        $this->assertSame('identity_changed', $case->meta['las_link_status']);
    }

    public function test_counts_and_filters_include_only_court_cnic_matches_and_drafts(): void
    {
        $this->asHead();
        $matched = $this->makeCase(['external_case_id' => 51, 'meta' => ['las_link_status' => 'cnic_matched']]);
        $draft = $this->makeCase(['jh_cms_interview_id' => 61, 'meta' => ['las_link_status' => 'draft']]);
        $nameOnly = $this->makeCase(['external_case_id' => 52, 'meta' => ['las_link_status' => 'name_matched']]);
        $unmatched = $this->makeCase();
        $advice = $this->makeCase(['assigned_pathway' => 'Legal Advice / Consultation', 'external_case_id' => 53, 'meta' => ['las_link_status' => 'verified']]);

        $this->get(route('cases.index', ['cms_link' => 'connected']))->assertOk()
            ->assertSee($matched->case_uid)->assertSee($draft->case_uid)
            ->assertDontSee($nameOnly->case_uid)->assertDontSee($advice->case_uid)
            ->assertViewHas('counts', fn ($counts) => $counts['cms_connected'] === 2 && $counts['cms_not_connected'] === 2);
        $this->get(route('cases.index', ['cms_link' => 'not_connected']))->assertOk()
            ->assertSee($nameOnly->case_uid)->assertSee($unmatched->case_uid)
            ->assertDontSee($matched->case_uid)->assertDontSee($draft->case_uid)->assertDontSee($advice->case_uid);
        $this->assertEqualsCanonicalizing([$matched->id, $draft->id], (new CasesExport(['cms_link' => 'connected']))->query()->pluck('id')->all());
        $this->assertEqualsCanonicalizing([$nameOnly->id, $unmatched->id], (new CasesExport(['cms_link' => 'not_connected']))->query()->pluck('id')->all());
    }

    public function test_bulk_program_reads_verify_each_case_and_skip_other_pathways(): void
    {
        $good = $this->makeCase(['external_case_id' => 51]);
        $bad = $this->makeCase(['external_case_id' => 52]);
        $advice = $this->makeCase(['external_case_id' => 53, 'assigned_pathway' => 'Mediation']);
        Http::fake([
            $this->url . '/cases/51' => Http::response(['id' => 51, 'cnic' => $good->cnic, 'caseStage' => 'Evidence']),
            $this->url . '/cases/52' => Http::response(['id' => 52, 'cnic' => '42201-7654321-1']),
        ]);
        $data = app(LasCmsSyncService::class)->fetchCases(collect([$good, $bad, $advice]));
        $this->assertSame([$good->id], array_keys($data));
        $this->assertSame('Evidence', $data[$good->id]['caseStage']);
        $this->assertNull($bad->fresh()->external_case_id);
        Http::assertSentCount(2);
    }

    public function test_opening_an_old_court_case_matches_cnic_and_shows_program_data(): void
    {
        $this->asHead();
        $case = $this->makeCase();
        Http::fake([
            $this->url . '/cases/lookup*' => Http::response(['program_id' => 51, 'cnic' => $case->cnic]),
            $this->url . '/cases/51' => Http::response(['id' => 51, 'cnic' => $case->cnic, 'courtName' => 'Matched Family Court']),
            $this->url . '/cases/51/hearings' => Http::response(['hearings' => []]),
            $this->url . '/cases/51/timeline' => Http::response(['timeline' => []]),
        ]);
        $this->get(route('cases.show', $case))->assertOk()->assertSee('Matched Family Court');
        $this->assertSame(51, (int) $case->fresh()->external_case_id);
        Http::assertNotSent(fn ($request) => $request->method() === 'POST');
    }

    public function test_reconcile_dry_run_does_not_persist_links(): void
    {
        $case = $this->makeCase();
        Http::fake([$this->url . '/cases/lookup*' => Http::response(['program_id' => 51, 'cnic' => $case->cnic])]);
        $this->artisan('las:reconcile-links', ['--case' => $case->case_uid, '--dry-run' => true])->assertSuccessful();
        $this->assertNull($case->fresh()->external_case_id);
        $this->artisan('las:reconcile-links', ['--case' => $case->case_uid])->assertSuccessful();
        $this->assertSame(51, (int) $case->fresh()->external_case_id);
    }

    public function test_old_imported_hearings_are_hidden_when_cnic_link_is_rejected(): void
    {
        $this->asHead();
        $case = $this->makeCase(['external_case_id' => 51, 'meta' => ['las_link_status' => 'name_matched']]);
        ServiceEncounter::create([
            'case_id' => $case->id, 'date' => '2026-11-02', 'type' => 'Court Hearing',
            'performed_by' => 'LAS CMS Sync', 'note' => 'UNRELATED CMS HEARING',
            'meta' => ['source' => 'las_cms', 'external_program_id' => 51],
        ]);
        Http::fake([$this->url . '/cases/51' => Http::response(['id' => 51, 'cnic' => '42201-7654321-1'])]);
        $this->get(route('cases.show', $case))->assertOk()->assertDontSee('UNRELATED CMS HEARING');
        $this->assertDatabaseHas('service_encounters', ['case_id' => $case->id, 'note' => 'UNRELATED CMS HEARING']);
    }

    public function test_offline_matching_uses_cnic_only_and_preserves_ambiguity(): void
    {
        $good = $this->makeCase();
        $nameOnly = $this->makeCase(['cnic' => null]);
        $duplicate = $this->makeCase(['cnic' => '4220176543211', 'external_case_id' => 99]);
        $file = tempnam(sys_get_temp_dir(), 'las-cms-test-');
        file_put_contents($file, "51|Different Name|42201-1234567-1|Dadu\n52|Local Client Name|42201-7654321-1|Dadu\n53|Other Name|42201-7654321-1|Hyderabad\n");
        try {
            $this->artisan('las:match-offline', ['file' => $file, '--name-fallback' => true, '--overwrite' => true])->assertSuccessful();
            $this->assertSame(51, (int) $good->fresh()->external_case_id);
            $this->assertNull($nameOnly->fresh()->external_case_id);
            $this->assertNull($duplicate->fresh()->external_case_id);
            $this->assertSame('cnic_ambiguous', $duplicate->fresh()->meta['las_link_status']);
        } finally {
            unlink($file);
        }
    }
}
