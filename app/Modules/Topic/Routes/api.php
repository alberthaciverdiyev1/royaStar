<?php

use App\Modules\Topic\Controllers\TopicController;
use Illuminate\Support\Facades\Route;

Route::controller(TopicController::class)->group(function () {
    Route::get('topics', 'index')->withoutMiddleware('auth:sanctum');
    Route::get('topics/{topic}', 'show')->withoutMiddleware('auth:sanctum');

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::post('topics', 'store');
        Route::put('topics/{topic}', 'update');
        Route::delete('topics/{topic}', 'delete');
    });
});
