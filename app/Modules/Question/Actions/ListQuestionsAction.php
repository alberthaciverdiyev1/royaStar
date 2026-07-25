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
        return ['topic'];
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applyExactFilters($query, ['topic_id'], $params);

        if (!empty($params['topic_ids'])) {
            $ids = is_array($params['topic_ids'])
                ? $params['topic_ids']
                : explode(',', $params['topic_ids']);
            $query->whereIn('topic_id', $ids);
        }

        $this->applyExactFilters($query, ['type', 'difficulty_level'], $params);
        $this->applySearch($query, ['question'], $params);
    }
}
