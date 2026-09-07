<?php

use App\Modules\AcceptedStudent\Controllers\AcceptedStudentController;
use Illuminate\Support\Facades\Route;

Route::controller(AcceptedStudentController::class)->group(function () {
    // Public showcase — only published records, ordered by custom exam points.
    Route::get('accepted-students', 'index')->withoutMiddleware('auth:sanctum');

    // Admin management — full control (incl. hidden records, create/update/delete).
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('accepted-students', 'indexAdmin');
        Route::get('accepted-students/{acceptedStudent}', 'show');
        Route::post('accepted-students', 'store');
        Route::put('accepted-students/{acceptedStudent}', 'update');
        Route::delete('accepted-students/{acceptedStudent}', 'delete');
    });
});
