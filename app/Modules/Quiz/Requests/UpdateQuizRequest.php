<?php

namespace App\Modules\Quiz\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuizRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|array',
            'type' => ['sometimes', Rule::in(['topic_based', 'general'])],
            'topic_id' => 'nullable|exists:topics,id',
            'lesson_id' => 'nullable|exists:lessons,id',
        ];
    }
}
