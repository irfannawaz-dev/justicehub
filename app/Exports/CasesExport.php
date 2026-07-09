<?php

namespace App\Exports;

use App\Models\CaseRecord;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CasesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(
        private ?string $hubId = null,
        private ?string $status = null,
        private ?string $pathway = null,
    ) {}

    public function query()
    {
        $q = CaseRecord::query()->with(['caseReferrals']);

        if ($this->hubId && $this->hubId !== 'all') {
            $q->where('hub_id', $this->hubId);
        }
        if ($this->status && $this->status !== 'all') {
            $q->where('status', $this->status);
        }
        if ($this->pathway && $this->pathway !== 'all') {
            $q->where('assigned_pathway', $this->pathway);
        }

        return $q->orderBy('intake_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'Case UID',
            'Hub',
            'Full Name',
            'Father / Husband',
            'Gender',
            'Age',
            'CNIC',
            'Contact Number',
            'District',
            'Tehsil',
            'UC / Village',
            'Marital Status',
            'Religion',
            'Education',
            'Occupation',
            'Income Bracket',
            'Disability',
            'Primary Issue',
            'Assigned Pathway',
            'Specific Pathway',
            'Assigned To',
            'Urgency',
            'Risk',
            'Status',
            'Intake Date',
            'How Heard About Us',
            'Consent',
            'Referred To',
            'Filing Status',
        ];
    }

    public function map($case): array
    {
        $ref = $case->caseReferrals->first();

        return [
            $case->case_uid,
            $case->hub_id,
            $case->name,
            $case->father_husband_name,
            $case->gender,
            $case->age,
            $case->cnic,
            $case->primary_contact,
            $case->district,
            $case->tehsil,
            $case->uc_village,
            $case->marital_status,
            $case->religion,
            $case->education_level,
            $case->occupation,
            $case->income_bracket,
            $case->disability_status,
            $case->primary_issue,
            $case->assigned_pathway,
            $case->specific_pathway,
            $case->assigned_to,
            $case->urgency,
            $case->risk,
            $case->status?->value ?? $case->status,
            $case->intake_date?->format('Y-m-d'),
            $case->heard_about_us,
            $case->consent,
            $ref?->referred_to,
            $ref?->filing_status,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '163029'],
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            ],
        ];
    }
}
