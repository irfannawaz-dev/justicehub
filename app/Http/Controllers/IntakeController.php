<?php

namespace App\Http\Controllers;

use App\Mail\NewCaseIntake;
use App\Models\CaseRecord;
use App\Models\Hub;
use App\Models\ServiceEncounter;
use App\Services\DashboardMetricsService;
use App\Services\LasCmsSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class IntakeController extends Controller
{
    public function create()
    {
        $user = auth()->user();

        $query = Hub::where('is_active', true)->orderBy('name');

        if (! $user->canSeeAllHubs()) {
            $query->where('id', $user->hub_id);
        }

        $hubs = $query->pluck('name', 'id'); // [hub_id => hub_name]

        // Hub → district mapping
        $hubDistricts = Hub::where('is_active', true)->pluck('district', 'id');

        // Location cascade: district → talukas, district+taluka → union councils
        $locationRows = DB::table('locations')
            ->select('district', 'taluka', 'union_council')
            ->orderBy('district')->orderBy('taluka')->orderBy('union_council')
            ->get();

        $locationData = [];
        foreach ($locationRows as $row) {
            $d  = $row->district;
            $t  = $row->taluka ?: '';
            $uc = $row->union_council ?: '';
            if ($t && !in_array($t, $locationData[$d]['talukas'] ?? [])) {
                $locationData[$d]['talukas'][] = $t;
            }
            if ($uc) {
                $key = $t ?: '__none__';
                $locationData[$d]['ucs'][$key][] = $uc;
            }
        }

        // Staff list for receiving-staff dropdown — scoped to hub
        $staffQuery = \App\Models\Staff::orderBy('name');
        if (! $user->canSeeAllHubs()) {
            $staffQuery->where('hub_id', $user->hub_id);
        }
        $allStaff = $staffQuery->get(['id', 'name', 'staff_uid', 'role', 'user_id']);

        // Auto-fill from User model directly (emp_id + designation always available)
        $defaultStaffName        = $user->name;
        $roleLabel = $user->role instanceof \App\Enums\UserRole ? $user->role->label() : (string) $user->role;
        $defaultStaffDesignation = trim(($user->emp_id ?? '') . ' - ' . ($user->designation ?? $roleLabel), ' -');

        // Lawyers for assignment — show ALL lawyers across all hubs (not filtered by hub)
        $lawyers = \App\Models\User::where('role', 'lawyer')->orderBy('name')->get(['id', 'name', 'hub_id']);

        // Hub Coordinators for pathway display (Mediation / Govt / NGO / Other)
        $coordinatorQuery = \App\Models\User::where('role', 'hub-coordinator')->orderBy('name');
        if (! $user->canSeeAllHubs()) {
            $coordinatorQuery->where('hub_id', $user->hub_id);
        }
        $hubCoordinators = $coordinatorQuery->pluck('name', 'hub_id'); // [hub_id => coordinator_name]

        $governmentPartners = \App\Models\Partner::where('category', 'Government')
            ->orderBy('name')->pluck('name');

        $ngoPartners = \App\Models\Partner::whereIn('category', ['NGO', 'Civil Society', 'CSO', 'NPO'])
            ->orderBy('name')->pluck('name');

        $adrPartners = \App\Models\Partner::whereIn('category', ['ADR', 'Mediation', 'Legal Aid'])
            ->orderBy('name')->pluck('name');

        return view('intake.create', compact('hubs', 'hubDistricts', 'locationData', 'lawyers', 'allStaff', 'defaultStaffName', 'defaultStaffDesignation', 'hubCoordinators', 'governmentPartners', 'ngoPartners', 'adrPartners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'hubLocation'       => 'required|string|exists:hubs,id',
            'staffReceiving'    => 'required|string',
            'consent'           => 'required|string',
            'heardAboutUs'      => 'required|string',
            'referralType'      => 'nullable|in:Incoming,Outgoing',
            'referralContactPerson' => 'nullable|string|max:255',
            'fullName'          => 'required|string|max:255',
            'fatherHusbandName' => 'required|string|max:255',
            'gender'            => 'required|string',
            'age'               => 'required|integer|min:0|max:120',
            'maritalStatus'     => 'required|string',
            'religion'          => 'required|string',
            'educationLevel'    => 'required|string',
            'monthlyIncome'     => 'required|string',
            'disabilityStatus'  => 'required|string',
            'primaryContact'    => ['required', 'digits:11'],
            'tehsil'            => 'required|string',
            'district'          => 'required|string',
            'preferredLanguage' => 'required|string',
            'category'          => 'required|string',
            'urgencyLevel'      => 'required|string',
            'assignedPathway'   => 'required|string',
        ]);

        // Hub-scoped users can only create cases at their own hub
        $hubId = auth()->user()->canSeeAllHubs()
            ? $request->hubLocation
            : auth()->user()->hub_id;

        // Generate case UID — LAS-{District}-{per_hub_sequence}
        $hub = \App\Models\Hub::find($hubId);
        $district = str_replace(' ', '-', $hub->district ?? $hubId);
        $maxSeq = CaseRecord::where('hub_id', $hubId)
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(case_uid, '-', -1) AS UNSIGNED)) as max_seq")
            ->value('max_seq');
        $hubSeq = ($maxSeq ?? 0) + 1;
        $caseUid = 'LAS-' . $district . '-' . $hubSeq;
        $caseRef = 'LAS-' . $district . '-REF-' . $hubSeq;
        $encounterId = 'SE-' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);

        $case = DB::transaction(function () use ($request, $caseUid, $caseRef, $encounterId, $hubId) {
            $case = CaseRecord::create([
                'case_uid'           => $caseUid,
                'case_ref'           => $caseRef,
                'encounter_id'       => $encounterId,
                'hub_id'             => $hubId,
                'name'               => $request->fullName,
                'father_husband_name'=> $request->fatherHusbandName,
                'gender'             => $request->gender,
                'gender_other'       => $request->genderOther,
                'age'                => $request->age,
                'cnic'               => $request->cnic,
                'marital_status'     => $request->maritalStatus,
                'religion'           => $request->religion,
                'education_level'    => $request->educationLevel,
                'occupation'         => $request->occupation,
                'income_bracket'     => $request->monthlyIncome,
                'disability_status'  => $request->disabilityStatus,
                'primary_contact'    => $request->primaryContact,
                'alternative_contact'=> $request->alternativeContact,
                'full_address'       => $request->fullAddress,
                'union_council'      => $request->unionCouncil,
                'tehsil'             => $request->tehsil,
                'district'           => $request->district,
                'language'           => $request->preferredLanguage,
                'intake_date'        => now()->toDateString(),
                'intake_time'        => now()->format('H:i'),
                'mode'               => 'Walk-in',
                'source'             => 'Self',
                'referral_source'         => $request->heardAboutUs,
                'referral_type'           => $request->referralType,
                'referral_contact_person' => $request->referralContactPerson,
                'consent'            => $request->consent === 'Yes, I consent',
                'no_consent_reason'  => $request->noConsentReason,
                'returning_client'   => $request->repeatClient === 'Repeat',
                'staff_receiving'    => $request->staffReceiving,
                'staff_designation'  => $request->staffDesignation,
                'primary_issue'      => $request->category,
                'issue_description'  => $request->issueDescription,
                'urgency'            => $request->urgencyLevel,
                'status'             => 'Active',
                'risk'               => 'Low',
                'sla_met'            => true,
                'assigned_pathway'   => $request->assignedPathway,
                'pathway_specific'   => $request->pathwaySpecific,
                'pathway_specific_other' => $request->pathwaySpecificOther,
                'pathway_govt_dept'  => $request->pathwayGovernmentDept,
                'pathway_ngo_name'   => $request->pathwayNgoName,
                'pathway_other_details' => $request->pathwayOtherDetails,
                'is_gbv'             => str_contains(strtolower($request->category ?? ''), 'gbv'),
                'is_child'           => str_contains(strtolower($request->category ?? ''), 'juvenile') || str_contains(strtolower($request->category ?? ''), 'child'),
                'is_minority'        => false,
                'is_disability'      => false,
                'is_underserved'     => false,
                'assigned_to'        => $this->resolveAssignedTo($request),
                'assigned_staff_id'  => (in_array($request->assignedPathway, ['Court Representation', 'Legal Advice / Consultation']) && $request->pathwaySpecific === 'Justice Hub Lawyer' && $request->assignedLawyer)
                                        ? \App\Models\Staff::where('user_id', $request->assignedLawyer)->value('id')
                                        : null,
                'last_update'        => now()->toDateString(),
            ]);

            // Insert pathway pivot
            DB::table('case_pathway')->insert([
                'case_id' => $case->id,
                'pathway_value' => $request->assignedPathway,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create initial service encounter
            ServiceEncounter::create([
                'case_id'      => $case->id,
                'date'         => now()->toDateString(),
                'type'         => 'Intake',
                'performed_by' => $request->staffReceiving,
                'note'         => 'Client registered via intake form. Referral source: ' . $request->heardAboutUs . '.',
            ]);

            return $case;
        });

        // Flush dashboard cache so new case appears immediately
        DashboardMetricsService::flush($hubId);
        DashboardMetricsService::flush('all');

        // Notify assigned user (in-app)
        if ($case && ($assignedUser = $case->getAssignedUser())) {
            $assignedUser->notify(new \App\Notifications\CaseNotification(
                title:      "New case assigned — {$case->case_uid}",
                message:    "A new case has been assigned to you. Client: {$case->name}. Pathway: {$case->assigned_pathway}. Urgency: {$case->urgency->value}.",
                actionText: 'View Case',
                actionUrl:  route('cases.show', $case),
                type:       'assigned',
            ));
        }

        // Send intake email to assigned user + CC hub coordinator + justice.hub@las.org.pk
        $emailError = null;
        if ($case) {
            try {
                $case->load('hub');
                $mailable = new NewCaseIntake($case);
                $assignedUser = $case->getAssignedUser();

                // Hub coordinator for this case's hub
                $coordinator = \App\Models\User::where('role', 'hub-coordinator')
                    ->where('hub_id', $case->hub_id)
                    ->whereNotNull('email')
                    ->first();

                // Build CC list
                $ccList = ['justice.hub@las.org.pk'];
                if ($coordinator && $coordinator->email && $coordinator->email !== ($assignedUser->email ?? '')) {
                    $ccList[] = $coordinator->email;
                }

                if ($assignedUser && $assignedUser->email) {
                    Mail::to($assignedUser->email)
                        ->cc($ccList)
                        ->send($mailable);
                } else {
                    Mail::to('justice.hub@las.org.pk')
                        ->cc(array_filter($ccList, fn($e) => $e !== 'justice.hub@las.org.pk'))
                        ->send($mailable);
                }
            } catch (\Exception $e) {
                $emailError = $e->getMessage();
                \Log::error('Intake email failed for ' . $case->case_uid . ': ' . $emailError);

                // Notify all Head users via in-app notification (database only, not mail)
                \App\Models\User::where('role', 'head')->get()->each(function ($admin) use ($case, $emailError) {
                    $admin->notify(
                        (new \App\Notifications\CaseNotification(
                            title:   "Email delivery failed — {$case->case_uid}",
                            message: "The intake notification email for case {$case->case_uid} (client: {$case->name}) could not be sent. Error: {$emailError}",
                            type:    'info',
                        ))->onConnection('sync')->onQueue('default')
                    );
                });
            }
        }

        // Push to LAS CMS if pathway is Court Representation
        if ($case && $case->assigned_pathway === 'Court Representation') {
            try {
                $sync = new LasCmsSyncService();
                $externalId = $sync->pushCase($case);
                if ($externalId) {
                    \Log::info("LasCMS: case {$caseUid} (id={$case->id}) pushed successfully → programs.id={$externalId}");
                } else {
                    \Log::error("LasCMS: pushCase() returned null for {$caseUid} (id={$case->id}). Pathway={$case->assigned_pathway}. Check DB credentials and table structure.");
                }
            } catch (\Exception $e) {
                \Log::error("LasCMS: exception pushing {$caseUid} (id={$case->id}): [{$e->getCode()}] {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}");
            }
        }

        $redirect = redirect()->route('cases.index')
            ->with('success', "Intake registered successfully. Case ID: {$caseUid}")
            ->with('open_slip', route('cases.slip', $case));
        if ($emailError) {
            $redirect->with('warning', "Notification email could not be sent. Admins have been alerted.");
        }
        return $redirect;
    }

    private function resolveAssignedTo(\Illuminate\Http\Request $request): string
    {
        $pathway  = $request->assignedPathway;
        $specific = $request->pathwaySpecific;

        // Lawyer assignment
        if (in_array($pathway, ['Court Representation', 'Legal Advice / Consultation'])
            && $specific === 'Justice Hub Lawyer'
            && $request->assignedLawyer) {
            return \App\Models\User::find($request->assignedLawyer)?->name ?? $request->staffReceiving;
        }

        // Hub Coordinator assignment
        $coordinatorPathways = ['Mediation', 'ADR / Dispute Resolution Support', 'Government Department / Public Institution', 'Civil Society / NGO / CSO / NPO', 'Other'];
        if (in_array($pathway, $coordinatorPathways) && $request->filled('assignedCoordinator')) {
            return $request->assignedCoordinator;
        }

        return $request->staffReceiving;
    }
}
