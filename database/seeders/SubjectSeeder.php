<?php

namespace Database\Seeders;

use App\Modules\Subject\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['az' => 'Riyaziyyat', 'en' => 'Mathematics', 'ru' => 'Математика'],
            ['az' => 'Fizika', 'en' => 'Physics', 'ru' => 'Физика'],
            ['az' => 'Kimya', 'en' => 'Chemistry', 'ru' => 'Химия'],
            ['az' => 'Biologiya', 'en' => 'Biology', 'ru' => 'Биология'],
            ['az' => 'Tarix', 'en' => 'History', 'ru' => 'История'],
            ['az' => 'Coğrafiya', 'en' => 'Geography', 'ru' => 'География'],
            ['az' => 'Azərbaycan dili', 'en' => 'Azerbaijani Language', 'ru' => 'Азербайджанский язык'],
            ['az' => 'İngilis dili', 'en' => 'English Language', 'ru' => 'Английский язык'],
            ['az' => 'Ədəbiyyat', 'en' => 'Literature', 'ru' => 'Литература'],
            ['az' => 'İnformatika', 'en' => 'Computer Science', 'ru' => 'Информатика'],
        ];

        foreach ($subjects as $name) {
            Subject::create(['name' => $name]);
        }
    }
}
