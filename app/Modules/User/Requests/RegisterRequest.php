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
        $rules = [
            'name' => 'required_if:type,student,teacher,parent,admin|string|max:255',
            'surname' => 'nullable|string|max:255',
            'phone' => 'required_if:type,student,teacher,parent,admin|string',
            'email' => 'required|email',
            'password' => 'required_if:type,student,teacher,parent,admin|string|min:8',
            'password_confirmation' => 'required_with:password|same:password',
            'type' => 'required|in:student,teacher,parent,school,admin',

            // Teacher
            'teacher.city_id' => 'required_if:type,teacher|exists:cities,id',

            // Student
            'student.grade_id' => 'required_if:type,student|exists:grades,id',
            'student.city_id' => 'required_if:type,student|exists:cities,id',

            // School
            'school.name' => 'required_if:type,school|string|max:255',
            'school.no' => 'nullable|string|max:255',
            'school.city_id' => 'required_if:type,school|exists:cities,id',
        ];

        if ($this->input('type') === 'school') {
            $rules['email'] = 'required|email|unique:school_registration_requests,email';
            $rules['phone'] = 'nullable|string';
            unset($rules['password_confirmation']);
        } else {
            $rules['email'] = 'required|email|unique:users,email';
            $rules['phone'] = 'required|string|unique:users,phone';
        }

        return $rules;
    }
}
