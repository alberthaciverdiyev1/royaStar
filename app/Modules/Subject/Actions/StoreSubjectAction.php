<?php

namespace App\Modules\Subject\Actions;

use App\Actions\BaseStoreAction;
use App\Modules\Subject\Models\Subject;

class StoreSubjectAction extends BaseStoreAction
{
    protected function modelClass(): string
    {
        return Subject::class;
    }
}
