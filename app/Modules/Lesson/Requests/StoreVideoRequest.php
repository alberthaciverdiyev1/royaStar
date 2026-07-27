<?php

namespace App\Modules\Lesson\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVideoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'lesson_id' => 'nullable|exists:lessons,id',
            'name' => 'nullable|string|max:255',
            'youtube_url' => 'required|string|max:255',
            'lang' => 'nullable|string',
        ];
    }
}
