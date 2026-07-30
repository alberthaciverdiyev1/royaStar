<?php

use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns consistent success response format', function () {
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->getJson('/api/grades');

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'status_code', 'message', 'data'])
        ->assertJson(['success' => true, 'status_code' => 200]);
});

it('returns consistent validation error format', function () {
    $response = $this->postJson('/api/auth/register', []);

    $response->assertStatus(422)
        ->assertJsonStructure(['success', 'status_code', 'errors'])
        ->assertJson(['success' => false]);
});

it('returns 404 for non-existent resources', function () {
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->getJson('/api/cities/99999');

    $response->assertStatus(404);
});

it('returns 401 for protected routes without token', function () {
    $response = $this->getJson('/api/profile');

    $response->assertStatus(401);
});

it('uses apiResource for standard CRUD endpoints', function () {
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->getJson('/api/cities/1');
    expect($response->status())->toBe(404);

    $response = $this->actingAs($user)->getJson('/api/grades/1');
    expect($response->status())->toBe(404);
});
