<?php

namespace App\Modules\Music\Requests;

use App\Modules\Music\Models\MusicSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMusicSettingsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'play_on_all' => 'required|boolean',
            'pages' => 'nullable|array',
            'pages.*' => ['string', Rule::in(array_keys(MusicSetting::PAGES))],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->boolean('play_on_all') && blank($this->input('pages'))) {
                $validator->errors()->add(
                    'pages',
                    'Ən azı bir səhifə seçin və ya "Bütün səhifələrdə çal" variantını aktiv edin.',
                );
            }
        });
    }
}
