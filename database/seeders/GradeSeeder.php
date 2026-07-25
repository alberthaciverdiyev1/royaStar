<?php

namespace Database\Seeders;

use App\Modules\Grade\Models\Grade;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            ['az' => '1-ci sinif', 'en' => '1st Grade', 'ru' => '1-й класс'],
            ['az' => '2-ci sinif', 'en' => '2nd Grade', 'ru' => '2-й класс'],
            ['az' => '3-cü sinif', 'en' => '3rd Grade', 'ru' => '3-й класс'],
            ['az' => '4-cü sinif', 'en' => '4th Grade', 'ru' => '4-й класс'],
            ['az' => '5-ci sinif', 'en' => '5th Grade', 'ru' => '5-й класс'],
            ['az' => '6-cı sinif', 'en' => '6th Grade', 'ru' => '6-й класс'],
            ['az' => '7-ci sinif', 'en' => '7th Grade', 'ru' => '7-й класс'],
            ['az' => '8-ci sinif', 'en' => '8th Grade', 'ru' => '8-й класс'],
            ['az' => '9-cu sinif', 'en' => '9th Grade', 'ru' => '9-й класс'],
            ['az' => '10-cu sinif', 'en' => '10th Grade', 'ru' => '10-й класс'],
            ['az' => '11-ci sinif', 'en' => '11th Grade', 'ru' => '11-й класс'],
        ];

        foreach ($grades as $name) {
            Grade::create(['name' => $name]);
        }
    }
}
