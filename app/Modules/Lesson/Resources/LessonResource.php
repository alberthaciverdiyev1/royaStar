<?php

namespace App\Modules\Lesson\Resources;

use App\Http\Resources\BaseResource;
use App\Modules\Lesson\Models\StudentLesson;
use Illuminate\Http\Request;

class LessonResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'topic_id' => $this->topic_id,
            'name' => $this->translate('name'),
            'description' => $this->translate('description'),
            'view_count' => $this->view?->count ?? 0,
            'created_at' => $this->created_at,
            $this->mergeWhen($this->relationLoaded('videos'), [
                'videos' => VideoResource::collection($this->videos),
            ]),
        ];

        if ($this->relationLoaded('studentLessons')) {
            $sl = $this->studentLessons->first();
            $data['progress'] = $sl ? $sl->progress : 0;
            $data['completed'] = $sl && $sl->completed_at !== null;
            $data['last_position'] = $sl?->last_position;
            $data['last_watched_at'] = $sl?->last_watched_at;
        }

        return $data;
    }
}
