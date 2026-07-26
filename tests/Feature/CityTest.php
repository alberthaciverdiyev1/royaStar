<?php

use App\Modules\User\Models\User;
use App\Modules\City\Models\City;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'api']);
});

// ─── List Cities ────────────────────────────────────────────────

it('lists all cities', function () {
    $user = User::factory()->create(['type' => 'student']);
    City::create(['name' => 'Baku']);
    City::create(['name' => 'Istanbul']);

    $response = $this->actingAs($user)->getJson('/api/cities');

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'status_code', 'data'])
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data'))->toHaveCount(2);
});

it('searches cities by name', function () {
    $user = User::factory()->create(['type' => 'student']);
    City::create(['name' => 'Baku']);
    City::create(['name' => 'Istanbul']);

    $response = $this->actingAs($user)->getJson('/api/cities?search=bak');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.name'))->toBe('Baku');
})->skip(fn () => DB::getDriverName() !== 'pgsql', 'filterLike requires PostgreSQL (unaccent + JSON casts)');

it('returns empty array when no cities exist', function () {
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->getJson('/api/cities');

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data'))->toBe([]);
});

// ─── Show City ──────────────────────────────────────────────────

it('shows a city by id', function () {
    $user = User::factory()->create(['type' => 'student']);
    $city = City::create(['name' => 'Baku']);

    $response = $this->actingAs($user)->getJson("/api/cities/{$city->id}");

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data.id'))->toBe($city->id);
});

it('returns 404 when showing non-existent city', function () {
    $user = User::factory()->create(['type' => 'student']);
    $response = $this->actingAs($user)->getJson('/api/cities/99999');

    $response->assertStatus(404)
        ->assertJson(['success' => false]);
});

it('allows admin to access public cities endpoint', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    City::create(['name' => 'Baku']);

    $response = $this->actingAs($admin)->getJson('/api/cities');

    $response->assertStatus(200)
        ->assertJson(['success' => true]);
    expect($response->json('data'))->toHaveCount(1);
});

// ─── Store City (Admin) ─────────────────────────────────────────

it('creates a city as admin', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/cities', [
        'name' => 'London',
    ]);

    $response->assertStatus(201)
        ->assertJson(['success' => true, 'status_code' => 201]);
    $this->assertDatabaseHas('cities', ['id' => $response->json('data.id')]);
});

it('fails to create city without admin role', function () {
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->postJson('/api/admin/cities', [
        'name' => 'London',
    ]);

    $response->assertStatus(403);
});

it('fails to create city without authentication', function () {
    $response = $this->postJson('/api/admin/cities', [
        'name' => 'London',
    ]);

    $response->assertStatus(401);
});

it('fails to create city with invalid data', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/cities', []);

    $response->assertStatus(422)
        ->assertJson(['success' => false]);
});

// ─── Update City (Admin) ────────────────────────────────────────

it('updates a city as admin', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $city = City::create(['name' => 'Old Name']);

    $response = $this->actingAs($admin)->putJson("/api/admin/cities/{$city->id}", [
        'name' => 'New Name',
    ]);

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data.name'))->toBe('New Name');
});

it('returns 404 when updating non-existent city', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->putJson('/api/admin/cities/99999', [
        'name' => 'Test',
    ]);

    $response->assertStatus(404);
});

it('fails to update city without admin role', function () {
    $city = City::create(['name' => 'Test']);
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->putJson("/api/admin/cities/{$city->id}", [
        'name' => 'Hacked',
    ]);

    $response->assertStatus(403);
});

// ─── Delete City (Admin) ────────────────────────────────────────

it('deletes a city as admin', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $city = City::create(['name' => 'To Delete']);

    $response = $this->actingAs($admin)->deleteJson("/api/admin/cities/{$city->id}");

    $response->assertStatus(200);
    $this->assertSoftDeleted($city);
});

it('returns 404 when deleting non-existent city', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->deleteJson('/api/admin/cities/99999');

    $response->assertStatus(404);
});

it('fails to delete city without admin role', function () {
    $city = City::create(['name' => 'Test']);
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->deleteJson("/api/admin/cities/{$city->id}");

    $response->assertStatus(403);
});

it('soft deletes city instead of hard delete', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $city = City::create(['name' => 'Soft Delete Test']);

    $this->actingAs($admin)->deleteJson("/api/admin/cities/{$city->id}");

    $this->assertDatabaseHas('cities', ['id' => $city->id]);
    expect(City::find($city->id))->toBeNull();
    expect(City::withTrashed()->find($city->id))->not->toBeNull();
});
