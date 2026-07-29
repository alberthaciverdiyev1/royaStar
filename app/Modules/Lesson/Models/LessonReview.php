<?php

namespace App\Modules\Lesson\Models;

use App\Modules\User\Models\User;
use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Model;

class LessonReview extends Model
{
    use SerializesDates;

    protected $fillable = ['user_id', 'lesson_id', 'rating', 'review'];

    public function user() { return $this->belongsTo(User::class); }
    public function lesson() { return $this->belongsTo(Lesson::class); }
}
