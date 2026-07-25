<?php

use App\Modules\Question\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;

Route::controller(QuestionController::class)->group(function () {

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('questions', 'index');
        Route::get('questions/{question}', 'show');
        Route::post('questions', 'store');
        Route::put('questions/{question}', 'update');
        Route::delete('questions/{question}', 'delete');
    });
});
