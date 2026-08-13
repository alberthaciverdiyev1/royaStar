<?php

use App\Modules\Setting\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('settings', [SettingController::class, 'show'])->withoutMiddleware('auth:sanctum');

Route::prefix('admin')->middleware('admin')->group(function () {
    Route::put('settings', [SettingController::class, 'update']);
    Route::get('website-texts', [SettingController::class, 'texts']);
    Route::put('website-texts', [SettingController::class, 'updateTexts']);
});
