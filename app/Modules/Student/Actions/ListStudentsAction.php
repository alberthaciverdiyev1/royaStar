<?php

namespace App\Modules\Student\Actions;

use App\Actions\BaseListAction;
use App\Modules\Student\Models\Student;
use Illuminate\Database\Eloquent\Builder;

class ListStudentsAction extends BaseListAction
{
    protected function modelClass(): string
    {
        return Student::class;
    }

    protected function defaultWith(): array
    {
        return ['user', 'grade', 'city'];
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applyExactFilters($query, ['grade_id', 'city_id'], $params);

        if (!empty($params['school_name'])) {
            $query->where('school_name', 'ilike', '%' . $params['school_name'] . '%');
        }

        if (!empty($params['search'])) {
            $search = trim($params['search']);
            if (mb_strlen($search) >= 2) {
                $query->whereHas('user', fn($q) => $q->where('name', 'ilike', '%' . $search . '%'));
            }
        }
    }
}
