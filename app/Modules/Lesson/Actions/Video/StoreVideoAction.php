<?php

namespace App\Modules\Lesson\Actions\Video;

use App\Actions\BaseStoreAction;
use App\Modules\Lesson\Models\Video;

class StoreVideoAction extends BaseStoreAction
{
    protected function modelClass(): string
    {
        return Video::class;
    }
}
