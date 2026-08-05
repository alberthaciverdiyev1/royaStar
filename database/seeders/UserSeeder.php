<?php

namespace Database\Seeders;

use App\Modules\City\Models\City;
use App\Modules\Grade\Models\Grade;
use App\Modules\Student\Models\Student;
use App\Modules\User\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $grade = Grade::query()->first();
        $city = City::query()->first();
        $admin = User::create([
            'name' => 'Admin',
            'surname' => 'Super',
            'phone' => '5550000000',
            'email' => 'admin@royastar.com',
            'password' => bcrypt(env('ADMIN_SEED_PASSWORD', 'password')),
            'type' => 'admin',
            'is_approved' => true,
        ]);
        $admin->assignRole('super-admin');

        $student1 = User::create([
            'name' => 'Səid',
            'surname' => 'Rəhimli',
            'phone' => '5550000004',
            'email' => 'said@royastar.com',
            'password' => bcrypt('password'),
            'type' => 'student',
            'is_approved' => true,
        ]);
        $student1->assignRole('student');
        Student::create([
            'user_id' => $student1->id,
            'grade_id' => $grade?->id,
            'city_id' => $city?->id,
        ]);

        $student2 = User::create([
            'name' => 'Aysu',
            'surname' => 'Kərimova',
            'phone' => '5550000005',
            'email' => 'aysu@royastar.com',
            'password' => bcrypt('password'),
            'type' => 'student',
            'is_approved' => true,
        ]);
        $student2->assignRole('student');
        Student::create([
            'user_id' => $student2->id,
            'grade_id' => $grade?->id,
            'city_id' => $city?->id,
        ]);

        $editor = User::create([
            'name' => 'Editor',
            'surname' => 'User',
            'phone' => '5550000007',
            'email' => 'editor@royastar.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
            'is_approved' => true,
        ]);
        $editor->assignRole('admin');
    }
}
