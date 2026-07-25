<?php

namespace App\Modules\Setting\Models;

use App\Traits\HasTranslations;
use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasTranslations, SerializesDates;

    protected $fillable = [
        'app_name', 'logo', 'favicon',
        'address', 'email', 'phone',
        'facebook', 'instagram', 'youtube', 'twitter', 'telegram', 'whatsapp',
        'about_text', 'terms_text', 'privacy_text',
        'maintenance_mode',
    ];

    protected function casts(): array
    {
        return [
            'app_name' => 'array',
            'address' => 'array',
            'about_text' => 'array',
            'terms_text' => 'array',
            'privacy_text' => 'array',
            'maintenance_mode' => 'boolean',
        ];
    }
}
