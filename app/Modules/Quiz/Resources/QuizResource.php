<?php

namespace App\Modules\Quiz\Resources;

use App\Modules\Lesson\Resources\LessonResource;
use App\Modules\Question\Resources\QuestionResource;
use App\Modules\Topic\Resources\TopicResource;
use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class QuizResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'topic_id' => $this->topic_id,
            'lesson_id' => $this->lesson_id,
            'name' => $this->translate('name'),
            'created_at' => $this->created_at,
            $this->mergeWhen($this->relationLoaded('lesson'), [
                'lesson' => new LessonResource($this->lesson),
            ]),
            $this->mergeWhen($this->relationLoaded('topic'), [
                'topic' => new TopicResource($this->topic),
            ]),
            $this->mergeWhen($this->relationLoaded('questions'), [
                'questions' => QuestionResource::collection($this->questions),
            ]),
        ];
    }
}
