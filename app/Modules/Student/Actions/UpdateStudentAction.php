<?php

namespace App\Modules\Student\Actions;

use App\Actions\BaseUpdateAction;
use App\Modules\Student\Models\Student;

class UpdateStudentAction extends BaseUpdateAction
{
    protected function modelClass(): string
    {
        return Student::class;
    }
}
