<?php

namespace App\Modules\Question\Actions;

use App\Actions\BaseDeleteAction;
use App\Modules\Question\Models\Question;

class DeleteQuestionAction extends BaseDeleteAction
{
    protected function modelClass(): string
    {
        return Question::class;
    }
}
