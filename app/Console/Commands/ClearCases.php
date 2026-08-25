<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearCases extends Command
{
    protected $signature   = 'cases:clear';
    protected $description = 'Delete all case-related data from the database (IRREVERSIBLE)';

    public function handle(): int
    {
        $this->warn('⚠  This will permanently delete ALL case data:');
        $this->line('   case_messages, service_encounters, case_referrals, documents,');
        $this->line('   feedback, complaints, notifications, and cases.');

        if (! $this->confirm('Are you absolutely sure?', false)) {
            $this->info('Aborted. No data was deleted.');
            return self::SUCCESS;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = [
            'case_messages',
            'service_encounters',
            'case_referrals',
            'documents',
            'feedback',
            'complaints',
            'notifications',
            'cases',
        ];

        foreach ($tables as $table) {
            try {
                $count = DB::table($table)->count();
                DB::table($table)->truncate();
                $this->line("  Cleared <fg=yellow>{$table}</> ({$count} rows)");
            } catch (\Exception $e) {
                $this->warn("  Skipped {$table}: " . $e->getMessage());
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('All case data cleared.');
        return self::SUCCESS;
    }
}
