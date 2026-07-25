<?php

use App\Modules\User\Models\User;
use App\Modules\User\Models\Otp;
use App\Modules\Grade\Models\Grade;
use App\Modules\City\Models\City;
use App\Modules\School\Models\SchoolRegistrationRequest;

beforeEach(function () {
    Spatie\Permission\Models\Role::create(['name' => 'student', 'guard_name' => 'api']);
    Spatie\Permission\Models\Role::create(['name' => 'teacher', 'guard_name' => 'api']);
    Spatie\Permission\Models\Role::create(['name' => 'parent', 'guard_name' => 'api']);
    Spatie\Permission\Models\Role::create(['name' => 'admin', 'guard_name' => 'api']);

    Grade::create(['name' => ['en' => 'Grade 1']]);
    City::create(['name' => ['en' => 'Baku']]);
});

// ─── Register ───────────────────────────────────────────────────

it('registers a new student', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'John',
        'surname' => 'Doe',
        'phone' => '+994501234567',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'type' => 'student',
        'student' => ['grade_id' => 1, 'city_id' => 1],
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['success', 'status_code', 'message', 'data' => ['user']])
        ->assertJson(['success' => true, 'status_code' => 201]);
});

it('registers a new teacher', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Jane',
        'surname' => 'Smith',
        'phone' => '+994501234568',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'type' => 'teacher',
        'teacher' => ['city_id' => 1],
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['success', 'status_code', 'message', 'data' => ['user']])
        ->assertJson(['success' => true, 'status_code' => 201]);
    $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'type' => 'teacher']);
});

it('registers a new parent', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Bob',
        'surname' => 'Parent',
        'phone' => '+994501234569',
        'email' => 'bob@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'type' => 'parent',
    ]);

    $response->assertStatus(201);
});

it('creates school registration request instead of user for school type', function () {
    $response = $this->postJson('/api/auth/register', [
        'email' => 'school@example.com',
        'type' => 'school',
        'school' => ['name' => 'Test School', 'city_id' => 1],
    ]);

    $response->assertStatus(201)
        ->assertJson(['message' => __('auth.school_registration_pending')]);
    $this->assertDatabaseHas('school_registration_requests', ['email' => 'school@example.com']);
    $this->assertDatabaseMissing('users', ['email' => 'school@example.com']);
});

it('fails registration with missing required fields', function () {
    $response = $this->postJson('/api/auth/register', [
        'type' => 'student',
    ]);

    $response->assertStatus(422)
        ->assertJson(['success' => false]);
});

it('fails registration with invalid email', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'John',
        'phone' => '+994501234570',
        'email' => 'not-an-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'type' => 'student',
    ]);

    $response->assertStatus(422)
        ->assertJson(['success' => false]);
});

it('fails registration with duplicate email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $response = $this->postJson('/api/auth/register', [
        'name' => 'John',
        'phone' => '+994501234571',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'type' => 'student',
    ]);

    $response->assertStatus(422);
});

it('fails registration with password confirmation mismatch', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'John',
        'phone' => '+994501234572',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different',
        'type' => 'student',
    ]);

    $response->assertStatus(422);
});

it('assigns role on registration', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Role',
        'surname' => 'Test',
        'phone' => '+994501234573',
        'email' => 'role@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'type' => 'teacher',
        'teacher' => ['city_id' => 1],
    ]);

    $response->assertStatus(201);
    $user = User::where('email', 'role@example.com')->first();
    expect($user->hasRole('teacher'))->toBeTrue();
});

// ─── Login ──────────────────────────────────────────────────────

it('logs in with valid phone credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $response = $this->postJson('/api/auth/login', [
        'login' => $user->phone,
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['user']]);
});

it('logs in with valid email credentials', function () {
    $user = User::factory()->create([
        'email' => 'loginemail@example.com',
        'phone' => '+994509999999',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'login' => 'loginemail@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['user']]);
});

it('fails login with wrong password', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $response = $this->postJson('/api/auth/login', [
        'login' => $user->phone,
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422);
});

it('fails login with non-existent user', function () {
    $response = $this->postJson('/api/auth/login', [
        'login' => '+994501234567',
        'password' => 'password123',
    ]);

    $response->assertStatus(422);
});

// ─── Admin Login ────────────────────────────────────────────────

it('logs in as admin', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
        'type' => 'admin',
        'password' => bcrypt('admin123'),
    ]);

    $response = $this->postJson('/api/auth/admin-login', [
        'email' => 'admin@example.com',
        'password' => 'admin123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'data' => ['user']]);
});

it('fails admin login with non-admin user', function () {
    User::factory()->create([
        'email' => 'regular@example.com',
        'type' => 'student',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/admin-login', [
        'email' => 'regular@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422);
});

it('fails admin login with wrong credentials', function () {
    $response = $this->postJson('/api/auth/admin-login', [
        'email' => 'admin@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422);
});

// ─── Logout ─────────────────────────────────────────────────────

it('logs out successfully', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $token = $user->createToken('auth-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/auth/logout');

    $response->assertStatus(200)
        ->assertJson(['message' => __('auth.logged_out')]);
});

it('fails logout without authentication', function () {
    $response = $this->postJson('/api/auth/logout');

    $response->assertStatus(401);
});

it('deletes token from database on logout', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $token = $user->createToken('auth-token')->plainTextToken;

    $user->refresh();
    expect($user->tokens()->count())->toBe(1);

    $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/auth/logout');

    $user->refresh();
    expect($user->tokens()->count())->toBe(0);
});

// ─── OTP ────────────────────────────────────────────────────────

it('sends otp without exposing code in response', function () {
    $response = $this->postJson('/api/auth/send-otp', [
        'phone' => '+994501234567',
    ]);

    $response->assertStatus(200)
        ->assertJsonMissingPath('data.otp')
        ->assertJson(['message' => __('auth.otp_sent')]);
});

it('creates otp record in database', function () {
    $this->postJson('/api/auth/send-otp', [
        'phone' => '+994501234567',
    ]);

    $this->assertDatabaseHas('otp', ['phone' => '+994501234567']);
});

it('replaces old otp when sending new one', function () {
    Otp::create(['phone' => '+994501234567', 'otp' => '111111', 'expires_at' => now()->addMinutes(10)]);

    $this->postJson('/api/auth/send-otp', [
        'phone' => '+994501234567',
    ]);

    $otps = Otp::where('phone', '+994501234567')->count();
    expect($otps)->toBe(1);

    $this->assertDatabaseMissing('otp', ['phone' => '+994501234567', 'otp' => '111111']);
});

it('verifies valid otp', function () {
    Otp::create([
        'phone' => '+994501234567',
        'otp' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->postJson('/api/auth/verify-otp', [
        'phone' => '+994501234567',
        'otp' => '123456',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => __('auth.otp_verified')]);
});

it('deletes otp after successful verification', function () {
    Otp::create([
        'phone' => '+994501234567',
        'otp' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);

    $this->postJson('/api/auth/verify-otp', [
        'phone' => '+994501234567',
        'otp' => '123456',
    ]);

    $this->assertDatabaseMissing('otp', ['phone' => '+994501234567', 'otp' => '123456']);
});

it('fails with expired otp', function () {
    Otp::create([
        'phone' => '+994501234567',
        'otp' => '123456',
        'expires_at' => now()->subMinutes(5),
    ]);

    $response = $this->postJson('/api/auth/verify-otp', [
        'phone' => '+994501234567',
        'otp' => '123456',
    ]);

    $response->assertStatus(422);
});

it('fails with wrong otp', function () {
    Otp::create([
        'phone' => '+994501234567',
        'otp' => '123456',
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->postJson('/api/auth/verify-otp', [
        'phone' => '+994501234567',
        'otp' => '000000',
    ]);

    $response->assertStatus(422);
});

it('fails otp verification with non-existent phone', function () {
    $response = $this->postJson('/api/auth/verify-otp', [
        'phone' => '+994500000000',
        'otp' => '123456',
    ]);

    $response->assertStatus(422);
});

it('fails otp verification with too short code', function () {
    $response = $this->postJson('/api/auth/verify-otp', [
        'phone' => '+994501234567',
        'otp' => '123',
    ]);

    $response->assertStatus(422);
});

it('fails send-otp without phone', function () {
    $response = $this->postJson('/api/auth/send-otp', []);

    $response->assertStatus(422);
});
