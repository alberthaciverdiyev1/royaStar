<?php

namespace App\Modules\Music\Actions;

use App\Actions\BaseListAction;
use App\Modules\Music\Models\MusicTrack;
use Illuminate\Database\Eloquent\Builder;

class ListMusicTracksAction extends BaseListAction
{
    protected function modelClass(): string
    {
        return MusicTrack::class;
    }

    protected function defaultOrder(): array
    {
        return ['sort_order' => 'asc', 'id' => 'asc'];
    }

    protected function applyFilters(Builder $query, array $params): void
    {
        $this->applySearch($query, ['title', 'artist', 'url'], $params);
        $this->applyExactFilters($query, ['is_active'], $params);
    }
}
