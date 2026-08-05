<?php

namespace App\Modules\Setting\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\BaseResource;

class SettingResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'app_name' => $this->app_name,
            'logo' => $this->logo,
            'favicon' => $this->favicon,
            'address' => $this->address,
            'email' => $this->email,
            'phone' => $this->phone,
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'youtube' => $this->youtube,
            'twitter' => $this->twitter,
            'telegram' => $this->telegram,
            'whatsapp' => $this->whatsapp,
            'about_text' => $this->about_text,
            'terms_text' => $this->terms_text,
            'privacy_text' => $this->privacy_text,
            'maintenance_mode' => $this->maintenance_mode,
        ];
    }
}
