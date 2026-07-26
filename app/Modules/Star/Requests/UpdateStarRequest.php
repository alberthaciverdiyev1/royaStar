<?php

namespace App\Modules\Star\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStarRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'point' => 'required|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
