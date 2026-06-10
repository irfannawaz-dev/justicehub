<?php

namespace Database\Seeders;

use App\Models\CaseRecord;
use App\Models\ServiceEncounter;
use Illuminate\Database\Seeder;

class ServiceEncounterSeeder extends Seeder
{
    public function run(): void
    {
        // Map case_uid => services array (extracted from JSX CASES data)
        $caseServices = [
            'CL-02471' => [
                ['date' => '2026-04-21', 'type' => 'Intake', 'by' => 'N. Memon', 'note' => 'Client registered. GBV screening negative. Safeguarding risk medium.'],
                ['date' => '2026-04-22', 'type' => 'Assessment', 'by' => 'Adv. F. Hussain', 'note' => 'Primary issue: inheritance. Pathway set: advice + mediation. SLA met (20 hrs).'],
                ['date' => '2026-04-22', 'type' => 'Legal Advice', 'by' => 'Adv. F. Hussain', 'note' => 'Explained Muslim Family Law Ordinance provisions. Next step: document collection.'],
            ],
            'CL-02468' => [
                ['date' => '2026-04-20', 'type' => 'Intake', 'by' => 'H. Soomro', 'note' => 'Referral from Panah Shelter. Safeguarding flag active. Immediate protection referral coordinated.'],
                ['date' => '2026-04-20', 'type' => 'Assessment', 'by' => 'Adv. R. Khan', 'note' => 'Urgency: Immediate. Protection pathway prioritised.'],
                ['date' => '2026-04-21', 'type' => 'Referral', 'by' => 'Adv. R. Khan', 'note' => 'Referred to Darul Aman for continued safe accommodation.'],
                ['date' => '2026-04-22', 'type' => 'Legal Advice', 'by' => 'Adv. R. Khan', 'note' => 'Filing protection order under DV Act. Khula petition to follow.'],
            ],
            'CL-02465' => [
                ['date' => '2026-04-18', 'type' => 'Intake', 'by' => 'Z. Ali', 'note' => 'Returning client — previous visit for wage dispute (2024).'],
                ['date' => '2026-04-19', 'type' => 'Assessment', 'by' => 'T. Panhwar', 'note' => 'NADRA route confirmed. Forms prepared.'],
                ['date' => '2026-04-21', 'type' => 'Documentation', 'by' => 'T. Panhwar', 'note' => 'CNIC application submitted at NADRA Latifabad. Tracking no. issued.'],
            ],
            'CL-02462' => [
                ['date' => '2026-04-17', 'type' => 'Intake', 'by' => 'K. Leghari', 'note' => 'Identified during legal literacy outreach. Walked in same afternoon.'],
                ['date' => '2026-04-18', 'type' => 'Assessment', 'by' => 'Adv. S. Abbasi', 'note' => 'Mediation pathway. Both parties contacted and agreed to session.'],
                ['date' => '2026-04-20', 'type' => 'Mediation', 'by' => 'Adv. S. Abbasi', 'note' => 'Session 1 of 2 held. Preliminary agreement on usufruct. Settlement draft in progress.'],
            ],
            'CL-02459' => [
                ['date' => '2026-04-16', 'type' => 'Intake', 'by' => 'M. Soomro', 'note' => 'Outreach identification. Minority status flagged. Vulnerability support required.'],
                ['date' => '2026-04-20', 'type' => 'Assessment', 'by' => 'Adv. P. Kumar', 'note' => 'SLA BREACH: assessment delayed by 32 hrs due to hub transport disruption. Escalated.'],
                ['date' => '2026-04-22', 'type' => 'Legal Advice', 'by' => 'Adv. P. Kumar', 'note' => 'Wage recovery under Payment of Wages Act. Labour Court petition being prepared.'],
            ],
            'CL-02455' => [
                ['date' => '2026-04-15', 'type' => 'Intake', 'by' => 'A. Mahar', 'note' => 'Police referral. Juvenile Justice System Act triggered. Child-protection flag active.'],
                ['date' => '2026-04-15', 'type' => 'Assessment', 'by' => 'Adv. N. Jatoi', 'note' => 'Same-day. Emergency bail application. Social welfare coordination.'],
                ['date' => '2026-04-16', 'type' => 'Litigation', 'by' => 'Adv. N. Jatoi', 'note' => 'Bail granted under JJSA. Juvenile diverted to rehabilitation pathway.'],
                ['date' => '2026-04-22', 'type' => 'Referral', 'by' => 'Adv. N. Jatoi', 'note' => 'Referred to Sukkur Child Protection Unit for continued psycho-social support.'],
            ],
            'CL-02453' => [
                ['date' => '2026-04-14', 'type' => 'Intake', 'by' => 'N. Memon', 'note' => 'Walk-in. Tenancy dispute registered.'],
                ['date' => '2026-04-15', 'type' => 'Assessment', 'by' => 'Adv. F. Hussain', 'note' => 'Mediation feasible. Landlord contacted.'],
                ['date' => '2026-04-17', 'type' => 'Mediation', 'by' => 'Adv. F. Hussain', 'note' => '3-month notice with relocation assistance agreed.'],
                ['date' => '2026-04-19', 'type' => 'Closure', 'by' => 'Adv. F. Hussain', 'note' => 'Case closed. Satisfaction survey: 5/5. Compliance confirmed.'],
            ],
            'CL-02450' => [
                ['date' => '2026-04-12', 'type' => 'Intake', 'by' => 'Digital Form', 'note' => 'Digital intake via NAZ Assist app. Returning client.'],
                ['date' => '2026-04-13', 'type' => 'Assessment', 'by' => 'Adv. R. Khan', 'note' => 'Documentation pathway. Paralegal assigned.'],
                ['date' => '2026-04-20', 'type' => 'Documentation', 'by' => 'T. Panhwar', 'note' => 'Death certificate application at Union Council. Nominee forms in progress.'],
            ],
            'CL-02448' => [
                ['date' => '2026-04-11', 'type' => 'Intake', 'by' => 'A. Mahar', 'note' => 'Walk-in. Quick triage; documentation light.'],
                ['date' => '2026-04-11', 'type' => 'Legal Advice', 'by' => 'Adv. N. Jatoi', 'note' => 'Explained recovery rights and procedures. Issued advisory letter to employer. Client opted to first try informal resolution.'],
                ['date' => '2026-04-11', 'type' => 'Case Closure', 'by' => 'Adv. N. Jatoi', 'note' => 'Client called to confirm wages received. Closed as advice-only resolution.'],
            ],
            'CL-02446' => [
                ['date' => '2026-04-10', 'type' => 'Intake', 'by' => 'T. Panhwar', 'note' => 'Walk-in inquiry. Light triage.'],
                ['date' => '2026-04-10', 'type' => 'Legal Advice', 'by' => 'Adv. F. Hussain', 'note' => 'Provided 30-min counsel on options. Information sheet handed over. Client to consider and return if needed.'],
                ['date' => '2026-04-10', 'type' => 'Case Closure', 'by' => 'Adv. F. Hussain', 'note' => 'Closed at intake. Advice-only encounter; no follow-up requested.'],
            ],
            'CL-02444' => [
                ['date' => '2026-04-09', 'type' => 'Intake', 'by' => 'K. Leghari', 'note' => 'Police referral. Minor: child protection protocol activated.'],
                ['date' => '2026-04-09', 'type' => 'Assessment', 'by' => 'Adv. P. Kumar', 'note' => 'Eligible for emergency bail. Litigation pathway assigned.'],
                ['date' => '2026-04-10', 'type' => 'Court Representation', 'by' => 'Adv. P. Kumar', 'note' => 'Bail application filed at Sessions Court. Diversion request submitted.'],
                ['date' => '2026-04-22', 'type' => 'Follow-up', 'by' => 'Adv. P. Kumar', 'note' => 'Hearing scheduled for Apr 28. Family meeting held.'],
            ],
            'CL-02441' => [
                ['date' => '2026-04-08', 'type' => 'Intake', 'by' => 'N. Memon', 'note' => 'Walk-in. Disability flag set; accessibility accommodations made.'],
                ['date' => '2026-04-08', 'type' => 'Legal Advice', 'by' => 'Adv. F. Hussain', 'note' => 'Counselled on NADRA disability desk procedure. Referral letter prepared.'],
                ['date' => '2026-04-09', 'type' => 'Referral', 'by' => 'Adv. F. Hussain', 'note' => 'Formal referral to NADRA disability desk, Sanghar. Loop-closure protocol: 7-14 days.'],
                ['date' => '2026-04-15', 'type' => 'Follow-up', 'by' => 'N. Memon', 'note' => 'Confirmation pending. Second follow-up scheduled.'],
            ],
            'CL-02438' => [
                ['date' => '2026-04-06', 'type' => 'Intake', 'by' => 'T. Panhwar', 'note' => 'Walk-in. Both parties present at hub.'],
                ['date' => '2026-04-06', 'type' => 'Assessment', 'by' => 'Adv. F. Hussain', 'note' => 'Mediation appropriate. Both consent obtained.'],
                ['date' => '2026-04-12', 'type' => 'Mediation', 'by' => 'Adv. F. Hussain', 'note' => 'Sitting 1. Issues mapped; underlying interests surfaced.'],
                ['date' => '2026-04-19', 'type' => 'Mediation', 'by' => 'Adv. F. Hussain', 'note' => 'Sitting 2. Heads of agreement reached. Drafting underway.'],
            ],
            'CL-02434' => [
                ['date' => '2026-04-04', 'type' => 'Intake', 'by' => 'N. Memon', 'note' => 'Walk-in. Minority flag set.'],
                ['date' => '2026-04-04', 'type' => 'Legal Advice', 'by' => 'Adv. F. Hussain', 'note' => 'Detailed counsel on Sindh Hindu Marriage Act. Procedural sheet provided.'],
                ['date' => '2026-04-04', 'type' => 'Case Closure', 'by' => 'Adv. F. Hussain', 'note' => 'Closed at intake. Client to return when ready to proceed.'],
            ],
            // --- Cases awaiting manager approval ---
            'CL-02431' => [
                ['date' => '2026-04-26', 'type' => 'Intake', 'by' => 'T. Panhwar', 'note' => 'Walk-in. Both brothers present and consenting.'],
                ['date' => '2026-04-26', 'type' => 'Assessment', 'by' => 'Adv. F. Hussain', 'note' => 'Mediation pathway proposed. Submitted to Mediation Manager for approval.'],
            ],
            'CL-02428' => [
                ['date' => '2026-04-25', 'type' => 'Intake', 'by' => 'A. Mahar', 'note' => 'Police referral. GBV protocol activated.'],
                ['date' => '2026-04-25', 'type' => 'Assessment', 'by' => 'Adv. N. Jatoi', 'note' => 'Court representation pathway proposed. Submitted to Litigation Manager for approval.'],
            ],
            'CL-02426' => [
                ['date' => '2026-04-23', 'type' => 'Intake', 'by' => 'K. Leghari', 'note' => 'Walk-in inquiry on loan dispute.'],
                ['date' => '2026-04-23', 'type' => 'Assessment', 'by' => 'Adv. P. Kumar', 'note' => 'Court representation pathway proposed.'],
                ['date' => '2026-04-24', 'type' => 'Approval', 'by' => 'Irfan Nawaz', 'note' => 'Litigation pathway REJECTED. Insufficient documentary evidence. Client advised to attempt informal resolution first.'],
            ],
            // --- Backfilled cases ---
            'CL-02469' => [
                ['date' => '2026-04-26', 'type' => 'Intake', 'by' => 'N. Memon', 'note' => 'Client registered. Spousal conflict screening conducted. No GBV indicators but client distressed.'],
                ['date' => '2026-04-27', 'type' => 'Assessment', 'by' => 'Adv. R. Khan', 'note' => 'Pathway: ADR with mediation first. Family court contingency held. SLA met (16 hrs).'],
            ],
            'CL-02467' => [
                ['date' => '2026-04-24', 'type' => 'Intake', 'by' => 'Z. Ali', 'note' => 'Client registered. Provided land record (Form VII) and field survey copy.'],
                ['date' => '2026-04-27', 'type' => 'Assessment', 'by' => 'Adv. S. Abbasi', 'note' => 'Documentary evidence sufficient for mediation. First session scheduled with both parties.'],
            ],
            'CL-02460' => [
                ['date' => '2026-04-15', 'type' => 'Intake', 'by' => 'H. Soomro', 'note' => 'Client registered. Minority flag (Christian). Wage slips and attendance proofs collected.'],
                ['date' => '2026-04-19', 'type' => 'Assessment', 'by' => 'Adv. P. Kumar', 'note' => 'Strong documentary case. Mediation viable; respondent willing to attend.'],
                ['date' => '2026-04-23', 'type' => 'Mediation', 'by' => 'Adv. P. Kumar', 'note' => 'First session held. Respondent acknowledged Rs. 92,000 owed; disputed remainder. Adjourned for documentation review.'],
                ['date' => '2026-04-27', 'type' => 'Mediation', 'by' => 'Adv. P. Kumar', 'note' => 'Second session: respondent presented payment register. Partial reconciliation reached on principal amount.'],
            ],
            'CL-02458' => [
                ['date' => '2026-04-14', 'type' => 'Intake', 'by' => 'F. Channa', 'note' => 'Client registered via women paralegal. GBV history disclosed. Safety plan agreed; safe contact channel set up.'],
                ['date' => '2026-04-18', 'type' => 'Assessment', 'by' => 'Adv. N. Jatoi', 'note' => 'GBV flag activated. Mediation accepted with safety protocol — no joint sessions, separate rooms only.'],
                ['date' => '2026-04-22', 'type' => 'Mediation', 'by' => 'Adv. N. Jatoi', 'note' => 'First session (separate rooms). Maintenance figure proposed at Rs. 18,000/month. Respondent counter-offered Rs. 8,000.'],
                ['date' => '2026-04-27', 'type' => 'Mediation', 'by' => 'Adv. N. Jatoi', 'note' => 'Second session: respondent agreed in principle to Rs. 14,000 + school fees. Drafting agreement.'],
            ],
            'CL-02454' => [
                ['date' => '2026-04-14', 'type' => 'Intake', 'by' => 'K. Leghari', 'note' => 'Client registered. Tenancy paperwork (kapra-mukhtara) reviewed; long-standing tenant.'],
                ['date' => '2026-04-20', 'type' => 'Assessment', 'by' => 'Adv. M. Soomro', 'note' => 'Tenant rights under Sindh Tenancy Act applicable. Mediation initiated to negotiate compensation.'],
                ['date' => '2026-04-27', 'type' => 'Mediation', 'by' => 'Adv. M. Soomro', 'note' => 'First session: landlord present. Discussed Sindh Tenancy Act protections and crop-share precedent. Adjourned for legal review.'],
            ],
            'CL-02456' => [
                ['date' => '2026-04-07', 'type' => 'Intake', 'by' => 'N. Memon', 'note' => 'Client registered. Death certificate, marriage certificate and Form VII collected.'],
                ['date' => '2026-04-11', 'type' => 'Assessment', 'by' => 'Adv. F. Hussain', 'note' => 'Sharia inheritance share calculated (1/8 of immovable property). Brothers contacted; agreed to mediation.'],
                ['date' => '2026-04-16', 'type' => 'Mediation', 'by' => 'Adv. F. Hussain', 'note' => 'First session: family disclosure of joint holdings. Boundary mapping commissioned.'],
                ['date' => '2026-04-21', 'type' => 'Mediation', 'by' => 'Adv. F. Hussain', 'note' => 'Second session: surveyor report reviewed. Brothers proposed in-kind partition; client agreed in principle.'],
                ['date' => '2026-04-26', 'type' => 'Mediation', 'by' => 'Adv. F. Hussain', 'note' => 'Third session: terms agreed — 4.2 acres allocated to client. Drafting settlement deed for revenue mutation.'],
            ],
            'CL-02451' => [
                ['date' => '2026-04-01', 'type' => 'Intake', 'by' => 'Z. Ali', 'note' => 'Couple registered jointly. No GBV indicators. Voluntary mediation request.'],
                ['date' => '2026-04-06', 'type' => 'Assessment', 'by' => 'Adv. R. Khan', 'note' => 'Both parties consenting; structured reconciliation pathway accepted. Children\'s arrangements prioritised.'],
                ['date' => '2026-04-11', 'type' => 'Mediation', 'by' => 'Adv. R. Khan', 'note' => 'First session: each party heard separately, then jointly. Trust-building exercise.'],
                ['date' => '2026-04-16', 'type' => 'Mediation', 'by' => 'Adv. R. Khan', 'note' => 'Second session: financial disclosure. Living arrangements discussed.'],
                ['date' => '2026-04-21', 'type' => 'Mediation', 'by' => 'Adv. R. Khan', 'note' => 'Third session: parenting plan drafted. Children\'s preferences respected.'],
                ['date' => '2026-04-26', 'type' => 'Mediation', 'by' => 'Adv. R. Khan', 'note' => 'Fourth session: agreement substantially complete. Drafting reconciliation memorandum.'],
            ],
            'CL-02449' => [
                ['date' => '2026-04-14', 'type' => 'Intake', 'by' => 'F. Khaskheli', 'note' => 'Referral from Panah Shelter. Active safeguarding flag. Children placed with mother under shelter protection.'],
                ['date' => '2026-04-17', 'type' => 'Assessment', 'by' => 'Adv. R. Khan', 'note' => 'GBV pattern severe. ADR considered with safety protocol; safety risk re-assessed after first session.'],
                ['date' => '2026-04-20', 'type' => 'Mediation', 'by' => 'Adv. R. Khan', 'note' => 'First (and only) session: respondent attempted intimidation despite separate-rooms protocol. Mediator terminated session for safety.'],
                ['date' => '2026-04-23', 'type' => 'Assessment', 'by' => 'Adv. R. Khan', 'note' => 'Mediation deemed unsuitable. Pathway escalated to litigation. Protection order petition prepared.'],
                ['date' => '2026-04-27', 'type' => 'Court', 'by' => 'Adv. R. Khan', 'note' => 'Protection order petition filed under DV Act. Interim custody motion filed simultaneously.'],
            ],
            'CL-02445' => [
                ['date' => '2026-04-03', 'type' => 'Intake', 'by' => 'K. Leghari', 'note' => 'Client registered. Land record from revenue department obtained.'],
                ['date' => '2026-04-08', 'type' => 'Assessment', 'by' => 'Adv. M. Soomro', 'note' => 'Mediation pathway opened; surveyor commissioned for joint mapping.'],
                ['date' => '2026-04-14', 'type' => 'Mediation', 'by' => 'Adv. M. Soomro', 'note' => 'First session: respondent attended but rejected surveyor findings.'],
                ['date' => '2026-04-20', 'type' => 'Mediation', 'by' => 'Adv. M. Soomro', 'note' => 'Second session: respondent failed to appear despite notice. Mediation declared unsuccessful.'],
                ['date' => '2026-04-26', 'type' => 'Court', 'by' => 'Adv. M. Soomro', 'note' => 'Civil suit for declaration of title and permanent injunction filed in Civil Court, Dadu.'],
            ],
            'CL-02463' => [
                ['date' => '2026-04-17', 'type' => 'Intake', 'by' => 'N. Memon', 'note' => 'Client registered. Children\'s welfare paramount; child protection screening positive (paternal household instability).'],
                ['date' => '2026-04-20', 'type' => 'Assessment', 'by' => 'Adv. F. Hussain', 'note' => 'Pathway: direct litigation. Mediation not advised given custody complexity.'],
                ['date' => '2026-04-23', 'type' => 'Documentation', 'by' => 'T. Panhwar (Paralegal)', 'note' => 'Children\'s B-Forms, school records and welfare statement compiled.'],
                ['date' => '2026-04-27', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Guardianship and custody petition filed in Family Court, Sanghar. Interim contact arrangement requested.'],
            ],
            'CL-02452' => [
                ['date' => '2026-03-01', 'type' => 'Intake', 'by' => 'F. Khaskheli', 'note' => 'Client registered following FIR. Bail status reviewed; client on interim bail.'],
                ['date' => '2026-03-08', 'type' => 'Assessment', 'by' => 'Adv. R. Khan', 'note' => 'Strong defence: alibi witnesses available, motive of complainant questionable.'],
                ['date' => '2026-03-16', 'type' => 'Court', 'by' => 'Adv. R. Khan', 'note' => 'Bail confirmation hearing — interim bail confirmed.'],
                ['date' => '2026-03-24', 'type' => 'Court', 'by' => 'Adv. R. Khan', 'note' => 'Charge framing. Defence application for discharge dismissed.'],
                ['date' => '2026-04-01', 'type' => 'Court', 'by' => 'Adv. R. Khan', 'note' => 'Prosecution evidence — complainant examined.'],
                ['date' => '2026-04-09', 'type' => 'Court', 'by' => 'Adv. R. Khan', 'note' => 'Cross-examination of complainant. Inconsistencies in testimony brought on record.'],
                ['date' => '2026-04-17', 'type' => 'Court', 'by' => 'Adv. R. Khan', 'note' => 'Continuation of cross-examination. Three witnesses for prosecution examined.'],
                ['date' => '2026-04-25', 'type' => 'Court', 'by' => 'Adv. R. Khan', 'note' => 'Defence motion to recall complainant for further cross-examination granted.'],
            ],
            'CL-02447' => [
                ['date' => '2026-04-02', 'type' => 'Intake', 'by' => 'H. Soomro', 'note' => 'Referral from Juvenile Probation Officer. Child protection flag. Family contact established.'],
                ['date' => '2026-04-08', 'type' => 'Assessment', 'by' => 'Adv. P. Kumar', 'note' => 'Juvenile Justice System Act 2018 applies. Pre-sentence diversion strongly indicated.'],
                ['date' => '2026-04-14', 'type' => 'Court', 'by' => 'Adv. P. Kumar', 'note' => 'Production before Juvenile Court. Bail granted on personal recognisance.'],
                ['date' => '2026-04-20', 'type' => 'Court', 'by' => 'Adv. P. Kumar', 'note' => 'Pre-sentence inquiry ordered. Probation officer report directed.'],
                ['date' => '2026-04-26', 'type' => 'Court', 'by' => 'Adv. P. Kumar', 'note' => 'Probation report received favourable; diversion application moved.'],
            ],
            'CL-02443' => [
                ['date' => '2026-03-10', 'type' => 'Intake', 'by' => 'F. Channa', 'note' => 'Referral from Darul Aman. Active GBV flag. Medical-legal report obtained.'],
                ['date' => '2026-03-16', 'type' => 'Assessment', 'by' => 'Adv. N. Jatoi', 'note' => 'Strong evidentiary base. Litigation pathway with criminal + civil parallel tracks.'],
                ['date' => '2026-03-23', 'type' => 'Court', 'by' => 'Adv. N. Jatoi', 'note' => 'Protection order petition filed. Interim ex-parte order granted.'],
                ['date' => '2026-03-29', 'type' => 'Court', 'by' => 'Adv. N. Jatoi', 'note' => 'Criminal complaint under Sindh DV Act registered. Charge sheet filed.'],
                ['date' => '2026-04-05', 'type' => 'Court', 'by' => 'Adv. N. Jatoi', 'note' => 'Charge framing. Prosecution evidence commenced — survivor examined-in-chief.'],
                ['date' => '2026-04-12', 'type' => 'Court', 'by' => 'Adv. N. Jatoi', 'note' => 'Cross-examination of survivor. Medical officer examined.'],
                ['date' => '2026-04-18', 'type' => 'Court', 'by' => 'Adv. N. Jatoi', 'note' => 'Investigating officer examined. Defence cross-examination of MO.'],
                ['date' => '2026-04-25', 'type' => 'Referral', 'by' => 'Adv. N. Jatoi', 'note' => 'Continued shelter referral coordinated with Darul Aman; counselling arranged.'],
            ],
            'CL-02436' => [
                ['date' => '2026-01-19', 'type' => 'Intake', 'by' => 'N. Memon', 'note' => 'Client registered. Death certificate of father (2014) and revenue records collected.'],
                ['date' => '2026-01-29', 'type' => 'Assessment', 'by' => 'Adv. F. Hussain', 'note' => 'Strong case: documented Sharia share denial. Direct litigation as ADR previously refused by brothers.'],
                ['date' => '2026-02-08', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Suit for partition filed. Plaint admitted; summons issued.'],
                ['date' => '2026-02-19', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Written statement filed by defendants. Issues framed.'],
                ['date' => '2026-03-01', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Plaintiff evidence — examination-in-chief.'],
                ['date' => '2026-03-12', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Cross-examination of plaintiff. Key documents tendered.'],
                ['date' => '2026-03-22', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Defendants\' evidence. Boundary report contested.'],
                ['date' => '2026-04-02', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Local Commissioner appointed for spot verification.'],
                ['date' => '2026-04-12', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Local Commissioner report received and accepted.'],
                ['date' => '2026-04-23', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Final arguments. Judgment reserved.'],
            ],
            'CL-02432' => [
                ['date' => '2026-01-12', 'type' => 'Intake', 'by' => 'N. Memon', 'note' => 'Client registered. Lease agreement and rent receipts/notices compiled.'],
                ['date' => '2026-01-24', 'type' => 'Assessment', 'by' => 'Adv. F. Hussain', 'note' => 'Direct litigation. Notice to quit duly served; precondition satisfied.'],
                ['date' => '2026-02-06', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Ejectment suit filed under Sindh Rented Premises Ordinance.'],
                ['date' => '2026-02-18', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Tender of rent disputed; tenant filed application for adjustment.'],
                ['date' => '2026-03-03', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Issues framed. Plaintiff evidence commenced.'],
                ['date' => '2026-03-15', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Tenant cross-examined plaintiff.'],
                ['date' => '2026-03-28', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Tenant\'s evidence. Plaintiff cross-examination.'],
                ['date' => '2026-04-10', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Final arguments concluded.'],
                ['date' => '2026-04-22', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Judgment reserved by Senior Civil Judge.'],
            ],
            'CL-02418' => [
                ['date' => '2025-12-02', 'type' => 'Intake', 'by' => 'F. Channa', 'note' => 'Client registered. Late husband\'s estate documentation compiled.'],
                ['date' => '2025-12-19', 'type' => 'Assessment', 'by' => 'Adv. N. Jatoi', 'note' => 'Direct litigation indicated; no scope for amicable settlement given history.'],
                ['date' => '2026-01-05', 'type' => 'Court', 'by' => 'Adv. N. Jatoi', 'note' => 'Suit for partition filed. Defendants put on notice.'],
                ['date' => '2026-01-23', 'type' => 'Court', 'by' => 'Adv. N. Jatoi', 'note' => 'Local Commission appointed; spot verification completed.'],
                ['date' => '2026-02-09', 'type' => 'Court', 'by' => 'Adv. N. Jatoi', 'note' => 'Plaintiff and defendant evidence concluded.'],
                ['date' => '2026-02-27', 'type' => 'Court', 'by' => 'Adv. N. Jatoi', 'note' => 'Final arguments. Decree of partition pronounced — share allocated.'],
                ['date' => '2026-03-16', 'type' => 'Court', 'by' => 'Adv. N. Jatoi', 'note' => 'Decree executed; mutation of revenue records ordered.'],
                ['date' => '2026-04-03', 'type' => 'Court', 'by' => 'Adv. N. Jatoi', 'note' => 'Mutation completed. Share registered in client\'s name.'],
                ['date' => '2026-04-20', 'type' => 'Documentation', 'by' => 'T. Panhwar (Paralegal)', 'note' => 'Updated Form VII collected and handed to client. File closed.'],
            ],
            'CL-02414' => [
                ['date' => '2025-12-01', 'type' => 'Intake', 'by' => 'K. Leghari', 'note' => 'Client registered post-FIR. Bail position reviewed.'],
                ['date' => '2025-12-24', 'type' => 'Assessment', 'by' => 'Adv. M. Soomro', 'note' => 'Strong defence; prosecution evidence weak. Litigation pathway opened.'],
                ['date' => '2026-01-16', 'type' => 'Court', 'by' => 'Adv. M. Soomro', 'note' => 'Bail granted. Charges framed.'],
                ['date' => '2026-02-09', 'type' => 'Court', 'by' => 'Adv. M. Soomro', 'note' => 'Prosecution witnesses examined; cross-examination yielded contradictions.'],
                ['date' => '2026-03-04', 'type' => 'Court', 'by' => 'Adv. M. Soomro', 'note' => 'Defence evidence led; alibi witnesses examined.'],
                ['date' => '2026-03-28', 'type' => 'Court', 'by' => 'Adv. M. Soomro', 'note' => 'Final arguments concluded.'],
                ['date' => '2026-04-20', 'type' => 'Court', 'by' => 'Adv. M. Soomro', 'note' => 'Judgment delivered: client acquitted on benefit of doubt.'],
            ],
            'CL-02411' => [
                ['date' => '2026-01-10', 'type' => 'Intake', 'by' => 'F. Khaskheli', 'note' => 'Client registered through shelter referral. GBV pattern documented.'],
                ['date' => '2026-01-27', 'type' => 'Assessment', 'by' => 'Adv. R. Khan', 'note' => 'Direct litigation. Maintenance + custody combined petition prepared.'],
                ['date' => '2026-02-13', 'type' => 'Court', 'by' => 'Adv. R. Khan', 'note' => 'Maintenance suit filed under Family Courts Act.'],
                ['date' => '2026-03-02', 'type' => 'Court', 'by' => 'Adv. R. Khan', 'note' => 'Interim maintenance application — Rs. 14,000 granted pending trial.'],
                ['date' => '2026-03-19', 'type' => 'Court', 'by' => 'Adv. R. Khan', 'note' => 'Plaintiff evidence; income documentation of respondent disputed.'],
                ['date' => '2026-04-05', 'type' => 'Court', 'by' => 'Adv. R. Khan', 'note' => 'Respondent\'s income examined via summoned bank statements.'],
                ['date' => '2026-04-22', 'type' => 'Court', 'by' => 'Adv. R. Khan', 'note' => 'Final judgment: maintenance fixed at Rs. 22,000/month + arrears of Rs. 168,000.'],
            ],
            'CL-02408' => [
                ['date' => '2025-12-29', 'type' => 'Intake', 'by' => 'N. Memon', 'note' => 'Client registered. Joint property dispute with cousins.'],
                ['date' => '2026-01-17', 'type' => 'Assessment', 'by' => 'Adv. F. Hussain', 'note' => 'ADR previously failed. Litigation initiated; settlement window kept open.'],
                ['date' => '2026-02-05', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Suit filed. Defendants entered appearance.'],
                ['date' => '2026-02-24', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Issues framed. Plaintiff evidence commenced.'],
                ['date' => '2026-03-15', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Court-mediated settlement discussions initiated mid-trial.'],
                ['date' => '2026-04-03', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Compromise terms agreed: 60/40 split with cash equalisation.'],
                ['date' => '2026-04-22', 'type' => 'Court', 'by' => 'Adv. F. Hussain', 'note' => 'Consent decree pronounced. Mutation directed.'],
            ],
            'CL-02402' => [
                ['date' => '2025-10-30', 'type' => 'Intake', 'by' => 'K. Leghari', 'note' => 'Client registered. Family land claim investigated.'],
                ['date' => '2025-11-20', 'type' => 'Assessment', 'by' => 'Adv. P. Kumar', 'note' => 'Documentary evidence weak; client insisted on filing despite advice. Pathway opened with caveats.'],
                ['date' => '2025-12-11', 'type' => 'Court', 'by' => 'Adv. P. Kumar', 'note' => 'Suit filed. Plaint admitted; summons issued.'],
                ['date' => '2026-01-02', 'type' => 'Court', 'by' => 'Adv. P. Kumar', 'note' => 'Defendants challenged maintainability; rejected.'],
                ['date' => '2026-01-23', 'type' => 'Court', 'by' => 'Adv. P. Kumar', 'note' => 'Plaintiff evidence; documents tendered did not support claim conclusively.'],
                ['date' => '2026-02-13', 'type' => 'Court', 'by' => 'Adv. P. Kumar', 'note' => 'Cross-examination weakened plaintiff case.'],
                ['date' => '2026-03-07', 'type' => 'Court', 'by' => 'Adv. P. Kumar', 'note' => 'Final arguments. Suit dismissed for failure to prove title.'],
                ['date' => '2026-03-28', 'type' => 'Court', 'by' => 'Adv. P. Kumar', 'note' => 'Client counselled on appeal options under CPC.'],
                ['date' => '2026-04-19', 'type' => 'Documentation', 'by' => 'T. Panhwar (Paralegal)', 'note' => 'Client elected not to appeal given costs. File closed.'],
            ],
            // --- Historical archived cases ---
            'CL-02391' => [
                ['date' => '2025-09-12', 'type' => 'Intake', 'by' => 'F. Channa', 'note' => 'Client registered. Loss certificate from local police station produced.'],
                ['date' => '2025-09-15', 'type' => 'Documentation', 'by' => 'T. Panhwar (Paralegal)', 'note' => 'NADRA Form-A completed; biometric appointment scheduled at NRC Sukkur.'],
                ['date' => '2025-09-22', 'type' => 'Documentation', 'by' => 'T. Panhwar (Paralegal)', 'note' => 'CNIC reissued; client received card. BISP linkage initiated.'],
                ['date' => '2025-09-26', 'type' => 'Documentation', 'by' => 'T. Panhwar (Paralegal)', 'note' => 'BISP enrolment restored. File closed.'],
            ],
            'CL-02398' => [
                ['date' => '2025-10-04', 'type' => 'Intake', 'by' => 'Z. Ali', 'note' => 'Client registered. Spousal separation; maintenance gap.'],
                ['date' => '2025-10-06', 'type' => 'Assessment', 'by' => 'Adv. R. Khan', 'note' => 'Mediation pathway. Respondent willing to attend.'],
                ['date' => '2025-10-15', 'type' => 'Mediation', 'by' => 'Adv. R. Khan', 'note' => 'First session: maintenance and arrears discussed; respondent contested figure.'],
                ['date' => '2025-10-25', 'type' => 'Mediation', 'by' => 'Adv. R. Khan', 'note' => 'Second session: financial disclosure. Compromise reached on principal amount.'],
                ['date' => '2025-11-05', 'type' => 'Mediation', 'by' => 'Adv. R. Khan', 'note' => 'Third session: agreement signed — Rs. 16,000/month + school fees.'],
                ['date' => '2025-11-08', 'type' => 'Documentation', 'by' => 'T. Panhwar (Paralegal)', 'note' => 'Settlement deed registered. File closed.'],
            ],
            'CL-02422' => [
                ['date' => '2025-12-08', 'type' => 'Intake', 'by' => 'N. Memon', 'note' => 'Client registered. Death certificate, nikahnama, and Form VII collected.'],
                ['date' => '2025-12-10', 'type' => 'Assessment', 'by' => 'Adv. F. Hussain', 'note' => 'Sharia inheritance share computed; mediation viable.'],
                ['date' => '2025-12-22', 'type' => 'Mediation', 'by' => 'Adv. F. Hussain', 'note' => 'First session: family disclosure. Joint holdings mapped.'],
                ['date' => '2026-01-08', 'type' => 'Mediation', 'by' => 'Adv. F. Hussain', 'note' => 'Second session: cash equivalent proposed in lieu of partition.'],
                ['date' => '2026-01-18', 'type' => 'Mediation', 'by' => 'Adv. F. Hussain', 'note' => 'Third session: terms agreed — Rs. 425,000 lump sum.'],
                ['date' => '2026-01-22', 'type' => 'Documentation', 'by' => 'T. Panhwar (Paralegal)', 'note' => 'Settlement deed executed; payment confirmed. File closed.'],
            ],
            'CL-02425' => [
                ['date' => '2025-12-19', 'type' => 'Intake', 'by' => 'H. Soomro', 'note' => 'Client registered. Wage register and attendance proofs compiled.'],
                ['date' => '2025-12-22', 'type' => 'Assessment', 'by' => 'Adv. P. Kumar', 'note' => 'Strong documentary case. Mediation initiated.'],
                ['date' => '2026-01-12', 'type' => 'Mediation', 'by' => 'Adv. P. Kumar', 'note' => 'First session: contractor disputed half the amount.'],
                ['date' => '2026-02-05', 'type' => 'Mediation', 'by' => 'Adv. P. Kumar', 'note' => 'Second session: payment register reviewed. Full amount conceded.'],
                ['date' => '2026-02-14', 'type' => 'Documentation', 'by' => 'T. Panhwar (Paralegal)', 'note' => 'Payment received in client account. File closed.'],
            ],
        ];

        foreach ($caseServices as $caseUid => $services) {
            $case = CaseRecord::where('case_uid', $caseUid)->first();

            if (! $case) {
                $this->command->warn("Case {$caseUid} not found — skipping its service encounters.");
                continue;
            }

            foreach ($services as $service) {
                ServiceEncounter::firstOrCreate(
                    [
                        'case_id' => $case->id,
                        'date'    => $service['date'],
                        'type'    => $service['type'],
                    ],
                    [
                        'performed_by' => $service['by'],
                        'note'         => $service['note'],
                    ]
                );
            }
        }
    }
}
