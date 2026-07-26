<?php

namespace App\Modules\Quiz\Models;

use App\Traits\SerializesDates;

use App\Modules\Question\Models\Question;
use App\Modules\Student\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentQuiz extends Model
{
    use SoftDeletes, SerializesDates;
    protected $fillable = [
        'student_id', 'quiz_id', 'question_id', 'answer',
        'correct_answer', 'is_correct', 'type',
    ];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }

    public function student() { return $this->belongsTo(Student::class); }
    public function quiz() { return $this->belongsTo(Quiz::class); }
    public function question() { return $this->belongsTo(Question::class); }
}
