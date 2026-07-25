<?php

namespace App\Modules\Subject\Actions;

use App\Actions\BaseDeleteAction;
use App\Modules\Subject\Models\Subject;

class DeleteSubjectAction extends BaseDeleteAction
{
    protected function modelClass(): string
    {
        return Subject::class;
    }
}
