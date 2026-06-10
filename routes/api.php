<?php

use App\Http\Controllers\Api\ClientLookupController;
use App\Http\Controllers\Api\LookupController;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (JSON endpoints for AJAX)
|--------------------------------------------------------------------------
| These routes are loaded within the "auth" middleware group via web session.
| They return JSON for Alpine.js AJAX calls (search, filters, chart data).
*/

Route::middleware(['auth', 'hub.scope'])->group(function () {
    Route::get('/search', [SearchController::class, 'search'])
        ->middleware('throttle:30,1')   // 30 requests per minute per user
        ->name('api.search');

    Route::get('/lookups/{group}', [LookupController::class, 'index'])
        ->middleware('throttle:60,1')   // 60 requests per minute per user
        ->name('api.lookups');

    Route::get('/client-lookup', [ClientLookupController::class, 'lookup'])
        ->middleware('throttle:30,1')
        ->name('api.client-lookup');
});
