#!/usr/bin/env python3
"""
Reads SBA and Sanghar tabs from Data Validation Sheet.xlsx
and outputs a JSON array of case records to stdout.
Usage: python extract_cases.py <path_to_xlsx>
"""
import sys, json, io
from datetime import datetime
import openpyxl

# Force UTF-8 output on Windows
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

XLSX_PATH = sys.argv[1] if len(sys.argv) > 1 else r'C:\xampp\htdocs\JusticeHub\Data Validation Sheet.xlsx'

HUB_MAP = {
    'SBA': 'JH-SBA-01',
    'Sanghar': 'JH-SAN-01',
}

PATHWAY_MAP = {
    'SLACC/ Lawyer - Legal Advice':   'Legal Advice / Consultation',
    'Lawyer - Court Representation':  'Court Representation',
    'Lawyer - Civil Documentation':   'Legal Advice / Consultation',
    'Accredited Mediator':            'Mediation',
    'Civil Society':                  'Civil Society / NGO / CSO / NPO',
    'Information & Awareness':        'Information & Awareness',
    'NADRA':                          'Government Department / Public Institution',
    'Other Govt Dept':                'Government Department / Public Institution',
    'Police':                         'Government Department / Public Institution',
    'Social Protection':              'Government Department / Public Institution',
}

def clean_str(v):
    if v is None: return None
    s = str(v).strip()
    return s if s else None

def clean_int(v):
    if v is None: return None
    try: return int(float(str(v)))
    except: return None

def clean_phone(v):
    if v is None: return None
    try:
        s = str(int(float(str(v))))
        return s[:15]
    except:
        s = str(v).strip()
        return s[:15] if s else None

def clean_cnic(v):
    if v is None: return None
    try:
        s = str(int(float(str(v))))
        return s if len(s) >= 10 else None
    except:
        s = str(v).strip().replace('-','')
        return s if s else None

wb = openpyxl.load_workbook(XLSX_PATH, read_only=True, data_only=True)
records = []

for sheet_name in ['SBA', 'Sanghar']:
    ws = wb[sheet_name]
    rows = list(ws.iter_rows(values_only=True))
    hub_id = HUB_MAP.get(sheet_name, sheet_name)

    for row in rows[1:]:   # skip header
        if not row[0]:     # skip empty rows
            continue

        # Parse timestamp
        ts = row[1]
        intake_date = None
        intake_time = None
        if isinstance(ts, datetime):
            intake_date = ts.strftime('%Y-%m-%d')
            intake_time = ts.strftime('%H:%M:%S')
        elif isinstance(ts, str) and ts.strip():
            try:
                dt = datetime.strptime(ts.strip().split(' ')[0], '%d/%m/%Y')
                intake_date = dt.strftime('%Y-%m-%d')
            except: pass

        # Consent
        consent_raw = clean_str(row[6]) or ''
        consent = 1 if 'yes' in consent_raw.lower() else 0

        # Returning client
        repeat_raw = clean_str(row[3]) or ''
        returning = 1 if repeat_raw.lower() == 'yes' else 0

        # Pathway
        referred_to = clean_str(row[33]) or ''
        assigned_pathway = PATHWAY_MAP.get(referred_to, referred_to or 'Information & Awareness')

        # Specific pathway (col 35 = SLACC/Lawyer)
        pathway_specific = clean_str(row[35])

        # Govt dept (col 34 = detailed referral notes)
        pathway_notes = clean_str(row[34])

        # Pathway specific for govt
        pathway_govt_dept = None
        if assigned_pathway == 'Government Department / Public Institution':
            pathway_govt_dept = clean_str(row[34]) or referred_to

        # NGO name
        pathway_ngo_name = clean_str(row[10])

        rec = {
            'case_uid':             clean_str(row[0]),
            'hub_id':               hub_id,
            'intake_date':          intake_date,
            'intake_time':          intake_time,
            'returning_client':     returning,
            'staff_receiving':      clean_str(row[4]),
            'staff_designation':    clean_str(row[5]),
            'consent':              consent,
            'no_consent_reason':    clean_str(row[7]),
            'referral_source':      clean_str(row[8]),
            'referral_contact_person': clean_str(row[9]),
            'name':                 clean_str(row[12]),
            'father_husband_name':  clean_str(row[13]),
            'gender':               clean_str(row[14]),
            'age':                  clean_int(row[15]),
            'cnic':                 clean_cnic(row[16]),
            'marital_status':       clean_str(row[17]),
            'religion':             clean_str(row[18]),
            'education_level':      clean_str(row[19]),
            'occupation':           clean_str(row[20]),
            'income_bracket':       clean_str(row[21]),
            'disability_status':    clean_str(row[22]),
            'primary_contact':      clean_phone(row[23]),
            'alternative_contact':  clean_phone(row[24]),
            'full_address':         clean_str(row[25]),
            'union_council':        clean_str(row[26]),
            'tehsil':               clean_str(row[27]),
            'district':             clean_str(row[28]),
            'language':             clean_str(row[29]),
            'issue_description':    clean_str(row[30]),
            'primary_issue':        clean_str(row[31]),
            'urgency':              clean_str(row[32]),
            'assigned_pathway':     assigned_pathway,
            'pathway_specific':     pathway_specific,
            'pathway_govt_dept':    pathway_govt_dept,
            'pathway_ngo_name':     pathway_ngo_name,
            'pathway_manager':      pathway_notes,
            'assigned_to':          clean_str(row[37]),
            'status':               'Active',
            'risk':                 'Low',
            'source_sheet':         sheet_name,
        }
        records.append(rec)

wb.close()
print(json.dumps(records, ensure_ascii=False))
