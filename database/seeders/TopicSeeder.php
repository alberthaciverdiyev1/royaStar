<?php

namespace Database\Seeders;

use App\Modules\Topic\Enums\DifficultyLevel;
use App\Modules\Topic\Models\Topic;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    public function run(): void
    {
        $topics = [
            ['name' => 'Qrammatika', 'difficulty_level' => DifficultyLevel::Elementary, 'grades' => [2, 3, 4, 5]],
            ['name' => 'Lüğət ehtiyatı', 'difficulty_level' => DifficultyLevel::Beginner, 'grades' => [1, 2, 3, 4]],
            ['name' => 'Oxu', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [4, 5, 6, 7]],
            ['name' => 'Danışıq və dinləmə', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [3, 4, 5, 6, 7]],
            ['name' => 'Yazı', 'difficulty_level' => DifficultyLevel::Advanced, 'grades' => [5, 6, 7, 8]],
        ];

        foreach ($topics as $topic) {
            $grades = $topic['grades'] ?? [];
            unset($topic['grades']);

            $model = Topic::create($topic);

            if (!empty($grades)) {
                $model->grades()->sync($grades);
            }
        }
    }
}
