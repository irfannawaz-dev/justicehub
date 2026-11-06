<?php

namespace App\Providers;

use App\Models\CaseRecord;
use App\Models\Complaint;
use App\Models\ServiceEncounter;
use App\Observers\CacheInvalidationObserver;
use App\Policies\CaseRecordPolicy;
use Illuminate\Support\Facades\Gate;
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

        Gate::policy(CaseRecord::class, CaseRecordPolicy::class);

        // ── Object-authorized route model binding ───────────────────
        Route::bind('case', function ($value) {
            $case = CaseRecord::findOrFail($value);
            $user = auth()->user();
            if (! $user) {
                return $case;
            }

            $ability = request()->isMethodSafe() ? 'view' : 'update';
            Gate::forUser($user)->authorize($ability, $case);

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
