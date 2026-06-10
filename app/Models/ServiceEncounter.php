<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceEncounter extends Model
{
    protected $fillable = [
        'case_id', 'date', 'type', 'performed_by',
        'staff_id', 'note', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'meta' => 'array',
        ];
    }

    public function caseRecord(): BelongsTo
    {
        return $this->belongsTo(CaseRecord::class, 'case_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
