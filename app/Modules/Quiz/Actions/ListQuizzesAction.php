<?php

namespace App\Modules\Quiz\Actions;

use App\Actions\BaseListAction;
use App\Modules\Quiz\Models\Quiz;
use Illuminate\Database\Eloquent\Builder;

class ListQuizzesAction extends BaseListAction
{
    protected function modelClass(): string
    {
        return Quiz::class;
    }

    protected function defaultWith(): array
    {
        return ['lesson'];
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applySearch($query, ['name'], $params);
        $this->applyExactFilters($query, ['lesson_id', 'type'], $params);
    }
}
