<?php

namespace App\Modules\Topic\Models;

use App\Traits\SerializesDates;

use App\Modules\Topic\Enums\DifficultyLevel;
use App\Modules\Grade\Models\Grade;
use App\Modules\Lesson\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topic extends Model
{
    use HasFactory, SoftDeletes, SerializesDates;

    protected $fillable = ['name', 'difficulty_level'];

    protected function casts(): array
    {
        return [
            'difficulty_level' => DifficultyLevel::class,
        ];
    }

    public function lessons() { return $this->hasMany(Lesson::class); }
    public function grades() { return $this->belongsToMany(Grade::class, 'grade_topics')->withTimestamps(); }
}
