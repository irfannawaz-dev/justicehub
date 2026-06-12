<x-layouts.app>
<div style="padding: 24px 34px 64px; max-width: 1280px; margin: 0 auto;">

    {{-- Header --}}
    <div style="margin-bottom: 22px; padding-bottom: 18px; border-bottom: 1px solid var(--rule);">
        <div class="label-cap" style="font-size: 9.5px; margin-bottom: 6px;">Tool 1 - Client Intake & Registration</div>
        <h1 class="serif" style="font-size: 34px; font-weight: 400; letter-spacing: -0.018em; margin: 0; line-height: 1.05;">
            Justice Hub Intake Form
        </h1>
        <div style="font-size: 12.5px; color: var(--ink-3); margin-top: 8px; line-height: 1.5;">
            This form is used to collect information about your visit to the Justice Hub. Your responses help us provide legal and support services tailored to your needs. All information is kept confidential.
        </div>
        <div style="font-size: 11px; color: var(--ink-4); margin-top: 8px;">* Indicates required question</div>
        <div style="font-size: 12px; color: var(--ink-3); margin-top: 6px;">Step <span id="jh-step-counter">1</span> of 5</div>
    </div>

    {{-- Step Wizard --}}
    <x-step-wizard :steps="[
        ['label' => 'Admin', 'icon' => 'clipboard-list'],
        ['label' => 'Referral', 'icon' => 'share-2'],
        ['label' => 'Beneficiary', 'icon' => 'users'],
        ['label' => 'Diagnostics', 'icon' => 'file-text'],
        ['label' => 'Pathway', 'icon' => 'arrow-right'],
    ]" :current="1" />

    {{-- Validation errors --}}
    @if($errors->any())
    <div style="margin-bottom:16px;padding:14px 16px;background:var(--burgundy-tint,#fdf2f2);border:1px solid var(--burgundy);border-left:4px solid var(--burgundy);border-radius:6px;">
        <div style="font-size:12px;font-weight:600;color:var(--burgundy);margin-bottom:6px;">Please fix the following before submitting:</div>
        <ul style="margin:0;padding-left:18px;font-size:12px;color:var(--ink-2);">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Form --}}
    {{-- Submit overlay --}}
    <div id="jh-submit-overlay" style="
        display: flex; align-items: center; justify-content: center;
        position: fixed; inset: 0; z-index: 9999;
        background: rgba(15,25,20,0.55); backdrop-filter: blur(3px);
        opacity: 0; pointer-events: none;
        transition: opacity 0.25s ease;">
        <div style="
            background: #fff; border-radius: 12px; padding: 36px 48px;
            text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            min-width: 300px;">
            <svg style="width:40px;height:40px;animation:jhSpin 0.9s linear infinite;color:var(--moss,#2d6a4f);margin:0 auto 16px;display:block;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
            </svg>
            <div style="font-size:17px;font-weight:700;color:#1a2e1f;margin-bottom:8px;">Registering Case…</div>
            <div style="font-size:13px;color:#64748b;line-height:1.5;">
                Saving case record and<br>sending notification emails.
            </div>
        </div>
    </div>
    <style>
        @keyframes jhSpin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>

    <form id="jh-intake-form" method="POST" action="{{ route('intake.store') }}" novalidate data-user-id="{{ auth()->id() }}">
        @csrf
        <div class="card" style="padding: 28px 32px;">

            {{-- ═══ STEP 1: Admin ═══ --}}
            <div id="intake-step-1">
                <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">Section 2</div>
                <h3 class="serif" style="font-size: 22px; font-weight: 500; margin: 0 0 6px 0; color: var(--forest);">Admin & Client Reference</h3>
                <div style="font-size: 12.5px; color: var(--ink-3); margin-bottom: 20px;">This section collects administrative and referral information.</div>

                <div style="display: grid; grid-template-columns: 1fr; gap: 16px; margin-bottom: 14px;">
                    <x-form-select name="hubLocation" label="1. Justice Hub Location" required :options="$hubs" :selected="$hubs->count() === 1 ? $hubs->keys()->first() : null" />
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 14px;">
                    {{-- Staff receiving: read-only for Lawyer/CourtClerk, dropdown for others --}}
                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:10px;font-weight:500;letter-spacing:0.06em;text-transform:uppercase;color:var(--ink-3);">
                            3. Staff receiving client <span style="color:var(--burgundy);">*</span>
                        </label>
                        @if(auth()->user()->isLawyer() || auth()->user()->isCourtClerk())
                            {{-- Lawyers and court clerks: locked to themselves --}}
                            <input type="text" name="staffReceiving" class="inp"
                                   value="{{ $defaultStaffName }}" readonly
                                   style="background:var(--parchment);cursor:default;">
                        @else
                            <select name="staffReceiving" id="staffReceivingSelect" class="inp" required>
                                <option value="{{ $defaultStaffName }}"
                                        data-designation="{{ $defaultStaffDesignation }}"
                                        selected>
                                    {{ $defaultStaffName }}
                                </option>
                                @foreach($allStaff->where('name', '!=', $defaultStaffName) as $s)
                                <option value="{{ $s->name }}"
                                        data-designation="{{ trim($s->staff_uid . ' - ' . $s->role, ' -') }}">
                                    {{ $s->name }}
                                </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    {{-- Designation: always read-only --}}
                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:10px;font-weight:500;letter-spacing:0.06em;text-transform:uppercase;color:var(--ink-3);">
                            4. Staff ID-Designation
                        </label>
                        <input type="text" name="staffDesignation" id="staffDesignationInput" class="inp"
                               value="{{ $defaultStaffDesignation }}" readonly
                               style="background:var(--parchment);cursor:default;">
                    </div>
                    @unless(auth()->user()->isLawyer() || auth()->user()->isCourtClerk())
                    <script>
                    document.getElementById('staffReceivingSelect')?.addEventListener('change', function() {
                        const opt = this.options[this.selectedIndex];
                        document.getElementById('staffDesignationInput').value = opt.dataset.designation ?? '';
                    });
                    </script>
                    @endunless
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; margin-bottom: 6px; font-size: 10px; font-weight: 500; letter-spacing: 0.06em; text-transform: uppercase; color: var(--ink-3);">
                        5. Consent Statement <span style="color: var(--burgundy);"> *</span>
                    </label>
                    <select name="consent" class="inp" required>
                        <option value="Yes, I consent">Yes, I consent</option>
                        <option value="No, I don't">No, I don't</option>
                    </select>
                </div>
                <div id="intake-no-consent-box" style="display:none; margin-top: 12px;">
                    <div style="padding: 12px 14px; background: var(--ochre-tint); border: 1px solid var(--ochre); border-left: 3px solid var(--ochre); font-size: 12px; color: var(--ink-2); margin-bottom: 12px;">
                        Justice Hub will not deny any service based on no consent unless the service critically depends on required data.
                    </div>
                    <x-form-input name="noConsentReason" label="6. Please explain limitations" type="textarea" placeholder="Record the reason and any service limitations..." />
                </div>
            </div>

            {{-- ═══ STEP 2: Referral Source ═══ --}}
            <div id="intake-step-2" style="display:none;">
                <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">Section 3</div>
                <h3 class="serif" style="font-size: 22px; font-weight: 500; margin: 0 0 6px 0; color: var(--forest);">Source of Referral</h3>
                <div style="font-size: 12.5px; color: var(--ink-3); margin-bottom: 20px;">Please select the most relevant option.</div>

                <x-form-select name="heardAboutUs" label="7. How you heard about us?" required lookup-group="intake.referral_source" />

                <div id="intake-other-source-box" style="display:none; margin-top: 12px;">
                    <x-form-input name="heardAboutUsOther" label="Please specify" placeholder="Enter referral source" />
                </div>
                <div id="intake-paralegal-box" style="display:none; margin-top: 12px;">
                    <x-form-input name="paralegalName" label="8. Name of Paralegal" placeholder="Paralegal name" />
                </div>
                <div id="intake-ngo-box" style="display:none; margin-top: 12px;">
                    <x-form-select name="ngoReferralOrg" label="9. Referred by NGO/CSO/NPO" lookup-group="intake.ngo_referral" />
                </div>
                <div id="intake-govt-box" style="display:none; margin-top: 12px;">
                    <x-form-select name="govtReferralDept" label="10. Referred by Govt Department" lookup-group="intake.govt_referral" />
                </div>
            </div>

            {{-- ═══ STEP 3: Beneficiary Profile ═══ --}}
            <div id="intake-step-3" style="display:none;">
                <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">Section 4</div>
                <h3 class="serif" style="font-size: 22px; font-weight: 500; margin: 0 0 6px 0; color: var(--forest);">Beneficiary Profile</h3>
                <div style="font-size: 12.5px; color: var(--ink-3); margin-bottom: 20px;">Personal details help us understand context and tailor support.</div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 14px;">
                    <div>
                        <x-form-input name="cnic" label="15. CNIC Number" :mono="true" hint="Numbers only without dash, e.g. 1234567891234" maxlength="13" />
                        <button type="button" id="intake-cnic-search-btn" class="btn-ghost" style="margin-top: 8px; font-size: 11px; padding: 5px 14px; display: inline-flex; align-items: center; gap: 5px;">
                            <x-lucide-search style="width:12px;height:12px;" /> Search Client
                        </button>
                        <span id="intake-repeat-status" style="margin-left: 8px; font-size: 11px; font-weight: 500;"></span>
                        <input type="hidden" name="repeatClient" value="">
                    </div>
                    <x-form-input name="primaryContact" label="22. Primary Contact Number" required :mono="true" placeholder="03XXXXXXXXX" maxlength="11" pattern="[0-9]{11}" inputmode="numeric" />
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 14px;">
                    <x-form-input name="fullName" label="11. Full Name (as per CNIC)" required />
                    <x-form-input name="fatherHusbandName" label="12. Father / Husband Name" required />
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 14px;">
                    <x-form-select name="gender" label="13. Gender" required lookup-group="intake.gender" />
                    <x-form-input name="age" label="14. Age (in years)" type="number" required min="0" max="120" />
                </div>
                <div id="intake-gender-other-box" style="display:none; margin-bottom: 14px;">
                    <x-form-input name="genderOther" label="Gender - Other" />
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 14px;">
                    <x-form-select name="maritalStatus" label="16. Marital Status" required lookup-group="intake.marital_status" />
                    <x-form-select name="religion" label="17. Religion" required lookup-group="intake.religion" />
                    <x-form-select name="educationLevel" label="18. Education Level" required lookup-group="intake.education_level" />
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 14px;">
                    <x-form-input name="occupation" label="19. Occupation" />
                    <x-form-select name="monthlyIncome" label="20. Monthly Income Bracket" required lookup-group="intake.income_bracket" />
                    <x-form-select name="disabilityStatus" label="21. Disability Status" required lookup-group="intake.disability_status" />
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 14px;">
                    <x-form-input name="alternativeContact" label="23. Alternative Contact" :mono="true" />
                    <x-form-input name="fullAddress" label="24. Full Address" type="textarea" />
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                    <div>
                        <label style="display:block; margin-bottom:6px; font-size:10px; font-weight:500; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">27. District <span style="color:var(--burgundy);">*</span></label>
                        <select name="district" id="intakeDistrict" required class="inp" onchange="intakeLocationCascade('district')">
                            <option value="">Select district…</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:6px; font-size:10px; font-weight:500; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">26. Tehsil / Taluka / Town <span style="color:var(--burgundy);">*</span></label>
                        <select name="tehsil" id="intakeTehsil" required class="inp" onchange="intakeLocationCascade('taluka')">
                            <option value="">Select district first…</option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display:block; margin-bottom:6px; font-size:10px; font-weight:500; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">25. Union Council</label>
                    <select name="unionCouncil" id="intakeUC" class="inp">
                        <option value="">Select taluka first…</option>
                    </select>
                </div>
                <x-form-select name="preferredLanguage" label="28. Preferred Language" required lookup-group="intake.preferred_language" />
                <div id="intake-lang-other-box" style="display:none; margin-top: 12px;">
                    <x-form-input name="preferredLanguageOther" label="Preferred Language - Other" />
                </div>
            </div>

            {{-- ═══ STEP 4: Problem Diagnostics ═══ --}}
            <div id="intake-step-4" style="display:none;">
                <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">Section 5</div>
                <h3 class="serif" style="font-size: 22px; font-weight: 500; margin: 0 0 6px 0; color: var(--forest);">Problem Diagnostics</h3>
                <div style="font-size: 12.5px; color: var(--ink-3); margin-bottom: 20px;">Outline the issue category, urgency, and brief description.</div>

                <x-form-input name="issueDescription" label="29. Brief description of the issue" type="textarea" placeholder="Briefly describe the issue..." />
                <div style="height: 14px;"></div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                    <x-form-select name="category" label="30. Category" required lookup-group="case.primary_issue" />
                    <x-form-select name="urgencyLevel" label="31. Urgency Level" required lookup-group="case.urgency" />
                </div>

            </div>

            {{-- ═══ STEP 5: Pathway Assignment ═══ --}}
            <div id="intake-step-5" style="display:none;">
                <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">Assignment</div>
                <h3 class="serif" style="font-size: 22px; font-weight: 500; margin: 0 0 6px 0; color: var(--forest);">32. Referred To / Assigned Service Pathway</h3>
                <div style="font-size: 12.5px; color: var(--ink-3); margin-bottom: 20px;">Select one primary pathway assigned after assessment.</div>

                <x-form-select name="assignedPathway" label="Primary pathway" required lookup-group="intake.assigned_pathway" />

                {{-- Cascading specifics — populated by jhInitIntakeWizard() --}}
                <div id="intake-pathway-specific-box" style="display:none; margin-top: 12px;">
                    <label style="display: block; margin-bottom: 6px; font-size: 10px; font-weight: 500; letter-spacing: 0.06em; text-transform: uppercase; color: var(--ink-3);">Specific pathway <span style="color: var(--burgundy);"> *</span></label>
                    <select name="pathwaySpecific" class="inp">
                        <option value="">Select...</option>
                    </select>
                    <div id="intake-pw-specific-other-box" style="display:none; margin-top: 10px;">
                        <x-form-input name="pathwaySpecificOther" label="Please specify" />
                    </div>
                </div>

                {{-- Lawyer assignment (Court Representation → Justice Hub Lawyer) --}}
                <div id="intake-pw-lawyer-box" style="display:none; margin-top: 12px;">
                    <label style="display:block; margin-bottom:6px; font-size:10px; font-weight:500; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">
                        Assigned Lawyer <span style="color:var(--burgundy);">*</span>
                    </label>
                    <select name="assignedLawyer" class="inp">
                        <option value="">— Select lawyer —</option>
                        @foreach($lawyers as $lawyer)
                        <option value="{{ $lawyer->id }}">{{ $lawyer->name }}</option>
                        @endforeach
                    </select>
                    @if($lawyers->isEmpty())
                    <p style="font-size:11px; color:var(--ink-4); margin:6px 0 0;">No lawyers found for this hub. Add staff first.</p>
                    @endif
                </div>

                {{-- Hub Coordinator display (Mediation / Govt / NGO / Other) --}}
                <div id="intake-pw-coordinator-box" style="display:none; margin-top: 12px;">
                    <label style="display:block; margin-bottom:6px; font-size:10px; font-weight:500; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">
                        Hub Coordinator
                    </label>
                    <input type="text" id="intake-pw-coordinator-name" class="inp" readonly
                           style="background:var(--parchment);cursor:default;"
                           value="" placeholder="— Coordinator for selected hub —">
                    <input type="hidden" name="assignedCoordinator" id="intake-pw-coordinator-hidden">
                </div>

                {{-- Government dept --}}
                <div id="intake-pw-govt-box" style="display:none; margin-top: 12px;">
                    <x-form-select name="pathwayGovernmentDept" label="Specific department / institution" required lookup-group="intake.pathway_govt" />
                </div>

                {{-- NGO --}}
                <div id="intake-pw-ngo-box" style="display:none; margin-top: 12px;">
                    <x-form-input name="pathwayNgoName" label="Name of organisation" placeholder="Organisation name" />
                </div>

                {{-- Other --}}
                <div id="intake-pw-other-box" style="display:none; margin-top: 12px;">
                    <x-form-input name="pathwayOtherDetails" label="Please specify" type="textarea" />
                </div>

                <div style="margin-top: 18px; padding: 14px 16px; background: var(--paper); border: 1px solid var(--rule); border-left: 3px solid var(--moss);">
                    <div style="font-weight: 600; font-size: 12.5px; color: var(--ink-2); margin-bottom: 6px;">Conclusion</div>
                    <div style="font-size: 12px; color: var(--ink-3); line-height: 1.55;">
                        Thank you for completing the intake form. For any questions, feedback, or complaints: {{ config('justice_hub.contact.organization') }} - {{ config('justice_hub.contact.phone') }}.
                    </div>
                </div>
            </div>

            {{-- ═══ Navigation Buttons ═══ --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 28px; padding-top: 22px; border-top: 1px solid var(--rule-2);">
                <button type="button" id="intake-back-btn" class="btn-ghost"
                    data-cancel-url="{{ route('cases.index') }}">Cancel</button>

                <div id="intake-validation-hint" style="font-size: 11px; color: var(--ink-3); display:none;"></div>

                <div style="display:flex; gap:10px;">
                    <button type="button" id="intake-next-btn" class="btn-primary">
                        Continue <x-lucide-chevron-right style="width:12px;height:12px;" />
                    </button>
                    <button type="submit" id="intake-submit-btn" class="btn-primary" style="background:var(--moss);display:none;" disabled>
                        <span id="intake-submit-idle" style="display:flex;align-items:center;gap:6px;">
                            <x-lucide-check-circle-2 style="width:13px;height:13px;" /> Register Intake
                        </span>
                        <span id="intake-submit-loading" style="display:none;align-items:center;gap:8px;">
                            <svg style="width:14px;height:14px;animation:jhSpin 0.8s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                            </svg>
                            Registering…
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
// ── Location cascade data ──
var _locHubDistricts = @json($hubDistricts);
var _locData = @json($locationData);
var _hubCoordinators = @json($hubCoordinators);

// ── Cascade function (global, always available for onchange) ──
function intakeLocationCascade(level) {
    var districtSel  = document.getElementById('intakeDistrict');
    var tehsilSel    = document.getElementById('intakeTehsil');
    var ucSel        = document.getElementById('intakeUC');
    if (!districtSel || !tehsilSel || !ucSel) return;

    var district = districtSel.value;
    var distData = _locData[district] || {};

    if (level === 'district') {
        tehsilSel.innerHTML = '<option value="">Select taluka…</option>';
        ucSel.innerHTML     = '<option value="">Select taluka first…</option>';

        if (!district) return;

        var talukas = distData.talukas || [];
        if (talukas.length > 0) {
            talukas.forEach(function(t) {
                var o = document.createElement('option');
                o.value = t; o.textContent = t;
                tehsilSel.appendChild(o);
            });
        } else {
            tehsilSel.innerHTML = '<option value="">(none listed)</option>';
            var ucs = (distData.ucs || {})['__none__'] || [];
            if (ucs.length > 0) {
                ucSel.innerHTML = '<option value="">Select union council…</option>';
                ucs.forEach(function(uc) {
                    var o = document.createElement('option');
                    o.value = uc; o.textContent = uc;
                    ucSel.appendChild(o);
                });
            }
        }
    }

    if (level === 'taluka') {
        var taluka = tehsilSel.value;
        ucSel.innerHTML = '<option value="">Select union council…</option>';
        if (!district) return;

        var ucsMap = distData.ucs || {};
        var ucs = ucsMap[taluka] || ucsMap['__none__'] || [];
        ucs.forEach(function(uc) {
            var o = document.createElement('option');
            o.value = uc; o.textContent = uc;
            ucSel.appendChild(o);
        });
    }
}

// ── Submit lock: prevent double-submit, show loading state ──
document.getElementById('jh-intake-form').addEventListener('submit', function() {
    var btn    = document.getElementById('intake-submit-btn');
    var idle   = document.getElementById('intake-submit-idle');
    var loading = document.getElementById('intake-submit-loading');
    var overlay = document.getElementById('jh-submit-overlay');

    // Swap button to loading state
    idle.style.display    = 'none';
    loading.style.display = 'flex';
    btn.disabled          = true;
    btn.style.opacity     = '0.85';
    btn.style.cursor      = 'not-allowed';

    // Show full-page overlay after a tiny delay (avoids flash if server is fast)
    setTimeout(function() {
        if (overlay) {
            overlay.style.opacity       = '1';
            overlay.style.pointerEvents = 'all';
        }
    }, 300);
});

// ── Populate district dropdown + wire hub change ──
(function() {
    var districtSel = document.getElementById('intakeDistrict');
    if (!districtSel) return;

    // Fill all districts
    var districts = Object.keys(_locData).sort();
    districts.forEach(function(d) {
        var o = document.createElement('option');
        o.value = d; o.textContent = d;
        districtSel.appendChild(o);
    });

    // Wire hub → district auto-select
    var hubSel = document.querySelector('[name="hubLocation"]');
    if (hubSel) {
        hubSel.addEventListener('change', function() {
            var district = _locHubDistricts[this.value] || '';
            districtSel.value = district;
            intakeLocationCascade('district');
        });

        // Auto-select district if hub already has a value
        if (hubSel.value && _locHubDistricts[hubSel.value]) {
            districtSel.value = _locHubDistricts[hubSel.value];
            intakeLocationCascade('district');
        }
    }
})();
</script>
</x-layouts.app>
