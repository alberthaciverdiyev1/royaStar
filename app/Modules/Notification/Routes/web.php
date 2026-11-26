<?php

use App\Modules\Notification\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::get('notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    Route::post('push/subscribe', [NotificationController::class, 'subscribe'])->name('push.subscribe');
    Route::delete('push/unsubscribe', [NotificationController::class, 'unsubscribe'])->name('push.unsubscribe');
});
