<?php

namespace App\Modules\Lesson\Models;

use App\Modules\Student\Models\Student;
use App\Modules\Student\Models\StudentActivity;
use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentLesson extends Model
{
    use SerializesDates;

    protected $fillable = ['student_id', 'lesson_id', 'progress', 'completed_at', 'last_position', 'last_watched_at'];

    protected function casts(): array
    {
        return [
            'progress' => 'integer',
            'completed_at' => 'datetime',
            'last_position' => 'integer',
            'last_watched_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
