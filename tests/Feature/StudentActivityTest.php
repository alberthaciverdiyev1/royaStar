<?php

use App\Modules\City\Models\City;
use App\Modules\Lesson\Models\Lesson;
use App\Modules\Lesson\Models\StudentLesson;
use App\Modules\Grade\Models\Grade;
use App\Modules\Quiz\Models\Quiz;
use App\Modules\Quiz\Models\StudentQuiz;
use App\Modules\Student\Models\Student;
use App\Modules\Student\Models\StudentActivity;
use App\Modules\Subject\Models\Subject;
use App\Modules\Topic\Models\Topic;
use App\Modules\Topic\Enums\DifficultyLevel;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clean activity DB between tests (separate connection, not wrapped in RefreshDatabase transaction)
    StudentActivity::query()->delete();

    $this->grade = Grade::create(['name' => ['en' => 'Grade 5']]);
    $this->city = City::create(['name' => ['en' => 'Baku']]);
    $this->subject = Subject::create(['name' => ['en' => 'Math']]);
    $this->topic = Topic::create([
        'subject_id' => $this->subject->id,
        'name' => ['en' => 'Algebra', 'az' => 'Cəbr'],
        'difficulty_level' => DifficultyLevel::Beginner->value,
    ]);
    $this->lesson = Lesson::create([
        'name' => ['en' => 'Intro', 'az' => 'Giriş'],
        'topic_id' => $this->topic->id,
    ]);
    $this->lesson2 = Lesson::create([
        'name' => ['en' => 'Basics', 'az' => 'Əsaslar'],
        'topic_id' => $this->topic->id,
    ]);

    $this->studentUser = User::factory()->create(['type' => 'student']);
    $this->student = $this->studentUser->student()->create([
        'grade_id' => $this->grade->id,
        'city_id' => $this->city->id,
    ]);
});

it('returns 401 when not authenticated', function () {
    $response = $this->getJson('/api/students/activities');

    $response->assertStatus(401);
});

it('returns empty array for student with no activities', function () {
    $response = $this->actingAs($this->studentUser)->getJson('/api/students/activities');

    $response->assertStatus(200)
        ->assertJson(['success' => true]);
    expect($response->json('data'))->toBe([]);
});

it('returns lesson_completed activities', function () {
    StudentLesson::create([
        'student_id' => $this->student->id,
        'lesson_id' => $this->lesson->id,
    ]);
    StudentLesson::create([
        'student_id' => $this->student->id,
        'lesson_id' => $this->lesson2->id,
    ]);

    StudentActivity::create([
        'student_id' => $this->student->id,
        'type' => 'lesson_completed',
        'reference_type' => 'lesson',
        'reference_id' => $this->lesson->id,
        'metadata' => [
            'lesson_name' => 'Intro',
            'topic_name' => 'Algebra',
            'subject_name' => 'Math',
            'completed_lessons' => 2,
            'total_lessons' => 2,
            'percentage' => 100,
        ],
    ]);
    StudentActivity::create([
        'student_id' => $this->student->id,
        'type' => 'lesson_completed',
        'reference_type' => 'lesson',
        'reference_id' => $this->lesson2->id,
        'metadata' => [
            'lesson_name' => 'Basics',
            'topic_name' => 'Algebra',
            'subject_name' => 'Math',
            'completed_lessons' => 2,
            'total_lessons' => 2,
            'percentage' => 100,
        ],
    ]);

    $response = $this->actingAs($this->studentUser)->getJson('/api/students/activities');

    $response->assertStatus(200);
    $activities = $response->json('data');
    expect($activities)->toHaveCount(2);
    expect($activities[0]['type'])->toBe('lesson_completed');
    expect($activities[0])->toHaveKeys([
        'type', 'topic_name', 'subject_name', 'lesson_name',
        'completed_lessons', 'total_lessons', 'percentage', 'date', 'created_at',
    ]);
    expect($activities[0]['percentage'])->toBe(100);
    expect($activities[0]['completed_lessons'])->toBe(2);
    expect($activities[0]['total_lessons'])->toBe(2);
});

it('returns quiz_completed activities', function () {
    $quiz = Quiz::create([
        'name' => ['en' => 'Algebra Quiz'],
        'lesson_id' => $this->lesson->id,
    ]);

    $question1 = \App\Modules\Question\Models\Question::create([
        'question' => ['en' => ['type' => 'text', 'content' => 'Q1?']],
        'type' => 'regular',
        'topic_id' => $this->topic->id,
        'difficulty_level' => DifficultyLevel::Beginner->value,
    ]);
    $question2 = \App\Modules\Question\Models\Question::create([
        'question' => ['en' => ['type' => 'text', 'content' => 'Q2?']],
        'type' => 'regular',
        'topic_id' => $this->topic->id,
        'difficulty_level' => DifficultyLevel::Beginner->value,
    ]);
    $quiz->questions()->attach([$question1->id, $question2->id]);

    StudentQuiz::create([
        'student_id' => $this->student->id,
        'quiz_id' => $quiz->id,
        'question_id' => $question1->id,
        'is_correct' => true,
    ]);
    StudentQuiz::create([
        'student_id' => $this->student->id,
        'quiz_id' => $quiz->id,
        'question_id' => $question2->id,
        'is_correct' => false,
    ]);

    $response = $this->actingAs($this->studentUser)->getJson('/api/students/activities');

    $response->assertStatus(200);
    $activities = $response->json('data');
    expect($activities)->toHaveCount(1);
    expect($activities[0]['type'])->toBe('quiz_completed');
    expect($activities[0])->toHaveKeys([
        'type', 'topic_name', 'subject_name', 'quiz_name',
        'correct_answers', 'total_questions', 'score', 'date', 'created_at',
    ]);
    expect($activities[0]['correct_answers'])->toBe(1);
    expect($activities[0]['total_questions'])->toBe(2);
    expect($activities[0]['score'])->toBe(50);
});

it('returns mixed activities ordered by date', function () {
    $quiz = Quiz::create([
        'name' => ['en' => 'Algebra Quiz'],
        'lesson_id' => $this->lesson->id,
    ]);

    $question = \App\Modules\Question\Models\Question::create([
        'question' => ['en' => ['type' => 'text', 'content' => 'Test?']],
        'type' => 'regular',
        'topic_id' => $this->topic->id,
        'difficulty_level' => DifficultyLevel::Beginner->value,
    ]);
    $quiz->questions()->attach($question->id);

    // Old lesson completion
    $sl = new StudentLesson([
        'student_id' => $this->student->id,
        'lesson_id' => $this->lesson->id,
    ]);
    $sl->created_at = now()->subDays(5);
    $sl->updated_at = now()->subDays(5);
    $sl->save();

    $lessonActivity = StudentActivity::create([
        'student_id' => $this->student->id,
        'type' => 'lesson_completed',
        'reference_type' => 'lesson',
        'reference_id' => $this->lesson->id,
        'metadata' => [
            'lesson_name' => 'Intro',
            'topic_name' => 'Algebra',
            'subject_name' => 'Math',
            'completed_lessons' => 1,
            'total_lessons' => 2,
            'percentage' => 50,
        ],
    ]);
    $lessonActivity->created_at = now()->subDays(5);
    $lessonActivity->save();

    // Recent quiz
    $sq = new StudentQuiz([
        'student_id' => $this->student->id,
        'quiz_id' => $quiz->id,
        'question_id' => $question->id,
        'is_correct' => true,
    ]);
    $sq->created_at = now()->subDay();
    $sq->updated_at = now()->subDay();
    $sq->save();

    $response = $this->actingAs($this->studentUser)->getJson('/api/students/activities');

    $response->assertStatus(200);
    $activities = $response->json('data');
    expect($activities)->toHaveCount(2);
    // Most recent first
    expect($activities[0]['type'])->toBe('quiz_completed');
    expect($activities[1]['type'])->toBe('lesson_completed');
});

it('returns 200 for non-student user but empty data', function () {
    $nonStudentUser = User::factory()->create(['type' => 'teacher']);

    $response = $this->actingAs($nonStudentUser)->getJson('/api/students/activities');

    $response->assertStatus(200);
    expect($response->json('data'))->toBe([]);
});

it('includes lesson progress percentage per topic', function () {
    Lesson::create([
        'name' => ['en' => 'Advanced'],
        'topic_id' => $this->topic->id,
    ]);

    // Complete only 1 out of 3 lessons
    StudentLesson::create([
        'student_id' => $this->student->id,
        'lesson_id' => $this->lesson->id,
    ]);

    StudentActivity::create([
        'student_id' => $this->student->id,
        'type' => 'lesson_completed',
        'reference_type' => 'lesson',
        'reference_id' => $this->lesson->id,
        'metadata' => [
            'lesson_name' => 'Intro',
            'topic_name' => 'Algebra',
            'subject_name' => 'Math',
            'completed_lessons' => 1,
            'total_lessons' => 3,
            'percentage' => 33,
        ],
    ]);

    $response = $this->actingAs($this->studentUser)->getJson('/api/students/activities');

    $response->assertStatus(200);
    $activity = $response->json('data.0');
    expect($activity['type'])->toBe('lesson_completed');
    expect($activity['completed_lessons'])->toBe(1);
    expect($activity['total_lessons'])->toBe(3);
    expect($activity['percentage'])->toBe(33);
});
