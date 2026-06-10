<?php

namespace Database\Seeders;

use App\Models\CaseRecord;
use App\Models\Feedback;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            ['id' => 'FB-016', 'caseId' => 'CA-02462', 'hub' => 'JH-DAD-01', 'client_name' => 'Zainab M.', 'anonymous' => false,
             'service' => 'Mediation & ADR', 'lawyer' => 'Adv. S. Abbasi', 'date' => '2026-04-23', 'channel' => 'in-person',
             'overall' => 5, 'helpfulness' => 5, 'respect' => 5, 'understood_rights' => 'yes', 'would_recommend' => 'yes',
             'comment' => 'My brothers and I were not speaking. Now we share the house. The mediator listened to all of us.', 'consent' => true],
            ['id' => 'FB-015', 'caseId' => 'CA-02453', 'hub' => 'JH-SAN-01', 'client_name' => 'Fatima S.', 'anonymous' => false,
             'service' => 'Mediation & ADR', 'lawyer' => 'Adv. F. Hussain', 'date' => '2026-04-22', 'channel' => 'phone',
             'overall' => 4, 'helpfulness' => 5, 'respect' => 5, 'understood_rights' => 'yes', 'would_recommend' => 'yes',
             'comment' => 'They explained everything in Sindhi. The agreement is signed and the landlord stopped harassing us.', 'consent' => true],
            ['id' => 'FB-014', 'caseId' => 'CA-02438', 'hub' => 'JH-SAN-01', 'client_name' => 'Salma R.', 'anonymous' => false,
             'service' => 'Mediation & ADR', 'lawyer' => 'Adv. F. Hussain', 'date' => '2026-04-22', 'channel' => 'in-person',
             'overall' => 5, 'helpfulness' => 5, 'respect' => 5, 'understood_rights' => 'yes', 'would_recommend' => 'yes',
             'comment' => 'Without the Hub I would never have understood my share. The mediator made my brothers listen.', 'consent' => true],
            ['id' => 'FB-013', 'caseId' => 'CA-02448', 'hub' => 'JH-SUK-01', 'client_name' => 'Bashir A.', 'anonymous' => false,
             'service' => 'Court Representation', 'lawyer' => 'Adv. N. Jatoi', 'date' => '2026-04-21', 'channel' => 'in-person',
             'overall' => 5, 'helpfulness' => 5, 'respect' => 5, 'understood_rights' => 'yes', 'would_recommend' => 'yes',
             'comment' => 'They treated my son like a child, not a criminal. He came home that same evening.', 'consent' => true],
            ['id' => 'FB-012', 'caseId' => 'CA-02446', 'hub' => 'JH-DAD-01', 'client_name' => 'Reshma D.', 'anonymous' => false,
             'service' => 'Free Legal Advice', 'lawyer' => 'Adv. P. Kumar', 'date' => '2026-04-20', 'channel' => 'sms',
             'overall' => 4, 'helpfulness' => 5, 'respect' => 4, 'understood_rights' => 'yes', 'would_recommend' => 'yes',
             'comment' => 'The information sheet was very helpful. I knew exactly what to do next.', 'consent' => true],
            ['id' => 'FB-011', 'caseId' => 'CA-02441', 'hub' => 'JH-SBA-01', 'client_name' => 'Yasmin Q.', 'anonymous' => false,
             'service' => 'NADRA & Documentation', 'lawyer' => 'N. Memon', 'date' => '2026-04-18', 'channel' => 'in-person',
             'overall' => 5, 'helpfulness' => 5, 'respect' => 5, 'understood_rights' => 'yes', 'would_recommend' => 'yes',
             'comment' => 'First CNIC of my life. I am 38 years old. The paralegal sat with me at the office until it was done.', 'consent' => true],
            ['id' => 'FB-010', 'caseId' => 'CA-02434', 'hub' => 'JH-SAN-01', 'client_name' => 'Pooja M.', 'anonymous' => false,
             'service' => 'Free Legal Advice', 'lawyer' => 'Adv. F. Hussain', 'date' => '2026-04-17', 'channel' => 'sms',
             'overall' => 4, 'helpfulness' => 4, 'respect' => 5, 'understood_rights' => 'yes', 'would_recommend' => 'yes',
             'comment' => 'Helpful and respectful. The wait was a bit long.', 'consent' => true],
            ['id' => 'FB-009', 'caseId' => 'CA-02431', 'hub' => 'JH-DAD-01', 'client_name' => 'Anwar S.', 'anonymous' => false,
             'service' => 'Mediation & ADR', 'lawyer' => 'Adv. M. Soomro', 'date' => '2026-04-15', 'channel' => 'phone',
             'overall' => 4, 'helpfulness' => 5, 'respect' => 4, 'understood_rights' => 'partial', 'would_recommend' => 'yes',
             'comment' => 'Mediation worked. Some procedure was complicated to understand.', 'consent' => true],
            ['id' => 'FB-008', 'caseId' => 'CA-02426', 'hub' => 'JH-HYD-01', 'client_name' => 'Anonymous', 'anonymous' => true,
             'service' => 'Free Legal Advice', 'lawyer' => 'Adv. F. Hussain', 'date' => '2026-04-14', 'channel' => 'sms',
             'overall' => 5, 'helpfulness' => 5, 'respect' => 5, 'understood_rights' => 'yes', 'would_recommend' => 'yes',
             'comment' => '', 'consent' => false],
            ['id' => 'FB-007', 'caseId' => 'CA-02425', 'hub' => 'JH-SUK-01', 'client_name' => 'Naseeb K.', 'anonymous' => false,
             'service' => 'Mediation & ADR', 'lawyer' => 'Adv. N. Jatoi', 'date' => '2026-04-12', 'channel' => 'in-person',
             'overall' => 3, 'helpfulness' => 3, 'respect' => 4, 'understood_rights' => 'partial', 'would_recommend' => 'maybe',
             'comment' => 'Felt rushed. The mediator was kind but did not have enough time to listen.', 'consent' => true],
            ['id' => 'FB-006', 'caseId' => 'CA-02422', 'hub' => 'JH-DAD-01', 'client_name' => 'Iqbal H.', 'anonymous' => false,
             'service' => 'NADRA & Documentation', 'lawyer' => 'K. Leghari', 'date' => '2026-04-11', 'channel' => 'phone',
             'overall' => 5, 'helpfulness' => 5, 'respect' => 5, 'understood_rights' => 'yes', 'would_recommend' => 'yes',
             'comment' => 'Excellent service. Documents in two weeks.', 'consent' => true],
            ['id' => 'FB-005', 'caseId' => 'CA-02418', 'hub' => 'JH-SUK-01', 'client_name' => 'Rashida B.', 'anonymous' => false,
             'service' => 'Court Representation', 'lawyer' => 'Adv. N. Jatoi', 'date' => '2026-04-09', 'channel' => 'in-person',
             'overall' => 5, 'helpfulness' => 5, 'respect' => 5, 'understood_rights' => 'yes', 'would_recommend' => 'yes',
             'comment' => 'They got my share back. After 11 years of being told it was impossible.', 'consent' => true],
            ['id' => 'FB-004', 'caseId' => 'CA-02414', 'hub' => 'JH-DAD-01', 'client_name' => 'Hassan I.', 'anonymous' => false,
             'service' => 'Court Representation', 'lawyer' => 'Adv. M. Soomro', 'date' => '2026-04-07', 'channel' => 'in-person',
             'overall' => 4, 'helpfulness' => 5, 'respect' => 5, 'understood_rights' => 'yes', 'would_recommend' => 'yes',
             'comment' => 'Acquittal. The lawyer was prepared.', 'consent' => true],
            ['id' => 'FB-003', 'caseId' => 'CA-02408', 'hub' => 'JH-SAN-01', 'client_name' => 'Anonymous', 'anonymous' => true,
             'service' => 'Mediation & ADR', 'lawyer' => 'Adv. F. Hussain', 'date' => '2026-04-04', 'channel' => 'sms',
             'overall' => 2, 'helpfulness' => 3, 'respect' => 3, 'understood_rights' => 'no', 'would_recommend' => 'no',
             'comment' => 'Did not feel I was heard. The other side dominated the room.', 'consent' => true],
            ['id' => 'FB-002', 'caseId' => 'CA-02402', 'hub' => 'JH-DAD-01', 'client_name' => 'Yasmin O.', 'anonymous' => false,
             'service' => 'Court Representation', 'lawyer' => 'Adv. P. Kumar', 'date' => '2026-03-30', 'channel' => 'phone',
             'overall' => 3, 'helpfulness' => 4, 'respect' => 5, 'understood_rights' => 'partial', 'would_recommend' => 'maybe',
             'comment' => 'The case was dismissed but the team explained why and what comes next.', 'consent' => true],
            ['id' => 'FB-001', 'caseId' => 'CA-02398', 'hub' => 'JH-SBA-01', 'client_name' => 'Ghulam M.', 'anonymous' => false,
             'service' => 'Free Legal Advice', 'lawyer' => 'Adv. R. Khan', 'date' => '2026-03-26', 'channel' => 'in-person',
             'overall' => 4, 'helpfulness' => 4, 'respect' => 5, 'understood_rights' => 'yes', 'would_recommend' => 'yes',
             'comment' => 'Good advice. They told me what I needed to do without making me feel small.', 'consent' => true],
        ];

        foreach ($records as $r) {
            $case = CaseRecord::where('case_ref', $r['caseId'])->first();

            Feedback::firstOrCreate(
                ['feedback_uid' => $r['id']],
                [
                    'case_id'          => $case?->id,
                    'hub_id'           => $r['hub'],
                    'client_name'      => $r['client_name'],
                    'is_anonymous'     => $r['anonymous'],
                    'service'          => $r['service'],
                    'lawyer'           => $r['lawyer'],
                    'date'             => $r['date'],
                    'channel'          => $r['channel'],
                    'score_overall'    => $r['overall'],
                    'score_helpfulness'=> $r['helpfulness'],
                    'score_respect'    => $r['respect'],
                    'understood_rights'=> $r['understood_rights'],
                    'would_recommend'  => $r['would_recommend'],
                    'comment'          => $r['comment'],
                    'consent_to_share' => $r['consent'],
                ]
            );
        }
    }
}
