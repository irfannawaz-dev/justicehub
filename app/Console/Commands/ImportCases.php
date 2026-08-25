<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class ImportCases extends Command
{
    protected $signature   = 'cases:import {xlsx? : Path to the Excel file}';
    protected $description = 'Import SBA and Sanghar cases from Data Validation Sheet.xlsx';

    public function handle(): int
    {
        $xlsx = $this->argument('xlsx')
            ?? base_path('../Data Validation Sheet.xlsx');

        if (! file_exists($xlsx)) {
            $this->error("File not found: {$xlsx}");
            return self::FAILURE;
        }

        $script = base_path('scripts/extract_cases.py');

        $this->info("Reading Excel: {$xlsx}");
        $cmd    = 'python -u ' . escapeshellarg($script) . ' ' . escapeshellarg($xlsx);
        $output = shell_exec($cmd);

        if (! $output) {
            $this->error('Python script returned no output.');
            return self::FAILURE;
        }

        $records = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Failed to parse JSON from Python script:');
            $this->line(substr($output, 0, 500));
            return self::FAILURE;
        }

        $this->info('Records extracted: ' . count($records));

        $sba      = collect($records)->where('source_sheet', 'SBA')->count();
        $sanghar  = collect($records)->where('source_sheet', 'Sanghar')->count();
        $this->line("  SBA: {$sba}   Sanghar: {$sanghar}");

        if (! $this->confirm('Proceed with import?', true)) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        $now     = now()->toDateTimeString();
        $bar     = $this->output->createProgressBar(count($records));
        $bar->start();
        $errors  = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($records as $r) {
            $cnic        = $r['cnic'] ?? null;
            $cnicHash    = $cnic ? hash('sha256', $cnic) : null;
            $cnicEncrypted = $cnic ? Crypt::encryptString($cnic) : null;

            $row = [
                'case_uid'                => $r['case_uid'],
                'hub_id'                  => $r['hub_id'],
                'intake_date'             => $r['intake_date'] ?? now()->toDateString(),
                'intake_time'             => $r['intake_time'],
                'returning_client'        => $r['returning_client'],
                'staff_receiving'         => mb_substr($r['staff_receiving'] ?? '', 0, 255),
                'staff_designation'       => mb_substr($r['staff_designation'] ?? '', 0, 255),
                'consent'                 => $r['consent'],
                'no_consent_reason'       => $r['no_consent_reason'],
                'referral_source'         => mb_substr($r['referral_source'] ?? '', 0, 255),
                'referral_contact_person' => mb_substr($r['referral_contact_person'] ?? '', 0, 255),
                'name'                    => mb_substr($r['name'] ?? '', 0, 255),
                'father_husband_name'     => mb_substr($r['father_husband_name'] ?? '', 0, 255),
                'gender'                  => $r['gender'] ?? 'Not Specified',
                'age'                     => $r['age'] ?? 0,
                'cnic'                    => $cnicEncrypted,
                'cnic_hash'               => $cnicHash,
                'marital_status'          => $r['marital_status'],
                'religion'                => $r['religion'],
                'education_level'         => $r['education_level'],
                'occupation'              => mb_substr($r['occupation'] ?? '', 0, 255),
                'income_bracket'          => $r['income_bracket'],
                'disability_status'       => $r['disability_status'],
                'primary_contact'         => $r['primary_contact'],
                'alternative_contact'     => $r['alternative_contact'],
                'full_address'            => $r['full_address'],
                'union_council'           => mb_substr($r['union_council'] ?? '', 0, 255),
                'tehsil'                  => mb_substr($r['tehsil'] ?? '', 0, 255),
                'district'                => mb_substr($r['district'] ?? '', 0, 255),
                'language'                => mb_substr($r['language'] ?? '', 0, 255),
                'issue_description'       => $r['issue_description'],
                'primary_issue'           => mb_substr($r['primary_issue'] ?? '', 0, 255),
                'urgency'                 => ucfirst(strtolower($r['urgency'] ?? 'low')),
                'assigned_pathway'        => $r['assigned_pathway'],
                'pathway_specific'        => mb_substr($r['pathway_specific'] ?? '', 0, 255),
                'pathway_govt_dept'       => mb_substr($r['pathway_govt_dept'] ?? '', 0, 255),
                'pathway_ngo_name'        => mb_substr($r['pathway_ngo_name'] ?? '', 0, 255),
                'pathway_manager'         => mb_substr($r['pathway_manager'] ?? '', 0, 255),
                'assigned_to'             => mb_substr($r['assigned_to'] ?? '', 0, 255),
                'status'                  => 'Active',
                'risk'                    => ucfirst(strtolower($r['urgency'] ?? 'low')),
                'case_ref'                => $r['case_uid'] . '-REF',
                'last_update'             => $r['intake_date'],
                'created_at'              => $now,
                'updated_at'              => $now,
            ];

            try {
                DB::table('cases')->insert($row);
            } catch (\Exception $e) {
                $errors++;
                if ($errors <= 5) {
                    $this->newLine();
                    $this->warn($r['case_uid'] . ': ' . substr($e->getMessage(), 0, 120));
                }
            }

            $bar->advance();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $bar->finish();
        $this->newLine(2);

        $total = DB::table('cases')->count();
        $this->info("Import complete. Total cases in DB: {$total}");
        if ($errors) {
            $this->warn("{$errors} batch(es) had errors — check output above.");
        }

        return self::SUCCESS;
    }
}
