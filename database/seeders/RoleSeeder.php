<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Roles
        Role::create(['name' => 'super-admin', 'guard_name' => 'api']);
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'school', 'guard_name' => 'api']);
        Role::create(['name' => 'teacher', 'guard_name' => 'api']);
        Role::create(['name' => 'student', 'guard_name' => 'api']);
        Role::create(['name' => 'parent', 'guard_name' => 'api']);
    }
}
