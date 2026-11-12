<?php

namespace App\Modules\Music\Actions;

use App\Actions\BaseDeleteAction;
use App\Modules\Music\Models\MusicTrack;

class DeleteMusicTrackAction extends BaseDeleteAction
{
    protected function modelClass(): string
    {
        return MusicTrack::class;
    }
}
