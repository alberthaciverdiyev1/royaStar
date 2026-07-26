<?php

namespace App\Modules\Exam\Actions;

use App\Actions\BaseDeleteAction;
use App\Modules\Exam\Models\Exam;

class DeleteExamAction extends BaseDeleteAction
{
    protected function modelClass(): string
    {
        return Exam::class;
    }
}
