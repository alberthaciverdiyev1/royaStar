<?php

use App\Modules\Payment\Controllers\PlanController;
use App\Modules\Payment\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::controller(PlanController::class)->group(function () {
    Route::get('plans', 'index')->withoutMiddleware('auth:sanctum');
    Route::get('plans/{plan}', 'show')->withoutMiddleware('auth:sanctum');

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::post('plans', 'store');
        Route::put('plans/{plan}', 'update');
        Route::delete('plans/{plan}', 'delete');
    });
});

Route::controller(SubscriptionController::class)->prefix('admin')->group(function () {
    Route::get('subscriptions', 'index')->middleware('admin');
    Route::get('subscriptions/{subscription}', 'show')->middleware('admin');
    Route::post('subscriptions', 'store')->middleware('admin');
});
