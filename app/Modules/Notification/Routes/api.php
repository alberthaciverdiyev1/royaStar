<?php

use App\Modules\Notification\Controllers\AdminNotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('admin')->group(function () {
    Route::get('notifications', [AdminNotificationController::class, 'index']);
    Route::delete('notifications/{notification}', [AdminNotificationController::class, 'destroy'])
        ->whereNumber('notification');

    Route::get('notification-settings', [AdminNotificationController::class, 'settings']);
    Route::put('notification-settings', [AdminNotificationController::class, 'updateSettings']);
});
