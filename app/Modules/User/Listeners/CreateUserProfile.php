<?php

namespace App\Modules\User\Listeners;

use App\Modules\Parent\Models\Family;
use App\Modules\Student\Models\Student;
use App\Modules\Teacher\Models\Teacher;
use App\Modules\User\Events\UserRegistered;

class CreateUserProfile
{
    public function handle(UserRegistered $event): void
    {
        $data = $event->data;

        match ($data['type'] ?? 'student') {
            'teacher' => Teacher::create([
                'user_id' => $event->user->id,
                'city_id' => $data['teacher']['city_id'] ?? null,
            ]),
            'student' => Student::create([
                'user_id' => $event->user->id,
                'grade_id' => $data['student']['grade_id'] ?? null,
                'city_id' => $data['student']['city_id'] ?? null,
            ]),
            'parent' => Family::create(['user_id' => $event->user->id]),
            default => null,
        };
    }
}
