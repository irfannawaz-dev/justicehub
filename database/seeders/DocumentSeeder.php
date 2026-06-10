<?php

namespace Database\Seeders;

use App\Models\CaseRecord;
use App\Models\Document;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            // --- CA-02441 · Yasmin Q. ---
            ['document_uid' => 'DOC-0241', 'case_ref' => 'CA-02441', 'type' => 'consent', 'name' => 'Intake consent & data-sharing form',
                'added_date' => '2026-04-04', 'added_by' => 'N. Memon (Paralegal)', 'source' => 'uploaded',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 2],
            ['document_uid' => 'DOC-0242', 'case_ref' => 'CA-02441', 'type' => 'id', 'name' => 'Disability identification letter (Civil Hospital)',
                'added_date' => '2026-04-04', 'added_by' => 'N. Memon (Paralegal)', 'source' => 'received',
                'status' => 'archived', 'confidentiality' => 'sensitive', 'document_ref' => 'CHK-DIS-2026/0118', 'pages' => 1],
            ['document_uid' => 'DOC-0243', 'case_ref' => 'CA-02441', 'type' => 'correspondence', 'name' => 'NADRA priority-slot request letter',
                'added_date' => '2026-04-12', 'added_by' => 'N. Memon (Paralegal)', 'source' => 'generated',
                'status' => 'submitted', 'confidentiality' => 'restricted', 'document_ref' => 'JH-NADRA-2026/041', 'pages' => 1],
            ['document_uid' => 'DOC-0244', 'case_ref' => 'CA-02441', 'type' => 'id', 'name' => 'CNIC (issued)',
                'added_date' => '2026-04-19', 'added_by' => 'Irfan Nawaz (Hub Manager)', 'source' => 'received',
                'status' => 'archived', 'confidentiality' => 'sensitive', 'document_ref' => '45301-XXXXXXX-Y', 'pages' => 1],

            // --- CA-02428 · Zubaida B. ---
            ['document_uid' => 'DOC-0231', 'case_ref' => 'CA-02428', 'type' => 'consent', 'name' => 'Intake consent & data-sharing form',
                'added_date' => '2026-03-22', 'added_by' => 'N. Memon (Paralegal)', 'source' => 'uploaded',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 2],
            ['document_uid' => 'DOC-0232', 'case_ref' => 'CA-02428', 'type' => 'consent', 'name' => 'Photo & case-story consent',
                'added_date' => '2026-03-22', 'added_by' => 'N. Memon (Paralegal)', 'source' => 'uploaded',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 1],
            ['document_uid' => 'DOC-0233', 'case_ref' => 'CA-02428', 'type' => 'medical', 'name' => 'Medico-Legal Certificate (MLC)',
                'added_date' => '2026-03-23', 'added_by' => 'Adv. R. Khan (Lawyer)', 'source' => 'received',
                'status' => 'archived', 'confidentiality' => 'sensitive', 'document_ref' => 'CHK-MLC-2026/0044', 'pages' => 3],
            ['document_uid' => 'DOC-0234', 'case_ref' => 'CA-02428', 'type' => 'filing', 'name' => 'Application for protection order',
                'added_date' => '2026-03-26', 'added_by' => 'Adv. R. Khan (Lawyer)', 'source' => 'generated',
                'status' => 'submitted', 'confidentiality' => 'restricted', 'document_ref' => 'PO-SBA-2026/41', 'pages' => 5],
            ['document_uid' => 'DOC-0235', 'case_ref' => 'CA-02428', 'type' => 'correspondence', 'name' => 'Shelter referral acknowledgement (Panah)',
                'added_date' => '2026-03-27', 'added_by' => 'Irfan Nawaz (Hub Manager)', 'source' => 'received',
                'status' => 'acknowledged', 'confidentiality' => 'sensitive', 'document_ref' => 'PNH-2026/REF-89', 'pages' => 1],
            ['document_uid' => 'DOC-0236', 'case_ref' => 'CA-02428', 'type' => 'evidence', 'name' => 'Witness statement transcripts (x2)',
                'added_date' => '2026-04-02', 'added_by' => 'Adv. R. Khan (Lawyer)', 'source' => 'generated',
                'status' => 'archived', 'confidentiality' => 'sensitive', 'document_ref' => null, 'pages' => 8],

            // --- CA-02444 · Juvenile bail ---
            ['document_uid' => 'DOC-0221', 'case_ref' => 'CA-02444', 'type' => 'consent', 'name' => 'Intake consent & data-sharing form (parent-signed)',
                'added_date' => '2026-04-08', 'added_by' => 'A. Mahar (Paralegal)', 'source' => 'uploaded',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 2],
            ['document_uid' => 'DOC-0222', 'case_ref' => 'CA-02444', 'type' => 'id', 'name' => 'Birth-registration certificate (juvenile)',
                'added_date' => '2026-04-08', 'added_by' => 'A. Mahar (Paralegal)', 'source' => 'received',
                'status' => 'archived', 'confidentiality' => 'sensitive', 'document_ref' => 'BRC-SUK-2010/2284', 'pages' => 1],
            ['document_uid' => 'DOC-0223', 'case_ref' => 'CA-02444', 'type' => 'filing', 'name' => 'Bail application under Juvenile Justice System Act',
                'added_date' => '2026-04-08', 'added_by' => 'Adv. N. Jatoi (Lawyer)', 'source' => 'generated',
                'status' => 'submitted', 'confidentiality' => 'restricted', 'document_ref' => 'JJSA-SUK-2026/118', 'pages' => 6],
            ['document_uid' => 'DOC-0224', 'case_ref' => 'CA-02444', 'type' => 'correspondence', 'name' => 'Court order — emergency bail granted',
                'added_date' => '2026-04-08', 'added_by' => 'Adv. N. Jatoi (Lawyer)', 'source' => 'received',
                'status' => 'archived', 'confidentiality' => 'restricted', 'document_ref' => 'JJSA-2026/ORD-44', 'pages' => 2],
            ['document_uid' => 'DOC-0225', 'case_ref' => 'CA-02444', 'type' => 'correspondence', 'name' => 'Diversion-to-rehabilitation referral',
                'added_date' => '2026-04-09', 'added_by' => 'Adv. N. Jatoi (Lawyer)', 'source' => 'generated',
                'status' => 'acknowledged', 'confidentiality' => 'restricted', 'document_ref' => 'CPU-SUK-2026/14', 'pages' => 1],

            // --- CA-02438 · Salma R. ---
            ['document_uid' => 'DOC-0211', 'case_ref' => 'CA-02438', 'type' => 'consent', 'name' => 'Intake consent & data-sharing form',
                'added_date' => '2026-03-30', 'added_by' => 'N. Memon (Paralegal)', 'source' => 'uploaded',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 2],
            ['document_uid' => 'DOC-0212', 'case_ref' => 'CA-02438', 'type' => 'evidence', 'name' => 'Death certificate (deceased husband)',
                'added_date' => '2026-03-30', 'added_by' => 'N. Memon (Paralegal)', 'source' => 'received',
                'status' => 'archived', 'confidentiality' => 'restricted', 'document_ref' => 'UC-SAN-DC-2024/881', 'pages' => 1],
            ['document_uid' => 'DOC-0213', 'case_ref' => 'CA-02438', 'type' => 'evidence', 'name' => 'Land record (Form VII-XII extract)',
                'added_date' => '2026-04-01', 'added_by' => 'N. Memon (Paralegal)', 'source' => 'received',
                'status' => 'archived', 'confidentiality' => 'restricted', 'document_ref' => 'REV-SAN-2026/0445', 'pages' => 4],
            ['document_uid' => 'DOC-0214', 'case_ref' => 'CA-02438', 'type' => 'other', 'name' => 'Mediation agreement — three-way settlement',
                'added_date' => '2026-04-22', 'added_by' => 'Adv. F. Hussain (Lawyer)', 'source' => 'generated',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 3],

            // --- CA-02434 · Pooja M. ---
            ['document_uid' => 'DOC-0201', 'case_ref' => 'CA-02434', 'type' => 'consent', 'name' => 'Intake consent & data-sharing form',
                'added_date' => '2026-04-09', 'added_by' => 'K. Leghari (Paralegal)', 'source' => 'uploaded',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 2],
            ['document_uid' => 'DOC-0202', 'case_ref' => 'CA-02434', 'type' => 'other', 'name' => 'Information sheet — Hindu Marriage Act provisions (Sindhi)',
                'added_date' => '2026-04-10', 'added_by' => 'K. Leghari (Paralegal)', 'source' => 'generated',
                'status' => 'archived', 'confidentiality' => 'public', 'document_ref' => 'JH-INFO-2025/HMA-SD', 'pages' => 4],

            // --- CA-02446 · Reshma D. ---
            ['document_uid' => 'DOC-0191', 'case_ref' => 'CA-02446', 'type' => 'consent', 'name' => 'Intake consent & data-sharing form',
                'added_date' => '2026-04-12', 'added_by' => 'K. Leghari (Paralegal)', 'source' => 'uploaded',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 2],
            ['document_uid' => 'DOC-0192', 'case_ref' => 'CA-02446', 'type' => 'other', 'name' => 'Information sheet — Workers\' rights & wage recovery',
                'added_date' => '2026-04-12', 'added_by' => 'K. Leghari (Paralegal)', 'source' => 'generated',
                'status' => 'archived', 'confidentiality' => 'public', 'document_ref' => 'JH-INFO-2025/WR-UR', 'pages' => 3],

            // --- CA-02448 · Bashir A. ---
            ['document_uid' => 'DOC-0181', 'case_ref' => 'CA-02448', 'type' => 'consent', 'name' => 'Intake consent & data-sharing form',
                'added_date' => '2026-04-02', 'added_by' => 'A. Mahar (Paralegal)', 'source' => 'uploaded',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 2],
            ['document_uid' => 'DOC-0182', 'case_ref' => 'CA-02448', 'type' => 'filing', 'name' => 'Court representation engagement letter',
                'added_date' => '2026-04-02', 'added_by' => 'Adv. N. Jatoi (Lawyer)', 'source' => 'generated',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 2],
            ['document_uid' => 'DOC-0183', 'case_ref' => 'CA-02448', 'type' => 'evidence', 'name' => 'Police FIR (witness)',
                'added_date' => '2026-04-04', 'added_by' => 'Adv. N. Jatoi (Lawyer)', 'source' => 'received',
                'status' => 'archived', 'confidentiality' => 'restricted', 'document_ref' => 'FIR-SUK-2026/0411', 'pages' => 2],

            // --- Active cases: consent forms + extras ---
            ['document_uid' => 'DOC-0301', 'case_ref' => 'CA-02471', 'type' => 'consent', 'name' => 'Intake consent & data-sharing form',
                'added_date' => '2026-04-21', 'added_by' => 'N. Memon (Paralegal)', 'source' => 'uploaded',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 2],
            ['document_uid' => 'DOC-0302', 'case_ref' => 'CA-02471', 'type' => 'evidence', 'name' => 'Land record extract (preliminary)',
                'added_date' => '2026-04-22', 'added_by' => 'N. Memon (Paralegal)', 'source' => 'received',
                'status' => 'archived', 'confidentiality' => 'restricted', 'document_ref' => 'REV-SAN-2026/0501', 'pages' => 2],

            ['document_uid' => 'DOC-0291', 'case_ref' => 'CA-02468', 'type' => 'consent', 'name' => 'Intake consent & data-sharing form',
                'added_date' => '2026-04-20', 'added_by' => 'H. Soomro (Paralegal)', 'source' => 'uploaded',
                'status' => 'signed', 'confidentiality' => 'sensitive', 'document_ref' => null, 'pages' => 2],
            ['document_uid' => 'DOC-0292', 'case_ref' => 'CA-02468', 'type' => 'medical', 'name' => 'MLC referral request',
                'added_date' => '2026-04-21', 'added_by' => 'Adv. R. Khan (Lawyer)', 'source' => 'generated',
                'status' => 'submitted', 'confidentiality' => 'sensitive', 'document_ref' => 'JH-MLC-REQ-2026/058', 'pages' => 1],

            ['document_uid' => 'DOC-0281', 'case_ref' => 'CA-02465', 'type' => 'consent', 'name' => 'Intake consent & data-sharing form',
                'added_date' => '2026-04-19', 'added_by' => 'K. Leghari (Paralegal)', 'source' => 'uploaded',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 2],

            ['document_uid' => 'DOC-0271', 'case_ref' => 'CA-02462', 'type' => 'consent', 'name' => 'Intake consent & data-sharing form',
                'added_date' => '2026-04-15', 'added_by' => 'K. Leghari (Paralegal)', 'source' => 'uploaded',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 2],
            ['document_uid' => 'DOC-0272', 'case_ref' => 'CA-02462', 'type' => 'other', 'name' => 'Mediation agreement — household reconciliation',
                'added_date' => '2026-04-23', 'added_by' => 'Adv. S. Abbasi (Lawyer)', 'source' => 'generated',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 2],

            ['document_uid' => 'DOC-0261', 'case_ref' => 'CA-02459', 'type' => 'consent', 'name' => 'Intake consent & data-sharing form',
                'added_date' => '2026-04-14', 'added_by' => 'T. Panhwar (Paralegal)', 'source' => 'uploaded',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 2],
            ['document_uid' => 'DOC-0262', 'case_ref' => 'CA-02459', 'type' => 'filing', 'name' => 'Court filing draft — pending review',
                'added_date' => '2026-04-20', 'added_by' => 'Adv. F. Hussain (Lawyer)', 'source' => 'generated',
                'status' => 'draft', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 7],

            ['document_uid' => 'DOC-0251', 'case_ref' => 'CA-02455', 'type' => 'consent', 'name' => 'Intake consent & data-sharing form',
                'added_date' => '2026-04-12', 'added_by' => 'A. Mahar (Paralegal)', 'source' => 'uploaded',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 2],

            ['document_uid' => 'DOC-0252', 'case_ref' => 'CA-02453', 'type' => 'consent', 'name' => 'Intake consent & data-sharing form',
                'added_date' => '2026-04-10', 'added_by' => 'N. Memon (Paralegal)', 'source' => 'uploaded',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 2],
            ['document_uid' => 'DOC-0253', 'case_ref' => 'CA-02453', 'type' => 'other', 'name' => 'Mediation agreement — landlord/tenant',
                'added_date' => '2026-04-22', 'added_by' => 'Adv. F. Hussain (Lawyer)', 'source' => 'generated',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 2],

            ['document_uid' => 'DOC-0254', 'case_ref' => 'CA-02450', 'type' => 'consent', 'name' => 'Intake consent & data-sharing form',
                'added_date' => '2026-04-08', 'added_by' => 'H. Soomro (Paralegal)', 'source' => 'uploaded',
                'status' => 'signed', 'confidentiality' => 'restricted', 'document_ref' => null, 'pages' => 2],
        ];

        foreach ($documents as $doc) {
            $caseRef = $doc['case_ref'];
            unset($doc['case_ref']);

            $case = CaseRecord::where('case_ref', $caseRef)->first();

            if (! $case) {
                $this->command->warn("Case {$caseRef} not found — skipping document {$doc['document_uid']}.");
                continue;
            }

            $doc['case_id'] = $case->id;

            Document::firstOrCreate(
                ['document_uid' => $doc['document_uid']],
                $doc
            );
        }
    }
}
