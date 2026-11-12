<?php

use App\Modules\Music\Controllers\MusicController;
use Illuminate\Support\Facades\Route;

Route::controller(MusicController::class)->group(function () {
    // Public — active playlist for the website frontend.
    Route::get('music', 'publicIndex')->withoutMiddleware('auth:sanctum');

    // Admin management — full control (incl. hidden tracks and settings).
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('music', 'index');
        Route::get('music/settings', 'settings');
        Route::put('music/settings', 'updateSettings');
        Route::get('music/metadata', 'metadata');
        Route::get('music/{music}', 'show')->whereNumber('music');
        Route::post('music', 'store');
        Route::put('music/{music}', 'update')->whereNumber('music');
        Route::delete('music/{music}', 'delete')->whereNumber('music');
    });
});
