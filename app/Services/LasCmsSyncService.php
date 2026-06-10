<?php

namespace App\Services;

use App\Models\CaseRecord;
use App\Models\ServiceEncounter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LasCmsSyncService
{
    protected $db;

    public function __construct()
    {
        $this->db = DB::connection('las_cms');
    }

    /**
     * Push a JusticeHub case to LAS CMS programs table.
     * Returns the external programs.id on success.
     */
    public function pushCase(CaseRecord $case): ?int
    {
        // Don't push if already linked
        if ($case->external_case_id) {
            return $case->external_case_id;
        }

        // Build UniqueNumber: YYYY-JusticeHub-{id}-{district}
        $uniqueNumber = now()->year . '-JusticeHub-' . $case->id . '-' . ($case->district ?: 'Unknown');

        try {
            $sharedData = [
                'programName'           => 'JusticeHub',
                'caseReferred'          => 'Justicehub',
                'districtName'          => $case->district ?: 'Unknown',
                'interviewDate'         => $case->intake_date?->format('Y-m-d'),
                'interviewerName'       => $case->staff_receiving ?: $case->assigned_to ?: 'JusticeHub',
                'clientName'            => $case->name,
                'fatherHusbandName'     => $case->father_husband_name ?: '-',
                'contactNumber'         => $case->primary_contact ?: '-',
                'cnic'                  => $case->cnic,
                'gender'                => $case->gender ?: 'Not specified',
                'age'                   => $case->age,
                'religion'              => $case->religion ?: 'Not specified',
                'relationShip'          => 'Self',
                'caseFacts'             => $case->issue_description,
                'caseSubmittedFAppro'   => 'Yes',
                'caseApprovalStatus'    => 'Pending',
                'lawyer1'               => $case->assigned_to,
                'natureOfCase'          => $case->primary_issue,
                'currentCaseStatus'     => $this->mapStatus($case->status),
                'UniqueNumber'          => $uniqueNumber,
                'uniqueYear'            => (string) now()->year,
                'username'              => 'JusticeHub-API',
                'created_at'            => now(),
                'updated_at'            => now(),
            ];

            $externalId = $this->db->table('programs')->insertGetId($sharedData);

            // Also insert into programs_detail as the initial record
            $this->db->table('programs_detail')->insert(array_merge($sharedData, [
                'programsid'  => $externalId,
                'change_type' => 'create',
            ]));

            // Update JusticeHub case with external reference
            $case->update([
                'external_case_id'  => $externalId,
                'external_synced_at' => now(),
            ]);

            Log::info("LasCMS: Pushed case {$case->case_uid} → programs.id={$externalId}");
            return $externalId;

        } catch (\Exception $e) {
            Log::error("LasCMS push failed for {$case->case_uid}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Pull hearings from LAS CMS for a specific case and create ServiceEncounters.
     */
    public function pullHearings(CaseRecord $case): int
    {
        if (!$case->external_case_id) {
            return 0;
        }

        $externalHearings = $this->db->table('hearings')
            ->where('programsID', $case->external_case_id)
            ->orderBy('date')
            ->get();

        $imported = 0;
        foreach ($externalHearings as $h) {
            // Check if already imported (by matching external hearing id in meta)
            $exists = ServiceEncounter::where('case_id', $case->id)
                ->where('type', 'Court Hearing')
                ->whereJsonContains('meta->external_hearing_id', $h->id)
                ->exists();

            if ($exists) continue;

            ServiceEncounter::create([
                'case_id'      => $case->id,
                'date'         => $h->date ?: now()->toDateString(),
                'type'         => 'Court Hearing',
                'performed_by' => 'LAS CMS Sync',
                'note'         => $h->hearingUpdate,
                'meta'         => [
                    'external_hearing_id' => $h->id,
                    'case_number'         => $h->caseNumber,
                    'next_hearing'        => $h->nextHearing,
                    'source'              => 'las_cms',
                ],
            ]);
            $imported++;
        }

        // Also pull latest status from programs table
        $extProgram = $this->db->table('programs')
            ->where('id', $case->external_case_id)
            ->first();

        if ($extProgram) {
            $updates = ['external_synced_at' => now()];

            // Sync next hearing date
            if ($extProgram->nextHearing) {
                $updates['meta'] = array_merge($case->meta ?? [], [
                    'next_hearing'     => $extProgram->nextHearing,
                    'court_name'       => $extProgram->courtName,
                    'case_number'      => $extProgram->caseNumber,
                    'case_stage'       => $extProgram->caseStage,
                    'case_decision'    => $extProgram->caseDecision,
                    'external_status'  => $extProgram->currentCaseStatus,
                ]);
            }

            $case->update($updates);
        }

        if ($imported > 0) {
            Log::info("LasCMS: Pulled {$imported} hearings for case {$case->case_uid}");
        }

        return $imported;
    }

    /**
     * Pull hearings for ALL linked cases.
     */
    public function pullAllHearings(): array
    {
        $cases = CaseRecord::whereNotNull('external_case_id')->get();
        $totalImported = 0;
        $casesUpdated = 0;

        foreach ($cases as $case) {
            $count = $this->pullHearings($case);
            $totalImported += $count;
            if ($count > 0) $casesUpdated++;
        }

        return ['hearings' => $totalImported, 'cases' => $casesUpdated];
    }

    /**
     * Map JusticeHub status to LAS CMS status.
     */
    protected function mapStatus(mixed $status): string
    {
        $val = $status instanceof \BackedEnum ? $status->value : (string) $status;
        return match ($val) {
            'Active'            => 'Running',
            'Pending Approval'  => 'Pending',
            'Closed'            => 'Decided',
            'Settlement'        => 'Settled/Compromise',
            'Rejected'          => 'Not Filed',
            default             => 'Running',
        };
    }
}
