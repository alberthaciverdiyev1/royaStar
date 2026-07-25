<?php

namespace App\Modules\Lesson\Actions\Video;

use App\Actions\BaseUpdateAction;
use App\Modules\Lesson\Models\Video;

class UpdateVideoAction extends BaseUpdateAction
{
    protected function modelClass(): string
    {
        return Video::class;
    }
}
