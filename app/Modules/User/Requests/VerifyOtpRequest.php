<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
        ];
    }
}
