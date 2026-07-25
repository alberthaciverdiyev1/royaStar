<?php

namespace App\Modules\Setting\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'app_name' => 'sometimes|array',
            'app_name.az' => 'sometimes|string',
            'app_name.en' => 'sometimes|string',
            'app_name.ru' => 'sometimes|string',
            'logo' => 'nullable|string',
            'favicon' => 'nullable|string',
            'address' => 'sometimes|array',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'facebook' => 'nullable|string',
            'instagram' => 'nullable|string',
            'youtube' => 'nullable|string',
            'twitter' => 'nullable|string',
            'telegram' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'about_text' => 'sometimes|array',
            'terms_text' => 'sometimes|array',
            'privacy_text' => 'sometimes|array',
            'maintenance_mode' => 'boolean',
        ];
    }
}
