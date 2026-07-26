<?php

namespace App\Modules\Exam\Actions;

use App\Actions\BaseUpdateAction;
use App\Modules\Exam\Models\Exam;

class UpdateExamAction extends BaseUpdateAction
{
    private array $questionIds = [];
    private bool $hasQuestionIds = false;

    protected function modelClass(): string
    {
        return Exam::class;
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
            $questions = [];
            foreach ($this->questionIds as $i => $id) {
                $questions[$id] = ['order' => $i + 1];
            }
            $model->questions()->sync($questions);
            $model->load('questions');
        }
    }
}
