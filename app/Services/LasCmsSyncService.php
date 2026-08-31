<?php

namespace App\Services;

use App\Models\CaseRecord;
use App\Models\ServiceEncounter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LasCmsSyncService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.las_cms.url'), '/');
        $this->apiKey  = config('services.las_cms.key');
    }

    protected function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'X-JusticeHub-Key' => $this->apiKey,
            'Accept'           => 'application/json',
            'Content-Type'     => 'application/json',
        ])->timeout(15);
    }

    /**
     * Push a JusticeHub case to LAS CMS via API.
     * Returns the external programs.id on success.
     */
    public function pushCase(CaseRecord $case): ?int
    {
        if ($case->external_case_id) {
            return $case->external_case_id;
        }

        $uniqueNumber = now()->year . '-JusticeHub-' . $case->id . '-' . ($case->district ?: 'Unknown');

        $payload = [
            'programName'         => 'JusticeHub',
            'caseReferred'        => 'Justicehub',
            'districtName'        => $case->district ?: 'Unknown',
            'interviewDate'       => $case->intake_date?->format('Y-m-d'),
            'interviewerName'     => $case->staff_receiving ?: $case->assigned_to ?: 'JusticeHub',
            'clientName'          => $case->name,
            'fatherHusbandName'   => $case->father_husband_name ?: '-',
            'contactNumber'       => $case->primary_contact ?: '-',
            'cnic'                => $case->cnic,
            'gender'              => $case->gender ?: 'Not specified',
            'age'                 => $case->age,
            'religion'            => $case->religion ?: 'Not specified',
            'relationShip'        => 'Self',
            'caseFacts'           => $case->issue_description,
            'caseSubmittedFAppro' => 'Yes',
            'caseApprovalStatus'  => 'Pending',
            'lawyer1'             => $case->assigned_to,
            'natureOfCase'        => [$case->primary_issue],
            'currentCaseStatus'   => $this->mapStatus($case->status),
            'UniqueNumber'        => $uniqueNumber,
            'uniqueYear'          => (string) now()->year,
            'username'            => 'JusticeHub-API',
        ];

        try {
            $response = $this->http()->post("{$this->baseUrl}/cases", $payload);

            if ($response->successful()) {
                $externalId = $response->json('id') ?? $response->json('data.id');

                if (!$externalId) {
                    Log::error("LasCMS pushCase: API returned success but no id for {$case->case_uid}. Response: " . $response->body());
                    return null;
                }

                $case->update([
                    'external_case_id'   => $externalId,
                    'external_synced_at' => now(),
                ]);

                Log::info("LasCMS: Pushed case {$case->case_uid} → programs.id={$externalId}");
                return $externalId;
            }

            Log::error("LasCMS pushCase failed for {$case->case_uid}: HTTP {$response->status()} — " . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error("LasCMS pushCase exception for {$case->case_uid}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update the status of an already-pushed case via API.
     */
    public function updateStatus(CaseRecord $case): bool
    {
        if (!$case->external_case_id) {
            Log::warning("LasCMS: updateStatus called on {$case->case_uid} but no external_case_id — skipping.");
            return false;
        }

        $payload = [
            'currentCaseStatus'  => $this->mapStatus($case->status),
            'caseApprovalStatus' => $this->mapApprovalStatus($case->status),
        ];

        try {
            $response = $this->http()->put("{$this->baseUrl}/cases/{$case->external_case_id}/status", $payload);

            if ($response->successful()) {
                $case->update([
                    'meta'               => array_merge($case->meta ?? [], ['cms_approval_status' => $payload['caseApprovalStatus']]),
                    'external_synced_at' => now(),
                ]);

                Log::info("LasCMS: Status updated for {$case->case_uid} → {$payload['currentCaseStatus']}");
                return true;
            }

            Log::error("LasCMS updateStatus failed for {$case->case_uid}: HTTP {$response->status()} — " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error("LasCMS updateStatus exception for {$case->case_uid}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch full case data + hearings in one go from the API.
     * Returns merged array or null on failure.
     */
    public function fetchCaseWithHearings(int $externalId): ?array
    {
        try {
            $caseResp    = $this->http()->get("{$this->baseUrl}/cases/{$externalId}");
            $hearingResp = $this->http()->get("{$this->baseUrl}/cases/{$externalId}/hearings");

            if (!$caseResp->successful()) {
                Log::warning("LasCMS fetchCaseWithHearings: case endpoint returned {$caseResp->status()} for id={$externalId}");
                return null;
            }

            $data     = $caseResp->json('data') ?? $caseResp->json() ?? [];
            $hearings = $hearingResp->successful()
                ? ($hearingResp->json('hearings') ?? $hearingResp->json('data') ?? [])
                : [];

            return array_merge($data, ['hearings' => $hearings]);

        } catch (\Exception $e) {
            Log::warning("LasCMS fetchCaseWithHearings exception for id={$externalId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Pull hearings from LAS CMS API for a specific case.
     */
    public function pullHearings(CaseRecord $case): int
    {
        if (!$case->external_case_id) {
            return 0;
        }

        try {
            $response = $this->http()->get("{$this->baseUrl}/cases/{$case->external_case_id}/hearings");

            if (!$response->successful()) {
                Log::error("LasCMS pullHearings failed for {$case->case_uid}: HTTP {$response->status()}");
                return 0;
            }

            $hearings = $response->json('data') ?? $response->json() ?? [];
            $imported = 0;

            foreach ($hearings as $h) {
                $hId = $h['id'] ?? null;
                if (!$hId) continue;

                $exists = ServiceEncounter::where('case_id', $case->id)
                    ->where('type', 'Court Hearing')
                    ->whereJsonContains('meta->external_hearing_id', $hId)
                    ->exists();

                if ($exists) continue;

                ServiceEncounter::create([
                    'case_id'      => $case->id,
                    'date'         => $h['date'] ?? now()->toDateString(),
                    'type'         => 'Court Hearing',
                    'performed_by' => 'LAS CMS Sync',
                    'note'         => $h['hearingUpdate'] ?? null,
                    'meta'         => [
                        'external_hearing_id' => $hId,
                        'case_number'         => $h['caseNumber'] ?? null,
                        'next_hearing'        => $h['nextHearing'] ?? null,
                        'source'              => 'las_cms',
                    ],
                ]);
                $imported++;
            }

            // Also sync latest status from GET /cases/{id}
            $this->syncCaseInfo($case);

            if ($imported > 0) {
                Log::info("LasCMS: Pulled {$imported} hearings for {$case->case_uid}");
            }

            return $imported;

        } catch (\Exception $e) {
            Log::error("LasCMS pullHearings exception for {$case->case_uid}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Sync case info (status, next hearing, court details) from GET /cases/{id}.
     */
    public function syncCaseInfo(CaseRecord $case): void
    {
        if (!$case->external_case_id) return;

        try {
            $response = $this->http()->get("{$this->baseUrl}/cases/{$case->external_case_id}");

            if (!$response->successful()) return;

            $data = $response->json('data') ?? $response->json();
            if (!$data) return;

            $metaUpdates = [];
            if (!empty($data['nextHearing']))      $metaUpdates['next_hearing']   = $data['nextHearing'];
            if (!empty($data['courtName']))         $metaUpdates['court_name']     = $data['courtName'];
            if (!empty($data['caseNumber']))        $metaUpdates['case_number']    = $data['caseNumber'];
            if (!empty($data['caseStage']))         $metaUpdates['case_stage']     = $data['caseStage'];
            if (!empty($data['caseDecision']))      $metaUpdates['case_decision']  = $data['caseDecision'];
            if (!empty($data['currentCaseStatus'])) $metaUpdates['external_status'] = $data['currentCaseStatus'];

            if ($metaUpdates) {
                $case->update([
                    'meta'               => array_merge($case->meta ?? [], $metaUpdates),
                    'external_synced_at' => now(),
                ]);
            }

        } catch (\Exception $e) {
            Log::warning("LasCMS syncCaseInfo exception for {$case->case_uid}: " . $e->getMessage());
        }
    }

    /**
     * Pull hearings for ALL linked cases.
     */
    public function pullAllHearings(): array
    {
        $cases = CaseRecord::whereNotNull('external_case_id')->get();
        $totalImported = 0;
        $casesUpdated  = 0;

        foreach ($cases as $case) {
            $count = $this->pullHearings($case);
            $totalImported += $count;
            if ($count > 0) $casesUpdated++;
        }

        return ['hearings' => $totalImported, 'cases' => $casesUpdated];
    }

    protected function mapStatus(mixed $status): string
    {
        $val = $status instanceof \BackedEnum ? $status->value : (string) $status;
        return match ($val) {
            'Active'           => 'Running',
            'Pending Approval' => 'Pending',
            'Closed'           => 'Decided',
            'Settlement'       => 'Settled/Compromise',
            'Rejected'         => 'Not Filed',
            default            => 'Running',
        };
    }

    protected function mapApprovalStatus(mixed $status): string
    {
        $val = $status instanceof \BackedEnum ? $status->value : (string) $status;
        return match ($val) {
            'Active'   => 'Approved',
            'Rejected' => 'Rejected',
            default    => 'Pending',
        };
    }
}
