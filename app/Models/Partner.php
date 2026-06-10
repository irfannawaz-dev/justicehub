<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Partner extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'category', 'type', 'focal_person',
        'active_referrals', 'completed_referrals', 'failed_referrals',
        'avg_response_hours', 'last_referral_date',
        'mou_expires', 'mou_status', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'last_referral_date' => 'date',
            'mou_expires' => 'date',
            'meta' => 'array',
        ];
    }

    public function hubs(): BelongsToMany
    {
        return $this->belongsToMany(Hub::class, 'hub_partner');
    }
}
