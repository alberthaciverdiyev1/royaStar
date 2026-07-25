<?php

namespace App\Modules\Grade\Actions;

use App\Actions\BaseListAction;
use App\Modules\Grade\Models\Grade;
use Illuminate\Database\Eloquent\Builder;

class ListGradesAction extends BaseListAction
{
    protected function modelClass(): string
    {
        return Grade::class;
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applySearch($query, ['name'], $params);
    }
}
