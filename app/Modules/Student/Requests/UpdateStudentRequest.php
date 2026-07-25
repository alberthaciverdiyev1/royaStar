<?php

namespace App\Modules\Student\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|exists:users,id',
            'grade_id' => 'nullable|exists:grades,id',
            'city_id' => 'nullable|exists:cities,id',
            'school_name' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
        ];
    }
}
