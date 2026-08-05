<?php

namespace App\Modules\Question\Resources;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class QuestionResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'lesson_id' => $this->lesson_id,
            'lesson_name' => $this->relationLoaded('lesson') ? $this->lesson?->name : null,
            'topic_id' => $this->relationLoaded('lesson') ? $this->lesson?->topic_id : null,
            'type' => $this->type,
            'answer_type' => $this->answer_type,
            'difficulty_level' => $this->difficulty_level,
            'question' => $this->question,
            'explanation_video_url' => $this->explanation_video_url,
            'created_at' => $this->created_at,
        ];

        foreach (['variant_a', 'variant_b', 'variant_c', 'variant_d', 'variant_e'] as $variant) {
            $data[$variant] = $this->{$variant};
        }

        // Sensitive answer fields are only exposed in admin context.
        if ($this->isAdmin()) {
            $data['right_answer'] = $this->right_answer;
            $data['open_answer'] = $this->open_answer;
            $data['explanation'] = $this->explanation;
        }

        return $data;
    }
}
