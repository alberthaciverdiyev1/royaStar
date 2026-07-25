<?php

namespace App\Modules\Question\Actions;

use App\Actions\BaseShowAction;
use App\Modules\Question\Models\Question;

class ShowQuestionAction extends BaseShowAction
{
    protected function modelClass(): string
    {
        return Question::class;
    }

    protected function defaultWith(): array
    {
        return ['topic'];
    }
}
