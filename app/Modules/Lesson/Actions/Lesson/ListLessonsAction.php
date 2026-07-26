<?php

namespace App\Modules\Lesson\Actions\Lesson;

use App\Actions\BaseListAction;
use App\Modules\Lesson\Models\Lesson;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ListLessonsAction extends BaseListAction
{
    protected function modelClass(): string
    {
        return Lesson::class;
    }

    protected function defaultWith(): array
    {
        return ['topic', 'view', 'videos'];
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applyExactFilters($query, ['topic_id'], $params);
        $this->applySearch($query, ['name'], $params);
    }

    public function execute(array $params = []): LengthAwarePaginator
    {
        $paginator = parent::execute($params);

        if ($student = auth()->user()?->student) {
            $paginator->getCollection()->load([
                'studentLessons' => fn($q) => $q->where('student_id', $student->id),
            ]);
        }

        return $paginator;
    }
}
