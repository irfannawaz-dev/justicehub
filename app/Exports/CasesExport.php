<?php

namespace App\Exports;

use App\Models\CaseRecord;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CasesExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithEvents, WithHeadings, WithMapping, WithStyles
{
    private const HEADINGS = [
        'Case UID',
        'Case Reference',
        'Encounter ID',
        'Hub Code',
        'Hub Name',
        'Intake Date',
        'Intake Time',
        'Intake Mode',
        'Intake Source',
        'Staff Receiving',
        'Staff Designation',
        'How Heard About Us',
        'Referral Type',
        'Referral Contact Person',
        'Consent Obtained',
        'No Consent Reason',
        'Returning Client',
        'Client Full Name',
        'Father / Husband Name',
        'Gender',
        'Gender Other',
        'Age',
        'CNIC',
        'Marital Status',
        'Religion',
        'Education Level',
        'Occupation',
        'Income Bracket',
        'Disability Status',
        'Primary Contact',
        'Alternative Contact',
        'Full Address',
        'Union Council / Village',
        'Tehsil / Taluka',
        'District',
        'Preferred Language',
        'Primary Legal Issue',
        'Secondary Legal Issue',
        'Issue Description',
        'Urgency',
        'Risk',
        'Case Status',
        'Disposition',
        'SLA Met',
        'GBV Flag',
        'Child Flag',
        'Minority Flag',
        'Disability Flag',
        'Underserved Flag',
        'Assigned Pathway',
        'Specific Pathway',
        'Specific Pathway Other',
        'Government Department',
        'NGO / Partner Name',
        'Other Pathway Details',
        'Complaint Department',
        'Assigned To',
        'Pathway Manager',
        'Approval Decision',
        'Approval Requested At',
        'Rejection Reason',
        'Rejected By',
        'Rejected At',
        'Litigation Stage',
        'Litigation Stage Changed By',
        'Litigation Stage Changed At',
        'ADR Stage',
        'ADR Stage Changed By',
        'ADR Stage Changed At',
        'Case Summary',
        'Last Update',
        'LAS CMS Program ID',
        'LAS CMS Interview ID',
        'LAS CMS Interview Status',
        'LAS CMS Link Status',
        'LAS CMS Match Method',
        'LAS CMS Synced At',
        'Latest Referral To',
        'Latest Referral Date',
        'Latest Referral Status',
        'Latest Referral Outcome',
        'Latest Referral Filing Status',
        'Latest Referral Tracking Number',
        'Created At',
        'Updated At',
    ];

    public function __construct(private array $filters = []) {}

    public function query(): Builder
    {
        $user = auth()->user();
        $activeHub = $user && ! $user->canSeeAllHubs()
            ? $user->hub_id
            : ($this->filters['_active_hub'] ?? session('active_hub', 'all'));

        $q = CaseRecord::query()
            ->with(['hub', 'caseReferrals'])
            ->forHub($activeHub);

        $this->applyRoleScope($q);

        $hub = $this->filters['hub'] ?? $this->filters['_active_hub'] ?? 'all';
        $status = $this->filters['status'] ?? 'all';
        $pathway = $this->filters['pathway'] ?? 'all';
        $disposition = $this->filters['disposition'] ?? 'all';
        $district = $this->filters['district'] ?? 'all';
        $search = $this->filters['search'] ?? '';
        $cmsLink = $this->filters['cms_link'] ?? 'all';

        if ($hub && $hub !== 'all') {
            $q->where('hub_id', $hub);
        }

        // Disposition filter (same as index)
        if ($disposition && $disposition !== 'all') {
            $litigationPathways = ['Court Representation', 'Representation in Court'];
            $adrPathways = ['Mediation', 'ADR / Dispute Resolution Support'];
            $referredPathways = ['Government Department / Public Institution', 'Civil Society / NGO / CSO / NPO', 'Referral', 'Other'];
            $advicePathways = ['Legal Advice / Consultation', 'Information & Awareness'];

            $q->where(function ($sq) use ($disposition, $litigationPathways, $adrPathways, $referredPathways, $advicePathways) {
                if ($disposition === 'litigation') {
                    $sq->where('disposition', 'litigation')
                        ->orWhere(fn ($q2) => $q2->whereNull('disposition')->whereIn('assigned_pathway', $litigationPathways));
                } elseif ($disposition === 'adr') {
                    $sq->where('disposition', 'adr')
                        ->orWhere(fn ($q2) => $q2->whereNull('disposition')->whereIn('assigned_pathway', $adrPathways));
                } elseif ($disposition === 'referred') {
                    $sq->where('disposition', 'referred')
                        ->orWhere(fn ($q2) => $q2->whereNull('disposition')->whereIn('assigned_pathway', $referredPathways));
                } elseif (in_array($disposition, ['advice', 'advice-only'], true)) {
                    $sq->whereIn('disposition', ['advice', 'advice-only'])
                        ->orWhere(fn ($q2) => $q2->whereNull('disposition')->whereIn('assigned_pathway', $advicePathways));
                }
            });
        }

        // Pathway filter (same mapping as index)
        if ($pathway && $pathway !== 'all') {
            $q->where(function ($sq) use ($pathway) {
                if ($pathway === 'mediation') {
                    $sq->where('assigned_pathway', 'Mediation');
                } elseif ($pathway === 'adr') {
                    $sq->where('assigned_pathway', 'ADR / Dispute Resolution Support');
                } elseif ($pathway === 'court') {
                    $sq->whereIn('assigned_pathway', ['Court Representation', 'Representation in Court']);
                } elseif ($pathway === 'referred') {
                    $sq->whereIn('assigned_pathway', ['Referral', 'Government Department / Public Institution', 'Civil Society / NGO / CSO / NPO']);
                } elseif ($pathway === 'legal_advice') {
                    $sq->where('assigned_pathway', 'Legal Advice / Consultation');
                } elseif ($pathway === 'info_awareness') {
                    $sq->where('assigned_pathway', 'Information & Awareness');
                } else {
                    $sq->where('assigned_pathway', $pathway);
                }
            });
        }

        // Status filter
        if ($status && $status !== 'all') {
            if ($status === 'active') {
                $q->where('status', 'Active');
            } elseif ($status === 'closed') {
                $q->whereIn('status', ['Closed', 'Settlement']);
            } elseif ($status === 'safeguarding') {
                $q->where(fn ($sq) => $sq->where('is_gbv', true)->orWhere('is_child', true));
            } elseif ($status === 'sla') {
                $q->where('sla_met', false);
            }
        }

        // District filter
        if ($district && $district !== 'all') {
            $q->where('district', $district);
        }

        // Search
        if ($search) {
            $q->where(function ($sq) use ($search) {
                $sq->where('name', 'like', "%{$search}%")
                    ->orWhere('case_uid', 'like', "%{$search}%")
                    ->orWhere('primary_issue', 'like', "%{$search}%")
                    ->orWhere('assigned_pathway', 'like', "%{$search}%")
                    ->orWhere('district', 'like', "%{$search}%");
            });
        }

        // CMS link filter
        if ($cmsLink && $cmsLink !== 'all') {
            if ($cmsLink === 'connected') {
                $q->lasConnected();
            } elseif ($cmsLink === 'not_connected') {
                $q->lasNotConnected();
            }
        }

        return $q->orderBy('intake_date', 'desc');
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function map($case): array
    {
        $ref = $case->caseReferrals->first();

        $row = [
            $case->case_uid,
            $case->case_ref,
            $case->encounter_id,
            $case->hub_id,
            $case->hub?->name,
            $this->dateValue($case->intake_date),
            $case->intake_time,
            $case->mode,
            $case->source,
            $case->staff_receiving,
            $case->staff_designation,
            $case->referral_source,
            $case->referral_type,
            $case->referral_contact_person,
            $this->yesNo($case->consent),
            $case->no_consent_reason,
            $this->yesNo($case->returning_client),
            $case->name,
            $case->father_husband_name,
            $case->gender,
            $case->gender_other,
            $case->age,
            $case->cnic,
            $case->marital_status,
            $case->religion,
            $case->education_level,
            $case->occupation,
            $case->income_bracket,
            $case->disability_status,
            $case->primary_contact,
            $case->alternative_contact,
            $case->full_address,
            $case->union_council,
            $case->tehsil,
            $case->district,
            $case->language,
            $case->primary_issue,
            $case->secondary_issue,
            $case->issue_description,
            $this->enumValue($case->urgency),
            $this->enumValue($case->risk),
            $this->enumValue($case->status),
            $this->enumValue($case->disposition),
            $this->yesNo($case->sla_met),
            $this->yesNo($case->is_gbv),
            $this->yesNo($case->is_child),
            $this->yesNo($case->is_minority),
            $this->yesNo($case->is_disability),
            $this->yesNo($case->is_underserved),
            $case->assigned_pathway,
            $case->pathway_specific,
            $case->pathway_specific_other,
            $case->pathway_govt_dept,
            $case->pathway_ngo_name,
            $case->pathway_other_details,
            $case->complaint_department,
            $case->assigned_to,
            $case->pathway_manager,
            $case->approval_decision,
            $this->dateTimeValue($case->requested_at),
            $case->rejection_reason,
            $case->rejected_by,
            $this->dateTimeValue($case->rejected_at),
            $case->litigation_stage,
            $case->litigation_stage_changed_by,
            $this->dateTimeValue($case->litigation_stage_changed_at),
            $case->adr_stage,
            $case->adr_stage_changed_by,
            $this->dateTimeValue($case->adr_stage_changed_at),
            $case->summary,
            $this->dateValue($case->last_update),
            $case->external_case_id,
            $case->jh_cms_interview_id,
            $case->jh_cms_interview_status,
            data_get($case->meta, 'las_link_status'),
            data_get($case->meta, 'las_match_method'),
            $this->dateTimeValue($case->external_synced_at),
            $ref?->referred_to,
            $this->dateValue($ref?->referral_date),
            $ref?->status,
            $ref?->outcome,
            $ref?->filing_status,
            $ref?->tracking_number,
            $this->dateTimeValue($case->created_at),
            $this->dateTimeValue($case->updated_at),
        ];

        return array_map([$this, 'escapeFormula'], $row);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '163029'],
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        $formats = [];

        foreach ([
            'Case UID',
            'Case Reference',
            'Encounter ID',
            'CNIC',
            'Primary Contact',
            'Alternative Contact',
            'LAS CMS Program ID',
            'LAS CMS Interview ID',
            'Latest Referral Tracking Number',
        ] as $heading) {
            $formats[$this->columnFor($heading)] = NumberFormat::FORMAT_TEXT;
        }

        return $formats;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = max(2, $sheet->getHighestRow());

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:{$highestColumn}1");
                $sheet->getRowDimension(1)->setRowHeight(34);
                $sheet->getStyle("A1:{$highestColumn}1")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $sheet->getStyle("A2:{$highestColumn}{$highestRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP);

                foreach ([
                    'No Consent Reason',
                    'Full Address',
                    'Issue Description',
                    'Other Pathway Details',
                    'Rejection Reason',
                    'Case Summary',
                    'Latest Referral Outcome',
                ] as $heading) {
                    $column = $this->columnFor($heading);
                    $sheet->getColumnDimension($column)->setAutoSize(false)->setWidth(38);
                    $sheet->getStyle("{$column}2:{$column}{$highestRow}")->getAlignment()->setWrapText(true);
                }
            },
        ];
    }

    private function applyRoleScope(Builder $query): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        if ($user->isLawyer()) {
            $query->where('assigned_to', $user->name)
                ->whereNotIn('assigned_pathway', ['Mediation', 'ADR / Dispute Resolution Support']);
        }

        if ($user->isCourtClerk()) {
            $query->where(fn (Builder $builder) => $builder
                ->where('disposition', 'litigation')
                ->orWhereIn('assigned_pathway', ['Representation in Court', 'Court Representation']));
        }

        if ($user->isLitigationManager()) {
            $query->whereIn('assigned_pathway', ['Representation in Court', 'Court Representation']);
        }

        if ($user->isMediationManager()) {
            $query->where('assigned_pathway', 'Mediation');
        }
    }

    private function yesNo(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value ? 'Yes' : 'No';
    }

    private function enumValue(mixed $value): mixed
    {
        return $value instanceof \BackedEnum ? $value->value : $value;
    }

    private function dateValue(mixed $value): ?string
    {
        return $this->formatDate($value, 'Y-m-d');
    }

    private function dateTimeValue(mixed $value): ?string
    {
        return $this->formatDate($value, 'Y-m-d H:i:s');
    }

    private function formatDate(mixed $value, string $format): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return $value instanceof \DateTimeInterface
                ? $value->format($format)
                : Carbon::parse($value)->format($format);
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    private function escapeFormula(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return preg_match('/^\s*[=+\-@]/u', $value) ? "'{$value}" : $value;
    }

    private function columnFor(string $heading): string
    {
        $index = array_search($heading, self::HEADINGS, true);

        return Coordinate::stringFromColumnIndex($index + 1);
    }
}
