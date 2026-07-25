<?php

namespace App\Modules\Topic\Actions;

use App\Actions\BaseDeleteAction;
use App\Modules\Topic\Models\Topic;

class DeleteTopicAction extends BaseDeleteAction
{
    protected function modelClass(): string
    {
        return Topic::class;
    }
}
