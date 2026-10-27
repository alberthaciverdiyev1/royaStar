<?php

use App\Modules\Popup\Controllers\PopupController;
use Illuminate\Support\Facades\Route;

Route::controller(PopupController::class)->group(function () {
    // Public — random published popup for the website frontend.
    Route::get('popups/random', 'random')->withoutMiddleware('auth:sanctum');

    // Admin management — full control (incl. hidden popups).
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('popups', 'index');
        Route::get('popups/{popup}', 'show')->whereNumber('popup');
        Route::post('popups', 'store');
        Route::put('popups/{popup}', 'update')->whereNumber('popup');
        Route::delete('popups/{popup}', 'delete')->whereNumber('popup');
    });
});
