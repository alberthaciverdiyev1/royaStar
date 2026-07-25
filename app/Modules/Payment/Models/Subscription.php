<?php

namespace App\Modules\Payment\Models;

use App\Traits\SerializesDates;

use App\Modules\School\Models\School;
use App\Modules\Teacher\Models\Teacher;
use App\Modules\Parent\Models\Family;
use App\Modules\Student\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use SoftDeletes, SerializesDates;

    protected $fillable = [
        'subscription_plan_id', 'school_id', 'teacher_id', 'family_id',
        'student_id', 'start_date', 'expires_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expires_at' => 'date',
        ];
    }

    public function plan() { return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id'); }
    public function school() { return $this->belongsTo(School::class); }
    public function teacher() { return $this->belongsTo(Teacher::class); }
    public function family() { return $this->belongsTo(Family::class); }
    public function student() { return $this->belongsTo(Student::class); }
}
