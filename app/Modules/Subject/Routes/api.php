<?php

use App\Modules\Subject\Controllers\SubjectController;
use Illuminate\Support\Facades\Route;

Route::controller(SubjectController::class)->group(function () {
    Route::get('subjects', 'index')->withoutMiddleware('auth:sanctum');
    Route::get('subjects/{subject}', 'show')->withoutMiddleware('auth:sanctum');

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::post('subjects', 'store');
        Route::put('subjects/{subject}', 'update');
        Route::delete('subjects/{subject}', 'delete');
    });
});
