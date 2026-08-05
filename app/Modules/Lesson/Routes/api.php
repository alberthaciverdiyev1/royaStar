<?php

use App\Modules\Lesson\Controllers\LessonController;
use App\Modules\Lesson\Controllers\LessonReviewController;
use App\Modules\Lesson\Controllers\VideoController;

use Illuminate\Support\Facades\Route;

Route::controller(LessonController::class)->group(function () {
    Route::get('topics/{topic}/lessons', 'index');
    Route::get('topics/{topic}/lessons/{lesson}', 'show');
    Route::post('topics/{topic}/lessons/{lesson}/progress', 'progress');

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::post('topics/{topic}/lessons', 'store');
        Route::put('topics/{topic}/lessons/{lesson}', 'update');
        Route::delete('topics/{topic}/lessons/{lesson}', 'delete');
    });
});

Route::prefix('admin')->middleware('admin')->controller(LessonReviewController::class)->group(function () {
    Route::get('lesson-reviews', 'index');
});

Route::controller(VideoController::class)->group(function () {
    Route::get('videos', 'index');
    Route::get('videos/{video}', 'show');

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::post('videos', 'store');
        Route::put('videos/{video}', 'update');
        Route::delete('videos/{video}', 'delete');
    });
});
