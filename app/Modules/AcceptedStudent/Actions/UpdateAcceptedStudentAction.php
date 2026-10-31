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
        $data = $this->persistImage($data);

        if (array_key_exists('exam_points', $data) && $data['exam_points'] === null) {
            $data['exam_points'] = 0;
        }

        return $data;
    }
}
