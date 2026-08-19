<?php

use App\Modules\Lesson\Models\Lesson;
use App\Modules\Lesson\Models\LessonReview;
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

describe('Lesson rating & comments', function () {
    it('accepts a rating without a written comment', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        $response = $this->actingAs($user)->postJson("/lesson/{$lesson->id}/rate", [
            'rating' => 4,
            'review' => '',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('lesson_reviews', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'rating' => 4,
        ]);
    });

    it('accepts a comment without a star rating (rating:null)', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        $response = $this->actingAs($user)->postJson("/lesson/{$lesson->id}/rate", [
            'rating' => null,
            'review' => 'Really helpful lesson, thanks!',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('lesson_reviews', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'review' => 'Really helpful lesson, thanks!',
            'rating' => null,
        ]);
    });

    it('accepts both a rating and a comment', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        $response = $this->actingAs($user)->postJson("/lesson/{$lesson->id}/rate", [
            'rating' => 5,
            'review' => 'Excellent lesson!',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('lesson_reviews', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'rating' => 5,
            'review' => 'Excellent lesson!',
        ]);
    });

    it('rejects a submission with neither a rating nor a comment', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        $response = $this->actingAs($user)->postJson("/lesson/{$lesson->id}/rate", [
            'rating' => null,
            'review' => '',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('lesson_reviews', 0);
    });

    it('rejects out-of-range ratings', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        foreach ([0, 6] as $badRating) {
            $this->actingAs($user)->postJson("/lesson/{$lesson->id}/rate", [
                'rating' => $badRating,
                'review' => 'oops',
            ])->assertStatus(422);
        }

        $this->assertDatabaseCount('lesson_reviews', 0);
    });

    it('rejects a duplicate review from the same user', function () {
        $user = User::factory()->create();
        $lesson = Lesson::factory()->create();

        $this->actingAs($user)->postJson("/lesson/{$lesson->id}/rate", [
            'rating' => 5,
            'review' => 'First feedback',
        ])->assertOk();

        $this->actingAs($user)->postJson("/lesson/{$lesson->id}/rate", [
            'rating' => 1,
            'review' => 'Second attempt',
        ])->assertStatus(409);

        $this->assertDatabaseCount('lesson_reviews', 1);
        $this->assertDatabaseHas('lesson_reviews', ['review' => 'First feedback']);
    });
});
