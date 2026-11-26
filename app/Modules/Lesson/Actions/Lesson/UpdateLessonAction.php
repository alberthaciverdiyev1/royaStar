<?php

namespace App\Modules\Lesson\Actions\Lesson;

use App\Actions\BaseUpdateAction;
use App\Modules\Lesson\Models\Lesson;
use App\Modules\Lesson\Models\Video;
use App\Modules\Notification\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;

class UpdateLessonAction extends BaseUpdateAction
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    protected function modelClass(): string
    {
        return Lesson::class;
    }

    protected function afterUpdate(Model $model): void
    {
        if (! request()->has('videos')) {
            return;
        }

        // The admin form rewrites the whole video list. Remember what already
        // existed so only genuinely new videos trigger a student notification.
        $existingUrls = $model->videos()->pluck('youtube_url')->all();

        $model->videos()->delete();

        $created = [];

        foreach (request()->input('videos', []) as $video) {
            $createdVideo = Video::create([
                'lesson_id' => $model->id,
                'youtube_url' => $video['youtube_url'],
                'name' => $video['name'] ?? null,
            ]);

            if (! in_array($createdVideo->youtube_url, $existingUrls, true)) {
                $created[] = $createdVideo;
            }
        }

        $this->notificationService->notifyVideos($created);
    }
}
