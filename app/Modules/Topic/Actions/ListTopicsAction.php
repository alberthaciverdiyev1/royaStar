<?php

namespace App\Modules\Topic\Actions;

use App\Actions\BaseListAction;
use App\Modules\Topic\Models\Topic;
use Illuminate\Database\Eloquent\Builder;

class ListTopicsAction extends BaseListAction
{
    protected function modelClass(): string
    {
        return Topic::class;
    }

    protected function defaultWith(): array
    {
        return ['grades'];
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applyExactFilters($query, ['difficulty_level'], $params);
        $this->applySearch($query, ['name'], $params);
        $this->applyDateRangeFilter($query, 'created_at', $params);

        if (!empty($params['grade_ids'])) {
            $gradeIds = is_array($params['grade_ids']) ? $params['grade_ids'] : explode(',', $params['grade_ids']);
            $query->whereHas('grades', fn($q) => $q->whereIn('grade_id', $gradeIds));
        }
    }
}
