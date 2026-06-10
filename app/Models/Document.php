<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'document_uid', 'case_id', 'type', 'name',
        'added_date', 'added_by', 'source', 'status',
        'confidentiality', 'document_ref', 'pages',
        'file_path', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'added_date' => 'date',
            'meta' => 'array',
        ];
    }

    public function caseRecord(): BelongsTo
    {
        return $this->belongsTo(CaseRecord::class, 'case_id');
    }
}
