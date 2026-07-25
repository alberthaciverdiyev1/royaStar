<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'surname' => 'sometimes|string|max:255',
            'avatar' => 'sometimes|string|max:255',
            'password' => 'sometimes|string|min:8',
        ];
    }
}
