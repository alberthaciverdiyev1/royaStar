<?php

namespace App\Modules\Music\Actions;

use App\Actions\BaseUpdateAction;
use App\Modules\Music\Models\MusicTrack;

class UpdateMusicTrackAction extends BaseUpdateAction
{
    use HandlesCover;

    protected function modelClass(): string
    {
        return MusicTrack::class;
    }

    protected function beforeUpdate(array $data): array
    {
        return $this->persistCover($data);
    }
}
