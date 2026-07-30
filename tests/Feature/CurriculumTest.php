<?php

use App\Modules\Topic\Models\Topic;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'api']);
});

describe('Topics', function () {
    it('creates a topic as admin', function () {
        $admin = User::factory()->create(['type' => 'admin']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->postJson('/api/admin/topics', [
            'name' => 'Algebra',
            'difficulty_level' => 1,
        ]);

        $response->assertStatus(201);
    });

    it('lists all topics', function () {
        $user = User::factory()->create(['type' => 'student']);

        $response = $this->actingAs($user)->getJson('/api/topics');

        $response->assertStatus(200);
    });
});

describe('Lessons', function () {
    it('creates a lesson under a topic as admin', function () {
        $admin = User::factory()->create(['type' => 'admin']);
        $admin->assignRole('admin');
        $topic = Topic::factory()->create();

        $response = $this->actingAs($admin)->postJson("/api/admin/topics/{$topic->id}/lessons", [
            'name' => 'Linear Equations',
            'description' => 'Introduction to linear equations',
        ]);

        $response->assertStatus(201);
    });
});
