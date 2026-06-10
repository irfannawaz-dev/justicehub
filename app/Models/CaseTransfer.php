<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseTransfer extends Model
{
    protected $fillable = [
        'case_id', 'from_assignee', 'to_assignee',
        'transferred_by', 'transfer_date', 'reason',
        'status', 'approved_by', 'decided_at', 'approval_note',
    ];

    protected function casts(): array
    {
        return [
            'transfer_date' => 'date',
            'decided_at'    => 'datetime',
        ];
    }

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseRecord::class, 'case_id');
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
