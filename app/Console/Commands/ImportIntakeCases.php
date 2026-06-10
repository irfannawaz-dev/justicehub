<?php

namespace App\Console\Commands;

use App\Models\CaseRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportIntakeCases extends Command
{
    protected $signature = 'import:cases {file? : Path to Excel file}';
    protected $description = 'Import intake cases from Excel file into the cases table';

    private array $hubMap = [
        'Hyderabad' => 'JH-HYD-01',
        'Sanghar'   => 'JH-SAN-01',
        'SBA'       => 'JH-SBA-01',
        'Dadu'      => 'JH-DAD-01',
        'Sukkur'    => 'JH-SUK-01',
        'Larkana'   => 'JH-LAR-01',
        'Karachi'   => 'JH-KHI-01',
        'Islamabad' => 'JH-ISB-01',
    ];

    private array $pathwayMap = [
        'Lawyer - Court Representation'   => 'Representation in Court',
        'SLACC/ Lawyer - Legal Advice'    => 'Legal Advice',
        'Accredited Mediator'             => 'Mediation & ADR',
        'Lawyer - Civil Documentation'    => 'Documentation',
        'Information & Awareness'         => 'Information & Awareness',
        'NADRA'                           => 'Referral',
        'Police'                          => 'Referral',
        'Other Govt Dept'                 => 'Referral',
        'Civil Society'                   => 'Referral',
        'Social Protection'               => 'Referral',
    ];

    public function handle(): int
    {
        $file = $this->argument('file') ?? base_path('JusticeHubIntakeForm.xlsx');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info('Loading Excel file...');
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $totalRows = $sheet->getHighestDataRow() - 1;
        $this->info("Found {$totalRows} rows to import.");

        $imported = 0;
        $skipped = 0;
        $errors = [];

        $bar = $this->output->createProgressBar($totalRows);
        $bar->start();

        // Find the max case number currently in db
        $maxNum = CaseRecord::selectRaw("MAX(CAST(SUBSTRING(case_uid, 4) AS UNSIGNED)) as max_num")->value('max_num');
        $nextNum = $maxNum ? $maxNum + 1 : 10001;

        for ($row = 2; $row <= $sheet->getHighestDataRow(); $row++) {
            try {
                $uniqueId = trim($sheet->getCell('A' . $row)->getValue() ?? '');

                if (empty($uniqueId)) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Skip if already imported
                if (CaseRecord::where('unique_id', $uniqueId)->exists()) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Parse timestamp
                $tsRaw = $sheet->getCell('B' . $row)->getValue();
                $intakeDate = null;
                $intakeTime = null;
                if (is_numeric($tsRaw)) {
                    $dt = ExcelDate::excelToDateTimeObject($tsRaw);
                    $intakeDate = $dt->format('Y-m-d');
                    $intakeTime = $dt->format('H:i');
                } elseif ($tsRaw) {
                    try {
                        $dt = new \DateTime($tsRaw);
                        $intakeDate = $dt->format('Y-m-d');
                        $intakeTime = $dt->format('H:i');
                    } catch (\Exception $e) {
                        $intakeDate = now()->toDateString();
                    }
                }

                $hubLocation = trim($sheet->getCell('C' . $row)->getValue() ?? '');
                $hubId = $this->hubMap[$hubLocation] ?? null;

                if (! $hubId) {
                    $errors[] = "Row {$row}: Unknown hub '{$hubLocation}'";
                    $bar->advance();
                    continue;
                }

                $gender = trim($sheet->getCell('O' . $row)->getValue() ?? '');
                $age = (int) ($sheet->getCell('P' . $row)->getValue() ?? 0);
                $category = trim($sheet->getCell('AF' . $row)->getValue() ?? 'General');
                $urgency = trim($sheet->getCell('AG' . $row)->getValue() ?? 'Low');
                $pathwayRaw = trim($sheet->getCell('AH' . $row)->getValue() ?? '');
                $consent = trim($sheet->getCell('G' . $row)->getValue() ?? '');
                $religion = trim($sheet->getCell('S' . $row)->getValue() ?? '');
                $disabilityStatus = trim($sheet->getCell('W' . $row)->getValue() ?? 'No');

                // Map pathway
                $assignedPathway = $this->pathwayMap[$pathwayRaw] ?? $pathwayRaw;

                // Generate case UIDs
                $caseUid = 'CL-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
                $caseRef = 'CA-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
                $encounterId = 'SE-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

                // Determine referral source detail
                $heardAboutUs = trim($sheet->getCell('I' . $row)->getValue() ?? '');

                // Determine vulnerability flags
                $isGbv = stripos($category, 'GBV') !== false;
                $isChild = $age < 18;
                $isMinority = $religion && ! in_array($religion, ['Muslim', 'Islam']);
                $isDisability = $disabilityStatus && ! in_array(strtolower($disabilityStatus), ['no', 'none', '']);
                $isUnderserved = $isGbv || $isChild || $isMinority || $isDisability;

                DB::transaction(function () use (
                    $sheet, $row, $uniqueId, $caseUid, $caseRef, $encounterId,
                    $hubId, $intakeDate, $intakeTime, $gender, $age, $category,
                    $urgency, $assignedPathway, $consent, $heardAboutUs,
                    $isGbv, $isChild, $isMinority, $isDisability, $isUnderserved, $pathwayRaw
                ) {
                    $case = CaseRecord::create([
                        'unique_id'          => $uniqueId,
                        'case_uid'           => $caseUid,
                        'case_ref'           => $caseRef,
                        'encounter_id'       => $encounterId,
                        'hub_id'             => $hubId,
                        'name'               => trim($sheet->getCell('M' . $row)->getValue() ?? ''),
                        'father_husband_name'=> trim($sheet->getCell('N' . $row)->getValue() ?? ''),
                        'gender'             => $gender,
                        'age'                => $age,
                        'cnic'               => trim($sheet->getCell('Q' . $row)->getValue() ?? ''),
                        'marital_status'     => trim($sheet->getCell('R' . $row)->getValue() ?? ''),
                        'religion'           => trim($sheet->getCell('S' . $row)->getValue() ?? ''),
                        'education_level'    => trim($sheet->getCell('T' . $row)->getValue() ?? ''),
                        'occupation'         => trim($sheet->getCell('U' . $row)->getValue() ?? ''),
                        'income_bracket'     => trim($sheet->getCell('V' . $row)->getValue() ?? ''),
                        'disability_status'  => trim($sheet->getCell('W' . $row)->getValue() ?? ''),
                        'primary_contact'    => substr(trim($sheet->getCell('X' . $row)->getValue() ?? ''), 0, 15),
                        'alternative_contact'=> substr(trim($sheet->getCell('Y' . $row)->getValue() ?? ''), 0, 15),
                        'full_address'       => trim($sheet->getCell('Z' . $row)->getValue() ?? ''),
                        'union_council'      => trim($sheet->getCell('AA' . $row)->getValue() ?? ''),
                        'tehsil'             => trim($sheet->getCell('AB' . $row)->getValue() ?? ''),
                        'district'           => trim($sheet->getCell('AC' . $row)->getValue() ?? ''),
                        'language'           => trim($sheet->getCell('AD' . $row)->getValue() ?? ''),
                        'intake_date'        => $intakeDate,
                        'intake_time'        => $intakeTime,
                        'mode'               => 'Walk-in',
                        'source'             => 'Self',
                        'referral_source'    => $heardAboutUs,
                        'consent'            => stripos($consent, 'Yes') !== false,
                        'no_consent_reason'  => trim($sheet->getCell('H' . $row)->getValue() ?? ''),
                        'returning_client'   => stripos(trim($sheet->getCell('D' . $row)->getValue() ?? ''), 'Yes') !== false,
                        'staff_receiving'    => trim($sheet->getCell('E' . $row)->getValue() ?? ''),
                        'staff_designation'  => trim($sheet->getCell('F' . $row)->getValue() ?? ''),
                        'primary_issue'      => $category,
                        'issue_description'  => trim($sheet->getCell('AE' . $row)->getValue() ?? ''),
                        'urgency'            => $urgency,
                        'status'             => 'Active',
                        'risk'               => $urgency === 'Immediate' || $urgency === 'High' ? 'High' : ($urgency === 'Medium' ? 'Medium' : 'Low'),
                        'sla_met'            => true,
                        'is_gbv'             => $isGbv,
                        'is_child'           => $isChild,
                        'is_minority'        => $isMinority,
                        'is_disability'      => $isDisability,
                        'is_underserved'     => $isUnderserved,
                        'assigned_pathway'   => $assignedPathway,
                        'pathway_other_details' => trim($sheet->getCell('AI' . $row)->getValue() ?? ''),
                        'assigned_to'        => trim($sheet->getCell('E' . $row)->getValue() ?? ''),
                        'last_update'        => $intakeDate,
                    ]);

                    // Insert pathway pivot
                    DB::table('case_pathway')->insert([
                        'case_id'       => $case->id,
                        'pathway_value' => $assignedPathway ?: 'Legal Advice',
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);

                    // Create initial service encounter
                    DB::table('service_encounters')->insert([
                        'case_id'      => $case->id,
                        'date'         => $intakeDate,
                        'type'         => 'Intake',
                        'performed_by' => trim($sheet->getCell('E' . $row)->getValue() ?? ''),
                        'note'         => 'Imported from Excel. Referral: ' . $heardAboutUs,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                });

                $nextNum++;
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$row}: " . $e->getMessage();
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Imported: {$imported}");
        $this->info("Skipped: {$skipped}");

        if (count($errors) > 0) {
            $this->warn('Errors (' . count($errors) . '):');
            foreach (array_slice($errors, 0, 20) as $err) {
                $this->line("  - {$err}");
            }
            if (count($errors) > 20) {
                $this->line('  ... and ' . (count($errors) - 20) . ' more.');
            }
        }

        return 0;
    }
}
