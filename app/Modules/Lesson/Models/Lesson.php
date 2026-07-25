<?php

namespace App\Modules\Lesson\Models;

use App\Modules\Quiz\Models\Quiz;
use App\Modules\Topic\Models\Topic;
use App\Modules\Lesson\Models\LessonView;
use App\Modules\Lesson\Models\StudentLesson;
use App\Traits\HasTranslations;
use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use HasFactory, SoftDeletes, HasTranslations, SerializesDates;
    protected $fillable = ['name', 'topic_id', 'description'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
        ];
    }

    public function topic() { return $this->belongsTo(Topic::class); }
    public function videos() { return $this->hasMany(Video::class); }
    public function quiz() { return $this->hasOne(Quiz::class); }
    public function studentLessons() { return $this->hasMany(StudentLesson::class); }
    public function view() { return $this->hasOne(LessonView::class); }
}
