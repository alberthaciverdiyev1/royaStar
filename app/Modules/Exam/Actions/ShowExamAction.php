<?php

namespace App\Modules\Exam\Actions;

use App\Actions\BaseShowAction;
use App\Modules\Exam\Models\Exam;

class ShowExamAction extends BaseShowAction
{
    protected function modelClass(): string
    {
        return Exam::class;
    }

    protected function defaultWith(): array
    {
        return ['grade', 'questions'];
    }
}
