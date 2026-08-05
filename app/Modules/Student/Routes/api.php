<?php

use App\Modules\Student\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::controller(StudentController::class)->group(function () {
    // Student directory is admin-only — it exposes grade/city/school/birth data.
    Route::get('students', 'index')->middleware('admin');
    Route::get('students/{student}', 'show')->middleware('admin');

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::post('students', 'store');
        Route::put('students/{student}', 'update');
        Route::delete('students/{student}', 'delete');
    });
});
