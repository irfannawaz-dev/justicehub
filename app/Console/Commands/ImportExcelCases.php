<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportExcelCases extends Command
{
    protected $signature   = 'cases:import-excel {--dry-run : Preview counts without writing to DB}';
    protected $description = 'Truncate cases and import from cases_import.json (5 hubs: Hyderabad, Dadu, Islamabad, SBA, Sanghar)';

    public function handle(): int
    {
        $jsonFile = storage_path('app/cases_import.json');
        if (! file_exists($jsonFile)) {
            $this->error("JSON not found: {$jsonFile}");
            $this->line('Run the Python extraction script first.');
            return 1;
        }

        $dryRun = $this->option('dry-run');

        $this->info('Loading extracted JSON…');
        $allRecords = json_decode(file_get_contents($jsonFile), true);
        $this->info('  ' . count($allRecords) . ' records loaded.');

        if (! $dryRun) {
            if (! $this->confirm('This will TRUNCATE the cases table and all related data. Continue?')) {
                $this->warn('Aborted.');
                return 0;
            }

            $this->info('Truncating related tables…');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('case_messages')->truncate();
            DB::table('case_referrals')->truncate();
            DB::table('case_pathway')->truncate();
            DB::table('service_encounters')->truncate();
            DB::table('documents')->truncate();
            DB::table('complaints')->truncate();
            DB::table('feedback')->truncate();
            DB::table('cases')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->info('  Done.');
        }

        // Group by hub for display
        $perHub = [];
        $now    = now()->toDateTimeString();

        $records = array_map(function ($r) use ($now) {
            $r['created_at'] = $now;
            $r['updated_at'] = $now;
            return $r;
        }, $allRecords);

        foreach ($records as $r) {
            $perHub[$r['hub_id']] = ($perHub[$r['hub_id']] ?? 0) + 1;
        }

        if (! $dryRun) {
            $this->info('Inserting ' . count($records) . ' cases…');
            foreach (array_chunk($records, 250) as $i => $chunk) {
                DB::table('cases')->insert($chunk);
                $this->output->write('.');
            }
            $this->newLine();
            $this->info('Import complete.');
        }

        $this->newLine();
        $rows = collect($perHub)->map(fn ($count, $hub) => [$hub, $count])->values()->toArray();
        $this->table(['Hub ID', 'Cases'], $rows);
        $this->info('Total: ' . count($records) . ' cases' . ($dryRun ? ' (DRY RUN — nothing written)' : ' imported.'));

        return 0;
    }
}
