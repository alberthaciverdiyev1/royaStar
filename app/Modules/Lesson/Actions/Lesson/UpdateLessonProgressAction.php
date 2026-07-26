<?php

namespace App\Modules\Lesson\Actions\Lesson;

use App\Modules\Lesson\Models\Lesson;
use App\Modules\Lesson\Models\LessonView;
use App\Modules\Lesson\Models\StudentLesson;
use App\Modules\Star\Services\StarService;
use Illuminate\Support\Facades\DB;

class UpdateLessonProgressAction
{
    public function __construct(
        private readonly StarService $starService,
    ) {}

    public function execute(int $lessonId, int $progress, ?int $position = null): StudentLesson
    {
        $student = auth()->user()->student;

        abort_unless($student, 403, 'Only students can track lesson progress');

        $lesson = Lesson::findOrFail($lessonId);

        return DB::transaction(function () use ($student, $lesson, $progress, $position) {
            $isCompleted = $progress >= 100;

            $existing = StudentLesson::where(['student_id' => $student->id, 'lesson_id' => $lesson->id])->first();
            $wasAlreadyCompleted = $existing && $existing->completed_at !== null;

            $updateData = [
                'progress' => $progress,
                'completed_at' => $isCompleted ? now() : null,
            ];

            if ($position !== null) {
                $updateData['last_position'] = $position;
                $updateData['last_watched_at'] = now();
            }

            $studentLesson = StudentLesson::updateOrCreate(
                ['student_id' => $student->id, 'lesson_id' => $lesson->id],
                $updateData
            );

            if (!$existing) {
                LessonView::updateOrCreate(['lesson_id' => $lesson->id])->increment('count');
            }

            if ($isCompleted && !$wasAlreadyCompleted) {
                $this->starService->awardLessonCompleted($student->user_id, $lesson->id);
                $studentLesson->loadMissing('lesson.topic.subject');
            }

            return $studentLesson->fresh();
        });
    }
}
