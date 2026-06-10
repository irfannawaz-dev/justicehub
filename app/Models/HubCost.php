<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HubCost extends Model
{
    protected $fillable = [
        'hub_id', 'quarter', 'cost_per_case',
        'total_operational_cost', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'cost_per_case' => 'decimal:2',
            'total_operational_cost' => 'decimal:2',
            'meta' => 'array',
        ];
    }

    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
    }
}
