<?php

namespace App\Modules\Subject\Actions;

use App\Actions\BaseListAction;
use App\Modules\Subject\Models\Subject;
use Illuminate\Database\Eloquent\Builder;

class ListSubjectsAction extends BaseListAction
{
    protected function modelClass(): string
    {
        return Subject::class;
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applySearch($query, ['name'], $params);
        $this->applyDateRangeFilter($query, 'created_at', $params);

        if (!empty($params['grade_ids'])) {
            $gradeIds = is_array($params['grade_ids']) ? $params['grade_ids'] : explode(',', $params['grade_ids']);
            $query->whereHas('topics', fn($q) => $q->whereHas('grades', fn($g) => $g->whereIn('grade_id', $gradeIds)));
        }
    }
}
