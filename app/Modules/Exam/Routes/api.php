<?php

use App\Modules\Exam\Controllers\ExamController;
use Illuminate\Support\Facades\Route;

Route::controller(ExamController::class)->group(function () {
    Route::get('exams', 'index')->middleware('auth:sanctum');
    Route::get('exams/{exam}', 'show')->middleware('auth:sanctum');

    Route::prefix('admin')->group(function () {
        Route::post('exams', 'store')->middleware('admin');
        Route::put('exams/{exam}', 'update')->middleware('admin');
        Route::delete('exams/{exam}', 'delete')->middleware('admin');
    });
});
