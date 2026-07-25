<?php

namespace App\Modules\Lesson\Actions\Video;

use App\Actions\BaseShowAction;
use App\Modules\Lesson\Models\Video;

class ShowVideoAction extends BaseShowAction
{
    protected function modelClass(): string
    {
        return Video::class;
    }

    protected function defaultWith(): array
    {
        return ['lesson'];
    }
}
