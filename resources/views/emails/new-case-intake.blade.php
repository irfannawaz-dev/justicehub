<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Case Registered</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f4f6f8; font-family: Arial, sans-serif; font-size: 14px; color: #1a202c; }
        .wrapper { max-width: 620px; margin: 30px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background-color: #1e3a5f; padding: 28px 32px; }
        .header h1 { margin: 0; color: #ffffff; font-size: 20px; font-weight: 700; letter-spacing: 0.3px; }
        .header p { margin: 6px 0 0; color: #a8c0d6; font-size: 13px; }
        .uid-badge { display: inline-block; background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; border-radius: 4px; padding: 4px 12px; font-size: 13px; font-weight: 700; margin: 20px 32px 0; }
        .body { padding: 20px 32px 32px; }
        .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; margin: 24px 0 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; }
        table.fields { width: 100%; border-collapse: collapse; }
        table.fields td { padding: 7px 0; vertical-align: top; }
        table.fields td.label { width: 42%; color: #64748b; font-size: 13px; }
        table.fields td.value { color: #1a202c; font-size: 13px; font-weight: 500; }
        .flag { display: inline-block; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; border-radius: 4px; padding: 2px 8px; font-size: 11px; font-weight: 600; margin-right: 4px; margin-bottom: 4px; }
        .flag.none { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
        .urgency-high { color: #dc2626; font-weight: 700; }
        .urgency-medium { color: #d97706; font-weight: 700; }
        .urgency-low { color: #16a34a; font-weight: 700; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 32px; font-size: 12px; color: #94a3b8; }
        .footer a { color: #0369a1; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        <h1>Justice Hub CMS</h1>
        <p>New Case Registration Notification</p>
    </div>

    <div class="uid-badge">{{ $case->case_uid }}</div>

    <div class="body">

        <p style="margin-top:16px; color:#334155;">
            A new case has been registered in the Justice Hub CMS. Please review the details below.
        </p>

        {{-- Client Information --}}
        <div class="section-title">Client Information</div>
        <table class="fields">
            <tr>
                <td class="label">Full Name</td>
                <td class="value">{{ $case->name }}</td>
            </tr>
            <tr>
                <td class="label">Father / Husband Name</td>
                <td class="value">{{ $case->father_husband_name ?: '—' }}</td>
            </tr>
            <tr>
                <td class="label">Gender</td>
                <td class="value">{{ $case->gender ?: '—' }}</td>
            </tr>
            <tr>
                <td class="label">Age</td>
                <td class="value">{{ $case->age ?: '—' }}</td>
            </tr>
            <tr>
                <td class="label">CNIC</td>
                <td class="value">{{ $case->cnic ?: 'Not provided' }}</td>
            </tr>
            <tr>
                <td class="label">Contact</td>
                <td class="value">{{ $case->primary_contact ?: '—' }}</td>
            </tr>
            <tr>
                <td class="label">District / Tehsil</td>
                <td class="value">{{ $case->district }}{{ $case->tehsil ? ' · ' . $case->tehsil : '' }}</td>
            </tr>
            <tr>
                <td class="label">Consent Given</td>
                <td class="value">{{ $case->consent ? 'Yes' : 'No' }}{{ !$case->consent && $case->no_consent_reason ? ' (' . $case->no_consent_reason . ')' : '' }}</td>
            </tr>
        </table>

        {{-- Case Details --}}
        <div class="section-title">Case Details</div>
        <table class="fields">
            <tr>
                <td class="label">Case UID</td>
                <td class="value">{{ $case->case_uid }}</td>
            </tr>
            <tr>
                <td class="label">Case Ref</td>
                <td class="value">{{ $case->case_ref }}</td>
            </tr>
            <tr>
                <td class="label">Justice Hub</td>
                <td class="value">{{ $case->hub?->name ?? $case->hub_id }}</td>
            </tr>
            <tr>
                <td class="label">Intake Date</td>
                <td class="value">{{ \Carbon\Carbon::parse($case->intake_date)->format('d M Y') }}</td>
            </tr>
            <tr>
                <td class="label">Primary Issue</td>
                <td class="value">{{ $case->primary_issue ?: '—' }}</td>
            </tr>
            @if($case->issue_description)
            <tr>
                <td class="label">Issue Description</td>
                <td class="value" style="white-space:pre-wrap;">{{ $case->issue_description }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Urgency Level</td>
                <td class="value">
                    @php $u = is_object($case->urgency) ? $case->urgency->value : $case->urgency; @endphp
                    <span class="{{ $u === 'High' || $u === 'Critical' ? 'urgency-high' : ($u === 'Medium' ? 'urgency-medium' : 'urgency-low') }}">{{ $u }}</span>
                </td>
            </tr>
            <tr>
                <td class="label">Assigned Pathway</td>
                <td class="value">{{ $case->assigned_pathway }}</td>
            </tr>
            @if($case->pathway_specific)
            <tr>
                <td class="label">Pathway Specific</td>
                <td class="value">{{ $case->pathway_specific }}</td>
            </tr>
            @endif
        </table>

        {{-- Safeguarding Flags --}}
        <div class="section-title">Safeguarding Flags</div>
        @php
            $flags = [];
            if ($case->is_gbv)        $flags[] = 'GBV';
            if ($case->is_child)      $flags[] = 'Child Protection';
            if ($case->is_minority)   $flags[] = 'Minority';
            if ($case->is_disability) $flags[] = 'Disability';
            if ($case->is_underserved) $flags[] = 'Underserved';
        @endphp
        @if(count($flags))
            @foreach($flags as $flag)
                <span class="flag">{{ $flag }}</span>
            @endforeach
        @else
            <span class="flag none">None identified</span>
        @endif

        {{-- Staff Assignment --}}
        <div class="section-title">Staff Assignment</div>
        <table class="fields">
            <tr>
                <td class="label">Received By</td>
                <td class="value">{{ $case->staff_receiving }}{{ $case->staff_designation ? ' · ' . $case->staff_designation : '' }}</td>
            </tr>
            <tr>
                <td class="label">Assigned To</td>
                <td class="value">{{ $case->assigned_to ?: '—' }}</td>
            </tr>
            <tr>
                <td class="label">Referral Source</td>
                <td class="value">{{ $case->referral_source ?: '—' }}</td>
            </tr>
        </table>

    </div>

    {{-- Footer --}}
    <div class="footer">
        This is an automated notification from Justice Hub CMS.
        Please do not reply to this email.
        Sent at {{ now()->format('d M Y, H:i') }} PKT.
    </div>

</div>
</body>
</html>
