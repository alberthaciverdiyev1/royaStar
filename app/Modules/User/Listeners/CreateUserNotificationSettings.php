<?php

namespace App\Modules\User\Listeners;

use App\Modules\User\Events\UserRegistered;
use App\Modules\User\Models\NotificationSetting;

class CreateUserNotificationSettings
{
    public function handle(UserRegistered $event): void
    {
        NotificationSetting::create(['user_id' => $event->user->id]);
    }
}
