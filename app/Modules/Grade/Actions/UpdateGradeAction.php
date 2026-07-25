<?php

namespace App\Modules\Grade\Actions;

use App\Actions\BaseUpdateAction;
use App\Modules\Grade\Models\Grade;

class UpdateGradeAction extends BaseUpdateAction
{
    protected function modelClass(): string
    {
        return Grade::class;
    }
}
