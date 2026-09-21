<?php

namespace App\Console\Commands;

use App\Models\CaseRecord;
use App\Services\LasCmsSyncService;
use Illuminate\Console\Command;

class ReconcileLasCmsLinks extends Command
{
    protected $signature = 'las:reconcile-links
                            {--case= : Reconcile one case by case_uid}
                            {--all : Reconcile all Court Representation cases}
                            {--dry-run : Check matches without saving changes}
                            {--delay=2200 : Delay between bulk requests in milliseconds}';

    protected $description = 'Verify and repair LAS CMS links using CNIC without changing stored CNIC formatting';

    public function handle(LasCmsSyncService $sync): int
    {
        $query = CaseRecord::query()
            ->where('assigned_pathway', 'Court Representation')
            ->whereNotNull('cnic');

        if ($caseUid = $this->option('case')) {
            $query->where('case_uid', $caseUid);
        } elseif (! $this->option('all')) {
            $this->error('Use --case=CASE_UID for one case or --all for every court case.');
            return self::FAILURE;
        }

        $cases = $query->orderBy('id')->get();
        if ($cases->isEmpty()) {
            $this->warn('No matching Court Representation cases were found.');
            return self::SUCCESS;
        }

        $delay = max(0, (int) $this->option('delay'));
        $dryRun = (bool) $this->option('dry-run');
        $counts = [];

        foreach ($cases as $index => $case) {
            $previousId = $case->external_case_id;
            if ($dryRun) {
                \DB::beginTransaction();
            }

            try {
                $matchedId = $sync->linkByCnic($case);
                $case->refresh();
                $status = data_get($case->meta, 'las_link_status', $matchedId ? 'verified' : 'failed');
            } finally {
                if ($dryRun) {
                    \DB::rollBack();
                }
            }

            $counts[$status] = ($counts[$status] ?? 0) + 1;

            $message = ($dryRun ? '[DRY RUN] ' : '') . "{$case->case_uid}: {$status}";
            if ($matchedId) {
                $message .= " (LAS #{$matchedId})";
                if ($previousId && (int) $previousId !== (int) $matchedId) {
                    $message .= " repaired from #{$previousId}";
                }
            }
            $this->line($message);

            if ($this->option('all') && $delay > 0 && $index < $cases->count() - 1) {
                usleep($delay * 1000);
            }
        }

        $this->newLine();
        foreach ($counts as $status => $count) {
            $this->info(ucwords(str_replace('_', ' ', $status)) . ": {$count}");
        }

        return self::SUCCESS;
    }
}
