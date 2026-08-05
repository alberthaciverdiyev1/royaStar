<?php

use App\Modules\City\Models\City;
use App\Modules\Exam\Models\Exam;
use App\Modules\Exam\Models\StudentExam;
use App\Modules\Grade\Models\Grade;
use App\Modules\Lesson\Models\Lesson;
use App\Modules\Question\Models\Question;
use App\Modules\Quiz\Models\Quiz;
use App\Modules\Quiz\Models\StudentQuiz;
use App\Modules\Star\Models\Star;
use App\Modules\Star\Services\StarService;
use App\Modules\Student\Models\Student;
use App\Modules\Topic\Enums\DifficultyLevel;
use App\Modules\Topic\Models\Topic;
use App\Modules\User\Models\User;
use App\Services\AssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'student', 'guard_name' => 'api']);

    $topic = Topic::create([
        'name' => 'Algebra',
        'difficulty_level' => DifficultyLevel::Beginner->value,
    ]);
    $lesson = Lesson::create([
        'topic_id' => $topic->id,
        'name' => 'Test Lesson',
        'description' => 'Test lesson description',
    ]);

    $this->lesson = $lesson;

    // Regular question — correct answer is 'b'
    $this->regularQuestion = Question::create([
        'question' => ['en' => [['type' => 'text', 'content' => 'What is 2+2?']]],
        'type' => 'regular',
        'right_answer' => 'b',
        'variant_a' => ['en' => [['type' => 'text', 'content' => '3']]],
        'variant_b' => ['en' => [['type' => 'text', 'content' => '4']]],
        'variant_c' => ['en' => [['type' => 'text', 'content' => '5']]],
        'variant_d' => ['en' => [['type' => 'text', 'content' => '6']]],
        'variant_e' => ['en' => [['type' => 'text', 'content' => '7']]],
        'difficulty_level' => DifficultyLevel::Beginner->value,
        'lesson_id' => $lesson->id,
    ]);

    // Open question — exact answer 'Paris'
    $this->openQuestion = Question::create([
        'question' => ['en' => [['type' => 'text', 'content' => 'Capital of France?']]],
        'type' => 'open',
        'answer_type' => 'exact',
        'open_answer' => ['en' => [['type' => 'text', 'content' => 'Paris']]],
        'difficulty_level' => DifficultyLevel::Beginner->value,
        'lesson_id' => $lesson->id,
    ]);

    // Open question — similar answer 'Azerbaijan'
    $this->similarQuestion = Question::create([
        'question' => ['en' => [['type' => 'text', 'content' => 'Country of Baku?']]],
        'type' => 'open',
        'answer_type' => 'similar',
        'open_answer' => ['en' => [['type' => 'text', 'content' => 'Azerbaijan']]],
        'difficulty_level' => DifficultyLevel::Beginner->value,
        'lesson_id' => $lesson->id,
    ]);

    $grade = Grade::create(['name' => 'Grade 1']);
    $city = City::create(['name' => 'Baku']);

    $user = User::factory()->create(['type' => 'student']);
    $user->assignRole('student');
    $this->user = $user;

    $this->student = Student::create([
        'user_id' => $user->id,
        'grade_id' => $grade->id,
        'city_id' => $city->id,
    ]);

    $this->service = app(AssessmentService::class);
});

// ─── resolveRightAnswerLetter ───────────────────────────────────

it('resolves a letter right_answer', function () {
    $q = Question::create([
        'question' => ['en' => [['type' => 'text', 'content' => 'Q']]],
        'type' => 'regular',
        'right_answer' => 'c',
        'variant_a' => ['en' => [['type' => 'text', 'content' => '1']]],
        'variant_b' => ['en' => [['type' => 'text', 'content' => '2']]],
        'variant_c' => ['en' => [['type' => 'text', 'content' => '3']]],
        'variant_d' => ['en' => [['type' => 'text', 'content' => '4']]],
        'variant_e' => ['en' => [['type' => 'text', 'content' => '5']]],
        'difficulty_level' => DifficultyLevel::Beginner->value,
        'lesson_id' => $this->lesson->id,
    ]);

    expect($this->service->resolveRightAnswerLetter($q, 'en'))->toBe('c');
});

it('resolves variant_x right_answer by matching variant text', function () {
    $q = Question::create([
        'question' => ['en' => [['type' => 'text', 'content' => 'Q']]],
        'type' => 'regular',
        'right_answer' => 'variant_d',
        'variant_a' => ['en' => [['type' => 'text', 'content' => '1']]],
        'variant_b' => ['en' => [['type' => 'text', 'content' => '2']]],
        'variant_c' => ['en' => [['type' => 'text', 'content' => '3']]],
        'variant_d' => ['en' => [['type' => 'text', 'content' => '4']]],
        'variant_e' => ['en' => [['type' => 'text', 'content' => '5']]],
        'difficulty_level' => DifficultyLevel::Beginner->value,
        'lesson_id' => $this->lesson->id,
    ]);

    expect($this->service->resolveRightAnswerLetter($q, 'en'))->toBe('d');
});

it('returns empty string for open questions', function () {
    expect($this->service->resolveRightAnswerLetter($this->openQuestion, 'en'))->toBe('');
});

it('returns empty string for null question', function () {
    expect($this->service->resolveRightAnswerLetter(null, 'en'))->toBe('');
});

// ─── evaluateAnswers ────────────────────────────────────────────

it('evaluates a fully correct quiz', function () {
    $questions = collect([$this->regularQuestion, $this->openQuestion])->keyBy('id');

    $result = $this->service->evaluateAnswers([
        ['question_id' => $this->regularQuestion->id, 'answer' => 'b'],
        ['question_id' => $this->openQuestion->id, 'answer' => 'Paris'],
    ], $questions, 'en');

    expect($result['score'])->toEqual(100)
        ->and($result['total'])->toBe(2)
        ->and($result['correct'])->toBe(2)
        ->and($result['wrong'])->toBe(0)
        ->and($result['skipped'])->toBe(0);
});

it('evaluates wrong, skipped, and correct answers', function () {
    $questions = collect([$this->regularQuestion, $this->openQuestion])->keyBy('id');

    $result = $this->service->evaluateAnswers([
        ['question_id' => $this->regularQuestion->id, 'answer' => 'a'], // wrong
        ['question_id' => $this->openQuestion->id, 'answer' => ''],     // skipped
    ], $questions, 'en');

    expect($result['score'])->toEqual(0)
        ->and($result['total'])->toBe(2)
        ->and($result['correct'])->toBe(0)
        ->and($result['wrong'])->toBe(1)
        ->and($result['skipped'])->toBe(1);
});

it('accepts a similar open answer that matches exactly', function () {
    $questions = collect([$this->similarQuestion])->keyBy('id');

    $result = $this->service->evaluateAnswers([
        ['question_id' => $this->similarQuestion->id, 'answer' => 'Azerbaijan'],
    ], $questions, 'en');

    expect($result['score'])->toEqual(100)
        ->and($result['correct'])->toBe(1)
        ->and($result['wrong'])->toBe(0);
});

it('accepts a similar open answer with light typos or formatting noise', function () {
    $questions = collect([$this->similarQuestion])->keyBy('id');

    $result = $this->service->evaluateAnswers([
        ['question_id' => $this->similarQuestion->id, 'answer' => ' azerbaijan! '],
    ], $questions, 'en');

    expect($result['score'])->toEqual(100)
        ->and($result['correct'])->toBe(1)
        ->and($result['wrong'])->toBe(0);
});

it('rejects a clearly unrelated similar answer', function () {
    $questions = collect([$this->similarQuestion])->keyBy('id');

    $result = $this->service->evaluateAnswers([
        ['question_id' => $this->similarQuestion->id, 'answer' => 'Brazil'],
    ], $questions, 'en');

    expect($result['score'])->toEqual(0)
        ->and($result['correct'])->toBe(0)
        ->and($result['wrong'])->toBe(1);
});

it('handles empty answers array', function () {
    $questions = collect([$this->regularQuestion])->keyBy('id');

    $result = $this->service->evaluateAnswers([], $questions, 'en');

    // Every question without a submitted answer counts as skipped, and a
    // per-question record is still produced so persisted scores stay consistent.
    expect($result['score'])->toEqual(0)
        ->and($result['total'])->toBe(1)
        ->and($result['correct'])->toBe(0)
        ->and($result['wrong'])->toBe(0)
        ->and($result['skipped'])->toBe(1)
        ->and($result['answers'])->toHaveCount(1);
});

it('persists skipped questions when submission is partial', function () {
    $questions = collect([$this->regularQuestion, $this->openQuestion])->keyBy('id');

    // Only one of the two questions is submitted.
    $result = $this->service->evaluateAnswers([
        ['question_id' => $this->regularQuestion->id, 'answer' => 'b'],
    ], $questions, 'en');

    expect($result['total'])->toBe(2)
        ->and($result['correct'])->toBe(1)
        ->and($result['skipped'])->toBe(1)
        ->and($result['answers'])->toHaveCount(2);

    $qids = collect($result['answers'])->pluck('question_id')->sort()->values()->all();
    expect($qids)->toBe([$this->regularQuestion->id, $this->openQuestion->id]);
});

it('ignores answers for unknown questions', function () {
    $questions = collect([$this->regularQuestion])->keyBy('id');

    $result = $this->service->evaluateAnswers([
        ['question_id' => 99999, 'answer' => 'b'],
    ], $questions, 'en');

    expect($result['total'])->toBe(1)
        ->and($result['correct'])->toBe(0);
});

// ─── submitQuiz ─────────────────────────────────────────────────

it('persists quiz attempts and awards stars on first completion', function () {
    Star::create(['type' => 'quiz_completed', 'point' => 10]);
    Star::create(['type' => 'quiz_perfect', 'point' => 5]);

    $quiz = Quiz::create(['name' => 'Test Quiz', 'lesson_id' => $this->lesson->id, 'type' => 'topic_based']);
    $quiz->questions()->attach([$this->regularQuestion->id, $this->openQuestion->id]);

    $result = $this->service->submitQuiz($this->user, $this->student, $quiz, [
        ['question_id' => $this->regularQuestion->id, 'answer' => 'b'],
        ['question_id' => $this->openQuestion->id, 'answer' => 'Paris'],
    ], 'en');

    expect($result['score'])->toEqual(100);

    $this->assertDatabaseCount('student_quizzes', 2);
    $this->assertDatabaseHas('student_quizzes', [
        'student_id' => $this->student->id,
        'quiz_id' => $quiz->id,
        'question_id' => $this->regularQuestion->id,
        'answer' => 'b',
        'is_correct' => true,
    ]);

    // Stars awarded
    $this->assertDatabaseHas('user_stars', [
        'user_id' => $this->user->id,
        'reference_type' => 'quiz',
        'reference_id' => $quiz->id,
    ]);
});

it('does not award duplicate stars on repeated quiz completion', function () {
    Star::create(['type' => 'quiz_completed', 'point' => 10]);
    Star::create(['type' => 'quiz_perfect', 'point' => 5]);

    $quiz = Quiz::create(['name' => 'Test Quiz', 'lesson_id' => $this->lesson->id, 'type' => 'topic_based']);
    $quiz->questions()->attach([$this->regularQuestion->id, $this->openQuestion->id]);

    $this->service->submitQuiz($this->user, $this->student, $quiz, [
        ['question_id' => $this->regularQuestion->id, 'answer' => 'b'],
        ['question_id' => $this->openQuestion->id, 'answer' => 'Paris'],
    ], 'en');

    // Second attempt replaces records
    $this->service->submitQuiz($this->user, $this->student, $quiz, [
        ['question_id' => $this->regularQuestion->id, 'answer' => 'b'],
        ['question_id' => $this->openQuestion->id, 'answer' => 'Paris'],
    ], 'en');

    // Old attempts soft-deleted and replaced, not duplicated
    expect(StudentQuiz::where('student_id', $this->student->id)->where('quiz_id', $quiz->id)->count())->toBe(2);

    // Stars awarded only once (quiz_completed + quiz_perfect)
    $this->assertDatabaseCount('user_stars', 2);
});

it('does not award perfect star when score is not 100', function () {
    Star::create(['type' => 'quiz_completed', 'point' => 10]);
    Star::create(['type' => 'quiz_perfect', 'point' => 5]);

    $quiz = Quiz::create(['name' => 'Test Quiz', 'lesson_id' => $this->lesson->id, 'type' => 'topic_based']);
    $quiz->questions()->attach([$this->regularQuestion->id, $this->openQuestion->id]);

    $this->service->submitQuiz($this->user, $this->student, $quiz, [
        ['question_id' => $this->regularQuestion->id, 'answer' => 'a'], // wrong
        ['question_id' => $this->openQuestion->id, 'answer' => 'Paris'],
    ], 'en');

    $this->assertDatabaseHas('user_stars', [
        'user_id' => $this->user->id,
        'star_id' => Star::where('type', 'quiz_completed')->first()->id,
    ]);
    $this->assertDatabaseMissing('user_stars', [
        'user_id' => $this->user->id,
        'star_id' => Star::where('type', 'quiz_perfect')->first()->id,
    ]);
});

// ─── submitExam ─────────────────────────────────────────────────

it('persists exam attempts and awards stars on passing score', function () {
    Star::create(['type' => 'exam_passed', 'point' => 10]);
    Star::create(['type' => 'exam_excellent', 'point' => 5]);

    $grade = Grade::create(['name' => 'Grade 1']);
    $exam = Exam::create([
        'name' => 'Final Exam',
        'description' => 'Test exam',
        'grade_id' => $grade->id,
        'passing_score' => 50,
        'type' => 'final',
    ]);
    $exam->questions()->attach([$this->regularQuestion->id, $this->openQuestion->id]);

    $result = $this->service->submitExam($this->user, $this->student, $exam, [
        ['question_id' => $this->regularQuestion->id, 'answer' => 'b'],
        ['question_id' => $this->openQuestion->id, 'answer' => 'Paris'],
    ], 'en');

    expect($result['score'])->toEqual(100);

    $this->assertDatabaseCount('student_exams', 2);
    $this->assertDatabaseHas('student_exams', [
        'student_id' => $this->student->id,
        'exam_id' => $exam->id,
        'question_id' => $this->regularQuestion->id,
        'is_correct' => true,
    ]);

    // passed + excellent (score 100 >= 90)
    $this->assertDatabaseCount('user_stars', 2);
});

it('replaces exam attempts on resubmission', function () {
    Star::create(['type' => 'exam_passed', 'point' => 10]);
    Star::create(['type' => 'exam_excellent', 'point' => 5]);

    $grade = Grade::create(['name' => 'Grade 1']);
    $exam = Exam::create([
        'name' => 'Final Exam',
        'description' => 'Test exam',
        'grade_id' => $grade->id,
        'passing_score' => 50,
        'type' => 'final',
    ]);
    $exam->questions()->attach([$this->regularQuestion->id, $this->openQuestion->id]);

    $this->service->submitExam($this->user, $this->student, $exam, [
        ['question_id' => $this->regularQuestion->id, 'answer' => 'b'],
        ['question_id' => $this->openQuestion->id, 'answer' => 'Paris'],
    ], 'en');

    $this->service->submitExam($this->user, $this->student, $exam, [
        ['question_id' => $this->regularQuestion->id, 'answer' => 'a'],
        ['question_id' => $this->openQuestion->id, 'answer' => 'Paris'],
    ], 'en');

    // Old attempts soft-deleted and replaced, not duplicated
    expect(StudentExam::where('student_id', $this->student->id)->where('exam_id', $exam->id)->count())->toBe(2);

    // Stars still only once (already awarded)
    $this->assertDatabaseCount('user_stars', 2);
});

// ─── buildResultFromAttempts ────────────────────────────────────

it('builds result from persisted quiz attempts', function () {
    $quiz = Quiz::create(['name' => 'Test Quiz', 'lesson_id' => $this->lesson->id, 'type' => 'topic_based']);

    StudentQuiz::create([
        'student_id' => $this->student->id,
        'quiz_id' => $quiz->id,
        'question_id' => $this->regularQuestion->id,
        'answer' => 'b',
        'correct_answer' => 'b',
        'is_correct' => true,
        'type' => 'regular',
    ]);
    StudentQuiz::create([
        'student_id' => $this->student->id,
        'quiz_id' => $quiz->id,
        'question_id' => $this->openQuestion->id,
        'answer' => null,
        'correct_answer' => 'Paris',
        'is_correct' => false,
        'type' => 'open',
    ]);

    $attempts = StudentQuiz::where('student_id', $this->student->id)
        ->where('quiz_id', $quiz->id)
        ->with('question')
        ->get();

    $result = $this->service->buildResultFromAttempts($attempts, 'en');

    expect($result['score'])->toEqual(50)
        ->and($result['total'])->toBe(2)
        ->and($result['correct'])->toBe(1)
        ->and($result['wrong'])->toBe(0)
        ->and($result['skipped'])->toBe(1)
        ->and($result['answers'])->toHaveCount(2);
});

it('builds empty result when no attempts', function () {
    $attempts = collect();

    $result = $this->service->buildResultFromAttempts($attempts, 'en');

    expect($result['score'])->toEqual(0)
        ->and($result['total'])->toBe(0)
        ->and($result['answers'])->toBe([]);
});

it('carries explanation_video_url into evaluation and persisted result rebuilds', function () {
    $this->regularQuestion->update(['explanation_video_url' => 'https://youtu.be/dQw4w9WgXcQ']);

    $quiz = Quiz::create(['name' => 'Video Quiz', 'lesson_id' => $this->lesson->id, 'type' => 'topic_based']);
    $quiz->questions()->attach([$this->regularQuestion->id, $this->openQuestion->id]);

    $result = $this->service->submitQuiz(
        $this->user,
        $this->student,
        $quiz,
        [
            ['question_id' => $this->regularQuestion->id, 'answer' => 'b'],
            ['question_id' => $this->openQuestion->id, 'answer' => 'Paris'],
        ],
        'en',
    );

    $byQuestion = collect($result['answers'])->keyBy('question_id');

    // Fresh submission carries the URL through the evaluation result.
    expect($byQuestion[$this->regularQuestion->id]['explanation_video_url'])->toBe('https://youtu.be/dQw4w9WgXcQ')
        ->and($byQuestion[$this->openQuestion->id]['explanation_video_url'])->toBeNull();

    // Rebuilding from persisted attempts also carries the URL.
    $attempts = StudentQuiz::where('student_id', $this->student->id)
        ->where('quiz_id', $quiz->id)
        ->with('question')
        ->get();

    $rebuilt = collect($this->service->buildResultFromAttempts($attempts, 'en')['answers'])->keyBy('question_id');
    expect($rebuilt[$this->regularQuestion->id]['explanation_video_url'])->toBe('https://youtu.be/dQw4w9WgXcQ')
        ->and($rebuilt[$this->openQuestion->id]['explanation_video_url'])->toBeNull();
});
