<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('admin/dashboard/stats', [App\Http\Controllers\DashboardController::class, 'stats'])->middleware(['auth:sanctum', 'admin']);
Route::get('admin/users', [App\Modules\User\Controllers\AdminUserController::class, 'index'])->middleware(['auth:sanctum', 'admin']);
Route::get('admin/users/{user}', [App\Modules\User\Controllers\AdminUserController::class, 'show'])->middleware(['auth:sanctum', 'admin']);
Route::get('admin/users/pending', [App\Modules\User\Controllers\AdminUserController::class, 'pending'])->middleware(['auth:sanctum', 'admin']);
Route::post('admin/users/{user}/approve', [App\Modules\User\Controllers\AdminUserController::class, 'approve'])->middleware(['auth:sanctum', 'admin']);
Route::post('admin/users/{user}/password', [App\Modules\User\Controllers\AdminUserController::class, 'changePassword'])->middleware(['auth:sanctum', 'admin']);

// Fallback for unauthenticated requests from auth middleware
Route::any('/login', function () {
    return apiResponse(statusCode: 401, message: __('crud.unauthenticated'));
});
