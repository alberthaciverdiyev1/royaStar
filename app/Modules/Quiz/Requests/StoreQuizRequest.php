<?php

namespace App\Modules\Quiz\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuizRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'required|array',
            'type' => ['required', Rule::in(['topic_based', 'general'])],
            'topic_id' => 'nullable|exists:topics,id',
            'lesson_id' => 'nullable|exists:lessons,id',
            'question_ids' => 'nullable|array',
            'question_ids.*' => 'exists:questions,id',
        ];
    }
}
