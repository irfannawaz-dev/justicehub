<?php

namespace Database\Seeders;

use App\Models\CaseRecord;
use App\Models\Complaint;
use App\Models\ComplaintAction;
use Illuminate\Database\Seeder;

class ComplaintSeeder extends Seeder
{
    public function run(): void
    {
        $complaints = [
            // --- Recent + open ---
            [
                'complaint_uid' => 'CMP-021', 'case_ref' => 'CA-02465',
                'submitted_date' => '2026-04-26', 'submitted_by' => 'Aisha M.',
                'is_anonymous' => false, 'channel' => 'phone',
                'category' => 'service-delay', 'severity' => 'medium', 'sla_days' => 14,
                'description' => 'Lawyer rescheduled my appointment three times. I have taken leave from work each time.',
                'hub_id' => 'JH-DAD-01', 'assigned_to' => 'Bilal Ahmed (Hub Manager)',
                'status' => 'in-progress', 'resolved_date' => null, 'resolution' => null,
                'client_satisfied' => 'n.a.',
                'actions' => [
                    ['date' => '2026-04-26', 'performed_by' => 'Bilal Ahmed', 'note' => 'Complaint received and logged. Acknowledgement SMS sent to client.'],
                    ['date' => '2026-04-27', 'performed_by' => 'Bilal Ahmed', 'note' => 'Reviewed lawyer schedule. Conflict identified — second hearing in same time slot.'],
                ],
            ],
            [
                'complaint_uid' => 'CMP-020', 'case_ref' => null,
                'submitted_date' => '2026-04-24', 'submitted_by' => 'Anonymous',
                'is_anonymous' => true, 'channel' => 'written',
                'category' => 'staff-conduct', 'severity' => 'high', 'sla_days' => 7,
                'description' => 'A paralegal was rude and dismissive when I asked questions about my CNIC application. Made me feel unwelcome.',
                'hub_id' => 'JH-SUK-01', 'assigned_to' => 'Adv. N. Jatoi (Senior Lawyer)',
                'status' => 'in-progress', 'resolved_date' => null, 'resolution' => null,
                'client_satisfied' => 'n.a.',
                'actions' => [
                    ['date' => '2026-04-24', 'performed_by' => 'Adv. N. Jatoi', 'note' => 'Written complaint received via complaint box. Logged.'],
                    ['date' => '2026-04-25', 'performed_by' => 'Adv. N. Jatoi', 'note' => 'Reviewed CCTV from cited time window. Identified paralegal A. Mahar.'],
                    ['date' => '2026-04-28', 'performed_by' => 'Adv. N. Jatoi', 'note' => 'Conducted private conversation with paralegal. Acknowledged tone was poor due to high caseload that day.'],
                ],
            ],
            [
                'complaint_uid' => 'CMP-019', 'case_ref' => 'CA-02459',
                'submitted_date' => '2026-04-22', 'submitted_by' => 'Yousaf K.',
                'is_anonymous' => false, 'channel' => 'in-person',
                'category' => 'communication', 'severity' => 'low', 'sla_days' => 30,
                'description' => 'I was told my case would be ready for filing last week. No one called to update me.',
                'hub_id' => 'JH-HYD-01', 'assigned_to' => 'T. Panhwar (Paralegal)',
                'status' => 'in-progress', 'resolved_date' => null, 'resolution' => null,
                'client_satisfied' => 'n.a.',
                'actions' => [
                    ['date' => '2026-04-22', 'performed_by' => 'T. Panhwar', 'note' => 'Walked in to file complaint. Apologised and confirmed delay was due to court date availability.'],
                ],
            ],

            // --- Resolved within SLA ---
            [
                'complaint_uid' => 'CMP-018', 'case_ref' => 'CA-02441',
                'submitted_date' => '2026-04-15', 'submitted_by' => 'Yasmin Q.',
                'is_anonymous' => false, 'channel' => 'phone',
                'category' => 'service-delay', 'severity' => 'medium', 'sla_days' => 14,
                'description' => 'My NADRA appointment was delayed by 3 weeks. Was told it would take 5 days.',
                'hub_id' => 'JH-SBA-01', 'assigned_to' => 'Irfan Nawaz (Hub Manager)',
                'status' => 'resolved', 'resolved_date' => '2026-04-19',
                'resolution' => 'Personally accompanied client to NADRA centre with priority slip. CNIC issued same day. Coordinated with NADRA focal point on average wait times.',
                'client_satisfied' => 'yes',
                'actions' => [
                    ['date' => '2026-04-15', 'performed_by' => 'Irfan Nawaz', 'note' => 'Complaint received by phone. Acknowledged.'],
                    ['date' => '2026-04-16', 'performed_by' => 'Irfan Nawaz', 'note' => 'Contacted NADRA focal point. Identified bottleneck in regional office.'],
                    ['date' => '2026-04-19', 'performed_by' => 'Irfan Nawaz', 'note' => 'Resolution — escorted client, CNIC issued. Closure SMS sent.'],
                ],
            ],
            [
                'complaint_uid' => 'CMP-017', 'case_ref' => null,
                'submitted_date' => '2026-04-12', 'submitted_by' => 'Partner: Aurat Foundation',
                'is_anonymous' => false, 'channel' => 'written',
                'category' => 'coordination', 'severity' => 'medium', 'sla_days' => 14,
                'description' => 'GBV referral acceptance email took 9 days to come back. The client could not wait that long.',
                'hub_id' => 'JH-SAN-01', 'assigned_to' => 'Adv. F. Hussain (Senior Lawyer)',
                'status' => 'resolved', 'resolved_date' => '2026-04-21',
                'resolution' => 'Reviewed referral inbox process. Implemented daily morning sweep of inbox by paralegal. Apology issued to Aurat Foundation. Process diagram shared.',
                'client_satisfied' => 'yes',
                'actions' => [
                    ['date' => '2026-04-12', 'performed_by' => 'Adv. F. Hussain', 'note' => 'Email received. Reviewed referral inbox — confirmed lag.'],
                    ['date' => '2026-04-15', 'performed_by' => 'Adv. F. Hussain', 'note' => 'Met with paralegal team. Designed new SOP for referral acknowledgement.'],
                    ['date' => '2026-04-21', 'performed_by' => 'Adv. F. Hussain', 'note' => 'New SOP rolled out. Communicated back to Aurat Foundation.'],
                ],
            ],
            [
                'complaint_uid' => 'CMP-016', 'case_ref' => 'CA-02434',
                'submitted_date' => '2026-04-08', 'submitted_by' => 'Pooja M.',
                'is_anonymous' => false, 'channel' => 'in-person',
                'category' => 'communication', 'severity' => 'low', 'sla_days' => 30,
                'description' => 'No one explained that I would need to come back a second time.',
                'hub_id' => 'JH-SAN-01', 'assigned_to' => 'N. Memon (Paralegal)',
                'status' => 'resolved', 'resolved_date' => '2026-04-10',
                'resolution' => 'Apologised. Established that intake checklist now requires the paralegal to verbally walk through next steps. Updated form to include a "next visit booked" field.',
                'client_satisfied' => 'yes',
                'actions' => [
                    ['date' => '2026-04-08', 'performed_by' => 'N. Memon', 'note' => 'Acknowledged complaint. Apologised in person.'],
                    ['date' => '2026-04-10', 'performed_by' => 'N. Memon', 'note' => 'Updated intake form with next-visit field. SOP refresh planned.'],
                ],
            ],
            [
                'complaint_uid' => 'CMP-015', 'case_ref' => 'CA-02428',
                'submitted_date' => '2026-04-05', 'submitted_by' => 'Zubaida B.',
                'is_anonymous' => false, 'channel' => 'phone',
                'category' => 'data-privacy', 'severity' => 'high', 'sla_days' => 7,
                'description' => 'My case details were discussed by two staff members in the waiting area where other clients could hear.',
                'hub_id' => 'JH-SBA-01', 'assigned_to' => 'Irfan Nawaz (Hub Manager)',
                'status' => 'resolved', 'resolved_date' => '2026-04-09',
                'resolution' => 'Spoke with both staff members involved. Issued formal verbal warning. Mandatory data protection refresh scheduled for both. Posted reminder signage in staff areas. Apology delivered in person to client.',
                'client_satisfied' => 'yes',
                'actions' => [
                    ['date' => '2026-04-05', 'performed_by' => 'Irfan Nawaz', 'note' => 'Complaint received. Severity assessed as HIGH (data privacy).'],
                    ['date' => '2026-04-06', 'performed_by' => 'Irfan Nawaz', 'note' => 'Identified the two staff members. Held separate conversations.'],
                    ['date' => '2026-04-08', 'performed_by' => 'Irfan Nawaz', 'note' => 'Verbal warning issued. Data Protection refresh scheduled.'],
                    ['date' => '2026-04-09', 'performed_by' => 'Irfan Nawaz', 'note' => 'In-person apology to client. Closure confirmed.'],
                ],
            ],
            [
                'complaint_uid' => 'CMP-014', 'case_ref' => 'CA-02425',
                'submitted_date' => '2026-04-03', 'submitted_by' => 'Naseeb K.',
                'is_anonymous' => false, 'channel' => 'in-person',
                'category' => 'service-quality', 'severity' => 'low', 'sla_days' => 30,
                'description' => 'Mediation session felt rushed. Mediator did not have enough time to listen.',
                'hub_id' => 'JH-SUK-01', 'assigned_to' => 'Adv. N. Jatoi (Senior Lawyer)',
                'status' => 'resolved', 'resolved_date' => '2026-04-14',
                'resolution' => 'Discussed with mediator. Confirmed scheduling pressure that day. Adjusted mediation booking blocks from 60 to 90 mins as a result. Offered client a follow-up session at no additional cost.',
                'client_satisfied' => 'yes',
                'actions' => [
                    ['date' => '2026-04-03', 'performed_by' => 'Adv. N. Jatoi', 'note' => 'Walk-in complaint. Acknowledged.'],
                    ['date' => '2026-04-09', 'performed_by' => 'Adv. N. Jatoi', 'note' => 'Reviewed scheduling logs. Spoke with mediator.'],
                    ['date' => '2026-04-14', 'performed_by' => 'Adv. N. Jatoi', 'note' => 'New 90-min mediation blocks rolled out. Follow-up offered to client.'],
                ],
            ],

            // --- Resolved past SLA ---
            [
                'complaint_uid' => 'CMP-013', 'case_ref' => 'CA-02418',
                'submitted_date' => '2026-03-22', 'submitted_by' => 'Anonymous',
                'is_anonymous' => true, 'channel' => 'written',
                'category' => 'discrimination', 'severity' => 'critical', 'sla_days' => 3,
                'description' => 'I was told the lawyer "doesn\'t handle Hindu cases" and was redirected to a different paralegal.',
                'hub_id' => 'JH-DAD-01', 'assigned_to' => 'Bilal Ahmed (Hub Manager)',
                'status' => 'resolved', 'resolved_date' => '2026-03-30',
                'resolution' => 'Investigated immediately. Identified the lawyer involved. Held formal disciplinary conversation. Conducted hub-wide refresher on non-discrimination clause in Code of Conduct. Issued written warning to lawyer and copied to LAS HQ.',
                'client_satisfied' => 'n.a.',
                'actions' => [
                    ['date' => '2026-03-22', 'performed_by' => 'Bilal Ahmed', 'note' => 'Complaint received via complaint box. Critical severity.'],
                    ['date' => '2026-03-23', 'performed_by' => 'Bilal Ahmed', 'note' => 'Identified lawyer. Held initial conversation.'],
                    ['date' => '2026-03-25', 'performed_by' => 'Bilal Ahmed', 'note' => 'Escalated to LAS HQ for guidance.'],
                    ['date' => '2026-03-28', 'performed_by' => 'Bilal Ahmed', 'note' => 'Hub-wide non-discrimination refresher delivered.'],
                    ['date' => '2026-03-30', 'performed_by' => 'Bilal Ahmed', 'note' => 'Written warning issued. Closure recorded. Anonymous complainant — could not deliver direct apology.'],
                ],
            ],
            [
                'complaint_uid' => 'CMP-012', 'case_ref' => 'CA-02408',
                'submitted_date' => '2026-03-08', 'submitted_by' => 'Imran T.',
                'is_anonymous' => false, 'channel' => 'phone',
                'category' => 'service-delay', 'severity' => 'medium', 'sla_days' => 14,
                'description' => 'Documentation case has been open for 4 months with no progress updates.',
                'hub_id' => 'JH-HYD-01', 'assigned_to' => 'T. Panhwar (Paralegal)',
                'status' => 'resolved', 'resolved_date' => '2026-03-26',
                'resolution' => 'Reviewed case file. Identified bottleneck (NADRA delay outside Hub control). Implemented monthly proactive update SMS for all open documentation cases. Apology and progress update delivered.',
                'client_satisfied' => 'yes',
                'actions' => [
                    ['date' => '2026-03-08', 'performed_by' => 'T. Panhwar', 'note' => 'Complaint received by phone.'],
                    ['date' => '2026-03-12', 'performed_by' => 'T. Panhwar', 'note' => 'Reviewed file. Confirmed NADRA-side delay.'],
                    ['date' => '2026-03-26', 'performed_by' => 'T. Panhwar', 'note' => 'Monthly SMS proactive updates rolled out across hub.'],
                ],
            ],

            // --- Older resolved within SLA ---
            [
                'complaint_uid' => 'CMP-011', 'case_ref' => 'CA-02402',
                'submitted_date' => '2026-02-28', 'submitted_by' => 'Salman R.',
                'is_anonymous' => false, 'channel' => 'in-person',
                'category' => 'communication', 'severity' => 'low', 'sla_days' => 30,
                'description' => 'Long wait time without explanation.',
                'hub_id' => 'JH-SBA-01', 'assigned_to' => 'Irfan Nawaz (Hub Manager)',
                'status' => 'resolved', 'resolved_date' => '2026-03-02',
                'resolution' => 'Posted estimated wait times at reception. Trained reception staff to verbally update clients every 20 mins.',
                'client_satisfied' => 'yes',
                'actions' => [
                    ['date' => '2026-02-28', 'performed_by' => 'Irfan Nawaz', 'note' => 'Walk-in complaint. Acknowledged.'],
                    ['date' => '2026-03-02', 'performed_by' => 'Irfan Nawaz', 'note' => 'Reception SOP updated. Wait-time signage posted.'],
                ],
            ],
            [
                'complaint_uid' => 'CMP-010', 'case_ref' => null,
                'submitted_date' => '2026-02-20', 'submitted_by' => 'Partner: Sindh Police WPD',
                'is_anonymous' => false, 'channel' => 'written',
                'category' => 'coordination', 'severity' => 'medium', 'sla_days' => 14,
                'description' => 'Joint case file did not include MLC reference number. Caused 2-day delay in our processing.',
                'hub_id' => 'JH-SAN-01', 'assigned_to' => 'Adv. F. Hussain (Senior Lawyer)',
                'status' => 'resolved', 'resolved_date' => '2026-03-01',
                'resolution' => 'Added MLC reference field as mandatory in joint-case file template. Trained intake paralegals on field. Apology and revised template shared with WPD.',
                'client_satisfied' => 'yes',
                'actions' => [
                    ['date' => '2026-02-20', 'performed_by' => 'Adv. F. Hussain', 'note' => 'Written complaint from WPD focal.'],
                    ['date' => '2026-03-01', 'performed_by' => 'Adv. F. Hussain', 'note' => 'Template updated. Training completed.'],
                ],
            ],
            [
                'complaint_uid' => 'CMP-009', 'case_ref' => 'CA-02398',
                'submitted_date' => '2026-02-14', 'submitted_by' => 'Reshma D.',
                'is_anonymous' => false, 'channel' => 'in-person',
                'category' => 'service-quality', 'severity' => 'low', 'sla_days' => 30,
                'description' => 'Information sheet was in Urdu — I read Sindhi.',
                'hub_id' => 'JH-DAD-01', 'assigned_to' => 'K. Leghari (Paralegal)',
                'status' => 'resolved', 'resolved_date' => '2026-02-21',
                'resolution' => 'Sourced and printed Sindhi version of all 12 information sheets. Now stocked at reception. Stocked at all 5 hubs.',
                'client_satisfied' => 'yes',
                'actions' => [
                    ['date' => '2026-02-14', 'performed_by' => 'K. Leghari', 'note' => 'Walk-in complaint. Apologised.'],
                    ['date' => '2026-02-21', 'performed_by' => 'K. Leghari', 'note' => 'Sindhi materials sourced and stocked across all hubs.'],
                ],
            ],
            [
                'complaint_uid' => 'CMP-008', 'case_ref' => 'CA-02391',
                'submitted_date' => '2026-02-04', 'submitted_by' => 'Anwar S.',
                'is_anonymous' => false, 'channel' => 'phone',
                'category' => 'communication', 'severity' => 'low', 'sla_days' => 30,
                'description' => 'Mediation procedure was complicated to understand.',
                'hub_id' => 'JH-DAD-01', 'assigned_to' => 'Adv. M. Soomro (Lawyer)',
                'status' => 'resolved', 'resolved_date' => '2026-02-15',
                'resolution' => 'Created plain-language mediation guide in Sindhi and Urdu. Now given to all clients before first mediation session.',
                'client_satisfied' => 'yes',
                'actions' => [
                    ['date' => '2026-02-04', 'performed_by' => 'Adv. M. Soomro', 'note' => 'Phone complaint received.'],
                    ['date' => '2026-02-15', 'performed_by' => 'Adv. M. Soomro', 'note' => 'Plain-language guide drafted, translated, and rolled out.'],
                ],
            ],
        ];

        foreach ($complaints as $data) {
            $actions = $data['actions'];
            $caseRef = $data['case_ref'];
            unset($data['actions'], $data['case_ref']);

            // Look up case_id from case_ref
            $data['case_id'] = null;
            if ($caseRef) {
                $case = CaseRecord::where('case_ref', $caseRef)->first();
                $data['case_id'] = $case?->id;
            }

            $complaint = Complaint::firstOrCreate(
                ['complaint_uid' => $data['complaint_uid']],
                $data,
            );

            // Insert complaint actions
            foreach ($actions as $action) {
                ComplaintAction::firstOrCreate([
                    'complaint_id' => $complaint->id,
                    'date'         => $action['date'],
                    'performed_by' => $action['performed_by'],
                ], [
                    'note' => $action['note'],
                ]);
            }
        }
    }
}
