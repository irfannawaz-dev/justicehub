<?php

namespace App\Console\Commands;

use App\Models\CaseRecord;
use App\Services\LasCmsSyncService;
use Illuminate\Console\Command;

class PushCasesToLasCms extends Command
{
    protected $signature = 'las:push-cases
                            {--case= : Push a specific case by case_uid}
                            {--dry-run : Show what would be pushed without actually pushing}';

    protected $description = 'Push unsynced Court Representation cases to LAS CMS API';

    public function handle(): int
    {
        $sync = new LasCmsSyncService();

        // Single case mode
        if ($caseUid = $this->option('case')) {
            $case = CaseRecord::where('case_uid', $caseUid)->first();
            if (!$case) {
                $this->error("Case {$caseUid} not found.");
                return 1;
            }
            if ($case->external_case_id) {
                $this->warn("Case {$caseUid} already linked → programs.id={$case->external_case_id}");
                return 0;
            }
            if ($this->option('dry-run')) {
                $this->info("[DRY RUN] Would push: {$caseUid} — {$case->name} ({$case->assigned_pathway})");
                return 0;
            }
            $id = $sync->pushCase($case);
            $id ? $this->info("Pushed {$caseUid} → programs.id={$id}")
                : $this->error("Failed to push {$caseUid} — check logs.");
            return $id ? 0 : 1;
        }

        // Bulk mode — all unsynced Court Representation cases
        $cases = CaseRecord::whereNull('external_case_id')
            ->where('assigned_pathway', 'Court Representation')
            ->orderBy('intake_date')
            ->get();

        $total = $cases->count();

        if ($total === 0) {
            $this->info('All Court Representation cases are already synced.');
            return 0;
        }

        $this->info("Found {$total} unsynced case(s) to push...");

        if ($this->option('dry-run')) {
            foreach ($cases as $case) {
                $this->line("  [DRY RUN] {$case->case_uid} — {$case->name} ({$case->district})");
            }
            return 0;
        }

        $bar     = $this->output->createProgressBar($total);
        $pushed  = 0;
        $failed  = 0;

        $bar->start();

        foreach ($cases as $case) {
            $id = $sync->pushCase($case);
            if ($id) {
                $pushed++;
            } else {
                $failed++;
                $this->newLine();
                $this->warn("  Failed: {$case->case_uid} ({$case->name}) — check laravel.log");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Pushed: {$pushed} | Failed: {$failed} | Total: {$total}");

        return $failed > 0 ? 1 : 0;
    }
}
