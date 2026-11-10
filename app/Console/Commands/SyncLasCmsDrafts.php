<?php

namespace App\Console\Commands;

use App\Models\CaseRecord;
use App\Services\LasCmsSyncService;
use Illuminate\Console\Command;

class SyncLasCmsDrafts extends Command
{
    protected $signature = 'las:sync-drafts {--case= : Sync one JusticeHub case UID}';

    protected $description = 'Resolve LAS CMS draft interview IDs to final program IDs';

    public function handle(LasCmsSyncService $sync): int
    {
        $query = CaseRecord::courtRepresentation()
            ->whereNotNull('jh_cms_interview_id')
            ->whereNull('external_case_id');

        if ($caseUid = $this->option('case')) {
            $query->where('case_uid', $caseUid);
        }

        $cases = $query
            ->orderBy('external_synced_at')
            ->orderBy('id')
            ->limit(50)
            ->get();
        $resolved = 0;
        $pending = 0;

        foreach ($cases as $case) {
            $programId = $sync->resolveDraft($case);
            $case->refresh();

            if ($programId) {
                $resolved++;
                $this->info("{$case->case_uid}: linked to LAS #{$programId}");
            } else {
                $pending++;
                $this->line("{$case->case_uid}: {$case->jh_cms_interview_status} (interview #{$case->jh_cms_interview_id})");
            }
        }

        $this->newLine();
        $this->info("Resolved: {$resolved} | Pending: {$pending}");

        return self::SUCCESS;
    }
}
