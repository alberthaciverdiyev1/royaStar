<?php

namespace App\Modules\Lesson\Actions\Lesson;

use App\Actions\BaseStoreAction;
use App\Modules\Lesson\Models\Lesson;
use App\Modules\Lesson\Models\Video;
use Illuminate\Database\Eloquent\Model;

class StoreLessonAction extends BaseStoreAction
{
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

        foreach ($videos as $video) {
            Video::create([
                'lesson_id' => $model->id,
                'youtube_url' => $video['youtube_url'],
                'name' => $video['name'] ?? null,
            ]);
        }
    }
}
