<?php

use App\Modules\Subject\Models\Subject;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'api']);
});

// ─── List Subjects ──────────────────────────────────────────────

it('lists all subjects', function () {
    $user = User::factory()->create(['type' => 'student']);
    Subject::create(['name' => 'Math']);
    Subject::create(['name' => 'Science']);

    $response = $this->actingAs($user)->getJson('/api/subjects');

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'status_code', 'data'])
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data'))->toHaveCount(2);
});

it('returns empty array when no subjects exist', function () {
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->getJson('/api/subjects');

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data'))->toBe([]);
});

// ─── Show Subject ───────────────────────────────────────────────

it('shows a subject by id', function () {
    $user = User::factory()->create(['type' => 'student']);
    $subject = Subject::create(['name' => 'Mathematics']);

    $response = $this->actingAs($user)->getJson("/api/subjects/{$subject->id}");

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data.id'))->toBe($subject->id);
    expect($response->json('data.name'))->toBe('Mathematics');
});

it('returns 404 when showing non-existent subject', function () {
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->getJson('/api/subjects/99999');

    $response->assertStatus(404)
        ->assertJson(['success' => false]);
});

// ─── Store Subject (Admin Only) ─────────────────────────────────

it('creates a subject as admin', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/subjects', [
        'name' => 'Mathematics',
    ]);

    $response->assertStatus(201)
        ->assertJson(['success' => true, 'status_code' => 201]);
    expect($response->json('data.name'))->toBe('Mathematics');
    $this->assertDatabaseHas('subjects', ['id' => $response->json('data.id')]);
});

it('fails to create subject without admin role', function () {
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->postJson('/api/admin/subjects', [
        'name' => 'Mathematics',
    ]);

    $response->assertStatus(403);
});

it('fails to create subject without authentication', function () {
    $response = $this->postJson('/api/admin/subjects', [
        'name' => 'Mathematics',
    ]);

    $response->assertStatus(401);
});

it('fails to create subject with missing name', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/subjects', []);

    $response->assertStatus(422)
        ->assertJson(['success' => false]);
});

// ─── Update Subject (Admin Only) ────────────────────────────────

it('updates a subject as admin', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $subject = Subject::create(['name' => 'Old Name']);

    $response = $this->actingAs($admin)->putJson("/api/admin/subjects/{$subject->id}", [
        'name' => 'Updated Name',
    ]);

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data.name'))->toBe('Updated Name');
});

it('returns 404 when updating non-existent subject', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->putJson('/api/admin/subjects/99999', [
        'name' => 'Test',
    ]);

    $response->assertStatus(404);
});

it('fails to update subject without admin role', function () {
    $subject = Subject::create(['name' => 'Test']);
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->putJson("/api/admin/subjects/{$subject->id}", [
        'name' => 'Hacked',
    ]);

    $response->assertStatus(403);
});

// ─── Delete Subject (Admin Only) ────────────────────────────────

it('deletes a subject as admin', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $subject = Subject::create(['name' => 'To Delete']);

    $response = $this->actingAs($admin)->deleteJson("/api/admin/subjects/{$subject->id}");

    $response->assertStatus(200);
    $this->assertSoftDeleted($subject);
});

it('returns 404 when deleting non-existent subject', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->deleteJson('/api/admin/subjects/99999');

    $response->assertStatus(404);
});

it('fails to delete subject without admin role', function () {
    $subject = Subject::create(['name' => 'Test']);
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->deleteJson("/api/admin/subjects/{$subject->id}");

    $response->assertStatus(403);
});

it('soft deletes subject instead of hard delete', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $subject = Subject::create(['name' => 'Soft Delete']);

    $this->actingAs($admin)->deleteJson("/api/admin/subjects/{$subject->id}");

    $this->assertDatabaseHas('subjects', ['id' => $subject->id]);
    expect(Subject::find($subject->id))->toBeNull();
    expect(Subject::withTrashed()->find($subject->id))->not->toBeNull();
});

// ─── Admin Access ───────────────────────────────────────────────

it('allows admin to list subjects', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    Subject::create(['name' => 'Math']);

    $response = $this->actingAs($admin)->getJson('/api/subjects');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(1);
});

it('allows admin to show a subject', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $subject = Subject::create(['name' => 'Math']);

    $response = $this->actingAs($admin)->getJson("/api/subjects/{$subject->id}");

    $response->assertStatus(200);
});

it('shows subject with topics', function () {
    $user = User::factory()->create(['type' => 'student']);
    $subject = Subject::create(['name' => 'Math']);
    $topic = \App\Modules\Topic\Models\Topic::create([
        'subject_id' => $subject->id,
        'name' => 'Algebra',
        'difficulty_level' => 1,
    ]);

    $response = $this->actingAs($user)->getJson("/api/subjects/{$subject->id}");

    $response->assertStatus(200);
    expect($response->json('data.topics'))->toHaveCount(1);
    expect($response->json('data.topics.0.id'))->toBe($topic->id);
});
