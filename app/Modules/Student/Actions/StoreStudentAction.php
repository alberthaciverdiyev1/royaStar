<?php

namespace App\Modules\Student\Actions;

use App\Actions\BaseStoreAction;
use App\Modules\Student\Models\Student;

class StoreStudentAction extends BaseStoreAction
{
    protected function modelClass(): string
    {
        return Student::class;
    }
}
