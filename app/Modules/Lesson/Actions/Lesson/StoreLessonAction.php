<?php

namespace App\Modules\Lesson\Actions\Lesson;

use App\Actions\BaseStoreAction;
use App\Modules\Lesson\Models\Lesson;
use App\Modules\Lesson\Models\Video;
use App\Modules\Notification\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;

class StoreLessonAction extends BaseStoreAction
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    protected function modelClass(): string
    {
        return Lesson::class;
    }

    protected function afterCreate(Model $model): void
    {
        $videos = request()->input('videos');
        if (empty($videos)) {
            return;
        }

        $created = [];

        foreach ($videos as $video) {
            $created[] = Video::create([
                'lesson_id' => $model->id,
                'youtube_url' => $video['youtube_url'],
                'name' => $video['name'] ?? null,
            ]);
        }

        $this->notificationService->notifyVideos($created);
    }
}
