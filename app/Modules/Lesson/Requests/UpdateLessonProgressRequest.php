<?php

namespace App\Modules\Lesson\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'progress' => 'required|integer|min:0|max:100',
            'position' => 'nullable|integer|min:0',
        ];
    }
}
