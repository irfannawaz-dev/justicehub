<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Lookup extends Model
{
    protected $fillable = [
        'group_key', 'value', 'label', 'sort_order',
        'is_active', 'parent_value', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'meta' => 'array',
        ];
    }

    /**
     * Get active options for a group key (cached).
     */
    public static function getOptions(string $groupKey, ?string $parentValue = null): array
    {
        $cacheKey = "lookups.{$groupKey}" . ($parentValue ? ".{$parentValue}" : '');
        $ttl = config('justice_hub.dashboard.lookups_cache_ttl', 86400);

        return Cache::remember($cacheKey, $ttl, function () use ($groupKey, $parentValue) {
            $query = static::where('group_key', $groupKey)
                ->where('is_active', true)
                ->orderBy('sort_order');

            if ($parentValue !== null) {
                $query->where('parent_value', $parentValue);
            }

            return $query->get()->map(fn ($l) => [
                'value' => $l->value,
                'label' => $l->label ?? $l->value,
                'meta'  => $l->meta,
            ])->toArray();
        });
    }

    /**
     * Get a simple value => label map for use in Blade selects.
     */
    public static function selectOptions(string $groupKey, ?string $parentValue = null): array
    {
        return collect(static::getOptions($groupKey, $parentValue))
            ->pluck('label', 'value')
            ->toArray();
    }

    /**
     * Clear all lookup caches.
     */
    public static function clearCache(?string $groupKey = null): void
    {
        if ($groupKey) {
            Cache::forget("lookups.{$groupKey}");
        } else {
            // Clear all lookup keys
            $groups = static::select('group_key')->distinct()->pluck('group_key');
            foreach ($groups as $group) {
                Cache::forget("lookups.{$group}");
            }
        }
    }
}
