<?php

namespace App\Modules\AcceptedStudent\Actions;

use App\Actions\BaseListAction;
use App\Modules\AcceptedStudent\Models\AcceptedStudent;
use Illuminate\Database\Eloquent\Builder;

class ListAcceptedStudentsAction extends BaseListAction
{
    protected function modelClass(): string
    {
        return AcceptedStudent::class;
    }

    protected function defaultWith(): array
    {
        return ['user'];
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applySearch($query, ['name', 'surname'], $params);
        $this->applyExactFilters($query, ['is_active', 'user_id'], $params);
    }
}
