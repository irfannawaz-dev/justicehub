<?php

namespace App\Models;

use App\Enums\ComplaintSeverity;
use App\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
    protected $fillable = [
        'complaint_uid', 'case_id', 'submitted_date', 'submitted_by',
        'is_anonymous', 'channel', 'category', 'severity', 'sla_days',
        'description', 'hub_id', 'assigned_to', 'status',
        'resolved_date', 'resolution', 'client_satisfied', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'severity' => ComplaintSeverity::class,
            'status' => ComplaintStatus::class,
            'submitted_date' => 'date',
            'resolved_date' => 'date',
            'is_anonymous' => 'boolean',
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

    public function actions(): HasMany
    {
        return $this->hasMany(ComplaintAction::class)->orderBy('date');
    }

    public function scopeForHub($query, ?string $hubId)
    {
        if ($hubId && $hubId !== 'all') {
            $query->where('hub_id', $hubId);
        }
        return $query;
    }

    public function isOverdue(): bool
    {
        if ($this->status === ComplaintStatus::Resolved) return false;
        $deadline = $this->submitted_date->addDays($this->sla_days);
        return now()->gt($deadline);
    }

    public function daysRemaining(): int
    {
        $deadline = $this->submitted_date->addDays($this->sla_days);
        return (int) now()->diffInDays($deadline, false);
    }
}
