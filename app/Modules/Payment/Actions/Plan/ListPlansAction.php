<?php

namespace App\Modules\Payment\Actions\Plan;

use App\Actions\BaseListAction;
use App\Modules\Payment\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Builder;

class ListPlansAction extends BaseListAction
{
    protected function modelClass(): string
    {
        return SubscriptionPlan::class;
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applySearch($query, ['name'], $params);
    }
}
