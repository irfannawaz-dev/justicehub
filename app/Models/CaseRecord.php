<?php

namespace App\Models;

use App\Enums\CaseStatus;
use App\Enums\CaseDisposition;
use App\Enums\UrgencyLevel;
use App\Enums\RiskLevel;
use App\Traits\HasHubScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseRecord extends Model
{
    use SoftDeletes, HasHubScope, Auditable;

    protected $table = 'cases';

    protected static function booted(): void
    {
        static::saving(function (self $case) {
            if ($case->isDirty('cnic') && $case->cnic) {
                $case->cnic_hash = hash('sha256', $case->cnic);
            }
        });
    }

    protected $fillable = [
        'unique_id', 'case_uid', 'case_ref', 'encounter_id', 'hub_id',
        'assigned_to', 'assigned_staff_id',
        // Client info
        'name', 'father_husband_name', 'gender', 'gender_other', 'age',
        'cnic', 'cnic_hash', 'marital_status', 'religion', 'education_level',
        'occupation', 'income_bracket', 'disability_status',
        'primary_contact', 'alternative_contact', 'full_address',
        'union_council', 'tehsil', 'district', 'language',
        // Intake
        'intake_date', 'intake_time', 'mode', 'source', 'referral_source',
        'referral_type', 'referral_contact_person',
        'consent', 'no_consent_reason', 'returning_client',
        'staff_receiving', 'staff_designation',
        // Classification
        'primary_issue', 'secondary_issue', 'issue_description',
        'urgency', 'status', 'disposition', 'risk',
        'sla_met',
        'litigation_stage', 'litigation_stage_changed_by', 'litigation_stage_changed_at',
        'adr_stage', 'adr_stage_changed_by', 'adr_stage_changed_at',
        // Vulnerability flags
        'is_gbv', 'is_child', 'is_minority', 'is_disability', 'is_underserved',
        // Pathway
        'assigned_pathway', 'pathway_specific', 'pathway_specific_other',
        'pathway_govt_dept', 'pathway_ngo_name', 'pathway_other_details',
        'complaint_department',
        // Approval
        'pathway_manager', 'approval_decision', 'requested_at',
        'rejection_reason', 'rejected_by', 'rejected_at',
        // Other
        'summary', 'last_update', 'meta',
        // External LAS CMS link
        'external_case_id', 'external_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CaseStatus::class,
            'disposition' => CaseDisposition::class,
            'urgency' => UrgencyLevel::class,
            'risk' => RiskLevel::class,
            'intake_date' => 'date',
            'last_update' => 'date',
            'requested_at' => 'datetime',
            'rejected_at' => 'datetime',
            'consent' => 'boolean',
            'returning_client' => 'boolean',
            'sla_met' => 'boolean',
            'is_gbv' => 'boolean',
            'is_child' => 'boolean',
            'is_minority' => 'boolean',
            'is_disability' => 'boolean',
            'is_underserved' => 'boolean',
            'meta' => 'array',
            // PII — encrypted at rest using APP_KEY (AES-256-CBC via Laravel's encrypter)
            // NOTE: re-seed or run php artisan db:seed --class=CaseSeeder after adding this cast
            'cnic' => 'encrypted',
        ];
    }

    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(\App\Models\CaseMessage::class, 'case_id')->orderBy('created_at');
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assigned_staff_id');
    }

    public function serviceEncounters(): HasMany
    {
        return $this->hasMany(ServiceEncounter::class, 'case_id')->orderBy('date');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'case_id');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'case_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class, 'case_id');
    }

    public function mediationParties(): HasMany
    {
        return $this->hasMany(MediationParty::class, 'case_id')->latest();
    }

    public function mediationDiary(): HasMany
    {
        return $this->hasMany(MediationDiary::class, 'case_id')->latest();
    }

    public function caseStudies(): HasMany
    {
        return $this->hasMany(CaseStudy::class, 'case_id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(CaseTransfer::class, 'case_id')->latest();
    }

    public function caseReferrals(): HasMany
    {
        return $this->hasMany(CaseReferral::class, 'case_id')->latest();
    }

    /**
     * Compute SLA compliance status based on urgency-defined hours and first encounter date.
     * Pass $firstEncounterDate as a date string; if null the method queries the DB.
     */
    public function computeSlaStatus(?string $firstEncounterDate = null): array
    {
        $hours     = config('justice_hub.sla.urgency_hours')[$this->urgency->value] ?? 168;
        $intakeDt  = \Carbon\Carbon::parse(
            $this->intake_date->toDateString() . ' ' . ($this->intake_time ?? '00:00')
        );
        $deadline  = $intakeDt->copy()->addHours($hours);
        $now       = now();

        // If not provided, try loading from DB
        if ($firstEncounterDate === null) {
            $firstEncounterDate = $this->serviceEncounters()->orderBy('date')->value('date');
        }

        if ($firstEncounterDate) {
            $firstDt  = \Carbon\Carbon::parse($firstEncounterDate)->startOfDay();
            $met      = $firstDt->lte($deadline);
            return [
                'status'          => $met ? 'met' : 'breach',
                'deadline'        => $deadline,
                'first_encounter' => $firstDt,
                'hours_limit'     => $hours,
                'hours_taken'     => (int) $intakeDt->diffInHours($firstDt),
                'hours_overdue'   => $met ? 0 : (int) $deadline->diffInHours($firstDt),
                'hours_remaining' => 0,
            ];
        }

        // No encounter yet
        $overdue = $now->gt($deadline);
        return [
            'status'          => $overdue ? 'breach' : 'pending',
            'deadline'        => $deadline,
            'first_encounter' => null,
            'hours_limit'     => $hours,
            'hours_taken'     => null,
            'hours_overdue'   => $overdue ? (int) $deadline->diffInHours($now) : 0,
            'hours_remaining' => $overdue ? 0 : (int) $now->diffInHours($deadline),
        ];
    }

    /** Return the User record for the currently assigned person, or null. */
    public function getAssignedUser(): ?\App\Models\User
    {
        // Prefer lookup via assigned_staff_id → Staff → User (cross-hub safe)
        if ($this->assigned_staff_id) {
            $userId = \App\Models\Staff::where('id', $this->assigned_staff_id)->value('user_id');
            if ($userId) return \App\Models\User::find($userId);
        }
        // Fallback: match by name only (no hub filter)
        if (! $this->assigned_to) return null;
        return \App\Models\User::where('name', $this->assigned_to)->first();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', CaseStatus::Active);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDisposition($query, string $disposition)
    {
        return $query->where('disposition', $disposition);
    }
}
