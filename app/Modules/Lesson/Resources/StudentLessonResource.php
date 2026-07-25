<?php

namespace App\Modules\Lesson\Resources;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class StudentLessonResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'lesson_id' => $this->lesson_id,
            'progress' => $this->progress,
            'completed' => $this->completed_at !== null,
            'completed_at' => $this->completed_at,
            'last_position' => $this->last_position,
            'last_watched_at' => $this->last_watched_at,
        ];
    }
}
