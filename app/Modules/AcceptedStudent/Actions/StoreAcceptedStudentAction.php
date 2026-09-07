<?php

namespace App\Modules\AcceptedStudent\Actions;

use App\Actions\BaseStoreAction;
use App\Modules\AcceptedStudent\Models\AcceptedStudent;

class StoreAcceptedStudentAction extends BaseStoreAction
{
    use HandlesImage;

    protected function modelClass(): string
    {
        return AcceptedStudent::class;
    }

    protected function beforeCreate(array $data): array
    {
        return $this->persistImage($data);
    }
}
