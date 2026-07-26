<?php

namespace App\Modules\Quiz\Actions;

use App\Actions\BaseUpdateAction;
use App\Modules\Quiz\Models\Quiz;

class UpdateQuizAction extends BaseUpdateAction
{
    private array $questionIds = [];
    private bool $hasQuestionIds = false;

    protected function modelClass(): string
    {
        return Quiz::class;
    }

    protected function beforeUpdate(array $data): array
    {
        $this->hasQuestionIds = array_key_exists('question_ids', $data);
        $this->questionIds = $data['question_ids'] ?? [];
        unset($data['question_ids']);

        return $data;
    }

    protected function afterUpdate($model): void
    {
        if ($this->hasQuestionIds) {
            $model->questions()->sync($this->questionIds);
            $model->load('questions');
        }
    }
}
