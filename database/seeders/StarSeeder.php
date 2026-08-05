<?php

namespace Database\Seeders;

use App\Modules\Star\Models\Star;
use Illuminate\Database\Seeder;

class StarSeeder extends Seeder
{
    private array $stars = [
        // ===== ENGAGEMENT =====
        [
            'type' => 'daily_login',
            'point' => 2, 'point_min' => 1, 'point_max' => 10, 'point_default' => 2,
            'max_per_day' => 1, 'sort_order' => 10,
            'icon' => '🔥', 'category' => 'engagement', 'group' => 'daily',
            'name' => 'Gündəlik giriş',
            'description' => 'Hər gün sistemə daxil olmaq',
        ],
        [
            'type' => 'login_streak',
            'point' => 10, 'point_min' => 5, 'point_max' => 50, 'point_default' => 10,
            'sort_order' => 20,
            'icon' => '⚡', 'category' => 'engagement', 'group' => 'daily',
            'name' => 'Ardıcıl giriş həftəsi',
            'description' => '7 gün ardıcıl sistemə daxil olmaq',
        ],
        [
            'type' => 'comment_added',
            'point' => 2, 'point_min' => 1, 'point_max' => 10, 'point_default' => 2,
            'max_per_day' => 5, 'sort_order' => 30,
            'icon' => '💬', 'category' => 'engagement', 'group' => 'social',
            'name' => 'Şərh yazıldı',
            'description' => 'Dərs və ya məqaləyə şərh yazmaq',
        ],

        // ===== LEARNING =====
        [
            'type' => 'lesson_completed',
            'point' => 5, 'point_min' => 2, 'point_max' => 20, 'point_default' => 5,
            'sort_order' => 40,
            'icon' => '📚', 'category' => 'learning', 'group' => 'lesson',
            'name' => 'Dərs tamamlandı',
            'description' => 'Dərsi 100% izləyib tamamlamaq',
        ],
        [
            'type' => 'video_watched',
            'point' => 1, 'point_min' => 1, 'point_max' => 5, 'point_default' => 1,
            'max_per_day' => 20, 'sort_order' => 50,
            'icon' => '🎥', 'category' => 'learning', 'group' => 'lesson',
            'name' => 'Video izləndi',
            'description' => 'Dərs daxilində videonu izləmək',
        ],
        [
            'type' => 'quiz_completed',
            'point' => 10, 'point_min' => 5, 'point_max' => 30, 'point_default' => 10,
            'sort_order' => 60,
            'icon' => '📝', 'category' => 'learning', 'group' => 'quiz',
            'name' => 'Test tamamlandı',
            'description' => 'Quiz/testi tamamlamaq',
        ],
        [
            'type' => 'quiz_perfect',
            'point' => 20, 'point_min' => 10, 'point_max' => 50, 'point_default' => 20,
            'sort_order' => 70,
            'icon' => '🏆', 'category' => 'learning', 'group' => 'quiz',
            'name' => 'Mükəmməl test',
            'description' => 'Testdən 100% bal toplamaq',
        ],

        // ===== ACHIEVEMENT =====
        [
            'type' => 'exam_passed',
            'point' => 30, 'point_min' => 10, 'point_max' => 100, 'point_default' => 30,
            'sort_order' => 80,
            'icon' => '🎓', 'category' => 'achievement', 'group' => 'exam',
            'name' => 'İmtahan keçildi',
            'description' => 'İmtahandan keçid balı toplamaq',
        ],
        [
            'type' => 'exam_excellent',
            'point' => 50, 'point_min' => 20, 'point_max' => 150, 'point_default' => 50,
            'sort_order' => 90,
            'icon' => '👑', 'category' => 'achievement', 'group' => 'exam',
            'name' => 'İmtahan əla',
            'description' => 'İmtahandan 90% və yuxarı bal toplamaq',
        ],
        [
            'type' => 'first_quiz',
            'point' => 15, 'point_min' => 5, 'point_max' => 30, 'point_default' => 15,
            'sort_order' => 100,
            'icon' => '🌟', 'category' => 'achievement', 'group' => 'onboarding',
            'name' => 'İlk test',
            'description' => 'İlk testi tamamlamaq',
        ],
        [
            'type' => 'profile_completed',
            'point' => 25, 'point_min' => 10, 'point_max' => 50, 'point_default' => 25,
            'sort_order' => 110,
            'icon' => '🎯', 'category' => 'achievement', 'group' => 'onboarding',
            'name' => 'Profil tamamlandı',
            'description' => 'Profil məlumatlarını tam doldurmaq',
        ],

        // ===== STREAKS =====
        [
            'type' => 'streak_3day',
            'point' => 5, 'point_min' => 2, 'point_max' => 20, 'point_default' => 5,
            'sort_order' => 120,
            'icon' => '🔗', 'category' => 'engagement', 'group' => 'streak',
            'name' => '3 gün ardıcıl',
            'description' => '3 gün ardıcıl dərs izləmək',
        ],
        [
            'type' => 'streak_7day',
            'point' => 15, 'point_min' => 5, 'point_max' => 50, 'point_default' => 15,
            'sort_order' => 130,
            'icon' => '🔥', 'category' => 'engagement', 'group' => 'streak',
            'name' => '7 gün ardıcıl',
            'description' => '7 gün ardıcıl dərs izləmək',
        ],
        [
            'type' => 'streak_30day',
            'point' => 50, 'point_min' => 20, 'point_max' => 100, 'point_default' => 50,
            'sort_order' => 140,
            'icon' => '💎', 'category' => 'engagement', 'group' => 'streak',
            'name' => '30 gün ardıcıl',
            'description' => '30 gün ardıcıl dərs izləmək',
        ],
    ];

    public function run(): void
    {
        foreach ($this->stars as $data) {
            Star::firstOrCreate(
                ['type' => $data['type']],
                $data
            );
        }
    }
}
