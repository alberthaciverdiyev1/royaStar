<?php

namespace App\Modules\Payment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'required|array',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'old_price' => 'nullable|numeric|min:0',
        ];
    }
}
