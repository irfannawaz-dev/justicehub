<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Hub extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'name', 'district', 'province', 'address', 'phone', 'phone2',
        'tier', 'staff_count', 'is_active', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function cases(): HasMany
    {
        return $this->hasMany(CaseRecord::class, 'hub_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class, 'hub_id');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'hub_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class, 'hub_id');
    }

    public function outreachActivities(): HasMany
    {
        return $this->hasMany(OutreachActivity::class, 'hub_id');
    }

    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'hub_partner');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'hub_id');
    }
}
