<?php

namespace App\Modules\Lesson\Models;

use App\Modules\Question\Models\Question;
use App\Modules\Quiz\Models\Quiz;
use App\Modules\Topic\Models\Topic;
use App\Modules\Lesson\Models\LessonView;
use App\Modules\Lesson\Models\StudentLesson;
use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use HasFactory, SoftDeletes, SerializesDates;
    protected $fillable = ['name', 'topic_id', 'description'];

    public function topic() { return $this->belongsTo(Topic::class); }
    public function videos() { return $this->hasMany(Video::class); }
    public function quizzes() { return $this->hasMany(Quiz::class); }
    public function questions() { return $this->hasMany(Question::class); }
    public function studentLessons() { return $this->hasMany(StudentLesson::class); }
    public function view() { return $this->hasOne(LessonView::class); }
    public function reviews() { return $this->hasMany(LessonReview::class); }
}
