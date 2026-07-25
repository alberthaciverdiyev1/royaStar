<?php

namespace App\Modules\Quiz\Actions;

use App\Actions\BaseDeleteAction;
use App\Modules\Quiz\Models\Quiz;

class DeleteQuizAction extends BaseDeleteAction
{
    protected function modelClass(): string
    {
        return Quiz::class;
    }
}
