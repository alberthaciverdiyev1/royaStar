<?php

use App\Modules\City\Controllers\CityController;
use Illuminate\Support\Facades\Route;

Route::controller(CityController::class)->group(function () {
    Route::get('cities/{city}', 'show');
    Route::get('cities', 'index')->withoutMiddleware('auth:sanctum');

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::post('cities', 'store');
        Route::put('cities/{city}', 'update');
        Route::delete('cities/{city}', 'delete');
    });
});
