<?php

namespace App\Console\Commands;

use App\Models\CaseRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MatchLasCmsOffline extends Command
{
    protected $signature = 'las:match-offline
                            {file : Path to programs_export.txt from LAS CMS}
                            {--dry-run : Show matches without saving}
                            {--name-fallback : Also try name matching when CNIC fails}
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
        [$byCnic, $byName] = $this->loadExport($filePath);
        $this->info("  Loaded " . (array_sum(array_map('count', $byCnic)) + array_sum(array_map('count', $byName))) . " LAS CMS records.");
        $this->info("  " . count($byCnic) . " unique CNICs, " . count($byName) . " unique names.");

        $dryRun       = (bool) $this->option('dry-run');
        $nameFallback = (bool) $this->option('name-fallback');
        $overwrite    = (bool) $this->option('overwrite');

        $query = CaseRecord::query()
            ->whereIn('assigned_pathway', ['Court Representation', 'Representation in Court']);

        if (!$overwrite) {
            $query->whereNull('external_case_id');
        }

        $cases  = $query->orderBy('id')->get();
        $total  = $cases->count();

        $this->info("Processing {$total} JusticeHub cases...\n");

        $counts = [
            'cnic_matched'     => 0,
            'name_matched'     => 0,
            'cnic_ambiguous'   => 0,
            'name_ambiguous'   => 0,
            'not_matched'      => 0,
            'invalid_cnic'     => 0,
            'skipped_no_data'  => 0,
        ];

        foreach ($cases as $case) {
            $result = $this->matchCase($case, $byCnic, $byName, $nameFallback);

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

                $case->update([
                    'external_case_id'   => $programId,
                    'external_synced_at' => now(),
                    'meta'               => $meta,
                ]);
            } elseif (!$programId && !$dryRun && in_array($status, ['invalid_cnic', 'not_matched', 'cnic_ambiguous', 'name_ambiguous'], true)) {
                $meta = array_merge($case->meta ?? [], [
                    'las_link_status'    => $status,
                    'las_match_method'   => $result['method'] ?? null,
                    'las_candidates'     => $result['candidates'] ?? [],
                ]);
                $case->update(['meta' => $meta]);
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

    /**
     * Load export file into two lookup tables:
     *  $byCnic[digits13] = [ [id, name, district], ... ]
     *  $byName[normalized] = [ [id, name, cnic, district], ... ]
     */
    private function loadExport(string $filePath): array
    {
        $byCnic = [];
        $byName = [];

        $handle = fopen($filePath, 'r');
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($line === '') continue;

            $parts = explode('|', $line, 4);
            if (count($parts) < 4) continue;

            [$id, $name, $cnic, $district] = $parts;
            $id = (int) $id;

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

            // Index by normalized name
            $key = $this->normalizeName($name);
            if ($key !== '') {
                $byName[$key][] = $record;
            }
        }
        fclose($handle);

        return [$byCnic, $byName];
    }

    private function matchCase(CaseRecord $case, array $byCnic, array $byName, bool $nameFallback): array
    {
        // ── 1. CNIC match ──────────────────────────────────────
        if ($case->cnic) {
            $digits = preg_replace('/\D/', '', $case->cnic);

            if (strlen($digits) !== 13) {
                // Invalid CNIC — fall through to name if enabled
                $cnicStatus = 'invalid_cnic';
            } elseif (isset($byCnic[$digits])) {
                $matches = $byCnic[$digits];

                if (count($matches) === 1) {
                    return [
                        'status'       => 'cnic_matched',
                        'program_id'   => $matches[0]['id'],
                        'matched_name' => $matches[0]['name'],
                        'method'       => 'cnic',
                    ];
                }

                // Multiple CNIC matches — try to narrow by district
                $districtMatches = array_filter($matches, fn($m) => $this->districtMatch($m['district'], $case->district ?? ''));
                if (count($districtMatches) === 1) {
                    $m = array_values($districtMatches)[0];
                    return [
                        'status'       => 'cnic_matched',
                        'program_id'   => $m['id'],
                        'matched_name' => $m['name'],
                        'method'       => 'cnic+district',
                    ];
                }

                return [
                    'status'     => 'cnic_ambiguous',
                    'program_id' => null,
                    'method'     => 'cnic',
                    'candidates' => array_map(fn($m) => ['id' => $m['id'], 'name' => $m['name']], $matches),
                ];
            } else {
                $cnicStatus = 'not_matched';
            }
        } else {
            $cnicStatus = 'skipped_no_data';
        }

        // ── 2. Name fallback ───────────────────────────────────
        if ($nameFallback && $case->name && in_array($cnicStatus, ['invalid_cnic', 'not_matched', 'skipped_no_data'], true)) {
            $key     = $this->normalizeName($case->name);
            $matches = $byName[$key] ?? [];

            // Also try partial / soundex match across all names
            if (empty($matches)) {
                $matches = $this->soundexSearch($case->name, $byName);
            }

            if (count($matches) === 1) {
                return [
                    'status'       => 'name_matched',
                    'program_id'   => $matches[0]['id'],
                    'matched_name' => $matches[0]['name'],
                    'method'       => 'name',
                ];
            }

            if (count($matches) > 1) {
                // Try to narrow by district
                $districtMatches = array_filter($matches, fn($m) => $this->districtMatch($m['district'], $case->district ?? ''));
                if (count($districtMatches) === 1) {
                    $m = array_values($districtMatches)[0];
                    return [
                        'status'       => 'name_matched',
                        'program_id'   => $m['id'],
                        'matched_name' => $m['name'],
                        'method'       => 'name+district',
                    ];
                }

                return [
                    'status'     => 'name_ambiguous',
                    'program_id' => null,
                    'method'     => 'name',
                    'candidates' => array_slice(array_map(fn($m) => ['id' => $m['id'], 'name' => $m['name']], $matches), 0, 5),
                ];
            }
        }

        return ['status' => $cnicStatus, 'program_id' => null, 'method' => 'cnic'];
    }

    private function normalizeName(string $name): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $name)));
    }

    private function soundexSearch(string $name, array $byName): array
    {
        $targetSoundex = soundex($name);
        $results = [];

        foreach ($byName as $key => $records) {
            if (soundex($key) === $targetSoundex) {
                foreach ($records as $r) {
                    $results[] = $r;
                }
            }
        }

        return $results;
    }

    private function districtMatch(string $lasDistrict, string $jhDistrict): bool
    {
        $normalize = fn($d) => mb_strtolower(trim(preg_replace('/\s+/', ' ', $d)));
        $las = $normalize($lasDistrict);
        $jh  = $normalize($jhDistrict);

        if ($las === $jh) return true;

        // Handle "Karachi WEST" vs "Karachi West" etc.
        return str_contains($las, $jh) || str_contains($jh, $las);
    }
}
