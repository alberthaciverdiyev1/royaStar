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
            'name' => 'Daily Login',
            'description' => 'Log in every day',
        ],
        [
            'type' => 'login_streak',
            'point' => 10, 'point_min' => 5, 'point_max' => 50, 'point_default' => 10,
            'sort_order' => 20,
            'icon' => '⚡', 'category' => 'engagement', 'group' => 'daily',
            'name' => 'Login Streak Week',
            'description' => 'Log in for 7 consecutive days',
        ],
        [
            'type' => 'comment_added',
            'point' => 2, 'point_min' => 1, 'point_max' => 10, 'point_default' => 2,
            'max_per_day' => 5, 'sort_order' => 30,
            'icon' => '💬', 'category' => 'engagement', 'group' => 'social',
            'name' => 'Comment Added',
            'description' => 'Write a comment on a lesson or article',
        ],

        // ===== LEARNING =====
        [
            'type' => 'lesson_completed',
            'point' => 5, 'point_min' => 2, 'point_max' => 20, 'point_default' => 5,
            'sort_order' => 40,
            'icon' => '📚', 'category' => 'learning', 'group' => 'lesson',
            'name' => 'Lesson Completed',
            'description' => 'Watch and complete a lesson at 100%',
        ],
        [
            'type' => 'video_watched',
            'point' => 1, 'point_min' => 1, 'point_max' => 5, 'point_default' => 1,
            'max_per_day' => 20, 'sort_order' => 50,
            'icon' => '🎥', 'category' => 'learning', 'group' => 'lesson',
            'name' => 'Video Watched',
            'description' => 'Watch a video within a lesson',
        ],
        [
            'type' => 'quiz_completed',
            'point' => 10, 'point_min' => 5, 'point_max' => 30, 'point_default' => 10,
            'sort_order' => 60,
            'icon' => '📝', 'category' => 'learning', 'group' => 'quiz',
            'name' => 'Quiz Completed',
            'description' => 'Complete a quiz or test',
        ],
        [
            'type' => 'quiz_perfect',
            'point' => 20, 'point_min' => 10, 'point_max' => 50, 'point_default' => 20,
            'sort_order' => 70,
            'icon' => '🏆', 'category' => 'learning', 'group' => 'quiz',
            'name' => 'Perfect Quiz',
            'description' => 'Score 100% on a quiz',
        ],

        // ===== ACHIEVEMENT =====
        [
            'type' => 'exam_passed',
            'point' => 30, 'point_min' => 10, 'point_max' => 100, 'point_default' => 30,
            'sort_order' => 80,
            'icon' => '🎓', 'category' => 'achievement', 'group' => 'exam',
            'name' => 'Exam Passed',
            'description' => 'Pass an exam with minimum score',
        ],
        [
            'type' => 'exam_excellent',
            'point' => 50, 'point_min' => 20, 'point_max' => 150, 'point_default' => 50,
            'sort_order' => 90,
            'icon' => '👑', 'category' => 'achievement', 'group' => 'exam',
            'name' => 'Excellent Exam',
            'description' => 'Score 90% or above on an exam',
        ],
        [
            'type' => 'first_quiz',
            'point' => 15, 'point_min' => 5, 'point_max' => 30, 'point_default' => 15,
            'sort_order' => 100,
            'icon' => '🌟', 'category' => 'achievement', 'group' => 'onboarding',
            'name' => 'First Quiz',
            'description' => 'Complete your first quiz',
        ],
        [
            'type' => 'profile_completed',
            'point' => 25, 'point_min' => 10, 'point_max' => 50, 'point_default' => 25,
            'sort_order' => 110,
            'icon' => '🎯', 'category' => 'achievement', 'group' => 'onboarding',
            'name' => 'Profile Completed',
            'description' => 'Complete your profile information',
        ],

        // ===== STREAKS =====
        [
            'type' => 'streak_3day',
            'point' => 5, 'point_min' => 2, 'point_max' => 20, 'point_default' => 5,
            'sort_order' => 120,
            'icon' => '🔗', 'category' => 'engagement', 'group' => 'streak',
            'name' => '3-Day Streak',
            'description' => 'Study for 3 consecutive days',
        ],
        [
            'type' => 'streak_7day',
            'point' => 15, 'point_min' => 5, 'point_max' => 50, 'point_default' => 15,
            'sort_order' => 130,
            'icon' => '🔥', 'category' => 'engagement', 'group' => 'streak',
            'name' => '7-Day Streak',
            'description' => 'Study for 7 consecutive days',
        ],
        [
            'type' => 'streak_30day',
            'point' => 50, 'point_min' => 20, 'point_max' => 100, 'point_default' => 50,
            'sort_order' => 140,
            'icon' => '💎', 'category' => 'engagement', 'group' => 'streak',
            'name' => '30-Day Streak',
            'description' => 'Study for 30 consecutive days',
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
