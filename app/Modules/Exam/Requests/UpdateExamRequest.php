<?php

namespace App\Modules\Exam\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExamRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'grade_id' => 'sometimes|exists:grades,id',
            'duration_minutes' => 'sometimes|integer|min:1|max:600',
            'passing_score' => 'sometimes|integer|min:0|max:100',
            'type' => ['sometimes', Rule::in(['general', 'midterm', 'final'])],
            'question_ids' => 'nullable|array',
            'question_ids.*' => 'exists:questions,id',
        ];
    }
}
