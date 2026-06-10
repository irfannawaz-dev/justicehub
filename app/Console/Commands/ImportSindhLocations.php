<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportSindhLocations extends Command
{
    protected $signature   = 'locations:import-sindh {--fresh : Truncate existing location data before importing}';
    protected $description = 'Import District/Taluka/AdminUnitType/UnionCouncil data from MasterReferenceDatasetforSindh.xlsx';

    public function handle(): void
    {
        $path = base_path('MasterReferenceDatasetforSindh.xlsx');

        if (! file_exists($path)) {
            $this->error("File not found: {$path}");
            return;
        }

        $this->info('Loading spreadsheet…');

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $highestRow  = $sheet->getHighestRow();

        if ($this->option('fresh')) {
            DB::table('locations')->truncate();
            $this->warn('Existing location data cleared.');
        }

        // Build hub_id lookup by district
        $hubsByDistrict = DB::table('hubs')->pluck('id', 'district');

        $rows    = [];
        $skipped = 0;
        $now     = now();

        // Col mapping (1-based):
        // D(4)=District, G(7)=ParentLocalBody (Taluka), L(12)=OfficialUnitEntry (Union Council)
        for ($row = 2; $row <= $highestRow; $row++) {
            $district     = trim($sheet->getCellByColumnAndRow(4, $row)->getValue());
            $taluka       = trim($sheet->getCellByColumnAndRow(7, $row)->getValue()); // col G = Parent Local Body
            $unionCouncil = trim($sheet->getCellByColumnAndRow(12, $row)->getValue()); // col L = Official Unit Entry

            if (! $district) {
                $skipped++;
                continue;
            }

            $rows[] = [
                'province'      => 'Sindh',
                'district'      => $district,
                'taluka'        => $taluka ?: null,
                'union_council' => $unionCouncil ?: null,
                'hub_id'        => $hubsByDistrict[$district] ?? null,
            ];

            // Insert in chunks of 500
            if (count($rows) >= 500) {
                DB::table('locations')->insert($rows);
                $rows = [];
                $this->output->write('.');
            }
        }

        if (count($rows)) {
            DB::table('locations')->insert($rows);
        }

        $imported = $highestRow - 1 - $skipped;
        $this->newLine();
        $this->info("Done! Imported {$imported} records. Skipped {$skipped} blank rows.");
    }
}
