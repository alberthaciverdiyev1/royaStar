<?php

namespace App\Modules\Quiz\Actions;

use App\Actions\BaseShowAction;
use App\Modules\Quiz\Models\Quiz;

class ShowQuizAction extends BaseShowAction
{
    protected function modelClass(): string
    {
        return Quiz::class;
    }

    protected function defaultWith(): array
    {
        return ['lesson', 'topic', 'questions'];
    }
}
