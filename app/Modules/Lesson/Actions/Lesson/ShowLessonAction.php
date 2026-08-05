<?php

namespace App\Modules\Lesson\Actions\Lesson;

use App\Actions\BaseShowAction;
use App\Modules\Lesson\Models\Lesson;

class ShowLessonAction extends BaseShowAction
{
    protected function modelClass(): string
    {
        return Lesson::class;
    }

    protected function defaultWith(): array
    {
        $with = ['topic', 'videos', 'quizzes', 'view'];

        if ($student = auth()->user()?->student) {
            $with['studentLessons'] = fn($q) => $q->where('student_id', $student->id);
        }

        return $with;
    }
}
