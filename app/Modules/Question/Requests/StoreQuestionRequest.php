<?php

namespace App\Modules\Question\Requests;

use App\Modules\Topic\Enums\DifficultyLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->input('type');

        $rules = [
            'question' => 'required|array',
            'question.*.type' => 'required|in:text,image,audio',
            'question.*.content' => 'required|string',
            'type' => 'required|string|in:regular,open',
            'explanation' => 'nullable|array',
            'explanation.*.type' => 'in:text,image,audio',
            'explanation.*.content' => 'string',
            'answer_type' => 'nullable|string|in:exact,similar',
            'difficulty_level' => ['required', Rule::enum(DifficultyLevel::class)],
            'lesson_id' => 'required|exists:lessons,id',
        ];

        if ($type === 'open') {
            $rules['answer_type'] = 'required|string|in:exact,similar';
            $rules['open_answer'] = 'required|array';
            $rules['open_answer.*.type'] = 'required|in:text,image,audio';
            $rules['open_answer.*.content'] = 'required|string';
        } else {
            $rules['variant_a'] = 'required|array';
            $rules['variant_a.*.type'] = 'required|in:text,image,audio';
            $rules['variant_a.*.content'] = 'required|string';
            $rules['variant_b'] = 'required|array';
            $rules['variant_b.*.type'] = 'required|in:text,image,audio';
            $rules['variant_b.*.content'] = 'required|string';
            $rules['variant_c'] = 'required|array';
            $rules['variant_c.*.type'] = 'required|in:text,image,audio';
            $rules['variant_c.*.content'] = 'required|string';
            $rules['variant_d'] = 'nullable|array';
            $rules['variant_d.*.type'] = 'in:text,image,audio';
            $rules['variant_d.*.content'] = 'string';
            $rules['variant_e'] = 'nullable|array';
            $rules['variant_e.*.type'] = 'in:text,image,audio';
            $rules['variant_e.*.content'] = 'string';
            $rules['right_answer'] = 'required|string';
            $rules['open_answer'] = 'nullable|array';
        }

        return $rules;
    }
}
