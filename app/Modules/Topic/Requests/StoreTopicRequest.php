<?php

namespace App\Modules\Topic\Requests;

use App\Modules\Topic\Enums\DifficultyLevel;
use Illuminate\Foundation\Http\FormRequest;

class StoreTopicRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $levels = implode(',', array_map(fn($case) => $case->value, DifficultyLevel::cases()));

        return [
            'name' => 'required|array',
            'difficulty_level' => "required|integer|in:{$levels}",
            'grade_ids' => 'nullable|array',
            'grade_ids.*' => 'integer|exists:grades,id',
        ];
    }
}
