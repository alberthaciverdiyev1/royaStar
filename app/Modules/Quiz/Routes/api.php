<?php

use App\Modules\Quiz\Controllers\QuizController;
use App\Modules\Quiz\Controllers\StudentQuizController;
use Illuminate\Support\Facades\Route;

Route::controller(QuizController::class)->group(function () {
    Route::get('quizzes', 'index')->middleware('auth:sanctum');
    Route::get('quizzes/{quiz}', 'show')->middleware('auth:sanctum');

    Route::prefix('admin')->group(function () {
        Route::post('quizzes', 'store')->middleware('admin');
        Route::put('quizzes/{quiz}', 'update')->middleware('admin');
        Route::delete('quizzes/{quiz}', 'delete')->middleware('admin');
    });
});

Route::controller(StudentQuizController::class)->group(function () {
    Route::post('quizzes/{quiz}/start', 'start')->middleware('auth:sanctum');
    Route::post('quizzes/{quiz}/submit', 'submit')->middleware('auth:sanctum');
    Route::get('quizzes/{quiz}/result', 'result')->middleware('auth:sanctum');
});
