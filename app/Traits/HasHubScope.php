<?php

namespace App\Traits;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;

/**
 * Adds hub-scoped filtering to any model that has a `hub_id` column.
 *
 * Respects user roles:
 * - Head & M&E Lead → can see all hubs (global scope)
 * - Hub Admin, Data Entry, Complaint Investigator → forced to their hub_id
 * - Viewer → depends on hub_id (null = all hubs, set = single hub)
 */
trait HasHubScope
{
    /**
     * Scope to a specific hub. Pass null or 'all' to skip filtering.
     */
    public function scopeForHub(Builder $query, ?string $hubId): Builder
    {
        if ($hubId && $hubId !== 'all') {
            return $query->where($this->getTable() . '.hub_id', $hubId);
        }

        return $query;
    }

    /**
     * Scope based on the currently authenticated user's role and hub assignment.
     * Automatically determines the correct hub filter.
     */
    public function scopeForAuthUser(Builder $query): Builder
    {
        $user = auth()->user();

        if (!$user) {
            return $query;
        }

        // Global roles: use session-selected hub (or all)
        if ($user->canSeeAllHubs()) {
            $activeHub = session('active_hub', 'all');
            return $this->scopeForHub($query, $activeHub);
        }

        // Hub-scoped roles: always filter to their assigned hub
        return $query->where($this->getTable() . '.hub_id', $user->hub_id);
    }
}
