<?php

namespace App\Modules\City\Actions;

use App\Actions\BaseListAction;
use App\Modules\City\Models\City;
use Illuminate\Database\Eloquent\Builder;

class ListCitiesAction extends BaseListAction
{
    protected function modelClass(): string
    {
        return City::class;
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applySearch($query, ['name'], $params);
    }
}
