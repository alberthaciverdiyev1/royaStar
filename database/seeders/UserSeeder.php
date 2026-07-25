<?php

namespace Database\Seeders;

use App\Modules\User\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'surname' => 'Super',
            'phone' => '5550000000',
            'email' => 'admin@royastar.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
        ]);
        $admin->assignRole('super-admin');

        $schoolAdmin = User::create([
            'name' => 'School',
            'surname' => 'Admin',
            'phone' => '5550000001',
            'email' => 'school@royastar.com',
            'password' => bcrypt('password'),
            'type' => 'school',
        ]);
        $schoolAdmin->assignRole('school');

        $teacher1 = User::create([
            'name' => 'Elvin',
            'surname' => 'Məmmədov',
            'phone' => '5550000002',
            'email' => 'elvin@royastar.com',
            'password' => bcrypt('password'),
            'type' => 'teacher',
        ]);
        $teacher1->assignRole('teacher');

        $teacher2 = User::create([
            'name' => 'Leyla',
            'surname' => 'Həsənova',
            'phone' => '5550000003',
            'email' => 'leyla@royastar.com',
            'password' => bcrypt('password'),
            'type' => 'teacher',
        ]);
        $teacher2->assignRole('teacher');

        $student1 = User::create([
            'name' => 'Səid',
            'surname' => 'Rəhimli',
            'phone' => '5550000004',
            'email' => 'said@royastar.com',
            'password' => bcrypt('password'),
            'type' => 'student',
        ]);
        $student1->assignRole('student');

        $student2 = User::create([
            'name' => 'Aysu',
            'surname' => 'Kərimova',
            'phone' => '5550000005',
            'email' => 'aysu@royastar.com',
            'password' => bcrypt('password'),
            'type' => 'student',
        ]);
        $student2->assignRole('student');

        $parent = User::create([
            'name' => 'Tural',
            'surname' => 'Rəhimli',
            'phone' => '5550000006',
            'email' => 'tural@royastar.com',
            'password' => bcrypt('password'),
            'type' => 'parent',
        ]);
        $parent->assignRole('parent');

        $editor = User::create([
            'name' => 'Editor',
            'surname' => 'User',
            'phone' => '5550000007',
            'email' => 'editor@royastar.com',
            'password' => bcrypt('password'),
            'type' => 'admin',
        ]);
        $editor->assignRole('admin');
    }
}
