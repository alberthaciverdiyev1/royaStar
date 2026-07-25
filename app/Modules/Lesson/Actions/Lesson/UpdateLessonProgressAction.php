<?php

namespace App\Modules\Lesson\Actions\Lesson;

use App\Modules\Lesson\Models\Lesson;
use App\Modules\Lesson\Models\LessonView;
use App\Modules\Lesson\Models\StudentLesson;
use App\Modules\Student\Models\StudentActivity;
use Illuminate\Support\Facades\DB;

class UpdateLessonProgressAction
{
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
                $studentLesson->loadMissing('lesson.topic.subject');
                $topic = $studentLesson->lesson?->topic;
                $subject = $topic?->subject;

                $totalLessons = $topic ? $topic->lessons()->count() : 0;
                $completedLessons = StudentLesson::where('student_id', $student->id)
                    ->whereIn('lesson_id', $topic->lessons()->pluck('id'))
                    ->whereNotNull('completed_at')
                    ->count();

                StudentActivity::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'reference_type' => 'lesson',
                        'reference_id' => $lesson->id,
                    ],
                    [
                        'type' => 'lesson_completed',
                        'metadata' => [
                            'lesson_name' => $lesson->localeValue('name'),
                            'topic_name' => $topic?->localeValue('name'),
                            'subject_name' => $subject?->localeValue('name'),
                            'completed_lessons' => $completedLessons,
                            'total_lessons' => $totalLessons,
                            'percentage' => $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0,
                        ],
                    ]
                );
            }

            return $studentLesson->fresh();
        });
    }
}
