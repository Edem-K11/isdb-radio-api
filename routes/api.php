<?php

use App\Http\Controllers\Api\V1\AppConfigController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\EpisodeController;
use App\Http\Controllers\Api\V1\StreamController;
use Illuminate\Support\Facades\Route;

/*
| Public, read-only API consumed by the Radio ISDB mobile app.
| No authentication: everything here is meant to be world-readable.
| Throttled to 60 requests/minute per client IP.
*/
Route::middleware('throttle:60,1')->prefix('v1')->group(function (): void {
    Route::get('stream', [StreamController::class, 'show']);

    Route::get('episodes', [EpisodeController::class, 'index']);
    Route::get('episodes/{slug}', [EpisodeController::class, 'show']);
    Route::post('episodes/{slug}/play', [EpisodeController::class, 'play']);

    Route::get('categories', [CategoryController::class, 'index']);

    Route::get('app-config', [AppConfigController::class, 'show']);
});
