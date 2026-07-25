<?php

namespace App\Modules\Topic\Actions;

use App\Actions\BaseUpdateAction;
use App\Modules\Topic\Models\Topic;
use Illuminate\Database\Eloquent\Model;

class UpdateTopicAction extends BaseUpdateAction
{
    private array $gradeIds = [];

    protected function modelClass(): string
    {
        return Topic::class;
    }

    protected function beforeUpdate(array $data): array
    {
        $this->gradeIds = $data['grade_ids'] ?? [];
        unset($data['grade_ids']);

        return $data;
    }

    protected function afterUpdate(Model $model): void
    {
        if (!empty($this->gradeIds)) {
            $model->grades()->sync($this->gradeIds);
        }
    }
}
