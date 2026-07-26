<?php

use App\Modules\Subject\Models\Subject;
use App\Modules\Topic\Models\Topic;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'api']);
});

describe('Subjects', function () {
    it('lists subjects', function () {
        $user = User::factory()->create(['type' => 'student']);
        Subject::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson('/api/subjects');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    });

    it('creates a subject as admin', function () {
        $admin = User::factory()->create(['type' => 'admin']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->postJson('/api/admin/subjects', ['name' => 'Mathematics']);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Mathematics');
    });

    it('shows a subject', function () {
        $user = User::factory()->create(['type' => 'student']);
        $subject = Subject::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/subjects/{$subject->id}");

        $response->assertStatus(200);
    });

    it('updates a subject as admin', function () {
        $admin = User::factory()->create(['type' => 'admin']);
        $admin->assignRole('admin');
        $subject = Subject::factory()->create();

        $response = $this->actingAs($admin)->putJson("/api/admin/subjects/{$subject->id}", ['name' => 'Updated']);

        $response->assertStatus(200);
    });

    it('deletes a subject as admin', function () {
        $admin = User::factory()->create(['type' => 'admin']);
        $admin->assignRole('admin');
        $subject = Subject::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/admin/subjects/{$subject->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted($subject);
    });

    it('validates required fields on create', function () {
        $admin = User::factory()->create(['type' => 'admin']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->postJson('/api/admin/subjects', []);

        $response->assertStatus(422);
    });
});

describe('Topics', function () {
    it('creates a topic under a subject as admin', function () {
        $admin = User::factory()->create(['type' => 'admin']);
        $admin->assignRole('admin');
        $subject = Subject::factory()->create();

        $response = $this->actingAs($admin)->postJson("/api/admin/subjects/{$subject->id}/topics", [
            'name' => 'Algebra',
            'difficulty_level' => 1,
        ]);

        $response->assertStatus(201);
    });

    it('lists topics filtered by subject', function () {
        $user = User::factory()->create(['type' => 'student']);
        $subject = Subject::factory()->create();
        Topic::factory()->count(2)->create(['subject_id' => $subject->id]);

        $response = $this->actingAs($user)->getJson("/api/subjects/{$subject->id}/topics");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
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
