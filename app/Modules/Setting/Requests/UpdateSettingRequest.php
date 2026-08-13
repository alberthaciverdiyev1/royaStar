<?php

namespace App\Modules\Setting\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'app_name' => 'sometimes|string',
            'logo' => 'nullable|string',
            'favicon' => 'nullable|string',
            'address' => 'sometimes|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'facebook' => 'nullable|string',
            'instagram' => 'nullable|string',
            'youtube' => 'nullable|string',
            'twitter' => 'nullable|string',
            'telegram' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'about_text' => 'sometimes|string',
            'terms_text' => 'sometimes|string',
            'privacy_text' => 'sometimes|string',
            'maintenance_mode' => 'boolean',
            'texts' => 'sometimes|array',
        ];
    }
}
