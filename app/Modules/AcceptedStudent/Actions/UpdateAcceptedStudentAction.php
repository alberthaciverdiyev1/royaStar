<?php

namespace App\Modules\AcceptedStudent\Actions;

use App\Actions\BaseUpdateAction;
use App\Modules\AcceptedStudent\Models\AcceptedStudent;

class UpdateAcceptedStudentAction extends BaseUpdateAction
{
    use HandlesImage;

    protected function modelClass(): string
    {
        return AcceptedStudent::class;
    }

    protected function beforeUpdate(array $data): array
    {
        return $this->persistImage($data);
    }
}
