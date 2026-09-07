<?php

namespace App\Modules\AcceptedStudent\Actions;

use App\Actions\BaseShowAction;
use App\Modules\AcceptedStudent\Models\AcceptedStudent;

class ShowAcceptedStudentAction extends BaseShowAction
{
    protected function modelClass(): string
    {
        return AcceptedStudent::class;
    }

    protected function defaultWith(): array
    {
        return ['user'];
    }
}
