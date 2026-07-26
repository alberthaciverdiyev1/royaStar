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
            1 => [ // Riyaziyyat
                ['name' => 'Rəqəmlər', 'difficulty_level' => DifficultyLevel::Beginner, 'grades' => [1, 2, 3, 4, 5]],
                ['name' => 'Cəbr', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [7, 8, 9]],
                ['name' => 'Həndəsə', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [7, 8, 9]],
                ['name' => 'Triqonometriya', 'difficulty_level' => DifficultyLevel::Advanced, 'grades' => [10, 11]],
                ['name' => 'Statistika', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [9, 10, 11]],
            ],
            2 => [ // Fizika
                ['name' => 'Mexanika', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [7, 8, 9]],
                ['name' => 'Termodinamika', 'difficulty_level' => DifficultyLevel::Advanced, 'grades' => [9, 10, 11]],
                ['name' => 'Optika', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [8, 9, 10]],
                ['name' => 'Elektromaqnetizm', 'difficulty_level' => DifficultyLevel::Advanced, 'grades' => [9, 10, 11]],
                ['name' => 'Nüvə fizikası', 'difficulty_level' => DifficultyLevel::Expert, 'grades' => [10, 11]],
            ],
            3 => [ // Kimya
                ['name' => 'Atom quruluşu', 'difficulty_level' => DifficultyLevel::Elementary, 'grades' => [7, 8]],
                ['name' => 'Kimyəvi rabitələr', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [8, 9]],
                ['name' => 'Reaksiyalar', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [8, 9, 10]],
                ['name' => 'Üzvi kimya', 'difficulty_level' => DifficultyLevel::Advanced, 'grades' => [10, 11]],
                ['name' => 'Məhlullar', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [9, 10, 11]],
            ],
            4 => [ // Biologiya
                ['name' => 'Hüceyrə biologiyası', 'difficulty_level' => DifficultyLevel::Elementary, 'grades' => [5, 6, 7]],
                ['name' => 'Genetika', 'difficulty_level' => DifficultyLevel::Advanced, 'grades' => [9, 10, 11]],
                ['name' => 'İnsan fiziologiyası', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [8, 9, 10, 11]],
                ['name' => 'Ekologiya', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [5, 6, 7, 8]],
                ['name' => 'Botanika', 'difficulty_level' => DifficultyLevel::Elementary, 'grades' => [5, 6, 7]],
            ],
            5 => [ // Tarix
                ['name' => 'Qədim tarix', 'difficulty_level' => DifficultyLevel::Elementary, 'grades' => [5, 6, 7]],
                ['name' => 'Orta əsrlər', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [6, 7, 8]],
                ['name' => 'Yeni dövr', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [9, 10, 11]],
                ['name' => 'Müasir tarix', 'difficulty_level' => DifficultyLevel::Advanced, 'grades' => [10, 11]],
                ['name' => 'Arxeologiya', 'difficulty_level' => DifficultyLevel::Elementary, 'grades' => [5, 6, 7]],
            ],
            6 => [ // Coğrafiya
                ['name' => 'Fiziki coğrafiya', 'difficulty_level' => DifficultyLevel::Elementary, 'grades' => [5, 6, 7]],
                ['name' => 'İqtisadi coğrafiya', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [9, 10, 11]],
                ['name' => 'Xəritəşünaslıq', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [5, 6, 7, 8]],
                ['name' => 'İqlim', 'difficulty_level' => DifficultyLevel::Elementary, 'grades' => [6, 7, 8]],
                ['name' => 'Demoqrafiya', 'difficulty_level' => DifficultyLevel::Advanced, 'grades' => [9, 10, 11]],
            ],
            7 => [ // Azərbaycan dili
                ['name' => 'Qrammatika', 'difficulty_level' => DifficultyLevel::Elementary, 'grades' => [1, 2, 3, 4]],
                ['name' => 'Orfoqrafiya', 'difficulty_level' => DifficultyLevel::Elementary, 'grades' => [1, 2, 3, 4]],
                ['name' => 'Oxu anlama', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [3, 4, 5, 6]],
                ['name' => 'Yazı', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [4, 5, 6, 7]],
                ['name' => 'Nitq mədəniyyəti', 'difficulty_level' => DifficultyLevel::Advanced, 'grades' => [8, 9, 10, 11]],
            ],
            8 => [ // İngilis dili
                ['name' => 'Qrammatika', 'difficulty_level' => DifficultyLevel::Elementary, 'grades' => [2, 3, 4, 5]],
                ['name' => 'Lüğət ehtiyatı', 'difficulty_level' => DifficultyLevel::Beginner, 'grades' => [1, 2, 3, 4]],
                ['name' => 'Oxu', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [4, 5, 6, 7]],
                ['name' => 'Danışıq və dinləmə', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [3, 4, 5, 6, 7]],
                ['name' => 'Yazı', 'difficulty_level' => DifficultyLevel::Advanced, 'grades' => [5, 6, 7, 8]],
            ],
            9 => [ // Ədəbiyyat
                ['name' => 'Poeziya', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [5, 6, 7, 8]],
                ['name' => 'Nəsr', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [7, 8, 9, 10, 11]],
                ['name' => 'Dram', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [8, 9, 10, 11]],
                ['name' => 'Ədəbi təhlil', 'difficulty_level' => DifficultyLevel::Advanced, 'grades' => [10, 11]],
                ['name' => 'Folklor', 'difficulty_level' => DifficultyLevel::Elementary, 'grades' => [3, 4, 5, 6]],
            ],
            10 => [ // İnformatika
                ['name' => 'Proqramlaşdırma', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [7, 8, 9, 10, 11]],
                ['name' => 'Alqoritmlər', 'difficulty_level' => DifficultyLevel::Advanced, 'grades' => [8, 9, 10, 11]],
                ['name' => 'Verilənlər strukturu', 'difficulty_level' => DifficultyLevel::Advanced, 'grades' => [9, 10, 11]],
                ['name' => 'Şəbəkələr', 'difficulty_level' => DifficultyLevel::Intermediate, 'grades' => [7, 8, 9]],
                ['name' => 'Verilənlər bazası', 'difficulty_level' => DifficultyLevel::Advanced, 'grades' => [9, 10, 11]],
            ],
        ];

        foreach ($topics as $subjectId => $subjectTopics) {
            foreach ($subjectTopics as $topic) {
                $grades = $topic['grades'] ?? [];
                unset($topic['grades']);

                $model = Topic::create(array_merge(
                    $topic,
                    ['subject_id' => $subjectId]
                ));

                if (!empty($grades)) {
                    $model->grades()->sync($grades);
                }
            }
        }
    }
}
