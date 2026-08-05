<?php

namespace App\Modules\Setting\Models;

use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use SerializesDates;

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
            'maintenance_mode' => 'boolean',
        ];
    }
}
