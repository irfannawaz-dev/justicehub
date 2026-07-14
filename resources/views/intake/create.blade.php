<x-layouts.app>
<div style="padding: 24px 34px 64px; max-width: 1280px; margin: 0 auto;">

    {{-- Header --}}
    <div style="margin-bottom: 22px; padding-bottom: 18px; border-bottom: 1px solid var(--rule);">
        <div class="label-cap" style="font-size: 9.5px; margin-bottom: 6px;">{{ __('intake.tool_label') }}</div>
        <h1 class="serif" style="font-size: 34px; font-weight: 400; letter-spacing: -0.018em; margin: 0; line-height: 1.05;">
            {{ __('intake.title') }}
        </h1>
        <div style="font-size: 12.5px; color: var(--ink-3); margin-top: 8px; line-height: 1.5;">
            {{ __('intake.description') }}
        </div>
        <div style="font-size: 11px; color: var(--ink-4); margin-top: 8px;">{{ __('intake.required_hint') }}</div>
        <div style="font-size: 12px; color: var(--ink-3); margin-top: 6px;">{!! __('intake.step_of', ['current' => '<span id="jh-step-counter">1</span>', 'total' => 5]) !!}</div>
    </div>

    {{-- Step Wizard --}}
    <x-step-wizard :steps="[
        ['label' => __('intake.step_admin'), 'icon' => 'clipboard-list'],
        ['label' => __('intake.step_referral'), 'icon' => 'share-2'],
        ['label' => __('intake.step_beneficiary'), 'icon' => 'users'],
        ['label' => __('intake.step_diagnostics'), 'icon' => 'file-text'],
        ['label' => __('intake.step_pathway'), 'icon' => 'arrow-right'],
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
            <div style="font-size:17px;font-weight:700;color:#1a2e1f;margin-bottom:8px;">{{ __('intake.registering') }}</div>
            <div style="font-size:13px;color:#64748b;line-height:1.5;">
                {{ __('intake.registering_desc') }}
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
                <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">{{ __('intake.s1_section') }}</div>
                <h3 class="serif" style="font-size: 22px; font-weight: 500; margin: 0 0 6px 0; color: var(--forest);">{{ __('intake.s1_title') }}</h3>
                <div style="font-size: 12.5px; color: var(--ink-3); margin-bottom: 20px;">{{ __('intake.s1_desc') }}</div>

                <div style="display: grid; grid-template-columns: 1fr; gap: 16px; margin-bottom: 14px;">
                    <x-form-select name="hubLocation" :label="__('intake.hub_location')" required :options="$hubs" :selected="$hubs->count() === 1 ? $hubs->keys()->first() : null" />
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 14px;">
                    {{-- Staff receiving: read-only for Lawyer/CourtClerk, dropdown for others --}}
                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:10px;font-weight:500;letter-spacing:0.06em;text-transform:uppercase;color:var(--ink-3);">
                            {{ __('intake.staff_receiving') }} <span style="color:var(--burgundy);">*</span>
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
                            {{ __('intake.staff_designation') }}
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
                        {{ __('intake.consent') }} <span style="color: var(--burgundy);"> *</span>
                    </label>
                    <select name="consent" class="inp" required>
                        <option value="Yes, I consent">{{ __('intake.consent_yes') }}</option>
                        <option value="No, I don't">{{ __('intake.consent_no') }}</option>
                    </select>
                </div>
                <div id="intake-no-consent-box" style="display:none; margin-top: 12px;">
                    <div style="padding: 12px 14px; background: var(--ochre-tint); border: 1px solid var(--ochre); border-left: 3px solid var(--ochre); font-size: 12px; color: var(--ink-2); margin-bottom: 12px;">
                        {{ __('intake.consent_warning') }}
                    </div>
                    <x-form-input name="noConsentReason" :label="__('intake.explain_limits')" type="textarea" placeholder="Record the reason and any service limitations..." />
                </div>
            </div>

            {{-- ═══ STEP 2: Referral Source ═══ --}}
            <div id="intake-step-2" style="display:none;">
                <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">{{ __('intake.s2_section') }}</div>
                <h3 class="serif" style="font-size: 22px; font-weight: 500; margin: 0 0 6px 0; color: var(--forest);">{{ __('intake.s2_title') }}</h3>
                <div style="font-size: 12.5px; color: var(--ink-3); margin-bottom: 20px;">{{ __('intake.s2_desc') }}</div>

                <x-form-select name="heardAboutUs" :label="__('intake.heard_about')" required lookup-group="intake.referral_source" />

                <div id="intake-other-source-box" style="display:none; margin-top: 12px;">
                    <x-form-input name="heardAboutUsOther" :label="__('intake.please_specify')" :placeholder="__('intake.enter_referral')" />
                </div>
                <div id="intake-paralegal-box" style="display:none; margin-top: 12px;">
                    <x-form-input name="paralegalName" :label="__('intake.paralegal_name')" />
                </div>
                <div id="intake-ngo-box" style="display:none; margin-top: 12px;">
                    <x-form-select name="ngoReferralOrg" :label="__('intake.ngo_referral')" lookup-group="intake.ngo_referral" />
                </div>
                <div id="intake-govt-box" style="display:none; margin-top: 12px;">
                    <x-form-select name="govtReferralDept" :label="__('intake.govt_referral')" lookup-group="intake.govt_referral" />
                </div>
            </div>

            {{-- ═══ STEP 3: Beneficiary Profile ═══ --}}
            <div id="intake-step-3" style="display:none;">
                <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">{{ __('intake.s3_section') }}</div>
                <h3 class="serif" style="font-size: 22px; font-weight: 500; margin: 0 0 6px 0; color: var(--forest);">{{ __('intake.s3_title') }}</h3>
                <div style="font-size: 12.5px; color: var(--ink-3); margin-bottom: 20px;">{{ __('intake.s3_desc') }}</div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 14px;">
                    <div>
                        <x-form-input name="cnic" :label="__('intake.cnic')" :mono="true" :hint="__('intake.cnic_hint')" maxlength="13" />
                        <button type="button" id="intake-cnic-search-btn" class="btn-ghost" style="margin-top: 8px; font-size: 11px; padding: 5px 14px; display: inline-flex; align-items: center; gap: 5px;">
                            <x-lucide-search style="width:12px;height:12px;" /> {{ __('intake.search_client') }}
                        </button>
                        <span id="intake-repeat-status" style="margin-left: 8px; font-size: 11px; font-weight: 500;"></span>
                        <input type="hidden" name="repeatClient" value="">
                    </div>
                    <x-form-input name="primaryContact" :label="__('intake.primary_contact')" required :mono="true" placeholder="03XXXXXXXXX" maxlength="11" pattern="[0-9]{11}" inputmode="numeric" />
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 14px;">
                    <x-form-input name="fullName" :label="__('intake.full_name')" required />
                    <x-form-input name="fatherHusbandName" :label="__('intake.father_husband')" required />
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 14px;">
                    <x-form-select name="gender" :label="__('intake.gender')" required lookup-group="intake.gender" />
                    <x-form-input name="age" :label="__('intake.age')" type="number" required min="0" max="120" />
                </div>
                <div id="intake-gender-other-box" style="display:none; margin-bottom: 14px;">
                    <x-form-input name="genderOther" :label="__('intake.gender_other')" />
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 14px;">
                    <x-form-select name="maritalStatus" :label="__('intake.marital_status')" required lookup-group="intake.marital_status" />
                    <x-form-select name="religion" :label="__('intake.religion')" required lookup-group="intake.religion" />
                    <x-form-select name="educationLevel" :label="__('intake.education_level')" required lookup-group="intake.education_level" />
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 14px;">
                    <x-form-input name="occupation" :label="__('intake.occupation')" />
                    <x-form-select name="monthlyIncome" :label="__('intake.monthly_income')" required lookup-group="intake.income_bracket" />
                    <x-form-select name="disabilityStatus" :label="__('intake.disability')" required lookup-group="intake.disability_status" />
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 14px;">
                    <x-form-input name="alternativeContact" :label="__('intake.alt_contact')" :mono="true" />
                    <x-form-input name="fullAddress" :label="__('intake.full_address')" type="textarea" />
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                    <div>
                        <label style="display:block; margin-bottom:6px; font-size:10px; font-weight:500; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">{{ __('intake.district') }} <span style="color:var(--burgundy);">*</span></label>
                        <select name="district" id="intakeDistrict" required class="inp" onchange="intakeLocationCascade('district')">
                            <option value="">{{ __('intake.select_district') }}</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:6px; font-size:10px; font-weight:500; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">{{ __('intake.tehsil') }} <span style="color:var(--burgundy);">*</span></label>
                        <select name="tehsil" id="intakeTehsil" required class="inp" onchange="intakeLocationCascade('taluka')">
                            <option value="">{{ __('intake.select_district_first') }}</option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display:block; margin-bottom:6px; font-size:10px; font-weight:500; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">{{ __('intake.union_council') }}</label>
                    <select name="unionCouncil" id="intakeUC" class="inp">
                        <option value="">{{ __('intake.select_taluka_first') }}</option>
                    </select>
                </div>
                <x-form-select name="preferredLanguage" :label="__('intake.preferred_lang')" required lookup-group="intake.preferred_language" />
                <div id="intake-lang-other-box" style="display:none; margin-top: 12px;">
                    <x-form-input name="preferredLanguageOther" :label="__('intake.preferred_lang_other')" />
                </div>
            </div>

            {{-- ═══ STEP 4: Problem Diagnostics ═══ --}}
            <div id="intake-step-4" style="display:none;">
                <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">{{ __('intake.s4_section') }}</div>
                <h3 class="serif" style="font-size: 22px; font-weight: 500; margin: 0 0 6px 0; color: var(--forest);">{{ __('intake.s4_title') }}</h3>
                <div style="font-size: 12.5px; color: var(--ink-3); margin-bottom: 20px;">{{ __('intake.s4_desc') }}</div>

                <x-form-input name="issueDescription" :label="__('intake.issue_desc')" type="textarea" :placeholder="__('intake.issue_placeholder')" required />
                <div style="height: 14px;"></div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                    <x-form-select name="category" :label="__('intake.category')" required lookup-group="case.primary_issue" />
                    <x-form-select name="urgencyLevel" :label="__('intake.urgency')" required lookup-group="case.urgency" />
                </div>

            </div>

            {{-- ═══ STEP 5: Pathway Assignment ═══ --}}
            <div id="intake-step-5" style="display:none;">
                <div class="label-cap" style="font-size: 9.5px; margin-bottom: 4px;">{{ __('intake.s5_section') }}</div>
                <h3 class="serif" style="font-size: 22px; font-weight: 500; margin: 0 0 6px 0; color: var(--forest);">{{ __('intake.s5_title') }}</h3>
                <div style="font-size: 12.5px; color: var(--ink-3); margin-bottom: 20px;">{{ __('intake.s5_desc') }}</div>

                <x-form-select name="assignedPathway" :label="__('intake.primary_pathway')" required lookup-group="intake.assigned_pathway" />

                {{-- Cascading specifics — populated by jhInitIntakeWizard() --}}
                <div id="intake-pathway-specific-box" style="display:none; margin-top: 12px;">
                    <label style="display: block; margin-bottom: 6px; font-size: 10px; font-weight: 500; letter-spacing: 0.06em; text-transform: uppercase; color: var(--ink-3);">{{ __('intake.specific_pathway') }} <span style="color: var(--burgundy);"> *</span></label>
                    <select name="pathwaySpecific" class="inp">
                        <option value="">{{ __('intake.select') }}</option>
                    </select>
                    <div id="intake-pw-specific-other-box" style="display:none; margin-top: 10px;">
                        <x-form-input name="pathwaySpecificOther" :label="__('intake.please_specify')" />
                    </div>
                </div>

                {{-- Department / Complaint Against (shown for Provincial Ombudsman) --}}
                <div id="intake-pw-complaint-dept-box" style="display:none; margin-top: 12px;">
                    <x-form-select name="complaintDepartment" :label="__('intake.complaint_dept')" required lookup-group="intake.complaint_department" />
                </div>

                {{-- Lawyer assignment (Court Representation → Justice Hub Lawyer) --}}
                <div id="intake-pw-lawyer-box" style="display:none; margin-top: 12px;">
                    <label style="display:block; margin-bottom:6px; font-size:10px; font-weight:500; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">
                        {{ __('intake.assigned_lawyer') }} <span style="color:var(--burgundy);">*</span>
                    </label>
                    <select name="assignedLawyer" id="lawyerSelect" class="inp">
                        <option value="">— Select lawyer —</option>
                        @foreach($lawyers as $lawyer)
                        <option value="{{ $lawyer->id }}" data-hub="{{ $lawyer->hub_id }}">{{ $lawyer->name }}</option>
                        @endforeach
                    </select>
                    @if($lawyers->isEmpty())
                    <p style="font-size:11px; color:var(--ink-4); margin:6px 0 0;">No lawyers found. Add lawyer accounts first.</p>
                    @endif
                </div>

                {{-- Hub Coordinator display (Mediation / Govt / NGO / Other) --}}
                <div id="intake-pw-coordinator-box" style="display:none; margin-top: 12px;">
                    <label style="display:block; margin-bottom:6px; font-size:10px; font-weight:500; letter-spacing:0.06em; text-transform:uppercase; color:var(--ink-3);">
                        {{ __('intake.hub_coordinator') }}
                    </label>
                    <input type="text" id="intake-pw-coordinator-name" class="inp" readonly
                           style="background:var(--parchment);cursor:default;"
                           value="" placeholder="— Coordinator for selected hub —">
                    <input type="hidden" name="assignedCoordinator" id="intake-pw-coordinator-hidden">
                </div>

                {{-- NGO --}}
                <div id="intake-pw-ngo-box" style="display:none; margin-top: 12px;">
                    <div>
                        <label class="jh-field-label">Specific Organisation / NGO *</label>
                        <select name="pathwayNgoName" class="inp" style="width:100%; font-size:13px; box-sizing:border-box;">
                            <option value="">— Select organisation —</option>
                            @foreach($ngoPartners as $ngo)
                                <option value="{{ $ngo }}" @selected(old('pathwayNgoName') === $ngo)>{{ $ngo }}</option>
                            @endforeach
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                {{-- Other --}}
                <div id="intake-pw-other-box" style="display:none; margin-top: 12px;">
                    <x-form-input name="pathwayOtherDetails" :label="__('intake.please_specify')" type="textarea" />
                </div>

                <div style="margin-top: 18px; padding: 14px 16px; background: var(--paper); border: 1px solid var(--rule); border-left: 3px solid var(--moss);">
                    <div style="font-weight: 600; font-size: 12.5px; color: var(--ink-2); margin-bottom: 6px;">{{ __('intake.conclusion_title') }}</div>
                    <div style="font-size: 12px; color: var(--ink-3); line-height: 1.55;">
                        {{ __('intake.conclusion_text') }}
                    </div>
                </div>
            </div>

            {{-- ═══ Navigation Buttons ═══ --}}
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 28px; padding-top: 22px; border-top: 1px solid var(--rule-2);">
                <button type="button" id="intake-back-btn" class="btn-ghost"
                    data-cancel-url="{{ route('cases.index') }}">{{ __('intake.cancel') }}</button>

                <div id="intake-validation-hint" style="font-size: 11px; color: var(--ink-3); display:none;"></div>

                <div style="display:flex; gap:10px;">
                    <button type="button" id="intake-next-btn" class="btn-primary">
                        {{ __('intake.continue') }} <x-lucide-chevron-right style="width:12px;height:12px;" />
                    </button>
                    <button type="submit" id="intake-submit-btn" class="btn-primary" style="background:var(--moss);display:none;" disabled>
                        <span id="intake-submit-idle" style="display:flex;align-items:center;gap:6px;">
                            <x-lucide-check-circle-2 style="width:13px;height:13px;" /> {{ __('intake.register_intake') }}
                        </span>
                        <span id="intake-submit-loading" style="display:none;align-items:center;gap:8px;">
                            <svg style="width:14px;height:14px;animation:jhSpin 0.8s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                            </svg>
                            {{ __('intake.registering') }}
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
var _governmentPartners = @json($governmentPartners->values());
var _ngoPartners = @json($ngoPartners->values());
var _adrPartners = @json($adrPartners->values());

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
