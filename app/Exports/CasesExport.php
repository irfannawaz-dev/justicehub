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
    public function __construct(private array $filters = []) {}

    public function query()
    {
        $q = CaseRecord::query()->with(['caseReferrals']);

        $hub        = $this->filters['hub'] ?? $this->filters['_active_hub'] ?? 'all';
        $status     = $this->filters['status'] ?? 'all';
        $pathway    = $this->filters['pathway'] ?? 'all';
        $disposition= $this->filters['disposition'] ?? 'all';
        $district   = $this->filters['district'] ?? 'all';
        $search     = $this->filters['search'] ?? '';
        $cmsLink    = $this->filters['cms_link'] ?? 'all';

        if ($hub && $hub !== 'all') {
            $q->where('hub_id', $hub);
        }

        // Disposition filter (same as index)
        if ($disposition && $disposition !== 'all') {
            $litigationPathways = ['Court Representation', 'Representation in Court'];
            $adrPathways        = ['Mediation', 'ADR / Dispute Resolution Support'];
            $referredPathways   = ['Government Department / Public Institution', 'Civil Society / NGO / CSO / NPO', 'Referral', 'Other'];
            $advicePathways     = ['Legal Advice / Consultation', 'Information & Awareness'];

            $q->where(function ($sq) use ($disposition, $litigationPathways, $adrPathways, $referredPathways, $advicePathways) {
                if ($disposition === 'litigation') {
                    $sq->where('disposition', 'litigation')
                       ->orWhere(fn($q2) => $q2->whereNull('disposition')->whereIn('assigned_pathway', $litigationPathways));
                } elseif ($disposition === 'adr') {
                    $sq->where('disposition', 'adr')
                       ->orWhere(fn($q2) => $q2->whereNull('disposition')->whereIn('assigned_pathway', $adrPathways));
                } elseif ($disposition === 'referred') {
                    $sq->where('disposition', 'referred')
                       ->orWhere(fn($q2) => $q2->whereNull('disposition')->whereIn('assigned_pathway', $referredPathways));
                } elseif ($disposition === 'advice') {
                    $sq->where('disposition', 'advice')
                       ->orWhere(fn($q2) => $q2->whereNull('disposition')->whereIn('assigned_pathway', $advicePathways));
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
            if ($status === 'active') $q->where('status', 'Active');
            elseif ($status === 'closed') $q->whereIn('status', ['Closed', 'Settlement']);
            elseif ($status === 'safeguarding') $q->where(fn($sq) => $sq->where('is_gbv', true)->orWhere('is_child', true));
            elseif ($status === 'sla') $q->where('sla_met', false);
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
                $q->whereNotNull('external_case_id')
                  ->where('meta->las_link_status', 'verified');
            } elseif ($cmsLink === 'not_connected') {
                $q->where(function ($sq) {
                    $sq->whereNull('external_case_id')
                       ->orWhereNull('meta->las_link_status')
                       ->orWhere('meta->las_link_status', '!=', 'verified');
                });
            }
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
            $case->urgency?->value ?? $case->urgency,
            $case->risk?->value ?? $case->risk,
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
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '163029'],
                ],
            ],
        ];
    }
}
