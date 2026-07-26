<?php

namespace App\Modules\Question\Requests;

use App\Modules\Topic\Enums\DifficultyLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type');

        $rules = [
            'question' => 'sometimes|array',
            'question.*.type' => 'in:text,image,audio',
            'question.*.content' => 'string',
            'type' => 'sometimes|string|in:regular,open',
            'explanation' => 'nullable|array',
            'explanation.*.type' => 'in:text,image,audio',
            'explanation.*.content' => 'string',
            'answer_type' => 'nullable|string|in:exact,similar',
            'difficulty_level' => ['sometimes', Rule::enum(DifficultyLevel::class)],
            'lesson_id' => 'sometimes|exists:lessons,id',
        ];

        if ($type === 'open') {
            $rules['answer_type'] = 'sometimes|string|in:exact,similar';
            $rules['open_answer'] = 'required|array';
            $rules['open_answer.*.type'] = 'in:text,image,audio';
            $rules['open_answer.*.content'] = 'string';
        } else {
            $rules['variant_a'] = 'sometimes|array';
            $rules['variant_a.*.type'] = 'in:text,image,audio';
            $rules['variant_a.*.content'] = 'string';
            $rules['variant_b'] = 'sometimes|array';
            $rules['variant_b.*.type'] = 'in:text,image,audio';
            $rules['variant_b.*.content'] = 'string';
            $rules['variant_c'] = 'sometimes|array';
            $rules['variant_c.*.type'] = 'in:text,image,audio';
            $rules['variant_c.*.content'] = 'string';
            $rules['variant_d'] = 'nullable|array';
            $rules['variant_d.*.type'] = 'in:text,image,audio';
            $rules['variant_d.*.content'] = 'string';
            $rules['variant_e'] = 'nullable|array';
            $rules['variant_e.*.type'] = 'in:text,image,audio';
            $rules['variant_e.*.content'] = 'string';
            $rules['right_answer'] = 'required_if:type,regular|string';
            $rules['open_answer'] = 'nullable|array';
        }

        return $rules;
    }
}
