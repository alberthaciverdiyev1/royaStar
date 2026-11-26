<?php

namespace App\Modules\Quiz\Actions;

use App\Actions\BaseStoreAction;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Quiz\Models\Quiz;

class StoreQuizAction extends BaseStoreAction
{
    private array $questionIds = [];

    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

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
        if (! empty($this->questionIds)) {
            $questions = [];
            foreach ($this->questionIds as $i => $id) {
                $questions[$id] = ['order' => $i + 1];
            }
            $model->questions()->sync($questions);
            $model->load('questions');
        }

        $this->notificationService->notifyQuiz($model);
    }
}
