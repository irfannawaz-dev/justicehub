<?php

namespace App\Console\Commands;

use App\Services\LasCmsSyncService;
use Illuminate\Console\Command;

class SyncLasCmsHearings extends Command
{
    protected $signature = 'las:sync-hearings {--case= : Sync a specific case by case_uid}';

    protected $description = 'Pull court hearings from LAS CMS for all linked cases (or a specific case)';

    public function handle(): int
    {
        $sync = new LasCmsSyncService();

        if ($caseUid = $this->option('case')) {
            $case = \App\Models\CaseRecord::where('case_uid', $caseUid)->first();
            if (!$case) {
                $this->error("Case {$caseUid} not found.");
                return 1;
            }
            if (!$case->external_case_id) {
                $this->warn("Case {$caseUid} is not linked to LAS CMS.");
                return 1;
            }
            $count = $sync->pullHearings($case);
            $this->info("Pulled {$count} new hearing(s) for {$caseUid}.");
            return 0;
        }

        $this->info('Syncing hearings from LAS CMS...');
        $result = $sync->pullAllHearings();
        $this->info("Done. {$result['hearings']} hearing(s) imported across {$result['cases']} case(s).");
        return 0;
    }
}
