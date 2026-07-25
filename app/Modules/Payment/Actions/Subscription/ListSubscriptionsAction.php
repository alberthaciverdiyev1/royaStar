<?php

namespace App\Modules\Payment\Actions\Subscription;

use App\Actions\BaseListAction;
use App\Modules\Payment\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;

class ListSubscriptionsAction extends BaseListAction
{
    protected function modelClass(): string
    {
        return Subscription::class;
    }

    protected function defaultWith(): array
    {
        return ['plan'];
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applyExactFilters($query, ['school_id', 'teacher_id', 'status'], $params);
    }
}
