<?php

namespace Database\Seeders;

use App\Modules\Setting\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::create([
            'app_name' => [
                'az' => 'RoyaStar',
                'en' => 'RoyaStar',
                'ru' => 'RoyaStar',
            ],
            'address' => [
                'az' => 'Bakı, Azərbaycan',
                'en' => 'Baku, Azerbaijan',
                'ru' => 'Баку, Азербайджан',
            ],
            'email' => 'info@royastar.az',
            'phone' => '+994555000000',
            'facebook' => 'https://facebook.com/royastar',
            'instagram' => 'https://instagram.com/royastar',
            'youtube' => 'https://youtube.com/@royastar',
            'twitter' => 'https://twitter.com/royastar',
            'telegram' => 'https://t.me/royastar',
            'whatsapp' => '+994555000000',
            'about_text' => [
                'az' => 'RoyaStar — Azərbaycanda rəqəmsal təhsil sahəsində lider platformadır. Biz müasir texnologiyalar vasitəsilə təhsili daha əlçatan və effektiv etmək məqsədi daşıyırıq.',
                'en' => 'RoyaStar is the leading digital education platform in Azerbaijan. We aim to make education more accessible and effective through modern technology.',
                'ru' => 'RoyaStar — ведущая платформа цифрового образования в Азербайджане. Мы стремимся сделать образование более доступным и эффективным с помощью современных технологий.',
            ],
            'terms_text' => [
                'az' => 'Bu istifadə şərtləri RoyaStar platformasından istifadəni tənzimləyir. Platformadan istifadə etməklə siz bu şərtləri qəbul etmiş olursunuz.',
                'en' => 'These terms of use govern your use of the RoyaStar platform. By using the platform, you accept these terms.',
                'ru' => 'Настоящие условия использования регулируют использование платформы RoyaStar. Используя платформу, вы принимаете эти условия.',
            ],
            'privacy_text' => [
                'az' => 'Məxfilik siyasətimiz şəxsi məlumatlarınızın toplanması, istifadəsi və qorunması haqqında məlumatları ehtiva edir.',
                'en' => 'Our privacy policy contains information about the collection, use and protection of your personal data.',
                'ru' => 'Наша политика конфиденциальности содержит информацию о сборе, использовании и защите ваших личных данных.',
            ],
            'maintenance_mode' => false,
        ]);
    }
}
