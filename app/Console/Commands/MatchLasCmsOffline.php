<?php

namespace App\Console\Commands;

use App\Models\CaseRecord;
use App\Support\LasCmsIdentity;
use Illuminate\Console\Command;

class MatchLasCmsOffline extends Command
{
    protected $signature = 'las:match-offline
                            {file : Path to programs_export.txt from LAS CMS}
                            {--dry-run : Show matches without saving}
                            {--name-fallback : Deprecated compatibility option; matching always requires CNIC}
                            {--overwrite : Overwrite existing external_case_id links}';

    protected $description = 'Match JusticeHub cases to LAS CMS programs using an offline export file (id|name|cnic|district)';

    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return self::FAILURE;
        }

        $this->info("Loading programs export...");
        $byCnic = $this->loadExport($filePath);
        $this->info("  Loaded " . array_sum(array_map('count', $byCnic)) . " LAS CMS records.");
        $this->info("  " . count($byCnic) . " unique CNICs.");

        $dryRun       = (bool) $this->option('dry-run');
        $overwrite    = (bool) $this->option('overwrite');

        $query = CaseRecord::query()
            ->courtRepresentation()->whereNull('jh_cms_interview_id');

        if (!$overwrite) {
            $query->whereNull('external_case_id');
        }

        $cases  = $query->orderBy('id')->get();
        $total  = $cases->count();

        $this->info("Processing {$total} JusticeHub cases...\n");

        $counts = [
            'cnic_matched'     => 0,
            'cnic_ambiguous'   => 0,
            'not_matched'      => 0,
            'invalid_cnic'     => 0,
            'skipped_no_data'  => 0,
        ];

        foreach ($cases as $case) {
            $result = $this->matchCase($case, $byCnic);

            $status    = $result['status'];
            $programId = $result['program_id'] ?? null;
            $counts[$status] = ($counts[$status] ?? 0) + 1;

            $line = "{$case->case_uid}: {$status}";
            if ($programId) {
                $line .= " → LAS #{$programId} ({$result['matched_name']})";
            }
            if (!empty($result['candidates'])) {
                $line .= " [" . implode(', ', array_map(fn($c) => "#{$c['id']} {$c['name']}", $result['candidates'])) . "]";
            }
            $this->line($line);

            if ($programId && !$dryRun) {
                $meta = array_merge($case->meta ?? [], [
                    'las_link_status'      => $status,
                    'las_link_verified_at' => now()->toIso8601String(),
                    'las_match_method'     => $result['method'],
                    'las_matched_name'     => $result['matched_name'] ?? null,
                ]);

                unset($meta['las_candidates'], $meta['las_link_candidates'], $meta['las_link_match_count']);
                $case->update([
                    'external_case_id'   => $programId,
                    'external_synced_at' => now(),
                    'meta'               => $meta,
                ]);
            } elseif (!$programId && !$dryRun && in_array($status, ['invalid_cnic', 'not_matched', 'cnic_ambiguous', 'skipped_no_data'], true)) {
                $meta = array_merge($case->meta ?? [], [
                    'las_link_status'    => $status,
                    'las_match_method'   => $result['method'] ?? null,
                    'las_candidates'     => $result['candidates'] ?? [],
                ]);
                unset($meta['las_link_verified_at'], $meta['las_link_candidates'], $meta['las_link_match_count']);
                $case->update(['meta' => $meta, 'external_case_id' => null, 'external_synced_at' => null]);
            }
        }

        $this->newLine();
        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Results:");
        foreach ($counts as $status => $count) {
            if ($count > 0) {
                $this->info("  " . str_pad(ucwords(str_replace('_', ' ', $status)), 20) . ": {$count}");
            }
        }

        return self::SUCCESS;
    }

    /** Index the export by valid CNIC; names and districts never establish a link. */
    private function loadExport(string $filePath): array
    {
        $byCnic = [];

        $handle = fopen($filePath, 'r');
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($line === '') continue;

            $parts = explode('|', $line, 4);
            if (count($parts) < 4) continue;

            [$id, $name, $cnic, $district] = $parts;
            $id = (int) $id;
            if ($id < 1) continue;

            $record = [
                'id'       => $id,
                'name'     => trim($name),
                'cnic'     => trim($cnic),
                'district' => trim($district),
            ];

            // Index by CNIC digits (skip placeholders like 888)
            $digits = preg_replace('/\D/', '', $cnic);
            if (strlen($digits) === 13) {
                $byCnic[$digits][] = $record;
            }

        }
        fclose($handle);

        return $byCnic;
    }

    private function matchCase(CaseRecord $case, array $byCnic): array
    {
        $cnic = LasCmsIdentity::normalizeCnic($case->cnic);
        if (strlen($cnic) !== 13) {
            return ['status' => 'invalid_cnic', 'program_id' => null, 'method' => 'cnic'];
        }
        $matches = collect($byCnic[$cnic] ?? [])
            ->filter(fn ($record) => LasCmsIdentity::matches($case, $record))
            ->unique('id')->values()->all();
        if (count($matches) === 1) {
            return [
                'status' => 'cnic_matched',
                'program_id' => $matches[0]['id'],
                'matched_name' => $matches[0]['name'],
                'method' => 'cnic',
            ];
        }
        return [
            'status' => $matches ? 'cnic_ambiguous' : 'not_matched',
            'program_id' => null,
            'method' => 'cnic',
            'candidates' => $matches,
        ];
    }

}
