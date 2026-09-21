<?php

namespace App\Console\Commands;

use App\Models\CaseRecord;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportExcelMerge extends Command
{
    protected $signature   = 'cases:import-merge
                              {file? : Path to the Excel file (default: ../Data Validation Sheet 4000+.xlsx)}
                              {--dry-run : Preview counts without writing to DB}';
    protected $description = 'Import cases from Excel, skipping any case_uid that already exists in the DB';

    // Hub name → hub_id mapping
    private const HUB_MAP = [
        'Dadu'       => 'JH-DAD-01',
        'Hyderabad'  => 'JH-HYD-01',
        'SBA'        => 'JH-SBA-01',
        'Sanghar'    => 'JH-SAN-01',
        'Islamabad'  => 'JH-ISB-01',
        'Karachi'    => 'JH-KHI-01',
        'Larkana'    => 'JH-LAR-01',
        'Sukkur'     => 'JH-SUK-01',
        // Also match full names used in the Excel "Justice Hub Location" column
        'Shaheed Benazirabad' => 'JH-SBA-01',
    ];

    // Excel "heard about us" → system referral_source
    private const SOURCE_MAP = [
        'Referred by Paralegals'            => 'Paralegal',
        'Community Outreach'                => 'Community Outreach / Awareness Session',
        'Word of Mouth'                     => 'Word of Mouth / Friend / Family',
        'Referred by Govt Dept.'            => 'Government Department',
        'District Peace Committee'          => 'District / Range Peace Committee',
        'Web/ Social Media'                 => 'Website / Social Media',
        'Referred by Civil Society/NGO/NPO' => 'NGO / CSO / NPO',
        'SMS'                               => 'SMS / WhatsApp Message',
        'Radio/ TV/ Newspaper'              => 'Radio / TV / Newspaper',
    ];

    // Excel "Referred to" → assigned_pathway
    private const PATHWAY_MAP = [
        'SLACC/ Lawyer - Legal Advice'    => 'Legal Advice / Consultation',
        'SLACC/ Lawyer'                   => 'Legal Advice / Consultation',
        'Lawyer - Legal Advice'           => 'Legal Advice / Consultation',
        'Lawyer - Court Representation'   => 'Court Representation',
        'Mediation'                       => 'Mediation',
        'ADR'                             => 'ADR / Dispute Resolution Support',
        'Govt. Department'                => 'Government Department / Public Institution',
        'Civil Society (CSO / NGO / NPO)' => 'Civil Society / NGO / CSO / NPO',
    ];

    // Data sheets to process (skip helper sheets like Sheet2, _EditQueue, etc.)
    private const DATA_SHEETS = ['Dadu', 'Hyderabad', 'SBA', 'Sanghar'];

    public function handle(): int
    {
        $filePath = $this->argument('file')
            ?? base_path('../Data Validation Sheet 4000+.xlsx');

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $dryRun = $this->option('dry-run');
        if ($dryRun) $this->warn('DRY RUN — no data will be written.');

        // Load existing case_uids for skip check
        $this->info('Loading existing case UIDs…');
        $existing = CaseRecord::pluck('case_uid')->flip()->toArray();
        $this->info('  ' . count($existing) . ' cases already in DB.');

        // Load Excel
        $this->info('Loading Excel file (this may take a moment)…');
        ini_set('memory_limit', '2G');

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        $totalInserted = 0;
        $totalSkipped  = 0;
        $totalErrors   = 0;
        $perHub        = [];

        foreach (self::DATA_SHEETS as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (! $sheet) {
                $this->warn("  Sheet '{$sheetName}' not found — skipping.");
                continue;
            }

            $maxRow = $sheet->getHighestRow();
            $this->info("Processing sheet '{$sheetName}' ({$maxRow} rows)…");

            $inserted = 0;
            $skipped  = 0;
            $errors   = 0;

            for ($row = 2; $row <= $maxRow; $row++) {
                $caseUid = trim((string) $sheet->getCell("A{$row}")->getValue());

                // Skip empty rows
                if (! $caseUid) continue;

                // Skip existing
                if (isset($existing[$caseUid])) {
                    $skipped++;
                    continue;
                }

                try {
                    $record = $this->mapRow($sheet, $row, $sheetName);

                    if (! $dryRun) {
                        CaseRecord::create($record);
                    }

                    $existing[$caseUid] = true; // prevent dups within the file
                    $inserted++;
                    $hub = $record['hub_id'];
                    $perHub[$hub] = ($perHub[$hub] ?? 0) + 1;
                } catch (\Throwable $e) {
                    $errors++;
                    if ($errors <= 10) {
                        $this->warn("  Row {$row} ({$caseUid}): {$e->getMessage()}");
                    }
                }
            }

            $this->info("  {$sheetName}: inserted={$inserted}, skipped={$skipped}, errors={$errors}");
            $totalInserted += $inserted;
            $totalSkipped  += $skipped;
            $totalErrors   += $errors;
        }

        // Summary
        $this->newLine();
        if (count($perHub)) {
            $rows = collect($perHub)->map(fn($c, $h) => [$h, $c])->values()->toArray();
            $this->table(['Hub ID', 'New Cases'], $rows);
        }
        $this->info("Total: {$totalInserted} inserted, {$totalSkipped} skipped (existing), {$totalErrors} errors.");
        if ($dryRun) $this->warn('DRY RUN — nothing was actually written.');

        return 0;
    }

    private function mapRow($sheet, int $row, string $sheetName): array
    {
        $val = fn(string $col) => trim((string) ($sheet->getCell("{$col}{$row}")->getValue() ?? ''));

        $caseUid     = $val('A');
        $hubLocation = $val('C') ?: $sheetName;
        $hubId       = self::HUB_MAP[$hubLocation] ?? self::HUB_MAP[$sheetName] ?? $hubLocation;

        // Timestamp → intake_date
        $rawTs = $sheet->getCell("B{$row}")->getValue();
        $intakeDate = $this->parseDate($rawTs);

        // Consent
        $consentRaw = $val('G');
        $consent = str_starts_with(strtolower($consentRaw), 'yes');

        // Repeat client
        $repeatRaw = $val('D');
        $returning = strtolower($repeatRaw) === 'yes';

        // Source / referral
        $heardAbout    = $val('I');
        $source        = self::SOURCE_MAP[$heardAbout] ?? $heardAbout;
        $paralegalName = $val('J');
        $ngoReferral   = $val('K');
        $govtReferral  = $val('L');
        $referralSource = $paralegalName ?: $ngoReferral ?: $govtReferral ?: null;

        // Contact — clean non-numeric chars except leading 0
        $primaryContact = preg_replace('/[^0-9]/', '', $val('X'));
        $altContact     = preg_replace('/[^0-9]/', '', $val('Y'));
        // Trim to 15 chars max (DB column limit)
        $primaryContact = $primaryContact ? substr($primaryContact, 0, 15) : null;
        $altContact     = $altContact ? substr($altContact, 0, 15) : null;

        // CNIC — strip dashes/spaces
        $cnic = preg_replace('/[^0-9]/', '', $val('Q'));
        $cnic = $cnic ?: null;

        // Age — fallback to 0
        $age = (int) $val('P');
        if ($age < 0 || $age > 150) $age = 0;

        // Pathway mapping from "Referred to" column
        $referredTo   = $val('AH');
        $pathway      = $this->mapPathway($referredTo);
        $caseReferred = $val('AK'); // "Legal Advice", "Legal Representation", etc.
        $responsible  = $val('AL');

        // Category / urgency
        $category = $val('AF');
        $urgency  = $val('AG') ?: 'Medium';
        // Normalize urgency
        $validUrgencies = ['Critical', 'High', 'Medium', 'Low', 'Immediate'];
        if (! in_array($urgency, $validUrgencies)) {
            $urgency = 'Medium';
        }

        // Staff receiving
        $staffReceiving = $val('E');
        $staffDesignation = $val('F');

        return [
            'case_uid'           => $caseUid,
            'case_ref'           => $caseUid,
            'hub_id'             => $hubId,
            'name'               => $val('M') ?: 'Unknown',
            'father_husband_name'=> $val('N') ?: null,
            'gender'             => $val('O') ?: 'Not specified',
            'age'                => $age,
            'cnic'               => $cnic,
            'marital_status'     => $val('R') ?: null,
            'religion'           => $val('S') ?: null,
            'education_level'    => $val('T') ?: null,
            'occupation'         => $val('U') ?: null,
            'income_bracket'     => $val('V') ?: null,
            'disability_status'  => $val('W') ?: null,
            'primary_contact'    => $primaryContact,
            'alternative_contact'=> $altContact,
            'full_address'       => $val('Z') ?: null,
            'union_council'      => $val('AA') ?: null,
            'tehsil'             => $val('AB') ?: null,
            'district'           => $val('AC') ?: null,
            'language'           => $val('AD') ?: null,
            'intake_date'        => $intakeDate,
            'source'             => $source,
            'referral_source'    => $referralSource,
            'consent'            => $consent,
            'no_consent_reason'  => $consent ? null : ($val('H') ?: null),
            'returning_client'   => $returning,
            'staff_receiving'    => $staffReceiving ?: null,
            'staff_designation'  => $staffDesignation ?: null,
            'primary_issue'      => $category ?: 'General',
            'issue_description'  => $val('AE') ?: null,
            'urgency'            => $urgency,
            'status'             => 'Active',
            'risk'               => 'Low',
            'sla_met'            => true,
            'assigned_pathway'   => $pathway,
            'assigned_to'        => $responsible ?: null,
            'meta'               => json_encode([
                'imported_from'      => 'Excel',
                'imported_at'        => now()->toDateTimeString(),
                'excel_sheet'        => $sheetName,
                'referred_to_raw'    => $referredTo ?: null,
                'case_referred_raw'  => $caseReferred ?: null,
                'referral_details'   => $val('AI') ?: null,
                'clear_path'         => $val('AJ') ?: null,
            ]),
        ];
    }

    private function parseDate($raw): string
    {
        if (! $raw) return now()->toDateString();

        // Excel serial date number
        if (is_numeric($raw)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($raw))->toDateString();
            } catch (\Throwable) {
                return now()->toDateString();
            }
        }

        // Try common date formats
        foreach (['d/m/Y', 'Y-m-d', 'm/d/Y', 'd-m-Y'] as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, (string) $raw)->toDateString();
            } catch (\Throwable) {
                continue;
            }
        }

        return now()->toDateString();
    }

    private function mapPathway(?string $referredTo): ?string
    {
        if (! $referredTo) return null;

        // Direct match
        if (isset(self::PATHWAY_MAP[$referredTo])) {
            return self::PATHWAY_MAP[$referredTo];
        }

        // Partial match
        $lower = strtolower($referredTo);
        if (str_contains($lower, 'court representation'))      return 'Court Representation';
        if (str_contains($lower, 'legal advice'))              return 'Legal Advice / Consultation';
        if (str_contains($lower, 'slacc') || str_contains($lower, 'lawyer')) return 'Legal Advice / Consultation';
        if (str_contains($lower, 'mediation'))                 return 'Mediation';
        if (str_contains($lower, 'adr'))                       return 'ADR / Dispute Resolution Support';
        if (str_contains($lower, 'govt') || str_contains($lower, 'government')) return 'Government Department / Public Institution';
        if (str_contains($lower, 'ngo') || str_contains($lower, 'cso') || str_contains($lower, 'civil')) return 'Civil Society / NGO / CSO / NPO';

        return null;
    }
}
