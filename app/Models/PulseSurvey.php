<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PulseSurvey extends Model
{
    protected $fillable = [
        'pulse_uid', 'outreach_id', 'session', 'date',
        'respondent_count', 'pre_score', 'post_score',
        'will_apply', 'would_recommend_pct',
        'demographics', 'comment', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'pre_score' => 'decimal:2',
            'post_score' => 'decimal:2',
            'would_recommend_pct' => 'decimal:2',
            'demographics' => 'array',
            'meta' => 'array',
        ];
    }

    public function outreachActivity(): BelongsTo
    {
        return $this->belongsTo(OutreachActivity::class, 'outreach_id');
    }
}
