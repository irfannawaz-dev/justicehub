<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseReferralLetter extends Model
{
    protected $fillable = [
        'case_referral_id',
        'our_ref',
        'note',
        'letter_date',
        'logged_by',
        'file_path',
        'file_name',
    ];

    protected function casts(): array
    {
        return [
            'letter_date' => 'date',
        ];
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(CaseReferral::class, 'case_referral_id');
    }
}
