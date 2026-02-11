<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MapDataController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Map API Routes (Public - no authentication required for map data)
Route::prefix('map')->name('api.map.')->group(function () {
    // Main map data with filters
    Route::get('/data', [MapDataController::class, 'getMapData'])->name('data');

    // Filter options for dropdowns
    Route::get('/filters', [MapDataController::class, 'getFilterOptions'])->name('filters');

    // Municipality details
    Route::get('/municipality/{municipality}', [MapDataController::class, 'getMunicipalityDetails'])->name('municipality');

    // Timeline data for animation
    Route::get('/timeline', [MapDataController::class, 'getTimelineData'])->name('timeline');

    // Comparison between municipalities
    Route::get('/compare', [MapDataController::class, 'compareData'])->name('compare');

    // Statistics summary
    Route::get('/statistics', [MapDataController::class, 'getStatistics'])->name('statistics');
});
