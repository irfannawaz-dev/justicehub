<?php

namespace App\Models;

use App\Traits\HasHubScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasHubScope;
    protected $table = 'feedback';

    protected $fillable = [
        'feedback_uid', 'case_id', 'client_name', 'is_anonymous',
        'hub_id', 'service', 'lawyer', 'date', 'channel',
        'score_overall', 'score_helpfulness', 'score_respect',
        'understood_rights', 'would_recommend', 'comment',
        'consent_to_share', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_anonymous' => 'boolean',
            'consent_to_share' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function caseRecord(): BelongsTo
    {
        return $this->belongsTo(CaseRecord::class, 'case_id');
    }

    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
    }

}
