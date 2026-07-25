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
                ['az' => 'Rəqəmlər', 'en' => 'Numbers', 'difficulty' => DifficultyLevel::Beginner, 'grades' => [1, 2, 3, 4, 5]],
                ['az' => 'Cəbr', 'en' => 'Algebra', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [7, 8, 9]],
                ['az' => 'Həndəsə', 'en' => 'Geometry', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [7, 8, 9]],
                ['az' => 'Triqonometriya', 'en' => 'Trigonometry', 'difficulty' => DifficultyLevel::Advanced, 'grades' => [10, 11]],
                ['az' => 'Statistika', 'en' => 'Statistics', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [9, 10, 11]],
            ],
            2 => [ // Fizika
                ['az' => 'Mexanika', 'en' => 'Mechanics', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [7, 8, 9]],
                ['az' => 'Termodinamika', 'en' => 'Thermodynamics', 'difficulty' => DifficultyLevel::Advanced, 'grades' => [9, 10, 11]],
                ['az' => 'Optika', 'en' => 'Optics', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [8, 9, 10]],
                ['az' => 'Elektromaqnetizm', 'en' => 'Electromagnetism', 'difficulty' => DifficultyLevel::Advanced, 'grades' => [9, 10, 11]],
                ['az' => 'Nüvə fizikası', 'en' => 'Nuclear Physics', 'difficulty' => DifficultyLevel::Expert, 'grades' => [10, 11]],
            ],
            3 => [ // Kimya
                ['az' => 'Atom quruluşu', 'en' => 'Atomic Structure', 'difficulty' => DifficultyLevel::Elementary, 'grades' => [7, 8]],
                ['az' => 'Kimyəvi rabitələr', 'en' => 'Chemical Bonds', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [8, 9]],
                ['az' => 'Reaksiyalar', 'en' => 'Reactions', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [8, 9, 10]],
                ['az' => 'Üzvi kimya', 'en' => 'Organic Chemistry', 'difficulty' => DifficultyLevel::Advanced, 'grades' => [10, 11]],
                ['az' => 'Məhlullar', 'en' => 'Solutions', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [9, 10, 11]],
            ],
            4 => [ // Biologiya
                ['az' => 'Hüceyrə biologiyası', 'en' => 'Cell Biology', 'difficulty' => DifficultyLevel::Elementary, 'grades' => [5, 6, 7]],
                ['az' => 'Genetika', 'en' => 'Genetics', 'difficulty' => DifficultyLevel::Advanced, 'grades' => [9, 10, 11]],
                ['az' => 'İnsan fiziologiyası', 'en' => 'Human Physiology', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [8, 9, 10, 11]],
                ['az' => 'Ekologiya', 'en' => 'Ecology', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [5, 6, 7, 8]],
                ['az' => 'Botanika', 'en' => 'Botany', 'difficulty' => DifficultyLevel::Elementary, 'grades' => [5, 6, 7]],
            ],
            5 => [ // Tarix
                ['az' => 'Qədim tarix', 'en' => 'Ancient History', 'difficulty' => DifficultyLevel::Elementary, 'grades' => [5, 6, 7]],
                ['az' => 'Orta əsrlər', 'en' => 'Medieval History', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [6, 7, 8]],
                ['az' => 'Yeni dövr', 'en' => 'Modern History', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [9, 10, 11]],
                ['az' => 'Müasir tarix', 'en' => 'Contemporary History', 'difficulty' => DifficultyLevel::Advanced, 'grades' => [10, 11]],
                ['az' => 'Arxeologiya', 'en' => 'Archaeology', 'difficulty' => DifficultyLevel::Elementary, 'grades' => [5, 6, 7]],
            ],
            6 => [ // Coğrafiya
                ['az' => 'Fiziki coğrafiya', 'en' => 'Physical Geography', 'difficulty' => DifficultyLevel::Elementary, 'grades' => [5, 6, 7]],
                ['az' => 'İqtisadi coğrafiya', 'en' => 'Economic Geography', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [9, 10, 11]],
                ['az' => 'Xəritəşünaslıq', 'en' => 'Map Reading', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [5, 6, 7, 8]],
                ['az' => 'İqlim', 'en' => 'Climate', 'difficulty' => DifficultyLevel::Elementary, 'grades' => [6, 7, 8]],
                ['az' => 'Demoqrafiya', 'en' => 'Demography', 'difficulty' => DifficultyLevel::Advanced, 'grades' => [9, 10, 11]],
            ],
            7 => [ // Azərbaycan dili
                ['az' => 'Qrammatika', 'en' => 'Grammar', 'difficulty' => DifficultyLevel::Elementary, 'grades' => [1, 2, 3, 4]],
                ['az' => 'Orfoqrafiya', 'en' => 'Spelling', 'difficulty' => DifficultyLevel::Elementary, 'grades' => [1, 2, 3, 4]],
                ['az' => 'Oxu anlama', 'en' => 'Reading Comprehension', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [3, 4, 5, 6]],
                ['az' => 'Yazı', 'en' => 'Writing', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [4, 5, 6, 7]],
                ['az' => 'Nitq mədəniyyəti', 'en' => 'Speech Culture', 'difficulty' => DifficultyLevel::Advanced, 'grades' => [8, 9, 10, 11]],
            ],
            8 => [ // İngilis dili
                ['az' => 'Qrammatika', 'en' => 'Grammar', 'difficulty' => DifficultyLevel::Elementary, 'grades' => [2, 3, 4, 5]],
                ['az' => 'Lüğət ehtiyatı', 'en' => 'Vocabulary', 'difficulty' => DifficultyLevel::Beginner, 'grades' => [1, 2, 3, 4]],
                ['az' => 'Oxu', 'en' => 'Reading', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [4, 5, 6, 7]],
                ['az' => 'Danışıq və dinləmə', 'en' => 'Speaking & Listening', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [3, 4, 5, 6, 7]],
                ['az' => 'Yazı', 'en' => 'Writing', 'difficulty' => DifficultyLevel::Advanced, 'grades' => [5, 6, 7, 8]],
            ],
            9 => [ // Ədəbiyyat
                ['az' => 'Poeziya', 'en' => 'Poetry', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [5, 6, 7, 8]],
                ['az' => 'Nəsr', 'en' => 'Prose', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [7, 8, 9, 10, 11]],
                ['az' => 'Dram', 'en' => 'Drama', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [8, 9, 10, 11]],
                ['az' => 'Ədəbi təhlil', 'en' => 'Literary Analysis', 'difficulty' => DifficultyLevel::Advanced, 'grades' => [10, 11]],
                ['az' => 'Folklor', 'en' => 'Folklore', 'difficulty' => DifficultyLevel::Elementary, 'grades' => [3, 4, 5, 6]],
            ],
            10 => [ // İnformatika
                ['az' => 'Proqramlaşdırma', 'en' => 'Programming', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [7, 8, 9, 10, 11]],
                ['az' => 'Alqoritmlər', 'en' => 'Algorithms', 'difficulty' => DifficultyLevel::Advanced, 'grades' => [8, 9, 10, 11]],
                ['az' => 'Verilənlər strukturu', 'en' => 'Data Structures', 'difficulty' => DifficultyLevel::Advanced, 'grades' => [9, 10, 11]],
                ['az' => 'Şəbəkələr', 'en' => 'Networks', 'difficulty' => DifficultyLevel::Intermediate, 'grades' => [7, 8, 9]],
                ['az' => 'Verilənlər bazası', 'en' => 'Databases', 'difficulty' => DifficultyLevel::Advanced, 'grades' => [9, 10, 11]],
            ],
        ];

        foreach ($topics as $subjectId => $subjectTopics) {
            foreach ($subjectTopics as $topic) {
                $grades = $topic['grades'] ?? [];
                unset($topic['grades']);

                $model = Topic::create([
                    'subject_id' => $subjectId,
                    'name' => ['az' => $topic['az'], 'en' => $topic['en']],
                    'difficulty_level' => $topic['difficulty'],
                ]);

                if (!empty($grades)) {
                    $model->grades()->sync($grades);
                }
            }
        }
    }
}
