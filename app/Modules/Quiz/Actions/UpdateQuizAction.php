<?php

namespace App\Modules\Quiz\Actions;

use App\Actions\BaseUpdateAction;
use App\Modules\Quiz\Models\Quiz;

class UpdateQuizAction extends BaseUpdateAction
{
    protected function modelClass(): string
    {
        return Quiz::class;
    }
}
