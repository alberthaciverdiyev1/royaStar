<?php

use App\Modules\Topic\Enums\DifficultyLevel;
use App\Modules\Topic\Models\Topic;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'api']);
});

// ─── List Topics ────────────────────────────────────────────────

it('lists all topics', function () {
    $user = User::factory()->create(['type' => 'student']);
    Topic::create(['name' => 'Algebra', 'difficulty_level' => 1]);
    Topic::create(['name' => 'Geometry', 'difficulty_level' => 2]);

    $response = $this->actingAs($user)->getJson('/api/topics');

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'status_code', 'data'])
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data'))->toHaveCount(2);
});

it('returns empty array when no topics exist', function () {
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->getJson('/api/topics');

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data'))->toBe([]);
});

// ─── Show Topic ─────────────────────────────────────────────────

it('shows a topic by id', function () {
    $user = User::factory()->create(['type' => 'student']);
    $topic = Topic::create(['name' => 'Algebra', 'difficulty_level' => 1]);

    $response = $this->actingAs($user)->getJson("/api/topics/{$topic->id}");

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data.id'))->toBe($topic->id);
    expect($response->json('data.name'))->toBe('Algebra');
});

it('returns 404 when showing non-existent topic', function () {
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->getJson('/api/topics/99999');

    $response->assertStatus(404)
        ->assertJson(['success' => false]);
});

// ─── Store Topic (Admin Only) ───────────────────────────────────

it('creates a topic as admin', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/topics', [
        'name' => 'Algebra',
        'difficulty_level' => DifficultyLevel::Beginner->value,
    ]);

    $response->assertStatus(201)
        ->assertJson(['success' => true, 'status_code' => 201]);
    expect($response->json('data.name'))->toBe('Algebra');
    $this->assertDatabaseHas('topics', ['id' => $response->json('data.id')]);
});

it('fails to create topic without admin role', function () {
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->postJson('/api/admin/topics', [
        'name' => 'Algebra',
        'difficulty_level' => 1,
    ]);

    $response->assertStatus(403);
});

it('fails to create topic without authentication', function () {
    $response = $this->postJson('/api/admin/topics', [
        'name' => 'Algebra',
        'difficulty_level' => 1,
    ]);

    $response->assertStatus(401);
});

it('fails to create topic with invalid difficulty_level', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/topics', [
        'name' => 'Algebra',
        'difficulty_level' => 99,
    ]);

    $response->assertStatus(422)
        ->assertJson(['success' => false]);
});

// ─── Update Topic (Admin Only) ──────────────────────────────────

it('updates a topic as admin', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $topic = Topic::create(['name' => 'Old Topic', 'difficulty_level' => 1]);

    $response = $this->actingAs($admin)->putJson("/api/admin/topics/{$topic->id}", [
        'name' => 'Updated Topic',
        'difficulty_level' => DifficultyLevel::Advanced->value,
    ]);

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data.name'))->toBe('Updated Topic');
    expect($response->json('data.difficulty_level'))->toBe(DifficultyLevel::Advanced->value);
});

it('returns 404 when updating non-existent topic', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->putJson('/api/admin/topics/99999', [
        'name' => 'Test',
    ]);

    $response->assertStatus(404);
});

it('fails to update topic without admin role', function () {
    $topic = Topic::create(['name' => 'Test', 'difficulty_level' => 1]);
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->putJson("/api/admin/topics/{$topic->id}", [
        'name' => 'Hacked',
    ]);

    $response->assertStatus(403);
});

it('fails to update topic with invalid difficulty_level', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $topic = Topic::create(['name' => 'Test', 'difficulty_level' => 1]);

    $response = $this->actingAs($admin)->putJson("/api/admin/topics/{$topic->id}", [
        'difficulty_level' => 99,
    ]);

    $response->assertStatus(422);
});

// ─── Delete Topic (Admin Only) ──────────────────────────────────

it('deletes a topic as admin', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $topic = Topic::create(['name' => 'To Delete', 'difficulty_level' => 1]);

    $response = $this->actingAs($admin)->deleteJson("/api/admin/topics/{$topic->id}");

    $response->assertStatus(200);
    $this->assertSoftDeleted($topic);
});

it('returns 404 when deleting non-existent topic', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->deleteJson('/api/admin/topics/99999');

    $response->assertStatus(404);
});

it('fails to delete topic without admin role', function () {
    $topic = Topic::create(['name' => 'Test', 'difficulty_level' => 1]);
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->deleteJson("/api/admin/topics/{$topic->id}");

    $response->assertStatus(403);
});

it('soft deletes topic instead of hard delete', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $topic = Topic::create(['name' => 'Soft Delete', 'difficulty_level' => 1]);

    $this->actingAs($admin)->deleteJson("/api/admin/topics/{$topic->id}");

    $this->assertDatabaseHas('topics', ['id' => $topic->id]);
    expect(Topic::find($topic->id))->toBeNull();
    expect(Topic::withTrashed()->find($topic->id))->not->toBeNull();
});

// ─── Admin Access ───────────────────────────────────────────────

it('allows admin to list topics', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    Topic::create(['name' => 'Algebra', 'difficulty_level' => 1]);

    $response = $this->actingAs($admin)->getJson('/api/topics');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(1);
});

it('allows admin to show a topic', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $topic = Topic::create(['name' => 'Algebra', 'difficulty_level' => 1]);

    $response = $this->actingAs($admin)->getJson("/api/topics/{$topic->id}");

    $response->assertStatus(200);
});
