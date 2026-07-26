<?php

namespace App\Modules\Exam\Actions;

use App\Actions\BaseListAction;
use App\Modules\Exam\Models\Exam;
use Illuminate\Database\Eloquent\Builder;

class ListExamsAction extends BaseListAction
{
    protected function modelClass(): string
    {
        return Exam::class;
    }

    protected function defaultWith(): array
    {
        return ['grade'];
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applySearch($query, ['name'], $params);
        $this->applyExactFilters($query, ['grade_id', 'type'], $params);
    }
}
