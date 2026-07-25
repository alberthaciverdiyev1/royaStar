<?php

namespace App\Modules\Topic\Resources;

use App\Modules\Grade\Resources\GradeResource;
use App\Modules\Subject\Resources\SubjectResource;
use App\Modules\Lesson\Resources\LessonResource;
use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class TopicResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject_id' => $this->subject_id,
            'name' => $this->translate('name'),
            'difficulty_level' => $this->difficulty_level?->value,
            'difficulty_label' => $this->difficulty_level?->label(),
            'created_at' => $this->created_at,
            $this->mergeWhen($this->relationLoaded('subject'), [
                'subject' => new SubjectResource($this->subject),
            ]),
            $this->mergeWhen($this->relationLoaded('lessons'), [
                'lessons' => LessonResource::collection($this->lessons),
            ]),
            $this->mergeWhen($this->relationLoaded('grades'), [
                'grades' => GradeResource::collection($this->grades),
            ]),
        ];
    }
}
