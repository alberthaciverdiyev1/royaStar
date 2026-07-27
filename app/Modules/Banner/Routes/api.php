<?php

use App\Modules\Banner\Controllers\BannerController;
use Illuminate\Support\Facades\Route;

Route::controller(BannerController::class)->group(function () {
    Route::get('banners', 'index')->withoutMiddleware('auth:sanctum');
    Route::get('banners/{banner}', 'show');

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::post('banners', 'store');
        Route::put('banners/{banner}', 'update');
        Route::delete('banners/{banner}', 'delete');
    });
});
