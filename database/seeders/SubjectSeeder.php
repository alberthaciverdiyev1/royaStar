<?php

namespace Database\Seeders;

use App\Modules\Subject\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            'Riyaziyyat', 'Fizika', 'Kimya', 'Biologiya', 'Tarix',
            'Coğrafiya', 'Azərbaycan dili', 'İngilis dili', 'Ədəbiyyat', 'İnformatika',
        ];

        foreach ($subjects as $name) {
            Subject::create(['name' => $name]);
        }
    }
}
