<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CitySeeder::class,
            GradeSeeder::class,
            SubjectSeeder::class,
            TopicSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
