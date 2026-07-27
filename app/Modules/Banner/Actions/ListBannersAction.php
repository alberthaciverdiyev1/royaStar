<?php

namespace App\Modules\Banner\Actions;

use App\Actions\BaseListAction;
use App\Modules\Banner\Models\Banner;
use Illuminate\Database\Eloquent\Builder;

class ListBannersAction extends BaseListAction
{
    protected function modelClass(): string
    {
        return Banner::class;
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applySearch($query, ['title', 'subtitle'], $params);
    }
}
