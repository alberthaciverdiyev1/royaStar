<?php

namespace App\Modules\Lesson\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'videos' => 'nullable|array',
            'videos.*.youtube_url' => 'required|string|max:255',
            'videos.*.name' => 'nullable|string|max:255',
        ];
    }
}
