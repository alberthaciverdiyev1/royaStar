<?php

namespace App\Modules\Quiz\Resources;

use App\Http\Resources\BaseResource;
use App\Modules\Question\Resources\QuestionResource;
use Illuminate\Http\Request;

class QuizResultResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $answers = $this->relationLoaded('answers')
            ? $this->answers->map(fn($a) => [
                'question_id' => $a->question_id,
                'question' => $a->question?->translate('question'),
                'type' => $a->type,
                'answer' => $a->answer,
                'correct_answer' => $a->correct_answer,
                'is_correct' => $a->is_correct,
            ])->values()
            : [];

        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'status' => $this->status,
            'started_at' => $this->started_at,
            'submitted_at' => $this->submitted_at,
            'score' => $this->score,
            'total_questions' => $this->total_questions,
            'correct_count' => $this->correct_count,
            'wrong_count' => $this->wrong_count,
            'skipped_count' => $this->skipped_count,
            'pending_review_count' => $this->pending_review_count,
            'answers' => $answers,
        ];
    }
}
