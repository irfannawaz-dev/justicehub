<?php

namespace App\Models;

use App\Enums\EvidenceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evidence extends Model
{
    protected $table = 'evidence';

    protected $fillable = [
        'evidence_uid', 'type', 'title', 'summary', 'date',
        'verified', 'verified_by', 'verified_date',
        'issuer', 'hub_id', 'document_ref',
        'tags', 'linked_indicator', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'type' => EvidenceType::class,
            'date' => 'date',
            'verified' => 'boolean',
            'verified_date' => 'date',
            'tags' => 'array',
            'meta' => 'array',
        ];
    }

    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
    }

    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }
}
