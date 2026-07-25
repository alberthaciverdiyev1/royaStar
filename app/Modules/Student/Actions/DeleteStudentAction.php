<?php

namespace App\Modules\Student\Actions;

use App\Actions\BaseDeleteAction;
use App\Modules\Student\Models\Student;

class DeleteStudentAction extends BaseDeleteAction
{
    protected function modelClass(): string
    {
        return Student::class;
    }
}
