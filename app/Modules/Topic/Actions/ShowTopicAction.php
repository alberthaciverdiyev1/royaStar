<?php

namespace App\Modules\Topic\Actions;

use App\Actions\BaseShowAction;
use App\Modules\Topic\Models\Topic;

class ShowTopicAction extends BaseShowAction
{
    protected function modelClass(): string
    {
        return Topic::class;
    }

    protected function defaultWith(): array
    {
        return ['lessons', 'grades'];
    }
}
