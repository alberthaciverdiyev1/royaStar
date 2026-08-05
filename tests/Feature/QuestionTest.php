<?php

use App\Modules\Question\Models\Question;
use App\Modules\Topic\Enums\DifficultyLevel;
use App\Modules\Topic\Models\Topic;
use App\Modules\Lesson\Models\Lesson;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'api']);
    $this->topic = Topic::create([
        'name' => 'Algebra',
        'difficulty_level' => DifficultyLevel::Beginner->value,
    ]);
    $this->lesson = Lesson::create([
        'topic_id' => $this->topic->id,
        'name' => 'Test Lesson',
        'description' => 'Test lesson description',
    ]);
    $this->admin = User::factory()->create(['type' => 'admin']);
    $this->admin->assignRole('admin');
});

// ─── Helpers ────────────────────────────────────────────────────────

function t(string $content): array
{
    return [['type' => 'text', 'content' => $content]];
}

function regularQuestionData($lessonId, $overrides = []): array
{
    return array_merge([
        'question' => t('What is 2+2?'),
        'type' => 'regular',
        'variant_a' => t('3'),
        'variant_b' => t('4'),
        'variant_c' => t('5'),
        'variant_d' => t('6'),
        'variant_e' => t('7'),
        'right_answer' => 'variant_b',
        'open_answer' => null,
        'explanation' => t('Basic addition'),
        'difficulty_level' => DifficultyLevel::Beginner->value,
        'lesson_id' => $lessonId,
    ], $overrides);
}

function openQuestionData($lessonId, $overrides = []): array
{
    return array_merge([
        'question' => t('Explain gravity'),
        'type' => 'open',
        'answer_type' => 'exact',
        'open_answer' => t('A force that attracts'),
        'explanation' => t('Physics concept'),
        'difficulty_level' => DifficultyLevel::Advanced->value,
        'lesson_id' => $lessonId,
    ], $overrides);
}

function assertQuestionStructure($data, $expectedType = null): void
{
    expect($data)->toHaveKeys([
        'id', 'lesson_id', 'type', 'right_answer', 'difficulty_level',
        'question', 'variant_a', 'variant_b', 'variant_c', 'variant_d', 'variant_e',
        'open_answer', 'explanation', 'created_at',
    ]);

    if ($expectedType) {
        expect($data['type'])->toBe($expectedType);
    }
}

// ─── List Questions (Admin) ─────────────────────────────────────────

it('lists all questions', function () {
    Question::create(regularQuestionData($this->lesson->id));
    Question::create(openQuestionData($this->lesson->id));

    $response = $this->actingAs($this->admin)->getJson('/api/admin/questions');

    $response->assertStatus(200)
        ->assertJsonStructure(['success', 'status_code', 'data', 'meta']);
    expect($response->json('success'))->toBeTrue();
    expect($response->json('status_code'))->toBe(200);
    expect($response->json('data'))->toHaveCount(2);
});

it('returns empty list when no questions exist', function () {
    $response = $this->actingAs($this->admin)->getJson('/api/admin/questions');

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data'))->toBe([]);
});

it('filters questions by lesson_id', function () {
    $topic2 = Topic::create([
        'name' => 'Geometry',
        'difficulty_level' => DifficultyLevel::Beginner->value,
    ]);
    $lesson2 = Lesson::create([
        'topic_id' => $topic2->id,
        'name' => 'Test Lesson 2',
        'description' => 'Test lesson 2 description',
    ]);
    Question::create(regularQuestionData($this->lesson->id));
    Question::create(regularQuestionData($lesson2->id));

    $response = $this->actingAs($this->admin)->getJson('/api/admin/questions?lesson_id=' . $this->lesson->id);

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.lesson_id'))->toBe($this->lesson->id);
});

it('includes lesson relation in question list', function () {
    Question::create(regularQuestionData($this->lesson->id));

    $response = $this->actingAs($this->admin)->getJson('/api/admin/questions');

    expect($response->json('data.0'))->toHaveKey('lesson_id');
    expect($response->json('data.0.lesson_id'))->toBe($this->lesson->id);
});

it('allows admin to list questions', function () {
    Question::create(regularQuestionData($this->lesson->id));

    $response = $this->actingAs($this->admin)->getJson('/api/admin/questions');

    $response->assertStatus(200);
});

it('blocks non-admin from listing questions', function () {
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->getJson('/api/admin/questions');

    $response->assertStatus(403);
});

// ─── Show Question (Admin) ─────────────────────────────────────────

it('shows a question by id', function () {
    $question = Question::create(regularQuestionData($this->lesson->id));

    $response = $this->actingAs($this->admin)->getJson("/api/admin/questions/{$question->id}");

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data.id'))->toBe($question->id);
    expect($response->json('data.question.0.content'))->toBe('What is 2+2?');
});

it('shows an open type question', function () {
    $question = Question::create(openQuestionData($this->lesson->id));

    $response = $this->actingAs($this->admin)->getJson("/api/admin/questions/{$question->id}");

    $response->assertStatus(200);
    expect($response->json('data.type'))->toBe('open');
    expect($response->json('data.open_answer.0.content'))->toBe('A force that attracts');
    expect($response->json('data.right_answer'))->toBeNull();
});

it('returns 404 for non-existent question', function () {
    $response = $this->actingAs($this->admin)->getJson('/api/admin/questions/99999');

    $response->assertStatus(404)
        ->assertJson(['success' => false]);
});

it('shows difficulty_level as enum value in question', function () {
    $question = Question::create(regularQuestionData($this->lesson->id, [
        'difficulty_level' => DifficultyLevel::Expert->value,
    ]));

    $response = $this->actingAs($this->admin)->getJson("/api/admin/questions/{$question->id}");

    expect($response->json('data.difficulty_level'))->toBe(DifficultyLevel::Expert->value);
});

it('allows admin to show question', function () {
    $question = Question::create(regularQuestionData($this->lesson->id));

    $response = $this->actingAs($this->admin)->getJson("/api/admin/questions/{$question->id}");

    $response->assertStatus(200);
});

it('returns raw content-block array for admin', function () {
    $question = Question::create(regularQuestionData($this->lesson->id));

    // Admin access — returns the plain content-block array as stored.
    $response = $this->actingAs($this->admin)->getJson("/api/admin/questions/{$question->id}");
    expect($response->json('data.question.0.content'))->toBe('What is 2+2?');
    expect($response->json('data.question'))->toBe(t('What is 2+2?'));
});

// ─── Store Question (Admin) ─────────────────────────────────────────

it('creates a regular question as admin', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $data = regularQuestionData($this->lesson->id);

    $response = $this->actingAs($admin)->postJson('/api/admin/questions', $data);

    $response->assertStatus(201)
        ->assertJson(['success' => true, 'status_code' => 201]);
    assertQuestionStructure($response->json('data'), 'regular');
    expect($response->json('data.variant_a.0'))->toBe(['type' => 'text', 'content' => '3']);
    expect($response->json('data.right_answer'))->toBe('variant_b');
    expect($response->json('data.difficulty_level'))->toBe(DifficultyLevel::Beginner->value);
    $this->assertDatabaseHas('questions', ['id' => $response->json('data.id')]);
});

it('creates an open question as admin', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $data = openQuestionData($this->lesson->id);

    $response = $this->actingAs($admin)->postJson('/api/admin/questions', $data);

    $response->assertStatus(201);
    expect($response->json('data.type'))->toBe('open');
    expect($response->json('data.open_answer.0'))->toBe(['type' => 'text', 'content' => 'A force that attracts']);
    // Open questions should have null variants
    expect($response->json('data.variant_a'))->toBeNull();
    expect($response->json('data.right_answer'))->toBeNull();
});

it('stores plain content-block arrays from the admin panel as-is', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    // This is exactly what the admin panel sends: plain content-block arrays.
    $response = $this->actingAs($admin)->postJson('/api/admin/questions', [
        'question' => [['type' => 'text', 'content' => 'salammm']],
        'type' => 'regular',
        'variant_a' => [['type' => 'text', 'content' => 'A']],
        'variant_b' => [['type' => 'text', 'content' => 'B']],
        'variant_c' => [['type' => 'text', 'content' => 'C']],
        'right_answer' => 'a',
        'difficulty_level' => DifficultyLevel::Beginner->value,
        'lesson_id' => $this->lesson->id,
    ]);

    $response->assertStatus(201);
    $id = $response->json('data.id');

    // Stored and returned shape is the same plain array (single-language).
    $stored = Question::find($id);
    expect($stored->question)->toBe([['type' => 'text', 'content' => 'salammm']])
        ->and($response->json('data.question'))->toBe([['type' => 'text', 'content' => 'salammm']]);
});

it('reads plain content-block arrays from any stored question', function () {
    $question = Question::create([
        'question' => [['type' => 'text', 'content' => 'legacy plain']],
        'type' => 'regular',
        'variant_a' => [['type' => 'text', 'content' => 'A']],
        'variant_b' => [['type' => 'text', 'content' => 'B']],
        'variant_c' => [['type' => 'text', 'content' => 'C']],
        'right_answer' => 'a',
        'difficulty_level' => DifficultyLevel::Beginner->value,
        'lesson_id' => $this->lesson->id,
    ]);

    // Read path exposes the plain content directly.
    expect($question->question)->toBe([['type' => 'text', 'content' => 'legacy plain']])
        ->and($question->variant_a)->toBe([['type' => 'text', 'content' => 'A']])
        ->and($question->open_answer ?? [])->toBe([]);
});

it('fails to create question without admin role', function () {
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->postJson('/api/admin/questions', regularQuestionData($this->lesson->id));

    $response->assertStatus(403);
});

it('fails to create question without authentication', function () {
    $response = $this->postJson('/api/admin/questions', regularQuestionData($this->lesson->id));

    $response->assertStatus(401);
});

it('fails to create question with missing required fields', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/questions', []);

    $response->assertStatus(422)
        ->assertJson(['success' => false]);
});

it('validates question field is required', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/questions', [
        'type' => 'regular',
        'lesson_id' => $this->lesson->id,
        'difficulty_level' => DifficultyLevel::Beginner->value,
    ]);

    $response->assertStatus(422);
    expect($response->json('errors'))->toHaveKey('question');
});

it('validates type field is required and must be valid', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/questions', [
        'question' => t('Test'),
    ]);

    $response->assertStatus(422);
    expect($response->json('errors'))->toHaveKey('type');
});

it('rejects invalid type value', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/questions', [
        'question' => t('Test'),
        'type' => 'invalid_type',
        'lesson_id' => $this->lesson->id,
        'difficulty_level' => DifficultyLevel::Beginner->value,
    ]);

    $response->assertStatus(422);
});

it('validates difficulty_level must be a valid enum value', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/questions', [
        'question' => t('Test'),
        'type' => 'regular',
        'difficulty_level' => 99,
        'lesson_id' => $this->lesson->id,
    ]);

    $response->assertStatus(422);
    expect($response->json('errors'))->toHaveKey('difficulty_level');
});

it('validates lesson_id must exist', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/questions', regularQuestionData(99999));

    $response->assertStatus(422);
    expect($response->json('errors'))->toHaveKey('lesson_id');
});

// ─── Type-Specific Validation (Store) ───────────────────────────────

it('requires open_answer when type is open', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/questions', [
        'question' => t('Explain?'),
        'type' => 'open',
        'difficulty_level' => DifficultyLevel::Beginner->value,
        'lesson_id' => $this->lesson->id,
    ]);

    $response->assertStatus(422);
    expect($response->json('errors'))->toHaveKey('open_answer');
});

it('does not require variants when type is open', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/questions', openQuestionData($this->lesson->id));

    $response->assertStatus(201);
});

it('does not require open_answer when type is regular', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/questions', regularQuestionData($this->lesson->id, [
        'open_answer' => null,
    ]));

    $response->assertStatus(201);
});

it('requires variant_a, variant_b, variant_c when type is regular', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/questions', [
        'question' => t('Test'),
        'type' => 'regular',
        'difficulty_level' => DifficultyLevel::Beginner->value,
        'lesson_id' => $this->lesson->id,
    ]);

    $response->assertStatus(422);
    expect($response->json('errors'))->toHaveKey('variant_a');
    expect($response->json('errors'))->toHaveKey('variant_b');
    expect($response->json('errors'))->toHaveKey('variant_c');
});

it('allows variant_d and variant_e to be optional for regular questions', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/questions', [
        'question' => t('Test'),
        'type' => 'regular',
        'variant_a' => t('A'),
        'variant_b' => t('B'),
        'variant_c' => t('C'),
        'right_answer' => 'variant_a',
        'difficulty_level' => DifficultyLevel::Beginner->value,
        'lesson_id' => $this->lesson->id,
    ]);

    $response->assertStatus(201);
    expect($response->json('data.variant_d'))->toBeNull();
    expect($response->json('data.variant_e'))->toBeNull();
});

it('requires right_answer when type is regular', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/questions', [
        'question' => t('Test'),
        'type' => 'regular',
        'variant_a' => t('A'),
        'variant_b' => t('B'),
        'variant_c' => t('C'),
        'difficulty_level' => DifficultyLevel::Beginner->value,
        'lesson_id' => $this->lesson->id,
    ]);

    $response->assertStatus(422);
    expect($response->json('errors'))->toHaveKey('right_answer');
});

it('validates question.*.type must be text or image', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->postJson('/api/admin/questions', [
        'question' => [['type' => 'video', 'content' => 'x']],
        'type' => 'regular',
        'variant_a' => t('A'),
        'variant_b' => t('B'),
        'variant_c' => t('C'),
        'right_answer' => 'variant_a',
        'difficulty_level' => DifficultyLevel::Beginner->value,
        'lesson_id' => $this->lesson->id,
    ]);

    $response->assertStatus(422);
    expect($response->json('errors'))->toHaveKey('question.0.type');
});

it('stores an image-type variant', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $data = regularQuestionData($this->lesson->id, [
        'variant_a' => [['type' => 'image', 'content' => 'variants/a.jpg']],
    ]);

    $response = $this->actingAs($admin)->postJson('/api/admin/questions', $data);

    $response->assertStatus(201);
    expect($response->json('data.variant_a.0'))->toBe(['type' => 'image', 'content' => 'variants/a.jpg']);
});

it('stores a base64 image and replaces content with url on create', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $data = regularQuestionData($this->lesson->id, [
        'variant_a' => [
            [
                'type' => 'image',
                'content' => 'data:image/png;base64,' . base64_encode('fake-png-content'),
            ],
        ],
    ]);

    $response = $this->actingAs($admin)->postJson('/api/admin/questions', $data);

    $response->assertStatus(201);
    $url = $response->json('data.variant_a.0.content');
    expect($url)->toMatch('#/storage/questions/[a-f0-9-]+\.png$#');
    // Verify the file was actually saved
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    $relativePath = substr(parse_url($url, PHP_URL_PATH), strlen('/storage/'));
    expect($disk->exists($relativePath))->toBeTrue();
    expect($disk->get($relativePath))->toBe('fake-png-content');
});

it('stores a base64 image on update', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $question = Question::create(regularQuestionData($this->lesson->id));

    $response = $this->actingAs($admin)->putJson("/api/admin/questions/{$question->id}", [
        'variant_a' => [
            [
                'type' => 'image',
                'content' => 'data:image/jpeg;base64,' . base64_encode('updated-image'),
            ],
        ],
    ]);

    $response->assertStatus(200);
    $url = $response->json('data.variant_a.0.content');
    expect($url)->toMatch('#/storage/questions/[a-f0-9-]+\.jpg$#');
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    $relativePath = substr(parse_url($url, PHP_URL_PATH), strlen('/storage/'));
    expect($disk->exists($relativePath))->toBeTrue();
    expect($disk->get($relativePath))->toBe('updated-image');
});

it('does not modify text content in image processing', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $data = regularQuestionData($this->lesson->id);
    $response = $this->actingAs($admin)->postJson('/api/admin/questions', $data);

    $response->assertStatus(201);
    expect($response->json('data.variant_a.0'))->toBe(['type' => 'text', 'content' => '3']);
});
// ─── Update Question (Admin) ────────────────────────────────────────

it('updates a regular question as admin', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $question = Question::create(regularQuestionData($this->lesson->id));

    $response = $this->actingAs($admin)->putJson("/api/admin/questions/{$question->id}", [
        'question' => t('What is 3+3?'),
        'variant_a' => t('5'),
        'variant_b' => t('6'),
        'variant_c' => t('7'),
        'right_answer' => 'variant_b',
    ]);

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'status_code' => 200]);
    expect($response->json('data.question.0'))->toBe(['type' => 'text', 'content' => 'What is 3+3?']);
    expect($response->json('data.right_answer'))->toBe('variant_b');
});

it('updates an open question as admin', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $question = Question::create(openQuestionData($this->lesson->id));

    $response = $this->actingAs($admin)->putJson("/api/admin/questions/{$question->id}", [
        'question' => t('Explain dark matter'),
        'open_answer' => t('Mysterious matter'),
    ]);

    $response->assertStatus(200);
    expect($response->json('data.question.0'))->toBe(['type' => 'text', 'content' => 'Explain dark matter']);
    expect($response->json('data.open_answer.0'))->toBe(['type' => 'text', 'content' => 'Mysterious matter']);
});

it('updates question type from regular to open', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $question = Question::create(regularQuestionData($this->lesson->id));

    $response = $this->actingAs($admin)->putJson("/api/admin/questions/{$question->id}", [
        'type' => 'open',
        'open_answer' => t('New answer'),
    ]);

    $response->assertStatus(200);
    expect($response->json('data.type'))->toBe('open');
    expect($response->json('data.open_answer.0'))->toBe(['type' => 'text', 'content' => 'New answer']);
});

it('returns 404 when updating non-existent question', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->putJson('/api/admin/questions/99999', [
        'question' => t('Test'),
    ]);

    $response->assertStatus(404);
});

it('fails to update question without admin role', function () {
    $question = Question::create(regularQuestionData($this->lesson->id));
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->putJson("/api/admin/questions/{$question->id}", [
        'question' => t('Hacked'),
    ]);

    $response->assertStatus(403);
});

it('fails to update question without authentication', function () {
    $question = Question::create(regularQuestionData($this->lesson->id));

    $response = $this->putJson("/api/admin/questions/{$question->id}", [
        'question' => t('Hacked'),
    ]);

    $response->assertStatus(401);
});

it('validates update with invalid difficulty_level', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $question = Question::create(regularQuestionData($this->lesson->id));

    $response = $this->actingAs($admin)->putJson("/api/admin/questions/{$question->id}", [
        'difficulty_level' => 99,
    ]);

    $response->assertStatus(422);
    expect($response->json('errors'))->toHaveKey('difficulty_level');
});

it('validates update with non-existent lesson_id', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $question = Question::create(regularQuestionData($this->lesson->id));

    $response = $this->actingAs($admin)->putJson("/api/admin/questions/{$question->id}", [
        'lesson_id' => 99999,
    ]);

    $response->assertStatus(422);
    expect($response->json('errors'))->toHaveKey('lesson_id');
});

// ─── Update Type-Specific Validation ────────────────────────────────

it('validates open_answer required when updating type to open', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $question = Question::create(regularQuestionData($this->lesson->id));

    $response = $this->actingAs($admin)->putJson("/api/admin/questions/{$question->id}", [
        'type' => 'open',
    ]);

    $response->assertStatus(422);
    expect($response->json('errors'))->toHaveKey('open_answer');
});

it('validates right_answer required when updating type to regular', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $question = Question::create(openQuestionData($this->lesson->id));

    $response = $this->actingAs($admin)->putJson("/api/admin/questions/{$question->id}", [
        'type' => 'regular',
        'variant_a' => t('A'),
        'variant_b' => t('B'),
        'variant_c' => t('C'),
    ]);

    $response->assertStatus(422);
    expect($response->json('errors'))->toHaveKey('right_answer');
});

// ─── Delete Question (Admin) ────────────────────────────────────────

it('deletes a question as admin', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $question = Question::create(regularQuestionData($this->lesson->id));

    $response = $this->actingAs($admin)->deleteJson("/api/admin/questions/{$question->id}");

    $response->assertStatus(200)
        ->assertJson(['success' => true]);
});

it('returns 404 when deleting non-existent question', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->deleteJson('/api/admin/questions/99999');

    $response->assertStatus(404);
});

it('fails to delete question without admin role', function () {
    $question = Question::create(regularQuestionData($this->lesson->id));
    $user = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($user)->deleteJson("/api/admin/questions/{$question->id}");

    $response->assertStatus(403);
});

it('fails to delete question without authentication', function () {
    $question = Question::create(regularQuestionData($this->lesson->id));

    $response = $this->deleteJson("/api/admin/questions/{$question->id}");

    $response->assertStatus(401);
});

it('soft deletes question instead of hard delete', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $question = Question::create(regularQuestionData($this->lesson->id));

    $this->actingAs($admin)->deleteJson("/api/admin/questions/{$question->id}");

    $this->assertDatabaseHas('questions', ['id' => $question->id]);
    expect(Question::find($question->id))->toBeNull();
    expect(Question::withTrashed()->find($question->id))->not->toBeNull();
});

it('excludes soft-deleted question from admin list', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $question = Question::create(regularQuestionData($this->lesson->id));
    $this->actingAs($admin)->deleteJson("/api/admin/questions/{$question->id}");

    $response = $this->actingAs($this->admin)->getJson('/api/admin/questions');

    expect($response->json('data'))->toHaveCount(0);
});

it('returns 404 when accessing deleted question', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');
    $question = Question::create(regularQuestionData($this->lesson->id));
    $this->actingAs($admin)->deleteJson("/api/admin/questions/{$question->id}");

    $response = $this->actingAs($this->admin)->getJson("/api/admin/questions/{$question->id}");

    $response->assertStatus(404);
});

// ─── Response Structure ─────────────────────────────────────────────

it('returns correct response structure for question list', function () {
    Question::create(regularQuestionData($this->lesson->id));

    $response = $this->actingAs($this->admin)->getJson('/api/admin/questions');

    $response->assertJsonStructure([
        'success',
        'status_code',
        'message',
        'data' => [['id', 'lesson_id', 'type', 'question']],
        'meta' => ['current_page', 'last_page', 'per_page', 'total'],
    ]);
});

it('formats created_at in Y-m-d H:i:s format', function () {
    $question = Question::create(regularQuestionData($this->lesson->id));

    $response = $this->actingAs($this->admin)->getJson("/api/admin/questions/{$question->id}");

    expect($response->json('data.created_at'))->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
});
