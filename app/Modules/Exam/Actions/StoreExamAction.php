<?php

namespace App\Modules\Exam\Actions;

use App\Actions\BaseStoreAction;
use App\Modules\Exam\Models\Exam;
use App\Modules\Notification\Services\NotificationService;

class StoreExamAction extends BaseStoreAction
{
    private array $questionIds = [];

    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    protected function modelClass(): string
    {
        return Exam::class;
    }

    protected function beforeCreate(array $data): array
    {
        $this->questionIds = $data['question_ids'] ?? [];
        unset($data['question_ids']);

        return $data;
    }

    protected function afterCreate($model): void
    {
        if (! empty($this->questionIds)) {
            $questions = [];
            foreach ($this->questionIds as $i => $id) {
                $questions[$id] = ['order' => $i + 1];
            }
            $model->questions()->sync($questions);
            $model->load('questions');
        }

        $this->notificationService->notifyExam($model);
    }
}
