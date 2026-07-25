<?php

namespace App\Modules\Student\Models;

use App\Traits\SerializesDates;

use App\Modules\User\Models\User;
use App\Modules\Grade\Models\Grade;
use App\Modules\City\Models\City;
use App\Modules\Lesson\Models\StudentLesson;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes, SerializesDates;

    protected $fillable = [
        'user_id', 'grade_id', 'city_id', 'school_name', 'birth_date',
        'created_by_id', 'is_active', 'level',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function grade() { return $this->belongsTo(Grade::class); }
    public function city() { return $this->belongsTo(City::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_id'); }
    public function studentLessons() { return $this->hasMany(StudentLesson::class); }
}
