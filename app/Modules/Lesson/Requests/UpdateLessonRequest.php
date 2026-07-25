<?php

namespace App\Modules\Lesson\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|array',
            'description' => 'nullable|array',
            'videos' => 'nullable|array',
            'videos.*.youtube_url' => 'required|string|url|max:255',
            'videos.*.name' => 'nullable|string|max:255',
            'videos.*.lang' => 'nullable|string',
        ];
    }
}
