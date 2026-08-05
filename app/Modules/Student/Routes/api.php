<?php

use App\Modules\Student\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::controller(StudentController::class)->group(function () {
    Route::get('students', 'index');

    Route::get('students/{student}', 'show');

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::post('students', 'store');
        Route::put('students/{student}', 'update');
        Route::delete('students/{student}', 'delete');
    });
});
