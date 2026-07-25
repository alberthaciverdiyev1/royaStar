<?php

namespace App\Modules\Topic\Actions;

use App\Actions\BaseStoreAction;
use App\Modules\Topic\Models\Topic;
use Illuminate\Database\Eloquent\Model;

class StoreTopicAction extends BaseStoreAction
{
    private array $gradeIds = [];

    protected function modelClass(): string
    {
        return Topic::class;
    }

    protected function beforeCreate(array $data): array
    {
        $this->gradeIds = $data['grade_ids'] ?? [];
        unset($data['grade_ids']);

        return $data;
    }

    protected function afterCreate(Model $model): void
    {
        if (!empty($this->gradeIds)) {
            $model->grades()->sync($this->gradeIds);
        }
    }
}
