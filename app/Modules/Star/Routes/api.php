<?php

use App\Modules\Star\Controllers\StarController;
use Illuminate\Support\Facades\Route;

Route::controller(StarController::class)->group(function () {
    Route::get('stars', 'index')->middleware('auth:sanctum');
    Route::get('stars/{star}', 'show')->middleware('auth:sanctum');
    Route::get('user/stars', 'userStars')->middleware('auth:sanctum');

    Route::prefix('admin')->group(function () {
        Route::put('stars/{star}', 'update')->middleware('admin');
    });
});
