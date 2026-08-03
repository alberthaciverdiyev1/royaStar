<?php

namespace App\Modules\Grade\Models;

use App\Traits\SerializesDates;
use App\Modules\Exam\Models\Exam;
use App\Modules\Topic\Models\Topic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model
{
    use SoftDeletes, SerializesDates;

    protected $fillable = ['name'];

    public function topics() { return $this->belongsToMany(Topic::class, 'grade_topics')->withTimestamps(); }
    public function exams() { return $this->hasMany(Exam::class); }
}
