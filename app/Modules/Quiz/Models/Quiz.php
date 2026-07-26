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
}
