<?php

namespace App\Modules\AcceptedStudent\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcceptedStudentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'name' => 'sometimes|required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:5000',
            'exam_points' => 'nullable|integer|min:0|max:1000',
            'is_active' => 'nullable|boolean',
        ];
    }
}
