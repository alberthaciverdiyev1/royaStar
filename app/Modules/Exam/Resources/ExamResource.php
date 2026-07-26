<?php

namespace App\Modules\Exam\Resources;

use App\Http\Resources\BaseResource;
use App\Modules\Grade\Resources\GradeResource;
use App\Modules\Question\Resources\QuestionResource;
use Illuminate\Http\Request;

class ExamResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'grade_id' => $this->grade_id,
            'duration_minutes' => $this->duration_minutes,
            'passing_score' => $this->passing_score,
            'total_questions' => $this->total_questions,
            'type' => $this->type,
            'created_at' => $this->created_at,
            $this->mergeWhen($this->relationLoaded('grade'), [
                'grade' => new GradeResource($this->grade),
            ]),
            $this->mergeWhen($this->relationLoaded('questions'), [
                'questions' => QuestionResource::collection($this->questions),
            ]),
        ];
    }
}
