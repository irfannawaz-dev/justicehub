<?php

/**
 * Justice Hub CRM — Master Lookup Configuration
 *
 * This file defines the DEFAULT values for all dropdown options, categories,
 * and statuses across the application. These are used to seed the `lookups`
 * database table on first run.
 *
 * At runtime, all dropdowns read from the `lookups` DB table (cached).
 * Admins can add/edit/disable options via Settings without touching code.
 *
 * Structure: 'group_key' => [ ['value' => ..., 'label' => ..., 'parent' => ..., 'meta' => ...], ... ]
 * - value:  stored in DB / used in code
 * - label:  display text (defaults to value if omitted)
 * - parent: for cascading dropdowns (e.g. pathway_specific depends on assigned_pathway)
 * - meta:   extra config (colors, SLA days, etc.)
 */

return [

    // ─────────────────────────────────────────────────────────────────────
    // CASE
    // ─────────────────────────────────────────────────────────────────────

    'case.primary_issue' => [
        'Civil Litigation',
        'Identity Documentation',
        'Family Law',
        'Criminal Litigation',
        'Public Grievances',
        'General',
        'Inheritance',
        'GBV / Family',
        'Documentation',
        'Juvenile Justice',
        'Labour',
        'Property',
        'Land / Tenancy',
        'Consumer',
        'Other',
    ],

    'case.mode' => [
        'Walk-in',
        'Referral',
        'Outreach',
        'Phone',
        'Digital',
    ],

    'case.source' => [
        'Self',
        'Community',
        'Police',
        'Shelter',
        'NGO',
        'Court',
        'Government',
        'Bar',
        'Family / Friend',
        'Paralegal',
        'Outreach',
        'Other',
    ],

    'case.status' => [
        ['value' => 'Active',           'meta' => ['color' => 'var(--forest)']],
        ['value' => 'Pending Approval',  'meta' => ['color' => 'var(--ochre)']],
        ['value' => 'Settlement',        'meta' => ['color' => 'var(--moss)']],
        ['value' => 'Closed',            'meta' => ['color' => 'var(--ink-3)']],
        ['value' => 'Rejected',          'meta' => ['color' => 'var(--burgundy)']],
    ],

    'case.disposition' => [
        ['value' => 'adr',          'label' => 'ADR'],
        ['value' => 'litigation',   'label' => 'Litigation'],
        ['value' => 'advice-only',  'label' => 'Advice Only'],
        ['value' => 'referred',     'label' => 'Referred'],
    ],

    'case.urgency' => [
        ['value' => 'Immediate', 'meta' => ['color' => 'var(--burgundy)', 'sla_hours' => 4]],
        ['value' => 'High',      'meta' => ['color' => 'var(--ochre)',    'sla_hours' => 24]],
        ['value' => 'Medium',    'meta' => ['color' => 'var(--forest)',   'sla_hours' => 72]],
        ['value' => 'Low',       'meta' => ['color' => 'var(--ink-3)',    'sla_hours' => 168]],
    ],

    'case.risk' => [
        ['value' => 'High',   'meta' => ['color' => 'var(--burgundy)']],
        ['value' => 'Medium', 'meta' => ['color' => 'var(--ochre)']],
        ['value' => 'Low',    'meta' => ['color' => 'var(--forest)']],
    ],

    'case.language' => [
        'Urdu',
        'English',
        'Sindhi',
        'Punjabi',
        'Saraiki',
        'Balochi',
        'Pashto',
        'Other',
    ],

    'case.pathway' => [
        'Legal Advice',
        'Mediation',
        'Litigation',
        'Documentation',
        'Referral',
        'Court Representation',
        'Mediation & ADR',
    ],

    // ─────────────────────────────────────────────────────────────────────
    // INTAKE FORM
    // ─────────────────────────────────────────────────────────────────────

    'intake.staff' => [
        'N. Memon',
        'S. Shah',
        'T. Panhwar',
        'K. Leghari',
        'A. Mahar',
        'H. Soomro',
        'F. Channa',
        'Z. Ali',
    ],

    'intake.staff_designation' => [
        'PL-001 - Paralegal',
        'PL-002 - Paralegal',
        'PL-003 - Paralegal',
        'PL-004 - Paralegal',
        'LO-001 - Legal Officer',
        'LO-002 - Legal Officer',
        'HC-001 - Hub Coordinator',
    ],

    'intake.referral_source' => [
        'Website / Social Media',
        'Google Search / Google Maps',
        'Radio / TV / Newspaper',
        'SMS / WhatsApp Message',
        'QR Code / Referral Card',
        'Walk-in / Passing by the Office',
        'Phone Call / Helpline',
        'Office Staff',
        'Court / Judicial Officer',
        'Bar Association',
        'Government Department',
        'NGO / CSO / NPO',
        'Shelter / Protection Service',
        'Paralegal',
        'Community Outreach / Awareness Session',
        'Community Leader / Local Representative',
        'District / Range Peace Committee',
        'Friend / Family / Word of Mouth',
        'Other - please specify',
    ],

    'intake.ngo_referral' => [
        'Human Rights Commission of Pakistan (HRCP)',
        'Aurat Foundation',
        "Shirkat Gah - Women's Resource Centre",
        'Strengthening Participatory Organization (SPO)',
        'Indus Resource Centre (IRC)',
        'PAIMAN Alumni Trust',
        'Civil Society Support Program (CSSP)',
        'Rozan',
        'Bedari',
        'Edhi Foundation',
        'Pakistan Red Crescent Society',
        'Sindh Rural Support Organization (SRSO)',
        'National Rural Support Programme (NRSP - Sindh)',
        'Pakistan Fisherfolk Forum (PFF)',
        'Society for the Protection of the Rights of the Child (SPARC)',
        'Hands Pakistan',
        'Pakistan Institute of Labour Education & Research (PILER)',
        'Research and Development Foundation (RDF)',
        'NRSP',
        'TRDP',
        'Orangi Pilot Project',
        'SAFWCO',
        'Chambers of Commerce',
        'FRDP',
        'World Wide Vision Network',
        'Press Club',
        'Other',
    ],

    'intake.govt_referral' => [
        'Deputy Commissioner Office',
        'Commissioner Office',
        'Assistant Commissioner Office',
        'Mukhtiarkar / Tapedar / Revenue Office',
        'Local Government / Union Council',
        'District Council / Municipal Committee',
        'District Legal Empowerment Committee',
        'District Bar / Legal Aid Committee',
        'Court / Judicial Office',
        'District Public Prosecutor Office',
        'District Attorney Office',
        'SSP Office - HRC / MFD',
        'DIG Office - WPC',
        'Police Station',
        'Women Police Station',
        'OSPC / Women Protection Cell',
        'Police Facilitation Centre',
        'Prison Department',
        'Probation & Parole Department',
        'NADRA',
        'Union Council Birth / Marriage / Death Registration Office',
        'Social Welfare Department',
        'Women Development Department',
        'Darul Aman',
        'Child Protection Authority / Child Protection Unit',
        'Child Helpline 1121',
        'Department of Empowerment of Persons with Disabilities',
        'Minority Affairs Department',
        'Human Rights Department',
        'Sindh Human Rights Commission',
        'Sindh Commission on the Status of Women',
        'Zakat & Ushr Department',
        'Bait-ul-Mal',
        'BISP / Social Protection Office',
        'Sindh Social Protection Authority',
        'Health Department',
        'Public Hospital / MLO Office',
        'Sindh Health Care Commission',
        'Labour & Human Resources Department',
        'Labour Court / Labour Office',
        'Sindh Employees Social Security Institution',
        'Employees Old-Age Benefits Institution',
        'Education Department',
        'School Education & Literacy Department',
        'Population Welfare Department',
        'Rehabilitation Department',
        'Provincial Disaster Management Authority',
        'Provincial Ombudsman / Mohtasib',
        'Federal Ombudsman / Mohtasib',
        'Ombudsman for Protection against Harassment of Women at Workplace',
        'Consumer Protection Department / Consumer Court',
        'Excise, Taxation & Narcotics Control Department',
        'Other - please specify',
    ],

    'intake.assigned_pathway' => [
        'Legal Advice / Consultation',
        'Court Representation',
        'Mediation',
        'ADR / Dispute Resolution Support',
        'Government Department / Public Institution',
        'Civil Society / NGO / CSO / NPO',
        'Other',
    ],

    // Cascading dropdowns: parent_value maps to the assigned_pathway value
    'intake.pathway_specific' => [
        ['value' => 'SLACC',                              'parent' => 'Legal Advice / Consultation'],
        ['value' => 'Justice Hub Lawyer',                  'parent' => 'Legal Advice / Consultation'],
        ['value' => 'NAZ Assist',                          'parent' => 'Legal Advice / Consultation'],
        ['value' => 'Other',                               'parent' => 'Legal Advice / Consultation'],
        ['value' => 'Justice Hub Lawyer',                  'parent' => 'Court Representation'],
        ['value' => 'Other',                               'parent' => 'Court Representation'],
        ['value' => 'Justice Hub Accredited Mediator',     'parent' => 'Mediation'],
        ['value' => 'MICADR',                              'parent' => 'Mediation'],
        ['value' => 'Other',                               'parent' => 'Mediation'],
        ['value' => 'Provincial Ombudsman',                'parent' => 'ADR / Dispute Resolution Support'],
        ['value' => 'Federal Ombudsman',                   'parent' => 'ADR / Dispute Resolution Support'],
        ['value' => 'Other',                               'parent' => 'ADR / Dispute Resolution Support'],
    ],

    'intake.pathway_govt' => [
        'NADRA',
        'Police',
        'Prison Department',
        'Committee for the Welfare of Prisoners',
        'District Legal Empowerment Committee',
        'District Administration / Revenue Office',
        'Local Government / Union Council',
        'Social Welfare Department',
        'Women Development Department',
        'Darul Aman / Government Shelter',
        'Health Department / Public Hospital / MLO Office',
        'Child Protection Authority',
        'Department of Empowerment of Persons with Disabilities',
        'Minority Affairs / Human Rights Department',
        'Zakat / Bait-ul-Mal / BISP',
        'Labour Department / SESSI / EOBI',
        'Federal Ombudsman / Mohtasib',
        'Provincial Ombudsman / Mohtasib',
        'Other Government Department',
    ],

    'intake.gender' => [
        'Male',
        'Female',
        'Transgender',
        'Prefer not to say',
        'Other',
    ],

    'intake.marital_status' => [
        'Married',
        'Divorced',
        'Widowed',
        'Separated',
        'Single',
        'Other',
    ],

    'intake.religion' => [
        'Muslim',
        'Hindu',
        'Christian',
        'Sikh',
        'Prefer not to say',
        'Other',
    ],

    'intake.education_level' => [
        'No Education',
        'Primary',
        'Matric / O Level',
        'Intermediate / A Level',
        'Graduation',
        'Masters and Above',
        'Religious Education',
        'Other',
    ],

    'intake.income_bracket' => [
        'Less than 30,000',
        '30,000 - 60,000',
        '60,000 - 100,000',
        'Above 100,000',
    ],

    'intake.disability_status' => [
        'Yes',
        'No',
    ],

    'intake.district' => [
        'Hyderabad',
        'Sanghar',
        'Sukkur',
        'Shaheed Benazirabad',
        'Dadu',
        'Larkana',
        'Karachi',
    ],

    'intake.preferred_language' => [
        'Urdu',
        'English',
        'Sindhi',
        'Punjabi',
        'Saraiki',
        'Balochi',
        'Other',
    ],

    // ─────────────────────────────────────────────────────────────────────
    // SERVICE ENCOUNTERS
    // ─────────────────────────────────────────────────────────────────────

    'service.type' => [
        'Intake',
        'Assessment & Triage',
        'Free Legal Advice',
        'Mediation & ADR',
        'NADRA & Documentation',
        'Representation in Court',
        'Court Hearing',
        'Awaiting Judgment',
        'Court Decision / Verdict',
        'Counselling',
        'Referral to Partner',
        'Follow-up',
        'Case Closure',
    ],

    // ─────────────────────────────────────────────────────────────────────
    // COMPLAINTS
    // ─────────────────────────────────────────────────────────────────────

    'complaint.category' => [
        ['value' => 'staff-conduct',   'label' => 'Staff conduct'],
        ['value' => 'service-delay',   'label' => 'Service delay'],
        ['value' => 'service-quality', 'label' => 'Service quality'],
        ['value' => 'communication',   'label' => 'Communication'],
        ['value' => 'data-privacy',    'label' => 'Data privacy'],
        ['value' => 'discrimination',  'label' => 'Discrimination'],
        ['value' => 'safeguarding',    'label' => 'Safeguarding'],
        ['value' => 'coordination',    'label' => 'Coordination'],
        ['value' => 'other',           'label' => 'Other'],
    ],

    'complaint.severity' => [
        ['value' => 'critical', 'label' => 'Critical', 'meta' => ['color' => 'var(--burgundy)', 'tint' => 'var(--burgundy-tint)', 'sla_days' => 3]],
        ['value' => 'high',     'label' => 'High',     'meta' => ['color' => 'var(--ochre)',    'tint' => 'var(--ochre-tint)',     'sla_days' => 7]],
        ['value' => 'medium',   'label' => 'Medium',   'meta' => ['color' => 'var(--forest)',   'tint' => 'rgba(22,48,41,0.08)',   'sla_days' => 14]],
        ['value' => 'low',      'label' => 'Low',      'meta' => ['color' => 'var(--ink-2)',    'tint' => 'var(--parchment-2)',    'sla_days' => 30]],
    ],

    'complaint.status' => [
        ['value' => 'open',        'label' => 'Open'],
        ['value' => 'in-progress', 'label' => 'In Progress'],
        ['value' => 'resolved',    'label' => 'Resolved'],
        ['value' => 'escalated',   'label' => 'Escalated'],
    ],

    'complaint.channel' => [
        'in-person',
        'phone',
        'written',
        'paralegal',
    ],

    // ─────────────────────────────────────────────────────────────────────
    // EVIDENCE REGISTER
    // ─────────────────────────────────────────────────────────────────────

    'evidence.type' => [
        ['value' => 'recognition',        'label' => 'Recognition'],
        ['value' => 'integration',        'label' => 'Integration'],
        ['value' => 'replication',        'label' => 'Replication'],
        ['value' => 'policy-citation',    'label' => 'Policy Citation'],
        ['value' => 'analytical-product', 'label' => 'Analytical Product'],
    ],

    // ─────────────────────────────────────────────────────────────────────
    // DOCUMENTS
    // ─────────────────────────────────────────────────────────────────────

    'document.type' => [
        ['value' => 'id',              'label' => 'Identity Document'],
        ['value' => 'consent',         'label' => 'Consent Form'],
        ['value' => 'filing',          'label' => 'Court Filing'],
        ['value' => 'evidence',        'label' => 'Evidence'],
        ['value' => 'medical',         'label' => 'Medical Record'],
        ['value' => 'correspondence',  'label' => 'Correspondence'],
        ['value' => 'other',           'label' => 'Other'],
    ],

    'document.source' => [
        'uploaded',
        'received',
        'generated',
    ],

    'document.status' => [
        'draft',
        'signed',
        'submitted',
        'acknowledged',
        'archived',
    ],

    'document.confidentiality' => [
        'public',
        'restricted',
        'sensitive',
    ],

    // ─────────────────────────────────────────────────────────────────────
    // OUTREACH
    // ─────────────────────────────────────────────────────────────────────

    'outreach.type' => [
        'Legal Literacy',
        'Paralegal Outreach',
        'Awareness',
    ],

    // ─────────────────────────────────────────────────────────────────────
    // FEEDBACK
    // ─────────────────────────────────────────────────────────────────────

    'feedback.channel' => [
        'in-person',
        'sms',
        'phone',
    ],

    'feedback.service' => [
        'Free Legal Advice',
        'Mediation & ADR',
        'Court Representation',
        'NADRA & Documentation',
    ],

    'feedback.understood_rights' => [
        'yes',
        'partial',
        'no',
    ],

    'feedback.would_recommend' => [
        'yes',
        'maybe',
        'no',
    ],

    // ─────────────────────────────────────────────────────────────────────
    // PARTNERS
    // ─────────────────────────────────────────────────────────────────────

    'partner.category' => [
        'Shelter',
        'Government',
        'Law Enforcement',
        'Health',
        'NGO',
    ],

    'partner.mou_status' => [
        'active',
        'expiring',
        'expired',
    ],

    // ─────────────────────────────────────────────────────────────────────
    // STAFF & TRAINING
    // ─────────────────────────────────────────────────────────────────────

    'staff.role' => [
        'Lawyer',
        'Paralegal',
        'Hub Manager',
        'M&E',
        'Admin',
    ],

    'staff.status' => [
        'active',
        'inactive',
    ],

    'training.code' => [
        ['value' => 'SOP-CORE',  'label' => 'Justice Hub SOPs · core operations',       'meta' => ['category' => 'sops',            'mandatory' => true,  'refresh' => 'annual']],
        ['value' => 'SAFE-CHILD','label' => 'Child safeguarding & protection',           'meta' => ['category' => 'safeguarding',    'mandatory' => true,  'refresh' => 'annual']],
        ['value' => 'SAFE-GBV',  'label' => 'GBV-sensitive intake & referral',           'meta' => ['category' => 'safeguarding',    'mandatory' => true,  'refresh' => 'annual']],
        ['value' => 'DATA-PROT', 'label' => 'Data protection & client confidentiality',  'meta' => ['category' => 'data-protection', 'mandatory' => true,  'refresh' => 'biennial']],
        ['value' => 'ADR-MED',   'label' => 'Mediation skills & ADR practice',           'meta' => ['category' => 'legal-skills',    'mandatory' => false, 'refresh' => 'one-off']],
        ['value' => 'PARA-CORE', 'label' => 'Paralegal foundations',                     'meta' => ['category' => 'legal-skills',    'mandatory' => false, 'refresh' => 'one-off']],
        ['value' => 'JUV-JUST',  'label' => 'Juvenile justice procedures',               'meta' => ['category' => 'legal-skills',    'mandatory' => false, 'refresh' => 'biennial']],
        ['value' => 'INT-COMM',  'label' => 'Trauma-informed client communication',      'meta' => ['category' => 'safeguarding',    'mandatory' => false, 'refresh' => 'biennial']],
    ],

    'training.category' => [
        'sops',
        'safeguarding',
        'data-protection',
        'legal-skills',
    ],

    // ─────────────────────────────────────────────────────────────────────
    // INDICATORS
    // ─────────────────────────────────────────────────────────────────────

    'indicator.level' => [
        'Goal',
        'Outcome 1',
        'Outcome 2',
        'Outcome 3',
        'Output 1',
        'Output 2',
        'Output 3',
        'Output 4',
    ],

    'indicator.priority' => [
        'P0',
        'P1',
    ],

    'indicator.cadence' => [
        'Monthly',
        'Quarterly',
        'Annual',
    ],

    'indicator.unit' => [
        '%',
        'people',
        'cases',
        'days',
        'PKR',
        'score',
        'count',
    ],

    // ─────────────────────────────────────────────────────────────────────
    // IMPACT REPORTS
    // ─────────────────────────────────────────────────────────────────────

    'report.template' => [
        'Program Overview',
        'Annual Impact Report',
        'Donor Report',
        'Policy Brief',
        'Case Study Collection',
    ],

    'report.period' => [
        'Quarter',
        'Half-year',
        'Year',
        'Custom',
    ],

    'report.audience' => [
        'Donors & Partners',
        'Government',
        'Communities',
        'Internal',
    ],

    // ─────────────────────────────────────────────────────────────────────
    // LEARNING & VFM
    // ─────────────────────────────────────────────────────────────────────

    'case_study.replication_potential' => [
        'High',
        'Medium',
        'Low',
    ],

    // ─────────────────────────────────────────────────────────────────────
    // PULSE SURVEYS
    // ─────────────────────────────────────────────────────────────────────

    'pulse.will_apply' => [
        'yes',
        'no',
        'maybe',
    ],

    'pulse.age_band' => [
        '18-25',
        '26-35',
        '36-50',
        '50+',
    ],

];
