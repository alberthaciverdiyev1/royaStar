<?php

namespace App\Modules\Music\Actions;

use App\Modules\Music\Models\MusicTrack;

class ShowMusicTrackAction
{
    public function execute(int $id): MusicTrack
    {
        return MusicTrack::query()->findOrFail($id);
    }
}
