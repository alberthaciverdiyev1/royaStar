<?php

namespace App\Modules\Star\Actions;

use App\Actions\BaseListAction;
use App\Modules\Star\Models\Star;
use Illuminate\Database\Eloquent\Builder;

class ListStarsAction extends BaseListAction
{
    protected function modelClass(): string
    {
        return Star::class;
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applySearch($query, ['type'], $params);
    }
}
