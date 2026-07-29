<?php

namespace App\Modules\Exam\Models;

use App\Modules\Question\Models\Question;
use App\Modules\Student\Models\Student;
use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentExam extends Model
{
    use SoftDeletes, SerializesDates;

    protected $fillable = [
        'student_id', 'exam_id', 'question_id', 'answer',
        'correct_answer', 'is_correct', 'type',
    ];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }

    public function student() { return $this->belongsTo(Student::class); }
    public function exam() { return $this->belongsTo(Exam::class); }
    public function question() { return $this->belongsTo(Question::class); }
}
