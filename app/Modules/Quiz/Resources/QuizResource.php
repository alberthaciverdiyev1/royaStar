<?php

namespace App\Modules\Quiz\Resources;

use App\Modules\Lesson\Resources\LessonResource;
use App\Modules\Question\Resources\QuestionResource;
use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class QuizResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'lesson_id' => $this->lesson_id,
            'name' => $this->name,
            'created_at' => $this->created_at,
            $this->mergeWhen($this->relationLoaded('lesson'), [
                'lesson' => new LessonResource($this->lesson),
            ]),
            $this->mergeWhen($this->relationLoaded('questions'), [
                'questions' => QuestionResource::collection($this->questions),
            ]),
        ];
    }
}
