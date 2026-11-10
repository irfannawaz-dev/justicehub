<?php

namespace App\Services;

use App\Models\CaseRecord;
use App\Models\ServiceEncounter;
use App\Support\LasCmsIdentity;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
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
        return LasCmsIdentity::normalizeCnic($cnic);
    }

    /** Kept for older callers; a name alone must never establish a link. */
    public function linkByName(CaseRecord $case): ?int
    {
        return $this->linkByCnic($case);
    }

    /** Match existing court cases by CNIC; never create a CMS record here. */
    public function linkByCnic(CaseRecord $case): ?int
    {
        if (!$case->isCourtRepresentation()) return null;
        if ($case->jh_cms_interview_id) return $this->resolveDraft($case);

        if (strlen($this->normalizeCnic($case->cnic)) !== 13) {
            $this->setLinkStatus($case, 'invalid_cnic', [], false, true);
            return null;
        }

        try {
            $response = $this->http()->get("{$this->baseUrl}/cases/lookup", [
                'cnic' => $this->formatCnic($case->cnic),
            ]);
            if (!$response->successful() && $response->status() !== 409) {
                $this->setLinkStatus($case, $response->status() === 404 ? 'not_found' : 'lookup_failed', [], false, $response->status() === 404);
                return null;
            }

            $data = $response->json('data') ?? $response->json() ?? [];
            if (!is_array($data)) {
                $this->setLinkStatus($case, 'lookup_failed');
                return null;
            }
            $candidates = $data['candidates'] ?? [$data];
            if (!is_array($candidates)) {
                $this->setLinkStatus($case, 'lookup_failed');
                return null;
            }
            $matches = [];
            $incomplete = false;
            foreach ($candidates as $candidate) {
                if (!is_array($candidate)) { $incomplete = true; continue; }
                $id = (int) ($candidate['program_id'] ?? $candidate['id'] ?? 0);
                if ($id < 1) { $incomplete = true; continue; }
                if (!isset($candidate['cnic'])) {
                    $candidate = $this->readProgram($id);
                    if (!$candidate) { $incomplete = true; continue; }
                }
                if (LasCmsIdentity::matches($case, $candidate)) {
                    $matches[$id] = array_merge($candidate, ['program_id' => $id]);
                }
            }

            $reportedCount = $data['match_count'] ?? $response->json('match_count', count($candidates));
            $incomplete = $incomplete || (int) $reportedCount > count($candidates);
            if (count($matches) === 1 && !$incomplete) {
                $id = (int) array_key_first($matches);
                $this->markCnicLinked($case, $id);
                return $id;
            }

            $this->setLinkStatus($case, $matches || $response->status() === 409 ? 'ambiguous' : 'cnic_mismatch', [
                'match_count' => count($matches),
                'candidates' => array_values($matches),
            ], false, true);
        } catch (\Exception $e) {
            $this->setLinkStatus($case, 'lookup_failed');
            Log::warning("LasCMS lookup failed for {$case->case_uid}: " . $e->getMessage());
        }
        return null;
    }

    private function readProgram(int $id): ?array
    {
        $response = $this->http()->get("{$this->baseUrl}/cases/{$id}");
        if (!$response->successful()) return null;
        $data = $response->json('data') ?? $response->json();
        if (!is_array($data)) return null;
        $returnedId = $data['program_id'] ?? $data['id'] ?? $id;
        return (int) $returnedId === $id ? $data : null;
    }

    private function markCnicLinked(CaseRecord $case, int $id): void
    {
        $this->setLinkStatus($case, 'verified', [], true);
        $case->update([
            'external_case_id' => $id,
            'meta' => array_merge($case->meta ?? [], [
                'las_match_method' => 'cnic',
                'las_link_verified_at' => now()->toIso8601String(),
            ]),
        ]);
    }

    /** Manual selection still requires a matching CNIC. */
    public function linkProgram(CaseRecord $case, int $id): ?int
    {
        if (!$case->isCourtRepresentation() || strlen($this->normalizeCnic($case->cnic)) !== 13) return null;
        try {
            $data = $this->readProgram($id);
            if (!$data || !LasCmsIdentity::matches($case, $data)) return null;
            $this->markCnicLinked($case, $id);
            return $id;
        } catch (\Exception $e) {
            Log::warning("LasCMS selection failed for {$case->case_uid}: " . $e->getMessage());
            return null;
        }
    }

    /** Read only the matched program, or the program resolved from our draft UID. */
    public function fetchCase(CaseRecord $case): ?array
    {
        if (!$case->isCourtRepresentation() || !$case->external_case_id) return null;
        try {
            $data = $this->readProgram((int) $case->external_case_id);
            if (!$data) return null;
            return $this->acceptProgram($case, $data);
        } catch (\Exception $e) {
            Log::warning("LasCMS case fetch failed for {$case->case_uid}: " . $e->getMessage());
            return null;
        }
    }

    /** Fetch the litigation board's programs with bounded parallel requests. */
    public function fetchCases(Collection $cases): array
    {
        $eligible = $cases->filter(fn (CaseRecord $case) => $case->isCourtRepresentation() && $case->external_case_id);
        if ($eligible->isEmpty()) return [];

        $responses = Http::pool(function (Pool $pool) use ($eligible) {
            foreach ($eligible as $case) {
                $pool->as($case->id)->withHeaders([
                    'X-JusticeHub-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])->timeout(15)->get("{$this->baseUrl}/cases/{$case->external_case_id}");
            }
        }, concurrency: 5);

        $programs = [];
        foreach ($eligible as $case) {
            $response = $responses[$case->id] ?? null;
            if (!$response instanceof Response || !$response->successful()) continue;
            $data = $response->json('data') ?? $response->json();
            if (!is_array($data)) continue;
            $program = $this->acceptProgram($case, $data);
            if ($program) $programs[$case->id] = $program;
        }
        return $programs;
    }

    private function acceptProgram(CaseRecord $case, array $data): ?array
    {
        $returnedId = $data['program_id'] ?? $data['id'] ?? $case->external_case_id;
        if ((int) $returnedId !== (int) $case->external_case_id) return null;

        $fromDraft = data_get($case->meta, 'las_match_method') === 'source'
            && data_get($case->meta, 'las_source_case_uid') === $case->case_uid;
        $sourceMatches = !isset($data['justicehub_case_uid']) || $data['justicehub_case_uid'] === $case->case_uid;
        if (($fromDraft && !$sourceMatches) || (!$fromDraft && !LasCmsIdentity::matches($case, $data))) {
            $this->setLinkStatus($case, 'identity_mismatch', [], false, true);
            return null;
        }
        if (!$fromDraft) $this->markCnicLinked($case, (int) $case->external_case_id);
        return $data;
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
        unset($meta['las_link_candidates'], $meta['las_candidates'], $meta['las_link_match_count']);
        if ($status !== 'verified') {
            unset($meta['las_link_verified_at'], $meta['las_match_method'], $meta['las_source_case_uid']);
        }
        if ($clearLink) {
            foreach (['court_name', 'case_number', 'next_hearing', 'case_stage', 'case_decision', 'external_status', 'cms_approval_status'] as $key) {
                unset($meta[$key]);
            }
        }

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
        if (!$case->isCourtRepresentation()) return null;

        if ($case->external_case_id) {
            return $this->fetchCase($case) ? (int) $case->external_case_id : null;
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
                $interviewStatus = $response->json('status', 'draft');
                $interviewId = $response->json('interview_id')
                    ?? ($interviewStatus === 'draft' ? ($response->json('id') ?? $response->json('data.id')) : null);
                // Generic id may be the interview ID, never treat it as a program when a draft exists.
                $externalId = $response->json('program_id') ?? ($interviewId ? null : ($response->json('id') ?? $response->json('data.id')));

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
        if (!$case->isCourtRepresentation()) return null;

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

            if ($response->json('justicehub_case_uid', $case->case_uid) !== $case->case_uid) {
                $this->setLinkStatus($case, 'source_mismatch', [], false, true);
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
        $this->setLinkStatus($case, 'draft', [], false, true);
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
                'las_match_method' => 'source',
                'las_source_case_uid' => $case->case_uid,
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
        if (!$this->fetchCase($case)) {
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
    public function fetchTimeline(CaseRecord $case): array
    {
        if (!$this->fetchCase($case)) return [];
        $externalId = $case->external_case_id;
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

    public function fetchCaseWithHearings(CaseRecord $case): ?array
    {
        $data = $this->fetchCase($case);
        if (!$data) return null;
        $externalId = $case->external_case_id;
        try {
            $hearingResp = $this->http()->get("{$this->baseUrl}/cases/{$externalId}/hearings");
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
        if (!$this->fetchCase($case)) {
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
                        'external_program_id' => $case->external_case_id,
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
        $data = $this->fetchCase($case);
        if (!$data) return;

        try {
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
        $cases = CaseRecord::courtRepresentation()->whereNotNull('external_case_id')->get();
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
