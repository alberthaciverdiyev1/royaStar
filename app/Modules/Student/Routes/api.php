<?php

use App\Modules\Student\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::controller(StudentController::class)->group(function () {
    Route::get('students', 'index')->withoutMiddleware('auth:sanctum');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('students/activities', 'activities');
    });

    Route::get('students/{student}', 'show')->withoutMiddleware('auth:sanctum');

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::post('students', 'store');
        Route::put('students/{student}', 'update');
        Route::delete('students/{student}', 'delete');
    });
});
