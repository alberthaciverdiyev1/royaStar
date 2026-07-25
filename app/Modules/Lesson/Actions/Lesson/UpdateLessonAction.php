<?php

namespace App\Modules\Lesson\Actions\Lesson;

use App\Actions\BaseUpdateAction;
use App\Modules\Lesson\Models\Lesson;
use App\Modules\Lesson\Models\Video;
use Illuminate\Database\Eloquent\Model;

class UpdateLessonAction extends BaseUpdateAction
{
    protected function modelClass(): string
    {
        return Lesson::class;
    }

    protected function afterUpdate(Model $model): void
    {
        $videos = request()->input('videos');
        if (empty($videos)) {
            return;
        }

        $model->videos()->delete();

        foreach ($videos as $video) {
            Video::create([
                'lesson_id' => $model->id,
                'youtube_url' => $video['youtube_url'],
                'name' => $video['name'] ?? null,
                'lang' => $video['lang'] ?? null,
            ]);
        }
    }
}
