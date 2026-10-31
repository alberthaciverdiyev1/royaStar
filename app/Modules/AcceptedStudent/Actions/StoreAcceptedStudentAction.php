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
        $data = $this->persistImage($data);

        // An explicit null skips the column default and violates NOT NULL.
        $data['exam_points'] = $data['exam_points'] ?? 0;

        return $data;
    }
}
