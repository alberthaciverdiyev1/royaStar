<?php

namespace App\Modules\Question\Actions;

use App\Actions\BaseStoreAction;
use App\Modules\Question\Models\Question;

class StoreQuestionAction extends BaseStoreAction
{
    protected function modelClass(): string
    {
        return Question::class;
    }

    protected function beforeCreate(array $data): array
    {
        processQuestionImages($data);

        return $data;
    }
}
