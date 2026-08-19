<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
//            RoleSeeder::class,
//            CitySeeder::class,
//            GradeSeeder::class,
//            UserSeeder::class,
//            SettingSeeder::class,
            StarSeeder::class,
//            EnglishDemoSeeder::class,
        ]);
    }
}
