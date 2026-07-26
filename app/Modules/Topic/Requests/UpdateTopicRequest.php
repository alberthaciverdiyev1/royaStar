<?php

namespace App\Modules\Topic\Requests;

use App\Modules\Topic\Enums\DifficultyLevel;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTopicRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $levels = implode(',', array_map(fn($case) => $case->value, DifficultyLevel::cases()));

        return [
            'subject_id' => 'sometimes|exists:subjects,id',
            'name' => 'sometimes|string|max:255',
            'difficulty_level' => "sometimes|integer|in:{$levels}",
            'grade_ids' => 'nullable|array',
            'grade_ids.*' => 'integer|exists:grades,id',
        ];
    }
}
