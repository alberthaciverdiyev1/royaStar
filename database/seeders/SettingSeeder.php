<?php

namespace Database\Seeders;

use App\Modules\Setting\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::updateOrCreate(
            ['id' => 1],
            [
                'app_name' => 'RoyaStar',
                'address' => 'Bakı, Azərbaycan',
                'email' => 'info@royastar.az',
                'phone' => '+994555000000',
                'facebook' => 'https://facebook.com/royastar',
                'instagram' => 'https://instagram.com/royastar',
                'youtube' => 'https://youtube.com/@royastar',
                'twitter' => 'https://twitter.com/royastar',
                'telegram' => 'https://t.me/royastar',
                'whatsapp' => '+994555000000',
                'about_text' => 'RoyaStar — Azərbaycanda rəqəmsal təhsil sahəsində lider platformadır. Biz müasir texnologiyalar vasitəsilə təhsili daha əlçatan və effektiv etmək məqsədi daşıyırıq.',
                'terms_text' => 'Bu istifadə şərtləri RoyaStar platformasından istifadəni tənzimləyir. Platformadan istifadə etməklə siz bu şərtləri qəbul etmiş olursunuz.',
                'privacy_text' => 'Məxfilik siyasətimiz şəxsi məlumatlarınızın toplanması, istifadəsi və qorunması haqqında məlumatları ehtiva edir.',
                'maintenance_mode' => false,
            ]
        );
    }
}
