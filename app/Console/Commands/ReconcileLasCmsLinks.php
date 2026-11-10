<?php

namespace App\Console\Commands;

use App\Models\CaseRecord;
use App\Services\LasCmsSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileLasCmsLinks extends Command
{
    protected $signature = 'las:reconcile-links
                            {--case= : Reconcile one case by case_uid}
                            {--all : Reconcile all Court Representation cases}
                            {--dry-run : Check matches without saving changes}
                            {--try-name : Fall back to name search when CNIC is invalid or not found}
                            {--delay=2200 : Delay between bulk requests in milliseconds}';

    protected $description = 'Verify and repair LAS CMS links using CNIC (with optional name fallback)';

    public function handle(LasCmsSyncService $sync): int
    {
        $query = CaseRecord::query()
            ->whereIn('assigned_pathway', ['Court Representation', 'Representation in Court'])
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

        $delay    = max(0, (int) $this->option('delay'));
        $dryRun   = (bool) $this->option('dry-run');
        $tryName  = (bool) $this->option('try-name');
        $counts   = [];

        foreach ($cases as $index => $case) {
            $previousId = $case->external_case_id;
            if ($dryRun) {
                DB::beginTransaction();
            }

            try {
                $matchedId = $sync->linkByCnic($case);
                $case->refresh();
                $linkStatus = data_get($case->meta, 'las_link_status', $matchedId ? 'verified' : 'failed');

                // Name fallback: try when CNIC is invalid or produced no match
                if (!$matchedId && $tryName && in_array($linkStatus, ['invalid_cnic', 'not_found'], true)) {
                    $matchedId  = $sync->linkByName($case);
                    $case->refresh();
                    $linkStatus = data_get($case->meta, 'las_link_status', $matchedId ? 'name_matched' : $linkStatus);
                }

                $status = $linkStatus;
            } finally {
                if ($dryRun) {
                    DB::rollBack();
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
