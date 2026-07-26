<?php

namespace App\Modules\Question\Actions;

use App\Actions\BaseListAction;
use App\Modules\Question\Models\Question;
use Illuminate\Database\Eloquent\Builder;

class ListQuestionsAction extends BaseListAction
{
    protected function modelClass(): string
    {
        return Question::class;
    }

    protected function defaultWith(): array
    {
        return ['lesson'];
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applyExactFilters($query, ['lesson_id'], $params);

        if (!empty($params['lesson_ids'])) {
            $ids = is_array($params['lesson_ids'])
                ? $params['lesson_ids']
                : explode(',', $params['lesson_ids']);
            $query->whereIn('lesson_id', $ids);
        }

        $this->applyExactFilters($query, ['type', 'difficulty_level'], $params);
        $this->applySearch($query, ['question'], $params);
    }
}
