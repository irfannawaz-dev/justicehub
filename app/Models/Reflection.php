<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reflection extends Model
{
    protected $fillable = [
        'date', 'staff', 'hub_id', 'title', 'description',
        'key_learning', 'implementation_notes', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'meta' => 'array',
        ];
    }

    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
    }
}
