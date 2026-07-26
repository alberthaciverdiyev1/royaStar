<?php

namespace App\Http\Controllers;

use App\Modules\City\Models\City;
use App\Modules\Grade\Models\Grade;
use App\Modules\Lesson\Models\Lesson;
use App\Modules\Student\Models\Student;
use App\Modules\User\Models\User;

class DashboardController extends Controller
{
    public function stats()
    {
        return apiResponse(data: [
            'cities' => City::count(),
            'grades' => Grade::count(),
            'lessons' => Lesson::count(),
            'students' => Student::count(),
            'teachers' => User::role('teacher')->count(),
            'users' => User::count(),
        ]);
    }
}
