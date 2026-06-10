<?php

namespace App\Models;

use App\Traits\HasHubScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use HasHubScope;
    protected $table = 'staff';

    protected $fillable = [
        'staff_uid', 'user_id', 'name', 'initials', 'role',
        'hub_id', 'status', 'joined_date', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'joined_date' => 'date',
            'meta' => 'array',
        ];
    }

    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainings(): BelongsToMany
    {
        return $this->belongsToMany(Training::class, 'staff_trainings')
            ->withPivot('completed_on', 'expires', 'delivered_by', 'certificate_ref')
            ->withTimestamps();
    }

    public function serviceEncounters(): HasMany
    {
        return $this->hasMany(ServiceEncounter::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
