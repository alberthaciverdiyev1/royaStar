<?php

namespace App\Modules\Quiz\Models;

use App\Traits\SerializesDates;

use App\Modules\Question\Models\Question;
use App\Modules\Student\Models\Student;
use App\Modules\Student\Models\StudentActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentQuiz extends Model
{
    use SoftDeletes, SerializesDates;
    protected $fillable = [
        'student_id', 'quiz_id', 'question_id', 'answer',
        'correct_answer', 'is_correct', 'type',
    ];

    protected static function booted(): void
    {
        static::created(function (self $attempt) {
            $quiz = $attempt->quiz;
            if (!$quiz) return;

            $totalQuestions = $quiz->questions()->count();
            if ($totalQuestions === 0) return;

            $answered = static::where('student_id', $attempt->student_id)
                ->where('quiz_id', $attempt->quiz_id)
                ->count();

            if ($answered < $totalQuestions) return;

            $attempt->loadMissing('quiz.lesson.topic.subject');
            $quiz = $attempt->quiz;
            $lesson = $quiz?->lesson;
            $topic = $lesson?->topic;
            $subject = $topic?->subject;

            $correctAnswers = static::where('student_id', $attempt->student_id)
                ->where('quiz_id', $attempt->quiz_id)
                ->where('is_correct', true)
                ->count();

            StudentActivity::updateOrCreate(
                ['student_id' => $attempt->student_id, 'reference_type' => 'quiz', 'reference_id' => $attempt->quiz_id],
                [
                    'type' => 'quiz_completed',
                    'metadata' => [
                        'quiz_name' => $quiz->localeValue('name'),
                        'lesson_name' => $lesson?->localeValue('name'),
                        'topic_name' => $topic?->localeValue('name'),
                        'subject_name' => $subject?->localeValue('name'),
                        'correct_answers' => $correctAnswers,
                        'total_questions' => $totalQuestions,
                        'score' => round(($correctAnswers / $totalQuestions) * 100),
                    ],
                ]
            );
        });
    }

    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }

    public function student() { return $this->belongsTo(Student::class); }
    public function quiz() { return $this->belongsTo(Quiz::class); }
    public function question() { return $this->belongsTo(Question::class); }
}
