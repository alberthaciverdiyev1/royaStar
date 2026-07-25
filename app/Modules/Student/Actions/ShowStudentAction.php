<?php

namespace App\Modules\Student\Actions;

use App\Actions\BaseShowAction;
use App\Modules\Student\Models\Student;

class ShowStudentAction extends BaseShowAction
{
    protected function modelClass(): string
    {
        return Student::class;
    }

    protected function defaultWith(): array
    {
        return ['user', 'grade', 'city'];
    }
}
