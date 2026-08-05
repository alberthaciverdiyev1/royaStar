<?php

namespace App\Modules\Quiz\Models;

use App\Traits\SerializesDates;

use App\Modules\Lesson\Models\Lesson;
use App\Modules\Question\Models\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use SoftDeletes, SerializesDates;
    protected $fillable = ['name', 'lesson_id', 'type'];

    public function lesson() { return $this->belongsTo(Lesson::class); }
    public function questions() { return $this->belongsToMany(Question::class, 'quiz_questions'); }

    /**
     * Whether a student of the given grade may view/take this quiz.
     * Quizzes inherit grade scope from their lesson's topic (grade_topics pivot).
     * A topic without grade restrictions, or a student without a grade, is always allowed.
     */
    public function isAvailableForGrade(?int $gradeId): bool
    {
        if (!$gradeId) {
            return true;
        }

        $topicGrades = $this->lesson?->topic?->grades()->pluck('grades.id');

        if ($topicGrades === null || $topicGrades->isEmpty()) {
            return true;
        }

        return $topicGrades->contains($gradeId);
    }
}
