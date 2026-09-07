<?php

namespace App\Modules\AcceptedStudent\Actions;

use App\Actions\BaseDeleteAction;
use App\Modules\AcceptedStudent\Models\AcceptedStudent;

class DeleteAcceptedStudentAction extends BaseDeleteAction
{
    protected function modelClass(): string
    {
        return AcceptedStudent::class;
    }
}
