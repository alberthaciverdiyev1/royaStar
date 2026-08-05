<?php

namespace App\Modules\Exam\Models;

use App\Modules\Grade\Models\Grade;
use App\Modules\Question\Models\Question;
use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use SoftDeletes, SerializesDates;

    protected $fillable = [
        'name', 'description', 'grade_id', 'duration_minutes',
        'passing_score', 'total_questions', 'type',
    ];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_question')
            ->withPivot('order')
            ->orderBy('exam_question.order')
            ->withTimestamps();
    }

    /**
     * Whether a student of the given grade may view/take this exam.
     * An exam with no grade or a student with no grade is always allowed.
     */
    public function isAvailableForGrade(?int $gradeId): bool
    {
        if (!$this->grade_id || !$gradeId) {
            return true;
        }

        return $this->grade_id === $gradeId;
    }
}
