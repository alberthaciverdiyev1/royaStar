<?php

namespace App\Modules\Quiz\Actions;

use App\Actions\BaseStoreAction;
use App\Modules\Quiz\Models\Quiz;

class StoreQuizAction extends BaseStoreAction
{
    private array $questionIds = [];

    protected function modelClass(): string
    {
        return Quiz::class;
    }

    protected function beforeCreate(array $data): array
    {
        $this->questionIds = $data['question_ids'] ?? [];
        unset($data['question_ids']);

        return $data;
    }

    protected function afterCreate($model): void
    {
        if (!empty($this->questionIds)) {
            $model->questions()->attach($this->questionIds);
            $model->load('questions');
        }
    }
}
