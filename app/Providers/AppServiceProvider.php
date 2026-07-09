<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\CaseRecord;
use App\Models\Complaint;
use App\Models\ServiceEncounter;
use App\Observers\CacheInvalidationObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ── Auto-flush dashboard cache when data changes ──
        CaseRecord::observe(CacheInvalidationObserver::class);
        ServiceEncounter::observe(CacheInvalidationObserver::class);

        // ── Hub-scoped route model binding ──────────────────────
        Route::bind('case', function ($value) {
            $case = CaseRecord::findOrFail($value);
            $user = auth()->user();
            if (! $user) return $case;

            // Hub scope: non-global users can only access their hub's cases
            if (! $user->canSeeAllHubs() && $case->hub_id !== $user->hub_id) {
                abort(403, 'You do not have access to this case.');
            }

            // Lawyer: can only access cases assigned to them
            if ($user->role === UserRole::Lawyer && $case->assigned_to !== $user->name) {
                abort(403, 'This case is not assigned to you.');
            }

            // Court Clerk: can only access litigation cases
            if ($user->role === UserRole::CourtClerk) {
                $isLitigation = $case->disposition === 'litigation'
                    || in_array($case->assigned_pathway, ['Representation in Court', 'Court Representation']);
                if (! $isLitigation) {
                    abort(403, 'Court clerks can only access litigation cases.');
                }
            }

            return $case;
        });

        Route::bind('complaint', function ($value) {
            $complaint = Complaint::findOrFail($value);
            $user = auth()->user();
            if ($user && ! $user->canSeeAllHubs() && $complaint->hub_id !== $user->hub_id) {
                abort(403, 'You do not have access to this complaint.');
            }
            return $complaint;
        });
    }
}
