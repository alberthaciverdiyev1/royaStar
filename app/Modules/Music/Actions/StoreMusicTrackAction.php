<?php

namespace App\Modules\Music\Actions;

use App\Actions\BaseStoreAction;
use App\Modules\Music\Models\MusicTrack;

class StoreMusicTrackAction extends BaseStoreAction
{
    use HandlesCover;

    protected function modelClass(): string
    {
        return MusicTrack::class;
    }

    protected function beforeCreate(array $data): array
    {
        return $this->persistCover($data);
    }
}
