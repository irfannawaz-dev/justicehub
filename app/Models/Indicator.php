<?php

namespace App\Models;

use App\Enums\IndicatorLevel;
use App\Enums\IndicatorCadence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Indicator extends Model
{
    protected $fillable = [
        'code', 'level', 'name', 'priority', 'cadence',
        'target', 'actual', 'unit', 'type', 'is_inverse', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'target' => 'decimal:2',
            'actual' => 'decimal:2',
            'is_inverse' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(IndicatorSnapshot::class, 'indicator_code', 'code')
            ->orderBy('month_iso');
    }

    public function ragStatus(): string
    {
        if ($this->target == 0) return 'green';
        $pct = $this->is_inverse
            ? ($this->target / max($this->actual, 0.01)) * 100
            : ($this->actual / $this->target) * 100;
        $green = config('justice_hub.rag_thresholds.green', 90);
        $amber = config('justice_hub.rag_thresholds.amber', 70);
        if ($pct >= $green) return 'green';
        if ($pct >= $amber) return 'amber';
        return 'red';
    }
}
