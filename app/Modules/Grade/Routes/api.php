<?php

use App\Modules\Grade\Controllers\GradeController;
use Illuminate\Support\Facades\Route;

Route::controller(GradeController::class)->group(function () {
    Route::get('grades', 'index')->withoutMiddleware(['auth:sanctum','admin']);
    Route::get('grades/{grade}', 'show');

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::post('grades', 'store');
        Route::put('grades/{grade}', 'update');
        Route::delete('grades/{grade}', 'delete');
    });
});
