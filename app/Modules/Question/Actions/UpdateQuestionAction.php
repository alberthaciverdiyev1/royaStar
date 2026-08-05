<?php

namespace App\Modules\Question\Actions;

use App\Actions\BaseUpdateAction;
use App\Modules\Question\Models\Question;

class UpdateQuestionAction extends BaseUpdateAction
{
    protected function modelClass(): string
    {
        return Question::class;
    }

    protected function beforeUpdate(array $data): array
    {
        processQuestionMedia($data);
        normalizeQuestionLocales($data);

        return $data;
    }
}
