<?php

namespace App\Modules\Popup\Actions;

use App\Actions\BaseListAction;
use App\Modules\Popup\Models\Popup;
use Illuminate\Database\Eloquent\Builder;

class ListPopupsAction extends BaseListAction
{
    protected function modelClass(): string
    {
        return Popup::class;
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applySearch($query, ['title', 'content'], $params);
        $this->applyExactFilters($query, ['is_active'], $params);
    }
}
