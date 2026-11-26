<?php

namespace App\Modules\Notification\Services;

use App\Modules\Exam\Models\Exam;
use App\Modules\Lesson\Models\Video;
use App\Modules\Notification\Models\Notification;
use App\Modules\Quiz\Models\Quiz;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function __construct(
        private readonly WebPushService $webPush,
    ) {}

    /**
     * A new video was added to a lesson. Students of the topic's grades are
     * notified; a topic without grade restrictions reaches every student.
     */
    public function notifyVideo(Video $video): ?Notification
    {
        $lesson = $video->lesson;
        if (! $lesson) {
            return null;
        }

        $gradeIds = $lesson->topic?->grades()->pluck('grades.id')->all() ?? [];

        $body = $lesson->name;
        if ($video->name && $video->name !== $lesson->name) {
            $body .= ' — '.$video->name;
        }

        return $this->notify(
            type: 'lesson_video',
            title: 'Yeni video dərs əlavə edildi',
            body: $body,
            url: route('lesson', $lesson->id),
            gradeIds: $gradeIds,
            data: ['lesson_id' => $lesson->id, 'video_id' => $video->id],
        );
    }

    public function notifyVideos(iterable $videos): void
    {
        foreach ($videos as $video) {
            $this->notifyVideo($video);
        }
    }

    /**
     * A new quiz was created. Grade scope comes from its lesson's topic.
     */
    public function notifyQuiz(Quiz $quiz): ?Notification
    {
        $quiz->loadMissing('lesson.topic.grades');

        $gradeIds = $quiz->lesson?->topic?->grades?->pluck('id')->all() ?? [];

        return $this->notify(
            type: 'quiz',
            title: 'Yeni quiz əlavə edildi',
            body: $quiz->name,
            url: route('quiz', $quiz->id),
            gradeIds: $gradeIds,
            data: ['quiz_id' => $quiz->id, 'lesson_id' => $quiz->lesson_id],
        );
    }

    /**
     * A new exam was created. An exam without a grade reaches every student.
     */
    public function notifyExam(Exam $exam): ?Notification
    {
        $gradeIds = $exam->grade_id ? [$exam->grade_id] : [];

        return $this->notify(
            type: 'exam',
            title: 'Yeni imtahan əlavə edildi',
            body: $exam->name,
            url: route('exam.detail', $exam->id),
            gradeIds: $gradeIds,
            data: ['exam_id' => $exam->id, 'grade_id' => $exam->grade_id],
        );
    }

    /**
     * Persist one notification, attach it to every matching student and push
     * it to their browsers.
     *
     * @param  array<int, int|string>  $gradeIds  empty = every student
     */
    public function notify(
        string $type,
        string $title,
        ?string $body,
        ?string $url,
        array $gradeIds = [],
        array $data = [],
    ): ?Notification {
        $userIds = $this->studentUserIds($gradeIds);

        if (empty($userIds)) {
            return null;
        }

        $notification = Notification::create([
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'data' => $data,
        ]);

        $now = now();
        $rows = array_map(fn (int $userId) => [
            'notification_id' => $notification->id,
            'user_id' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $userIds);

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('notification_user')->insert($chunk);
        }

        $payload = [
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'icon' => config('webpush.icon'),
            'tag' => 'rs-notification-'.$notification->id,
        ];

        // Send only once the surrounding transaction (if any) has committed.
        DB::afterCommit(fn () => $this->webPush->sendToUsers($userIds, $payload));

        return $notification;
    }

    /**
     * @param  array<int, int|string>  $gradeIds
     * @return array<int, int>
     */
    private function studentUserIds(array $gradeIds): array
    {
        $gradeIds = array_values(array_filter($gradeIds, fn ($id) => $id !== null && $id !== ''));

        $query = User::query()->where('type', 'student');

        if (! empty($gradeIds)) {
            $query->whereHas('student', fn ($q) => $q->whereIn('grade_id', $gradeIds));
        }

        return $query->pluck('users.id')->all();
    }
}
