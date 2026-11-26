<?php

namespace App\Modules\Notification\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notify_lesson_video' => ['sometimes', 'boolean'],
            'notify_quiz' => ['sometimes', 'boolean'],
            'notify_exam' => ['sometimes', 'boolean'],
        ];
    }
}
