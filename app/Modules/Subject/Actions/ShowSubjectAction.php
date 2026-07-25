<?php

namespace App\Modules\Subject\Actions;

use App\Actions\BaseShowAction;
use App\Modules\Subject\Models\Subject;

class ShowSubjectAction extends BaseShowAction
{
    protected function modelClass(): string
    {
        return Subject::class;
    }

    protected function defaultWith(): array
    {
        return ['topics'];
    }
}
