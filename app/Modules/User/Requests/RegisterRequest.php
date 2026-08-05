<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required_with:password|same:password',
            'type' => 'required|in:student',

            // Student
            'student.grade_id' => 'required|exists:grades,id',
            'student.city_id' => 'required|exists:cities,id',
        ];
    }
}
