<?php

namespace App\Console\Commands;

use App\Models\CaseRecord;
use Illuminate\Console\Command;

class FixImportedMeta extends Command
{
    protected $signature   = 'cases:fix-imported-meta';
    protected $description = 'Fix double-encoded meta and formula UIDs from Excel import';

    public function handle(): int
    {
        // 1. Fix formula UIDs
        $formulas = CaseRecord::where('case_uid', 'like', '=%')->get();
        foreach ($formulas as $c) {
            $hub = str_replace(['JH-', '-01'], '', $c->hub_id);
            $hubName = match ($c->hub_id) {
                'JH-DAD-01' => 'Dadu',
                'JH-HYD-01' => 'Hyderabad',
                'JH-SBA-01' => 'SBA',
                'JH-SAN-01' => 'Sanghar',
                default      => $hub,
            };
            $maxNum = CaseRecord::where('case_uid', 'like', "LAS-{$hubName}-%")
                ->get()
                ->map(fn($r) => (int) str_replace("LAS-{$hubName}-", '', $r->case_uid))
                ->max() ?? 0;
            $newUid = "LAS-{$hubName}-" . ($maxNum + 1);
            $c->update(['case_uid' => $newUid, 'case_ref' => $newUid]);
            $this->info("Fixed formula UID: {$c->id} → {$newUid}");
        }
        $this->info("Fixed {$formulas->count()} formula UIDs.");

        // 2. Fix double-encoded meta
        $fixed = 0;
        CaseRecord::whereNotNull('meta')
            ->where('meta', 'like', '%imported_from%')
            ->chunkById(100, function ($cases) use (&$fixed) {
                foreach ($cases as $c) {
                    $raw = $c->getRawOriginal('meta');
                    $decoded = json_decode($raw, true);
                    if (is_string($decoded)) {
                        $inner = json_decode($decoded, true);
                        if (is_array($inner)) {
                            $c->meta = $inner;
                            $c->save();
                            $fixed++;
                        }
                    }
                }
            });
        $this->info("Fixed double-encoded meta for {$fixed} cases.");

        return 0;
    }
}
