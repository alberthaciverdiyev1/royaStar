<?php

use App\Modules\Exam\Models\Exam;
use App\Modules\Grade\Models\Grade;
use App\Modules\Lesson\Models\Lesson;
use App\Modules\Question\Models\Question;
use App\Modules\Quiz\Models\Quiz;
use App\Modules\Star\Models\Star;
use App\Modules\Star\Models\UserStar;
use App\Modules\Star\Services\StarService;
use App\Modules\Topic\Enums\DifficultyLevel;
use App\Modules\Topic\Models\Topic;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'admin', 'guard_name' => 'api']);
    Role::create(['name' => 'student', 'guard_name' => 'api']);
});

// ─── Admin privilege escalation ──────────────────────────────────────

it('rejects registration with admin type', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Hacker',
        'phone' => '+994501234599',
        'email' => 'hacker@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'type' => 'admin',
    ]);

    $response->assertStatus(422);
    $this->assertDatabaseMissing('users', ['email' => 'hacker@example.com']);
});

it('does not issue a usable token to a newly registered unapproved student', function () {
    $city = App\Modules\City\Models\City::create(['name' => 'Baku']);
    $grade = Grade::create(['name' => 'Grade 1']);

    $response = $this->postJson('/api/auth/register', [
        'name' => 'Ali',
        'phone' => '+994509999999',
        'email' => 'ali@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'type' => 'student',
        'student' => ['grade_id' => $grade->id, 'city_id' => $city->id],
    ]);

    $response->assertStatus(201);

    // No auth cookie / token is issued while the account is pending approval.
    expect($response->headers->getCookies())->toBeEmpty();

    $user = User::where('email', 'ali@example.com')->first();
    expect($user->is_approved)->toBeFalse()
        ->and($user->tokens()->count())->toBe(0);
});

it('fails admin login for unapproved admin', function () {
    User::factory()->create([
        'email' => 'unapproved@example.com',
        'type' => 'admin',
        'is_approved' => false,
        'password' => bcrypt('admin123'),
    ]);

    $this->postJson('/api/auth/admin-login', [
        'email' => 'unapproved@example.com',
        'password' => 'admin123',
    ])->assertStatus(422);
});

it('blocks unapproved admin from admin API endpoints', function () {
    $unapproved = User::factory()->create(['type' => 'admin', 'is_approved' => false]);
    $unapproved->assignRole('admin');

    $this->actingAs($unapproved)
        ->getJson('/api/admin/users')
        ->assertStatus(403);
});

// ─── Route shadowing fix ────────────────────────────────────────────

it('routes admin pending users to the pending endpoint', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $pending = User::factory()->create(['type' => 'student', 'is_approved' => false]);

    $response = $this->actingAs($admin)->getJson('/api/admin/users/pending');

    $response->assertStatus(200);
    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($pending->id);
});

// ─── API answer leakage fix ─────────────────────────────────────────

function makeSecureQuiz(): array
{
    $topic = Topic::create(['name' => 'Algebra', 'difficulty_level' => DifficultyLevel::Beginner->value]);
    $lesson = Lesson::create(['topic_id' => $topic->id, 'name' => 'Lesson', 'description' => 'desc']);
    $question = Question::create([
        'question' => [['type' => 'text', 'content' => '2+2?']],
        'variant_a' => [['type' => 'text', 'content' => '3']],
        'variant_b' => [['type' => 'text', 'content' => '4']],
        'variant_c' => [['type' => 'text', 'content' => '5']],
        'variant_d' => [['type' => 'text', 'content' => '6']],
        'variant_e' => [['type' => 'text', 'content' => '7']],
        'right_answer' => 'variant_b',
        'type' => 'regular',
        'difficulty_level' => DifficultyLevel::Beginner->value,
        'lesson_id' => $lesson->id,
    ]);
    $quiz = Quiz::create(['name' => 'Quiz 1', 'lesson_id' => $lesson->id, 'type' => 'quiz']);
    $quiz->questions()->attach($question->id);

    return [$quiz, $question];
}

it('does not leak question answers through quiz show for students', function () {
    [$quiz, $question] = makeSecureQuiz();
    $student = User::factory()->create(['type' => 'student']);

    $response = $this->actingAs($student)->getJson("/api/quizzes/{$quiz->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.questions.0.id', $question->id)
        ->assertJsonMissingPath('data.questions.0.right_answer')
        ->assertJsonMissingPath('data.questions.0.open_answer')
        ->assertJsonMissingPath('data.questions.0.explanation');
});

it('still exposes question answers to admins through quiz show', function () {
    [$quiz] = makeSecureQuiz();
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->getJson("/api/quizzes/{$quiz->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.questions.0.right_answer', 'variant_b');
});

// ─── Student directory authorization ────────────────────────────────

it('blocks students from listing all students', function () {
    $student = User::factory()->create(['type' => 'student']);

    $this->actingAs($student)->getJson('/api/students')->assertStatus(403);
});

it('allows admins to list students', function () {
    $admin = User::factory()->create(['type' => 'admin']);
    $admin->assignRole('admin');

    $this->actingAs($admin)->getJson('/api/students')->assertStatus(200);
});

// ─── Monthly star filter ────────────────────────────────────────────

it('filters total stars by month', function () {
    $user = User::factory()->create(['type' => 'student']);
    $star = Star::create([
        'type' => 'quiz_completed',
        'point' => 10,
        'name' => 'Quiz',
    ]);

    $lastMonth = new UserStar([
        'user_id' => $user->id,
        'star_id' => $star->id,
        'reference_type' => 'quiz',
        'reference_id' => 1,
    ]);
    $lastMonth->created_at = now()->subMonth();
    $lastMonth->save();

    $thisMonth = new UserStar([
        'user_id' => $user->id,
        'star_id' => $star->id,
        'reference_type' => 'quiz',
        'reference_id' => 2,
    ]);
    $thisMonth->created_at = now();
    $thisMonth->save();

    $service = app(StarService::class);

    expect($service->getUserTotalStars($user->id))->toBe(20);
    expect($service->getUserTotalStars($user->id, now()->format('Y-m')))->toBe(10);
    expect($service->getUserTotalStars($user->id, now()->subMonth()->format('Y-m')))->toBe(10);
});

// ─── Lesson/video content API requires auth ──────────────────────────

it('rejects unauthenticated access to lesson list API', function () {
    $topic = Topic::create(['name' => 'Algebra', 'difficulty_level' => DifficultyLevel::Beginner->value]);

    $this->getJson("/api/topics/{$topic->id}/lessons")->assertStatus(401);
});

it('rejects unauthenticated access to video list API', function () {
    $this->getJson('/api/videos')->assertStatus(401);
});

it('allows authenticated students to list lessons for a topic', function () {
    $topic = Topic::create(['name' => 'Algebra', 'difficulty_level' => DifficultyLevel::Beginner->value]);
    Lesson::create(['topic_id' => $topic->id, 'name' => 'Lesson 1', 'description' => 'desc']);
    $student = User::factory()->create(['type' => 'student']);

    $this->actingAs($student)->getJson("/api/topics/{$topic->id}/lessons")->assertStatus(200);
});

// ─── Lesson page must not award completion star ─────────────────────

it('does not award lesson completed star by opening lesson page', function () {
    $topic = Topic::create(['name' => 'Algebra', 'difficulty_level' => DifficultyLevel::Beginner->value]);
    $lesson = Lesson::create(['topic_id' => $topic->id, 'name' => 'Lesson 1', 'description' => 'desc']);
    $star = Star::create([
        'type' => 'lesson_completed',
        'point' => 5,
        'name' => 'Lesson',
    ]);
    $student = User::factory()->create(['type' => 'student']);

    $this->actingAs($student)->get("/lesson/{$lesson->id}");

    expect(UserStar::where('user_id', $student->id)
        ->where('star_id', $star->id)
        ->count())->toBe(0);
});

// ─── Grade restriction on exams ─────────────────────────────────────

function makeGradeStudent(string $gradeName): array
{
    $city = App\Modules\City\Models\City::create(['name' => 'Baku']);
    $grade = Grade::create(['name' => $gradeName]);
    $user = User::factory()->create(['type' => 'student']);
    $user->student()->create(['grade_id' => $grade->id, 'city_id' => $city->id]);

    return [$user, $grade];
}

it('blocks students from starting an exam outside their grade', function () {
    [$user, $grade1] = makeGradeStudent('Grade 1');
    $grade2 = Grade::create(['name' => 'Grade 2']);
    $exam = Exam::create([
        'name' => 'Exam 1',
        'grade_id' => $grade2->id,
        'passing_score' => 60,
    ]);

    $this->actingAs($user)
        ->postJson("/api/exams/{$exam->id}/start")
        ->assertStatus(403);
});

it('allows students to start an exam for their own grade', function () {
    [$user, $grade] = makeGradeStudent('Grade 1');
    $exam = Exam::create([
        'name' => 'Exam 1',
        'grade_id' => $grade->id,
        'passing_score' => 60,
    ]);

    $this->actingAs($user)
        ->postJson("/api/exams/{$exam->id}/start")
        ->assertStatus(200);
});

// ─── Grade restriction on quiz (inherited from lesson → topic grades) ───

function makeGradeTopicQuiz(string $gradeName, ?int $attachToGrade = null): array
{
    $city = App\Modules\City\Models\City::create(['name' => 'Baku']);
    $grade = Grade::create(['name' => $gradeName]);
    $user = User::factory()->create(['type' => 'student']);
    $user->student()->create(['grade_id' => $grade->id, 'city_id' => $city->id]);

    $topic = Topic::create(['name' => 'Algebra', 'difficulty_level' => DifficultyLevel::Beginner->value]);
    if ($attachToGrade !== null) {
        $topic->grades()->attach($attachToGrade);
    }
    $lesson = Lesson::create(['topic_id' => $topic->id, 'name' => 'Lesson 1', 'description' => 'desc']);
    $quiz = Quiz::create(['name' => 'Quiz 1', 'lesson_id' => $lesson->id, 'type' => 'quiz']);

    return [$user, $grade, $quiz];
}

it('blocks students from taking a quiz whose topic is for another grade', function () {
    [$user, $grade, $quiz] = makeGradeTopicQuiz('Grade 1');
    $otherGrade = Grade::create(['name' => 'Grade 2']);

    // Topic restricted to Grade 2, but student is in Grade 1
    $quiz->lesson->topic->grades()->sync([$otherGrade->id]);

    $this->actingAs($user)
        ->postJson("/api/quizzes/{$quiz->id}/start")
        ->assertStatus(403);
});

it('allows students to take a quiz whose topic matches their grade', function () {
    [$user, $grade, $quiz] = makeGradeTopicQuiz('Grade 1');

    $quiz->lesson->topic->grades()->sync([$grade->id]);

    $this->actingAs($user)
        ->postJson("/api/quizzes/{$quiz->id}/start")
        ->assertStatus(200);
});

it('allows students to take a quiz whose topic has no grade restriction', function () {
    [$user, $grade, $quiz] = makeGradeTopicQuiz('Grade 1');

    $this->actingAs($user)
        ->postJson("/api/quizzes/{$quiz->id}/start")
        ->assertStatus(200);
});

// ─── Grade restriction on web exam pages ───────────────────────────────

it('blocks students from viewing exam details of another grade on the web', function () {
    [$user, $grade1] = makeGradeStudent('Grade 1');
    $grade2 = Grade::create(['name' => 'Grade 2']);
    $exam = Exam::create([
        'name' => 'Exam 1',
        'grade_id' => $grade2->id,
        'passing_score' => 60,
    ]);

    $this->actingAs($user)
        ->get("/exam/{$exam->id}")
        ->assertStatus(403);
});

it('allows students to view exam details of their own grade on the web', function () {
    [$user, $grade] = makeGradeStudent('Grade 1');
    $exam = Exam::create([
        'name' => 'Exam 1',
        'grade_id' => $grade->id,
        'passing_score' => 60,
    ]);

    $this->actingAs($user)
        ->get("/exam/{$exam->id}")
        ->assertStatus(200);
});

// ─── Inline feedback must not leak answers on the web solve pages ──────
//
// The correct answer and the explanation video are NEVER embedded in the solve
// page HTML. Instead the browser POSTs the single selected answer to a
// server-side check endpoint and only receives right/wrong + the video.

it('does not leak the correct answer or video into the quiz solve page', function () {
    [$quiz, $question] = makeSecureQuiz();
    $question->update(['explanation_video_url' => 'https://youtu.be/dQw4w9WgXcQ']);
    $user = User::factory()->create(['type' => 'student']);

    $this->actingAs($user)
        ->get("/quiz/{$quiz->id}")
        ->assertStatus(200)
        ->assertDontSee('data-correct')
        ->assertDontSee('data-video')
        ->assertDontSee('dQw4w9WgXcQ')
        ->assertSee("/quiz/{$quiz->id}/check-answer");
});

it('does not leak the open-question model answer into the quiz solve page', function () {
    [$quiz, $question] = makeSecureQuiz();
    $openQuestion = Question::create([
        'question' => [['type' => 'text', 'content' => 'Capital of France?']],
        'open_answer' => [['type' => 'text', 'content' => 'Paris']],
        'type' => 'open',
        'answer_type' => 'exact',
        'difficulty_level' => DifficultyLevel::Beginner->value,
    ]);
    $quiz->questions()->attach($openQuestion->id);
    $user = User::factory()->create(['type' => 'student']);

    $this->actingAs($user)
        ->get("/quiz/{$quiz->id}")
        ->assertStatus(200)
        ->assertDontSee('Paris');
});

it('does not leak the correct answer or video into the exam solve page', function () {
    [$user, $grade] = makeGradeStudent('Grade 1');
    $exam = Exam::create([
        'name' => 'Exam 1',
        'grade_id' => $grade->id,
        'passing_score' => 60,
    ]);
    $question = Question::create([
        'question' => [['type' => 'text', 'content' => 'Q?']],
        'variant_a' => [['type' => 'text', 'content' => '3']],
        'variant_b' => [['type' => 'text', 'content' => '4']],
        'variant_c' => [['type' => 'text', 'content' => '5']],
        'variant_d' => [['type' => 'text', 'content' => '6']],
        'variant_e' => [['type' => 'text', 'content' => '7']],
        'right_answer' => 'variant_b',
        'type' => 'regular',
        'difficulty_level' => DifficultyLevel::Beginner->value,
        'explanation_video_url' => 'https://youtu.be/abcdEFGH123',
    ]);
    $exam->questions()->attach($question->id);

    $this->actingAs($user)
        ->get("/exam/{$exam->id}/start")
        ->assertStatus(200)
        ->assertDontSee('data-correct')
        ->assertDontSee('data-video')
        ->assertDontSee('abcdEFGH123');
});

it('evaluates a single quiz answer server-side via check-answer', function () {
    [$quiz, $question] = makeSecureQuiz(); // right_answer = variant_b
    [$user, $grade] = makeGradeStudent('Grade 1');

    $this->actingAs($user)
        ->postJson("/quiz/{$quiz->id}/check-answer", [
            'question_id' => $question->id,
            'answer' => 'b',
        ])
        ->assertStatus(200)
        ->assertJson([
            'type' => 'regular',
            'correct' => true,
            'correct_answer' => 'b',
        ]);

    $this->actingAs($user)
        ->postJson("/quiz/{$quiz->id}/check-answer", [
            'question_id' => $question->id,
            'answer' => 'c',
        ])
        ->assertStatus(200)
        ->assertJson([
            'type' => 'regular',
            'correct' => false,
            'correct_answer' => 'b',
        ]);
});

it('never leaks the model answer for open questions via check-answer', function () {
    [$quiz, $question] = makeSecureQuiz();
    $openQuestion = Question::create([
        'question' => [['type' => 'text', 'content' => 'Capital of France?']],
        'open_answer' => [['type' => 'text', 'content' => 'Paris']],
        'type' => 'open',
        'answer_type' => 'exact',
        'difficulty_level' => DifficultyLevel::Beginner->value,
        'explanation_video_url' => 'https://youtu.be/openVid12345',
    ]);
    $quiz->questions()->attach($openQuestion->id);
    [$user, $grade] = makeGradeStudent('Grade 1');

    $this->actingAs($user)
        ->postJson("/quiz/{$quiz->id}/check-answer", [
            'question_id' => $openQuestion->id,
            'answer' => 'Paris',
        ])
        ->assertStatus(200)
        ->assertJsonPath('type', 'open')
        ->assertJsonPath('correct', false)
        ->assertJsonPath('correct_answer', null)
        ->assertJsonPath('explanation_video_url', 'https://youtu.be/openVid12345');
});

it('rejects check-answer for a question that is not part of the quiz', function () {
    [$quiz, $question] = makeSecureQuiz();
    $foreignQuestion = Question::create([
        'question' => [['type' => 'text', 'content' => 'Foreign?']],
        'variant_a' => [['type' => 'text', 'content' => 'x']],
        'variant_b' => [['type' => 'text', 'content' => 'y']],
        'right_answer' => 'variant_a',
        'type' => 'regular',
        'difficulty_level' => DifficultyLevel::Beginner->value,
    ]);
    [$user, $grade] = makeGradeStudent('Grade 1');

    $this->actingAs($user)
        ->postJson("/quiz/{$quiz->id}/check-answer", [
            'question_id' => $foreignQuestion->id,
            'answer' => 'a',
        ])
        ->assertStatus(404);
});
