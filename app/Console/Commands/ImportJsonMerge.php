<?php

namespace App\Console\Commands;

use App\Models\CaseRecord;
use Illuminate\Console\Command;

class ImportJsonMerge extends Command
{
    protected $signature   = 'cases:import-json {file? : Path to JSON file (default: storage/app/cases_merge.json)} {--dry-run}';
    protected $description = 'Import cases from a pre-extracted JSON file, skipping existing case_uids';

    public function handle(): int
    {
        $file = $this->argument('file') ?? storage_path('app/cases_merge.json');
        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $dryRun = $this->option('dry-run');
        if ($dryRun) $this->warn('DRY RUN — no data will be written.');

        $records = json_decode(file_get_contents($file), true);
        $this->info(count($records) . ' records in JSON file.');

        $existing = CaseRecord::pluck('case_uid')->flip()->toArray();
        $this->info(count($existing) . ' cases already in DB.');

        $inserted = 0;
        $skipped  = 0;
        $errors   = 0;
        $perHub   = [];

        foreach ($records as $i => $r) {
            $uid = $r['case_uid'] ?? null;
            if (! $uid) continue;

            if (isset($existing[$uid])) {
                $skipped++;
                continue;
            }

            if (! $dryRun) {
                try {
                    // Encode meta as JSON string if it's an array
                    if (isset($r['meta']) && is_array($r['meta'])) {
                        $r['meta'] = json_encode($r['meta']);
                    }
                    CaseRecord::create($r);
                } catch (\Throwable $e) {
                    $errors++;
                    if ($errors <= 10) {
                        $this->warn("  {$uid}: {$e->getMessage()}");
                    }
                    continue;
                }
            }

            $existing[$uid] = true;
            $inserted++;
            $hub = $r['hub_id'] ?? '?';
            $perHub[$hub] = ($perHub[$hub] ?? 0) + 1;
        }

        $this->newLine();
        if (count($perHub)) {
            $this->table(['Hub ID', 'New Cases'], collect($perHub)->map(fn($c, $h) => [$h, $c])->values()->toArray());
        }
        $this->info("Inserted: {$inserted}, Skipped: {$skipped}, Errors: {$errors}");
        if ($dryRun) $this->warn('DRY RUN — nothing was written.');

        return 0;
    }
}
