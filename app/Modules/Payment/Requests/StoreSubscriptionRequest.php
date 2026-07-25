<?php

namespace App\Modules\Payment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'start_date' => 'required|date',
            'expires_at' => 'required|date|after:start_date',
            'status' => 'required|string',
            'school_id' => 'nullable|exists:schools,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'family_id' => 'nullable|exists:families,id',
            'student_id' => 'nullable|exists:students,id',
        ];
    }
}
