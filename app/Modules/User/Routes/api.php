<?php

use App\Modules\User\Controllers\AuthController;
use App\Modules\User\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::withoutMiddleware(['auth:sanctum'])->prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware(['throttle:5,1', 'non-admin']);
    Route::post('login', [AuthController::class, 'login'])->middleware(['throttle:10,1', 'non-admin']);
    Route::post('admin-login', [AuthController::class, 'adminLogin'])->middleware('throttle:10,1');
    Route::post('send-otp', [AuthController::class, 'sendOtp'])->middleware('throttle:3,1');
    Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
});

Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'show']);
    Route::put('/', [ProfileController::class, 'update']);
});
