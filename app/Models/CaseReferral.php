<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseReferral extends Model
{
    protected $fillable = [
        'case_id',
        'referred_to',
        'contact_person',
        'contact_phone',
        'referral_date',
        'reason',
        'follow_up_date',
        'status',
        'outcome',
        'referred_by',
        // Focal person
        'focal_person_name',
        'focal_person_designation',
        'focal_person_phone',
        'focal_person_email',
        // Follow-up
        'partner_tracking_ref',
        // Closure
        'closed_at',
        'closed_outcome',
        'closed_note',
    ];

    protected function casts(): array
    {
        return [
            'referral_date'  => 'date',
            'follow_up_date' => 'date',
            'closed_at'      => 'datetime',
        ];
    }

    public function caseRecord(): BelongsTo
    {
        return $this->belongsTo(CaseRecord::class, 'case_id');
    }

    public function letters(): HasMany
    {
        return $this->hasMany(CaseReferralLetter::class)->orderBy('letter_date');
    }

    public function threads(): HasMany
    {
        return $this->hasMany(CaseReferralThread::class)->orderBy('thread_date');
    }

    public function isClosed(): bool
    {
        return $this->closed_at !== null;
    }
}
