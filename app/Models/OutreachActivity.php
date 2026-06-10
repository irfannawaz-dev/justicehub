<?php

namespace App\Models;

use App\Traits\HasHubScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutreachActivity extends Model
{
    use HasHubScope;
    protected $fillable = [
        'outreach_uid', 'date', 'hub_id', 'type', 'location',
        'facilitator', 'total_participants', 'female_participants',
        'minority_participants', 'disability_participants',
        'topic', 'naz_promoted', 'slacc', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'naz_promoted' => 'boolean',
            'slacc' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
    }

    public function pulseSurveys(): HasMany
    {
        return $this->hasMany(PulseSurvey::class, 'outreach_id');
    }

}
