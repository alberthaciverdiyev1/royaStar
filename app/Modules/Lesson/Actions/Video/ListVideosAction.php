<?php

namespace App\Modules\Lesson\Actions\Video;

use App\Actions\BaseListAction;
use App\Modules\Lesson\Models\Video;
use Illuminate\Database\Eloquent\Builder;

class ListVideosAction extends BaseListAction
{
    protected function modelClass(): string
    {
        return Video::class;
    }

    protected function defaultWith(): array
    {
        return ['lesson'];
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applyExactFilters($query, ['lesson_id'], $params);
        $this->applySearch($query, ['name'], $params);
    }
}
