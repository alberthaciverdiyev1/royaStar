<?php

namespace App\Modules\Lesson\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVideoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'lesson_id' => 'nullable|exists:lessons,id',
            'name' => 'nullable|string|max:255',
            'youtube_url' => 'sometimes|string|max:255',
            'lang' => 'sometimes|string',
        ];
    }
}
