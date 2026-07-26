<?php

use App\Modules\User\Models\User;
use App\Modules\Grade\Models\Grade;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'api']);
});

// ─── List Grades ────────────────────────────────────────────────

it('lists all grades', function () {
    $user = User::factory()->create(['type' => 'student']);
    Grade::create(['name' => 'Grade 1']);
    Grade::create(['name' => 'Grade 2']);

    $response = $this->actingAs($user)->getJson('/api/grades');

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'status_code', 'data'])
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data'))->toHaveCount(2);
});

it('returns empty array when no grades exist', function () {
    $user = User::factory()->create(['type' => 'student']);
    $response = $this->actingAs($user)->getJson('/api/grades');

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data'))->toBe([]);
});

// ─── Show Grade ─────────────────────────────────────────────────

it('shows a grade by id', function () {
    $user = User::factory()->create(['type' => 'student']);
    $grade = Grade::create(['name' => 'Grade 1']);

    $response = $this->actingAs($user)->getJson("/api/grades/{$grade->id}");

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data.id'))->toBe($grade->id);
});

it('returns 404 when showing non-existent grade', function () {
    $user = User::factory()->create(['type' => 'student']);
    $response = $this->actingAs($user)->getJson('/api/grades/99999');

    $response->assertStatus(404)
        ->assertJson(['success' => false]);
});

it('allows admin to access public grades endpoint', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    Grade::create(['name' => 'Grade 1']);

    $response = $this->actingAs($admin)->getJson('/api/grades');

    $response->assertStatus(200)
        ->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(1);
});

// ─── Store Grade (Admin) ────────────────────────────────────────

it('creates a grade as admin', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/grades', [
        'name' => 'Grade 1',
    ]);

    $response->assertStatus(201)
        ->assertJson(['success' => true, 'status_code' => 201]);
    $this->assertDatabaseHas('grades', ['id' => $response->json('data.id')]);
});

it('fails to create grade without admin role', function () {
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->postJson('/api/admin/grades', [
        'name' => 'Grade 1',
    ]);

    $response->assertStatus(403);
});

it('fails to create grade without authentication', function () {
    $response = $this->postJson('/api/admin/grades', [
        'name' => 'Grade 1',
    ]);

    $response->assertStatus(401);
});

it('fails to create grade with invalid data', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/grades', []);

    $response->assertStatus(422)
        ->assertJson(['success' => false]);
});

// ─── Update Grade (Admin) ───────────────────────────────────────

it('updates a grade as admin', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $grade = Grade::create(['name' => 'Old Name']);

    $response = $this->actingAs($admin)->putJson("/api/admin/grades/{$grade->id}", [
        'name' => 'Updated Grade',
    ]);

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data.name'))->toBe('Updated Grade');
});

it('returns 404 when updating non-existent grade', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->putJson('/api/admin/grades/99999', [
        'name' => 'Test',
    ]);

    $response->assertStatus(404);
});

it('fails to update grade without admin role', function () {
    $grade = Grade::create(['name' => 'Test']);
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->putJson("/api/admin/grades/{$grade->id}", [
        'name' => 'Hacked',
    ]);

    $response->assertStatus(403);
});

// ─── Delete Grade (Admin) ───────────────────────────────────────

it('deletes a grade as admin', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $grade = Grade::create(['name' => 'To Delete']);

    $response = $this->actingAs($admin)->deleteJson("/api/admin/grades/{$grade->id}");

    $response->assertStatus(200);
    $this->assertSoftDeleted($grade);
});

it('returns 404 when deleting non-existent grade', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->deleteJson('/api/admin/grades/99999');

    $response->assertStatus(404);
});

it('fails to delete grade without admin role', function () {
    $grade = Grade::create(['name' => 'Test']);
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->deleteJson("/api/admin/grades/{$grade->id}");

    $response->assertStatus(403);
});

it('soft deletes grade instead of hard delete', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $grade = Grade::create(['name' => 'Soft Delete Test']);

    $this->actingAs($admin)->deleteJson("/api/admin/grades/{$grade->id}");

    $this->assertDatabaseHas('grades', ['id' => $grade->id]);
    expect(Grade::find($grade->id))->toBeNull();
    expect(Grade::withTrashed()->find($grade->id))->not->toBeNull();
});
