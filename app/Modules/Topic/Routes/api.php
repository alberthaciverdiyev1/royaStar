<?php

use App\Modules\Topic\Controllers\TopicController;
use Illuminate\Support\Facades\Route;

Route::controller(TopicController::class)->group(function () {
    Route::get('subjects/{subject}/topics', 'index')->withoutMiddleware('auth:sanctum');
    Route::get('subjects/{subject}/topics/{topic}', 'show')->withoutMiddleware('auth:sanctum');

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::post('subjects/{subject}/topics', 'store');
        Route::put('subjects/{subject}/topics/{topic}', 'update');
        Route::delete('subjects/{subject}/topics/{topic}', 'delete');
    });
});
