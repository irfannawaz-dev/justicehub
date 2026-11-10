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
     * Normalize CNIC: strip all non-digits then format as XXXXX-XXXXXXX-X
     */
    public function formatCnic(string $cnic): string
    {
        $digits = $this->normalizeCnic($cnic);
        if (strlen($digits) !== 13) return $cnic;  // return as-is if unexpected length
        return substr($digits, 0, 5) . '-' . substr($digits, 5, 7) . '-' . substr($digits, 12, 1);
    }

    public function normalizeCnic(?string $cnic): string
    {
        return preg_replace('/\D/', '', (string) $cnic);
    }

    /**
     * Look up a programs record by client name (and optionally district).
     * Used as fallback when CNIC is missing or invalid.
     * Returns programs.id if exactly one match found, null otherwise.
     */
    public function linkByName(CaseRecord $case): ?int
    {
        if (!$case->name) return null;

        try {
            $params = ['name' => $case->name];
            if ($case->district) {
                $params['district'] = $case->district;
            }

            $response = $this->http()->get("{$this->baseUrl}/cases/search", $params);

            if ($response->status() === 404) {
                $this->setLinkStatus($case, 'name_not_found', [], false, true);
                return null;
            }

            if (!$response->successful()) {
                Log::info("LasCMS linkByName: no match for {$case->name} ({$case->case_uid}) — HTTP {$response->status()}");
                return null;
            }

            $matchCount = $response->json('match_count', 0);
            $candidates = $response->json('candidates', []);

            if ($matchCount === 0) {
                $this->setLinkStatus($case, 'name_not_found', [], false, true);
                return null;
            }

            if ($matchCount > 1) {
                // Store candidates for manual review
                $this->setLinkStatus($case, 'name_ambiguous', [
                    'match_count' => $matchCount,
                    'candidates'  => collect($candidates)->map(fn ($c) => [
                        'program_id'  => $c['program_id'],
                        'clientName'  => $c['clientName'],
                        'districtName'=> $c['districtName'],
                        'interviewDate' => $c['interviewDate'],
                    ])->values()->all(),
                ], false, false);
                Log::info("LasCMS linkByName: ambiguous ({$matchCount} matches) for {$case->name} ({$case->case_uid})");
                return null;
            }

            $program = $candidates[0];
            $externalId = $program['program_id'] ?? null;

            if (!$externalId) return null;

            $case->update([
                'external_case_id'   => $externalId,
                'external_synced_at' => now(),
                'meta'               => array_merge($case->meta ?? [], [
                    'las_link_status'      => 'name_matched',
                    'las_link_verified_at' => now()->toIso8601String(),
                    'las_match_method'     => 'name',
                    'las_matched_name'     => $program['clientName'],
                ]),
            ]);

            Log::info("LasCMS: Linked {$case->case_uid} → programs.id={$externalId} via name match ('{$case->name}')");
            return $externalId;

        } catch (\Exception $e) {
            Log::warning("LasCMS linkByName exception for {$case->case_uid}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Look up an existing programs record by CNIC and link it to the JusticeHub case.
     * Does NOT create a new record — only matches an existing one.
     * Returns the programs.id if matched, null otherwise.
     */
    public function linkByCnic(CaseRecord $case): ?int
    {
        if ($case->jh_cms_interview_id) {
            return $this->resolveDraft($case);
        }

        if (!$case->cnic) return null;

        $localCnic = $this->normalizeCnic($case->cnic);
        if (strlen($localCnic) !== 13) {
            $this->setLinkStatus($case, 'invalid_cnic', [], false, true);
            return null;
        }

        $formattedCnic = $this->formatCnic($case->cnic);

        try {
            $response = $this->http()->get("{$this->baseUrl}/cases/lookup", [
                'cnic' => $formattedCnic,  // send formatted: 42201-1234567-1
            ]);

            if (!$response->successful()) {
                $status = match ($response->status()) {
                    404 => 'not_found',
                    409 => 'ambiguous',
                    default => 'lookup_failed',
                };
                $this->setLinkStatus($case, $status, [
                    'match_count' => $response->json('match_count'),
                    'candidates'  => $response->json('candidates', []),
                ], false, in_array($response->status(), [404, 409], true));
                Log::info("LasCMS linkByCnic: no match for CNIC {$case->cnic} ({$case->case_uid}) — HTTP {$response->status()}");
                return null;
            }

            $matchedCnic = $this->normalizeCnic($response->json('cnic'));
            if (!hash_equals($localCnic, $matchedCnic)) {
                $this->setLinkStatus($case, 'cnic_mismatch', [], false, true);
                Log::warning("LasCMS linkByCnic: returned CNIC mismatch for {$case->case_uid}");
                return null;
            }

            $externalId = $response->json('program_id')
                ?? $response->json('id')
                ?? $response->json('data.id');

            if (!$externalId) {
                Log::info("LasCMS linkByCnic: response OK but no id for CNIC {$case->cnic} ({$case->case_uid})");
                return null;
            }

            $case->update([
                'external_case_id'   => $externalId,
                'external_synced_at' => now(),
                'meta'               => array_merge($case->meta ?? [], [
                    'las_link_status'      => 'verified',
                    'las_link_verified_at' => now()->toIso8601String(),
                ]),
            ]);

            Log::info("LasCMS: Linked {$case->case_uid} → programs.id={$externalId} via CNIC match");
            return $externalId;

        } catch (\Exception $e) {
            Log::warning("LasCMS linkByCnic exception for {$case->case_uid}: " . $e->getMessage());
            return null;
        }
    }

    protected function setLinkStatus(
        CaseRecord $case,
        string $status,
        array $details = [],
        bool $touchSyncTime = false,
        bool $clearLink = false,
    ): void
    {
        $meta = array_merge($case->meta ?? [], ['las_link_status' => $status]);

        foreach ($details as $key => $value) {
            if ($value !== null && $value !== []) {
                $meta['las_link_' . $key] = $value;
            }
        }

        $payload = ['meta' => $meta];
        if ($touchSyncTime) {
            $payload['external_synced_at'] = now();
        }
        if ($clearLink) {
            $payload['external_case_id'] = null;
            $payload['external_synced_at'] = null;
        }

        $case->update($payload);
    }

    /**
     * Push a JusticeHub case to LAS CMS via API.
     * Creates a CMS draft interview; the lawyer later converts it to a program.
     * Returns programs.id only when the CMS response already contains one.
     */
    public function pushCase(CaseRecord $case): ?int
    {
        if ($case->external_case_id) {
            return $case->external_case_id;
        }

        if ($case->jh_cms_interview_id) {
            return $this->resolveDraft($case);
        }

        $uniqueNumber = now()->year . '-JusticeHub-' . $case->id . '-' . ($case->district ?: 'Unknown');
        $assignedUser = $case->getAssignedUser();

        $payload = [
            'justicehub_case_uid' => $case->case_uid,
            'programName'         => 'JusticeHub',
            'caseReferred'        => 'Justicehub',
            'districtName'        => $case->district ?: 'Unknown',
            'interviewDate'       => $case->intake_date?->format('Y-m-d'),
            'interviewerName'     => $case->assigned_to ?: $case->staff_receiving ?: 'JusticeHub',
            'clientName'          => $case->name,
            'fatherHusbandName'   => $case->father_husband_name ?: '-',
            'contactNumber'       => $case->primary_contact ?: '-',
            'cnic'                => $this->formatCnic($case->cnic ?? ''),
            'gender'              => $case->gender ?: 'Not specified',
            'age'                 => $case->age,
            'religion'            => $case->religion ?: 'Not specified',
            'relationShip'        => 'Self',
            'caseFacts'           => $case->issue_description,
            'caseSubmittedFAppro' => 'Yes',
            'caseApprovalStatus'  => 'Pending',
            'lawyer1'             => $case->assigned_to,
            'lawyerEmail'         => $assignedUser?->email,
            'natureOfCase'        => [$case->primary_issue],
            'currentCaseStatus'   => $this->mapStatus($case->status),
            'UniqueNumber'        => $uniqueNumber,
            'uniqueYear'          => (string) now()->year,
            'username'            => 'JusticeHub-API',
        ];

        try {
            $response = $this->http()->post("{$this->baseUrl}/cases", $payload);

            if ($response->successful()) {
                $externalId = $response->json('program_id') ?? $response->json('id') ?? $response->json('data.id');
                $interviewId = $response->json('interview_id');
                $interviewStatus = $response->json('status', 'draft');

                if (!$externalId && $interviewId) {
                    $this->markDraftLinked($case, (int) $interviewId, $interviewStatus);
                    Log::info("LasCMS: Created draft interview #{$interviewId} for {$case->case_uid}");

                    return null;
                }

                if (!$externalId) {
                    Log::error("LasCMS pushCase: API returned success but no id for {$case->case_uid}. Response: " . $response->body());
                    return null;
                }

                $this->markProgramLinked($case, (int) $externalId, $interviewId, $interviewStatus);

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

    /** Resolve a CMS draft interview to its final programs.id when available. */
    public function resolveDraft(CaseRecord $case): ?int
    {
        try {
            $caseUid = rawurlencode($case->case_uid);
            $response = $this->http()->get("{$this->baseUrl}/cases/source/{$caseUid}");

            if ($response->status() === 404) {
                return null;
            }

            if (!$response->successful()) {
                Log::warning("LasCMS resolveDraft failed for {$case->case_uid}: HTTP {$response->status()} - " . $response->body());

                return null;
            }

            $programId = $response->json('program_id');
            $interviewId = $response->json('interview_id') ?? $case->jh_cms_interview_id;
            $status = $response->json('status', 'draft');

            if ($programId) {
                $this->markProgramLinked($case, (int) $programId, $interviewId, $status);
                Log::info("LasCMS: Resolved {$case->case_uid} draft to programs.id={$programId}");

                return (int) $programId;
            }

            if ($interviewId) {
                $this->markDraftLinked($case, (int) $interviewId, $status);
            }
        } catch (\Exception $e) {
            Log::warning("LasCMS resolveDraft exception for {$case->case_uid}: " . $e->getMessage());
        }

        return null;
    }

    private function markDraftLinked(CaseRecord $case, int $interviewId, ?string $status): void
    {
        $case->update([
            'jh_cms_interview_id' => $interviewId,
            'jh_cms_interview_status' => $status ?: 'draft',
            'external_synced_at' => now(),
            'meta' => array_merge($case->meta ?? [], [
                'las_link_status' => 'draft',
                'las_interview_id' => $interviewId,
                'las_interview_status' => $status ?: 'draft',
            ]),
        ]);
    }

    private function markProgramLinked(
        CaseRecord $case,
        int $programId,
        int|string|null $interviewId = null,
        ?string $interviewStatus = null,
    ): void {
        $case->update([
            'external_case_id' => $programId,
            'jh_cms_interview_id' => $interviewId ?: $case->jh_cms_interview_id,
            'jh_cms_interview_status' => $interviewStatus ?: 'converted',
            'external_synced_at' => now(),
            'meta' => array_merge($case->meta ?? [], [
                'las_link_status' => 'verified',
                'las_link_verified_at' => now()->toIso8601String(),
                'las_interview_id' => $interviewId ?: $case->jh_cms_interview_id,
                'las_interview_status' => $interviewStatus ?: 'converted',
            ]),
        ]);
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
    /**
     * Fetch the full audit timeline from LAS CMS for a case.
     * Returns array of timeline events or empty array on failure.
     */
    public function fetchTimeline(int $externalId): array
    {
        try {
            $response = $this->http()->get("{$this->baseUrl}/cases/{$externalId}/timeline");

            if (!$response->successful()) {
                Log::warning("LasCMS fetchTimeline: HTTP {$response->status()} for id={$externalId}");
                return [];
            }

            return $response->json('timeline') ?? [];

        } catch (\Exception $e) {
            Log::warning("LasCMS fetchTimeline exception for id={$externalId}: " . $e->getMessage());
            return [];
        }
    }

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

            $hearings = $response->json('hearings') ?? $response->json('data') ?? $response->json() ?? [];
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

            // Sync LAS CMS status → JusticeHub status column
            $externalStatus = $data['currentCaseStatus'] ?? null;
            $newStatus = match(strtolower((string) $externalStatus)) {
                'decided', 'disposed', 'closed'      => \App\Enums\CaseStatus::Closed,
                'settled/compromise', 'settled'      => \App\Enums\CaseStatus::Settlement,
                'running', 'pending', 'active', ''   => null,  // don't change
                default                              => null,
            };

            $updatePayload = [
                'meta'               => array_merge($case->meta ?? [], $metaUpdates),
                'external_synced_at' => now(),
            ];

            // Only close — never reopen a case from LAS CMS
            if ($newStatus && $case->status === \App\Enums\CaseStatus::Active) {
                $updatePayload['status'] = $newStatus;
                Log::info("LasCMS: Synced status for {$case->case_uid} → {$newStatus->value} (from LAS: {$externalStatus})");
            }

            if ($metaUpdates || $newStatus) {
                $case->update($updatePayload);
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
