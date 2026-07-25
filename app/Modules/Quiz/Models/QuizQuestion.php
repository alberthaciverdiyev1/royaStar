<?php

namespace App\Modules\Quiz\Models;

use App\Traits\SerializesDates;

use App\Modules\Question\Models\Question;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use SerializesDates;
    protected $fillable = ['quiz_id', 'question_id'];

    public function quiz() { return $this->belongsTo(Quiz::class); }
    public function question() { return $this->belongsTo(Question::class); }
}
