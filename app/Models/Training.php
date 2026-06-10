<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Training extends Model
{
    protected $fillable = [
        'code', 'name', 'category', 'mandatory',
        'refresh', 'audience', 'description', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'mandatory' => 'boolean',
            'audience' => 'array',
            'meta' => 'array',
        ];
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'staff_trainings')
            ->withPivot('completed_on', 'expires', 'delivered_by', 'certificate_ref')
            ->withTimestamps();
    }
}
