<?php

namespace App\Modules\Star\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStarRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'point' => 'required|integer|min:1',
            'type' => 'required|string|max:255',
        ];
    }
}
