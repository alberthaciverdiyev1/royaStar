<?php

namespace App\Modules\Grade\Actions;

use App\Actions\BaseDeleteAction;
use App\Modules\Grade\Models\Grade;

class DeleteGradeAction extends BaseDeleteAction
{
    protected function modelClass(): string
    {
        return Grade::class;
    }
}
