<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseReferralThread extends Model
{
    protected $fillable = [
        'case_referral_id',
        'direction',
        'type',
        'thread_date',
        'note',
        'logged_by',
    ];

    protected function casts(): array
    {
        return [
            'thread_date' => 'date',
        ];
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(CaseReferral::class, 'case_referral_id');
    }

    public function isFromPartner(): bool
    {
        return $this->direction === 'from_partner';
    }
}
