<?php

namespace App\Modules\Question\Resources;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class QuestionResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $textFields = ['question', 'variant_a', 'variant_b', 'variant_c', 'variant_d', 'variant_e', 'open_answer', 'explanation'];

        $data = [
            'id' => $this->id,
            'topic_id' => $this->topic_id,
            'type' => $this->type,
            'answer_type' => $this->answer_type,
            'right_answer' => $this->right_answer,
            'difficulty_level' => $this->difficulty_level,
            'created_at' => $this->created_at,
        ];

        foreach ($textFields as $field) {
            $data[$field] = $this->translate($field);
        }

        return $data;
    }
}
