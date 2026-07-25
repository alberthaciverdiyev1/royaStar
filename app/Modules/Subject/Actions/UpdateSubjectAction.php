<?php

namespace App\Modules\Subject\Actions;

use App\Actions\BaseUpdateAction;
use App\Modules\Subject\Models\Subject;

class UpdateSubjectAction extends BaseUpdateAction
{
    protected function modelClass(): string
    {
        return Subject::class;
    }
}
