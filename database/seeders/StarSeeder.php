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
            'name' => ['az' => 'Gündəlik giriş', 'en' => 'Daily Login', 'ru' => 'Ежедневный вход'],
            'description' => ['az' => 'Hər gün sistemə daxil olmaq', 'en' => 'Log in every day', 'ru' => 'Входите каждый день'],
        ],
        [
            'type' => 'login_streak',
            'point' => 10, 'point_min' => 5, 'point_max' => 50, 'point_default' => 10,
            'sort_order' => 20,
            'icon' => '⚡', 'category' => 'engagement', 'group' => 'daily',
            'name' => ['az' => 'Ardıcıl giriş həftəsi', 'en' => 'Login Streak Week', 'ru' => 'Неделя входов'],
            'description' => ['az' => '7 gün ardıcıl sistemə daxil olmaq', 'en' => 'Log in for 7 consecutive days', 'ru' => 'Вход 7 дней подряд'],
        ],
        [
            'type' => 'comment_added',
            'point' => 2, 'point_min' => 1, 'point_max' => 10, 'point_default' => 2,
            'max_per_day' => 5, 'sort_order' => 30,
            'icon' => '💬', 'category' => 'engagement', 'group' => 'social',
            'name' => ['az' => 'Şərh yazıldı', 'en' => 'Comment Added', 'ru' => 'Добавлен комментарий'],
            'description' => ['az' => 'Dərs və ya məqaləyə şərh yazmaq', 'en' => 'Write a comment on a lesson or article', 'ru' => 'Написать комментарий к уроку или статье'],
        ],

        // ===== LEARNING =====
        [
            'type' => 'lesson_completed',
            'point' => 5, 'point_min' => 2, 'point_max' => 20, 'point_default' => 5,
            'sort_order' => 40,
            'icon' => '📚', 'category' => 'learning', 'group' => 'lesson',
            'name' => ['az' => 'Dərs tamamlandı', 'en' => 'Lesson Completed', 'ru' => 'Урок завершен'],
            'description' => ['az' => 'Dərsi 100% izləyib tamamlamaq', 'en' => 'Watch and complete a lesson at 100%', 'ru' => 'Просмотреть и завершить урок на 100%'],
        ],
        [
            'type' => 'video_watched',
            'point' => 1, 'point_min' => 1, 'point_max' => 5, 'point_default' => 1,
            'max_per_day' => 20, 'sort_order' => 50,
            'icon' => '🎥', 'category' => 'learning', 'group' => 'lesson',
            'name' => ['az' => 'Video izləndi', 'en' => 'Video Watched', 'ru' => 'Видео просмотрено'],
            'description' => ['az' => 'Dərs daxilində videonu izləmək', 'en' => 'Watch a video within a lesson', 'ru' => 'Просмотреть видео в уроке'],
        ],
        [
            'type' => 'quiz_completed',
            'point' => 10, 'point_min' => 5, 'point_max' => 30, 'point_default' => 10,
            'sort_order' => 60,
            'icon' => '📝', 'category' => 'learning', 'group' => 'quiz',
            'name' => ['az' => 'Test tamamlandı', 'en' => 'Quiz Completed', 'ru' => 'Тест завершен'],
            'description' => ['az' => 'Quiz/testi tamamlamaq', 'en' => 'Complete a quiz or test', 'ru' => 'Завершить тест'],
        ],
        [
            'type' => 'quiz_perfect',
            'point' => 20, 'point_min' => 10, 'point_max' => 50, 'point_default' => 20,
            'sort_order' => 70,
            'icon' => '🏆', 'category' => 'learning', 'group' => 'quiz',
            'name' => ['az' => 'Mükəmməl test', 'en' => 'Perfect Quiz', 'ru' => 'Идеальный тест'],
            'description' => ['az' => 'Testdən 100% bal toplamaq', 'en' => 'Score 100% on a quiz', 'ru' => 'Набрать 100% в тесте'],
        ],

        // ===== ACHIEVEMENT =====
        [
            'type' => 'exam_passed',
            'point' => 30, 'point_min' => 10, 'point_max' => 100, 'point_default' => 30,
            'sort_order' => 80,
            'icon' => '🎓', 'category' => 'achievement', 'group' => 'exam',
            'name' => ['az' => 'İmtahan keçildi', 'en' => 'Exam Passed', 'ru' => 'Экзамен сдан'],
            'description' => ['az' => 'İmtahandan keçid balı toplamaq', 'en' => 'Pass an exam with minimum score', 'ru' => 'Сдать экзамен с минимальным баллом'],
        ],
        [
            'type' => 'exam_excellent',
            'point' => 50, 'point_min' => 20, 'point_max' => 150, 'point_default' => 50,
            'sort_order' => 90,
            'icon' => '👑', 'category' => 'achievement', 'group' => 'exam',
            'name' => ['az' => 'İmtahan əla', 'en' => 'Excellent Exam', 'ru' => 'Отличный экзамен'],
            'description' => ['az' => 'İmtahandan 90% və yuxarı bal toplamaq', 'en' => 'Score 90% or above on an exam', 'ru' => 'Набрать 90% и выше на экзамене'],
        ],
        [
            'type' => 'first_quiz',
            'point' => 15, 'point_min' => 5, 'point_max' => 30, 'point_default' => 15,
            'sort_order' => 100,
            'icon' => '🌟', 'category' => 'achievement', 'group' => 'onboarding',
            'name' => ['az' => 'İlk test', 'en' => 'First Quiz', 'ru' => 'Первый тест'],
            'description' => ['az' => 'İlk testi tamamlamaq', 'en' => 'Complete your first quiz', 'ru' => 'Завершить первый тест'],
        ],
        [
            'type' => 'profile_completed',
            'point' => 25, 'point_min' => 10, 'point_max' => 50, 'point_default' => 25,
            'sort_order' => 110,
            'icon' => '🎯', 'category' => 'achievement', 'group' => 'onboarding',
            'name' => ['az' => 'Profil tamamlandı', 'en' => 'Profile Completed', 'ru' => 'Профиль заполнен'],
            'description' => ['az' => 'Profil məlumatlarını tam doldurmaq', 'en' => 'Complete your profile information', 'ru' => 'Заполнить информацию профиля'],
        ],

        // ===== STREAKS =====
        [
            'type' => 'streak_3day',
            'point' => 5, 'point_min' => 2, 'point_max' => 20, 'point_default' => 5,
            'sort_order' => 120,
            'icon' => '🔗', 'category' => 'engagement', 'group' => 'streak',
            'name' => ['az' => '3 gün ardıcıl', 'en' => '3-Day Streak', 'ru' => '3 дня подряд'],
            'description' => ['az' => '3 gün ardıcıl dərs izləmək', 'en' => 'Study for 3 consecutive days', 'ru' => 'Заниматься 3 дня подряд'],
        ],
        [
            'type' => 'streak_7day',
            'point' => 15, 'point_min' => 5, 'point_max' => 50, 'point_default' => 15,
            'sort_order' => 130,
            'icon' => '🔥', 'category' => 'engagement', 'group' => 'streak',
            'name' => ['az' => '7 gün ardıcıl', 'en' => '7-Day Streak', 'ru' => '7 дней подряд'],
            'description' => ['az' => '7 gün ardıcıl dərs izləmək', 'en' => 'Study for 7 consecutive days', 'ru' => 'Заниматься 7 дней подряд'],
        ],
        [
            'type' => 'streak_30day',
            'point' => 50, 'point_min' => 20, 'point_max' => 100, 'point_default' => 50,
            'sort_order' => 140,
            'icon' => '💎', 'category' => 'engagement', 'group' => 'streak',
            'name' => ['az' => '30 gün ardıcıl', 'en' => '30-Day Streak', 'ru' => '30 дней подряд'],
            'description' => ['az' => '30 gün ardıcıl dərs izləmək', 'en' => 'Study for 30 consecutive days', 'ru' => 'Заниматься 30 дней подряд'],
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
