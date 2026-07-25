<?php

namespace App\Modules\Lesson\Actions\Lesson;

use App\Actions\BaseDeleteAction;
use App\Modules\Lesson\Models\Lesson;

class DeleteLessonAction extends BaseDeleteAction
{
    protected function modelClass(): string
    {
        return Lesson::class;
    }
}
