<?php

namespace App\Console\Commands;

use App\Models\CaseRecord;
use Illuminate\Console\Command;

class BackfillCnicHash extends Command
{
    protected $signature = 'cases:backfill-cnic-hash';
    protected $description = 'Populate cnic_hash for all existing cases that have a CNIC but no hash';

    public function handle(): int
    {
        $total   = 0;
        $filled  = 0;
        $skipped = 0;

        CaseRecord::whereNull('cnic_hash')
            ->whereNotNull('cnic')
            ->where('cnic', '!=', '')
            ->chunkById(200, function ($cases) use (&$total, &$filled, &$skipped) {
                foreach ($cases as $case) {
                    $total++;
                    $plain = $case->cnic; // accessor decrypts if needed

                    if (! $plain) {
                        $skipped++;
                        continue;
                    }

                    $normalized = preg_replace('/\D/', '', $plain);

                    if (! $normalized) {
                        $skipped++;
                        continue;
                    }

                    $case->cnic_hash = hash('sha256', $normalized);
                    $case->saveQuietly(); // skip model events to avoid re-encrypting
                    $filled++;
                }
            });

        $this->info("Done. Processed: {$total} | Filled: {$filled} | Skipped: {$skipped}");

        return self::SUCCESS;
    }
}
