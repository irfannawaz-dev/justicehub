<?php

namespace App\Observers;

use App\Services\DashboardMetricsService;
use Illuminate\Database\Eloquent\Model;

/**
 * Automatically flushes the dashboard cache when any observed model is created,
 * updated, or deleted. Attached to CaseRecord and ServiceEncounter so that
 * dashboard/scorecard data stays fresh without manual flush calls.
 */
class CacheInvalidationObserver
{
    public function created(Model $model): void
    {
        DashboardMetricsService::flush();
    }

    public function updated(Model $model): void
    {
        DashboardMetricsService::flush();
    }

    public function deleted(Model $model): void
    {
        DashboardMetricsService::flush();
    }
}
