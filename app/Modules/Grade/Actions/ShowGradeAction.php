<?php

namespace App\Modules\Grade\Actions;

use App\Actions\BaseShowAction;
use App\Modules\Grade\Models\Grade;

class ShowGradeAction extends BaseShowAction
{
    protected function modelClass(): string
    {
        return Grade::class;
    }
}
