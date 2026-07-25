<?php

namespace App\Modules\Lesson\Actions\Video;

use App\Actions\BaseDeleteAction;
use App\Modules\Lesson\Models\Video;

class DeleteVideoAction extends BaseDeleteAction
{
    protected function modelClass(): string
    {
        return Video::class;
    }
}
