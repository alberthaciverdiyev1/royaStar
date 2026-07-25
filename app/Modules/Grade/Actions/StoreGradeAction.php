<?php

namespace App\Modules\Grade\Actions;

use App\Actions\BaseStoreAction;
use App\Modules\Grade\Models\Grade;

class StoreGradeAction extends BaseStoreAction
{
    protected function modelClass(): string
    {
        return Grade::class;
    }
}
