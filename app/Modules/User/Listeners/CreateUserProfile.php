<?php

namespace App\Modules\User\Listeners;

use App\Modules\Student\Models\Student;
use App\Modules\User\Events\UserRegistered;

class CreateUserProfile
{
    public function handle(UserRegistered $event): void
    {
        $data = $event->data;

        Student::create([
            'user_id' => $event->user->id,
            'grade_id' => $data['student']['grade_id'] ?? null,
            'city_id' => $data['student']['city_id'] ?? null,
        ]);
    }
}
