<?php

use App\Modules\Exam\Controllers\ExamController;
use App\Modules\Exam\Controllers\StudentExamController;
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

Route::controller(StudentExamController::class)->group(function () {
    Route::post('exams/{exam}/start', 'start')->middleware('auth:sanctum');
    Route::post('exams/{exam}/submit', 'submit')->middleware('auth:sanctum');
    Route::get('exams/{exam}/result', 'result')->middleware('auth:sanctum');
});
