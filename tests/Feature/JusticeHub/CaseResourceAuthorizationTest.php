<?php

namespace Tests\Feature\JusticeHub;

use App\Enums\UserRole;
use App\Models\CaseRecord;
use App\Models\CaseReferral;
use App\Models\CaseReferralLetter;
use App\Models\Document;
use App\Models\Hub;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CaseResourceAuthorizationTest extends TestCase
{
    private function makeUser(UserRole $role, ?string $hubId, string $name): User
    {
        $user = User::factory()->create([
            'name' => $name,
            'role' => $role,
            'hub_id' => $hubId,
            'is_active' => true,
        ]);
        $user->assignRole($role->value);

        return $user;
    }

    private function makeCase(
        string $hubId,
        ?string $assignedTo = 'Other Lawyer',
        ?string $pathway = 'Court Representation',
        ?string $disposition = 'litigation',
    ): CaseRecord {
        $token = Str::lower(Str::random(10));

        return CaseRecord::create([
            'case_uid' => 'AUTH-'.$token,
            'case_ref' => 'REF-'.$token,
            'hub_id' => $hubId,
            'assigned_to' => $assignedTo,
            'name' => 'Protected Client '.$token,
            'gender' => 'Female',
            'age' => 30,
            'intake_date' => now()->toDateString(),
            'primary_issue' => 'Family Law',
            'urgency' => 'Medium',
            'status' => 'Active',
            'disposition' => $disposition,
            'risk' => 'Low',
            'assigned_pathway' => $pathway,
            'consent' => true,
            'sla_met' => true,
        ]);
    }

    private function makeDocument(CaseRecord $case, string $name = 'restricted.pdf'): Document
    {
        $path = 'documents/'.$case->id.'/'.$name;
        Storage::disk('local')->put($path, 'restricted evidence');

        return Document::create([
            'document_uid' => 'DOC-'.Str::upper(Str::random(8)),
            'case_id' => $case->id,
            'type' => 'Evidence',
            'name' => $name,
            'added_date' => now()->toDateString(),
            'added_by' => 'Other Lawyer',
            'source' => 'Upload',
            'status' => 'Pending Review',
            'confidentiality' => 'Restricted',
            'file_path' => $path,
        ]);
    }

    private function makeLetter(CaseRecord $case): CaseReferralLetter
    {
        $referral = CaseReferral::create([
            'case_id' => $case->id,
            'referred_to' => 'Protected Partner',
            'referral_date' => now()->toDateString(),
        ]);
        $path = 'referral-letters/'.$case->id.'/private.pdf';
        Storage::disk('local')->put($path, 'private referral');

        return CaseReferralLetter::create([
            'case_referral_id' => $referral->id,
            'letter_date' => now()->toDateString(),
            'file_path' => $path,
            'file_name' => 'private.pdf',
        ]);
    }

    public function test_unassigned_lawyer_cannot_access_case_slip_or_upload_documents(): void
    {
        Storage::fake('local');
        $hub = Hub::firstOrFail();
        $lawyer = $this->makeUser(UserRole::Lawyer, $hub->id, 'Validation Lawyer');
        $case = $this->makeCase($hub->id);
        $this->actingAs($lawyer);

        $this->get(route('cases.show', $case))->assertForbidden();
        $this->get(route('cases.slip', $case))->assertForbidden();
        $this->post(route('documents.store', $case), [
            'name' => 'Unauthorized upload',
            'type' => 'Evidence',
            'confidentiality' => 'Restricted',
            'added_by' => $lawyer->name,
            'file' => UploadedFile::fake()->create('unauthorized.pdf', 10, 'application/pdf'),
        ])->assertForbidden();

        $this->assertDatabaseMissing('documents', [
            'case_id' => $case->id,
            'name' => 'Unauthorized upload',
        ]);
    }

    public function test_assigned_lawyer_keeps_case_and_document_workflow_access(): void
    {
        Storage::fake('local');
        $hub = Hub::firstOrFail();
        $lawyer = $this->makeUser(UserRole::Lawyer, $hub->id, 'Assigned Lawyer');
        $case = $this->makeCase($hub->id, $lawyer->name);
        $document = $this->makeDocument($case);
        $letter = $this->makeLetter($case);
        $this->actingAs($lawyer);

        $this->get(route('cases.slip', $case))->assertOk()->assertSee($case->name);
        $this->get(route('documents.download', $document))
            ->assertOk()
            ->assertDownload($document->name);
        $this->get(route('referral-letters.download', $letter))
            ->assertOk()
            ->assertDownload($letter->file_name);
        $this->post(route('documents.verify', $document))->assertRedirect();
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'Verified',
        ]);

        $this->post(route('documents.store', $case), [
            'name' => 'Authorized upload',
            'type' => 'Evidence',
            'confidentiality' => 'Restricted',
            'added_by' => $lawyer->name,
            'file' => UploadedFile::fake()->create('authorized.pdf', 10, 'application/pdf'),
        ])->assertRedirect();
        $this->assertDatabaseHas('documents', [
            'case_id' => $case->id,
            'name' => 'Authorized upload',
        ]);
    }

    public function test_direct_document_and_letter_ids_cannot_cross_hub_boundary(): void
    {
        Storage::fake('local');
        $hubs = Hub::take(2)->get();
        $lawyer = $this->makeUser(UserRole::Lawyer, $hubs[0]->id, 'Cross Hub Lawyer');
        $case = $this->makeCase($hubs[1]->id, $lawyer->name);
        $document = $this->makeDocument($case);
        $letter = $this->makeLetter($case);
        $this->actingAs($lawyer);

        $this->get(route('documents.download', $document))->assertForbidden();
        $this->post(route('documents.verify', $document))->assertForbidden();
        $this->get(route('referral-letters.download', $letter))->assertForbidden();

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'status' => 'Pending Review',
        ]);
    }

    public function test_pathway_managers_cannot_access_documents_outside_their_pathway(): void
    {
        Storage::fake('local');
        $hub = Hub::firstOrFail();
        $mediationCase = $this->makeCase($hub->id, null, 'Mediation', 'adr');
        $litigationCase = $this->makeCase($hub->id);
        $mediationDocument = $this->makeDocument($mediationCase, 'mediation.pdf');
        $litigationDocument = $this->makeDocument($litigationCase, 'litigation.pdf');

        $litigationManager = $this->makeUser(UserRole::LitigationManager, null, 'Litigation Manager');
        $this->actingAs($litigationManager);
        $this->get(route('documents.download', $mediationDocument))->assertForbidden();
        $this->get(route('documents.download', $litigationDocument))->assertOk();

        $mediationManager = $this->makeUser(UserRole::MediationManager, null, 'Mediation Manager');
        $this->actingAs($mediationManager);
        $this->get(route('documents.download', $litigationDocument))->assertForbidden();
        $this->get(route('documents.download', $mediationDocument))->assertOk();
    }

    public function test_head_and_hub_coordinator_keep_their_intended_scope(): void
    {
        Storage::fake('local');
        $hubs = Hub::take(2)->get();
        $firstCase = $this->makeCase($hubs[0]->id);
        $secondCase = $this->makeCase($hubs[1]->id);
        $firstDocument = $this->makeDocument($firstCase, 'first.pdf');
        $secondDocument = $this->makeDocument($secondCase, 'second.pdf');

        $coordinator = $this->makeUser(UserRole::HubCoordinator, $hubs[0]->id, 'Hub Coordinator');
        $this->actingAs($coordinator);
        $this->get(route('documents.download', $firstDocument))->assertOk();
        $this->get(route('documents.download', $secondDocument))->assertForbidden();

        $head = $this->makeUser(UserRole::Head, null, 'Head User');
        $this->actingAs($head);
        $this->get(route('documents.download', $firstDocument))->assertOk();
        $this->get(route('documents.download', $secondDocument))->assertOk();
    }

    public function test_body_supplied_case_id_cannot_bypass_assignment(): void
    {
        $hub = Hub::firstOrFail();
        $lawyer = $this->makeUser(UserRole::Lawyer, $hub->id, 'Encounter Lawyer');
        $otherCase = $this->makeCase($hub->id);
        $ownCase = $this->makeCase($hub->id, $lawyer->name);
        $this->actingAs($lawyer);

        $payload = [
            'type' => 'Court Hearing',
            'date' => now()->toDateString(),
            'performed_by' => $lawyer->name,
            'note' => 'Authorization regression check',
        ];

        $this->post(route('encounters.log'), ['case_id' => $otherCase->id] + $payload)
            ->assertForbidden();
        $this->assertDatabaseMissing('service_encounters', [
            'case_id' => $otherCase->id,
            'note' => $payload['note'],
        ]);

        $this->post(route('encounters.log'), ['case_id' => $ownCase->id] + $payload)
            ->assertRedirect();
        $this->assertDatabaseHas('service_encounters', [
            'case_id' => $ownCase->id,
            'note' => $payload['note'],
        ]);
    }

    public function test_policy_preserves_court_clerk_and_viewer_read_only_behavior(): void
    {
        $hub = Hub::firstOrFail();
        $litigationCase = $this->makeCase($hub->id, null, null, 'litigation');
        $mediationCase = $this->makeCase($hub->id, null, 'Mediation', 'adr');

        $clerk = $this->makeUser(UserRole::CourtClerk, $hub->id, 'Court Clerk');
        $this->assertTrue(Gate::forUser($clerk)->allows('view', $litigationCase));
        $this->assertFalse(Gate::forUser($clerk)->allows('view', $mediationCase));
        $this->assertFalse(Gate::forUser($clerk)->allows('update', $litigationCase));

        $viewer = $this->makeUser(UserRole::Viewer, $hub->id, 'Read Only Viewer');
        $this->assertTrue(Gate::forUser($viewer)->allows('view', $litigationCase));
        $this->assertFalse(Gate::forUser($viewer)->allows('update', $litigationCase));
    }

    public function test_lawyer_cannot_gain_assignment_by_changing_profile_name(): void
    {
        $hub = Hub::firstOrFail();
        $assignedLawyer = $this->makeUser(UserRole::Lawyer, $hub->id, 'Assigned Identity');
        $attacker = $this->makeUser(UserRole::Lawyer, $hub->id, 'Other Identity');
        $staff = Staff::create([
            'staff_uid' => 'STF-'.Str::upper(Str::random(8)),
            'user_id' => $assignedLawyer->id,
            'name' => $assignedLawyer->name,
            'initials' => 'AI',
            'role' => 'Lawyer',
            'hub_id' => $hub->id,
            'status' => 'active',
            'joined_date' => now()->toDateString(),
        ]);
        $case = $this->makeCase($hub->id, $assignedLawyer->name);
        $case->update(['assigned_staff_id' => $staff->id]);
        $this->actingAs($attacker);

        $this->patch(route('profile.update'), [
            'name' => $assignedLawyer->name,
            'email' => $attacker->email,
        ])->assertSessionHasErrors('name');
        $this->assertSame('Other Identity', $attacker->fresh()->name);

        // Stable assignment remains authoritative even if a name collision exists.
        $attacker->update(['name' => $assignedLawyer->name]);
        $this->assertFalse(Gate::forUser($attacker->fresh())->allows('view', $case->fresh()));
        $this->assertTrue(Gate::forUser($assignedLawyer)->allows('view', $case->fresh()));
    }

    public function test_unique_profile_rename_preserves_legacy_case_assignment(): void
    {
        $hub = Hub::firstOrFail();
        $lawyer = $this->makeUser(UserRole::Lawyer, $hub->id, 'Legacy Lawyer');
        $case = $this->makeCase($hub->id, $lawyer->name);
        $this->actingAs($lawyer);

        $this->patch(route('profile.update'), [
            'name' => 'Renamed Lawyer',
            'email' => $lawyer->email,
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

        $lawyer->refresh();
        $case->refresh();
        $this->assertSame('Renamed Lawyer', $case->assigned_to);
        $this->assertTrue(Gate::forUser($lawyer)->allows('view', $case));
    }
}
