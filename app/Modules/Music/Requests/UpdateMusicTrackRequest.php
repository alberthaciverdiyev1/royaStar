<?php

namespace App\Modules\Music\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMusicTrackRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'artist' => 'nullable|string|max:255',
            'url' => 'required|string|max:2048|url:http,https',
            'cover' => 'nullable|string|max:20000000',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ];
    }
}
