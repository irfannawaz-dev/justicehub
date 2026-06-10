<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('locations')->truncate();

        $file = base_path('MasterReferenceDatasetforSindh.xlsx');
        if (! file_exists($file)) {
            $this->command->error("Excel file not found: {$file}");
            return;
        }

        // Hub → district mapping
        $hubMap = DB::table('hubs')->where('is_active', true)
            ->pluck('id', 'district')
            ->toArray();

        $reader = new Xlsx();
        $spreadsheet = $reader->load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $maxRow = $sheet->getHighestRow();

        $batch = [];
        for ($r = 2; $r <= $maxRow; $r++) {
            $province = $sheet->getCell("B{$r}")->getValue() ?? '';
            $district = $sheet->getCell("D{$r}")->getValue() ?? '';
            $taluka   = $sheet->getCell("E{$r}")->getValue() ?? '';
            $ucName   = $sheet->getCell("K{$r}")->getValue() ?? '';
            $ucOfficial = $sheet->getCell("L{$r}")->getValue() ?? '';

            if (! $district) continue;

            // Use column K (cleaned name), fall back to column L (official entry)
            $uc = $ucName ?: $ucOfficial ?: null;

            $batch[] = [
                'province'       => $province ?: 'Sindh',
                'district'       => $district,
                'taluka'         => $taluka ?: null,
                'union_council'  => $uc,
                'hub_id'         => $hubMap[$district] ?? null,
            ];

            if (count($batch) >= 500) {
                DB::table('locations')->insert($batch);
                $batch = [];
            }
        }

        if (count($batch) > 0) {
            DB::table('locations')->insert($batch);
        }

        $count = DB::table('locations')->count();
        $this->command->info("Imported {$count} location records.");
    }
}
