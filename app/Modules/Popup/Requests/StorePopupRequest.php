<?php

namespace App\Modules\Popup\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePopupRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string|max:20000',
            // Base64 data URI uploads can be a few MB before the client-side downscale.
            'image' => 'nullable|string|max:20000000',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // A popup must carry at least some content (text or image).
            if (blank($this->input('title')) && blank($this->input('content')) && blank($this->input('image'))) {
                $validator->errors()->add('content', 'Popup üçün ən azı başlıq, mətn və ya şəkil daxil edilməlidir.');
            }
        });
    }
}
