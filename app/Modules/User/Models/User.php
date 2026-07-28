<?php

namespace App\Modules\User\Models;

use App\Traits\SerializesDates;

use App\Modules\Student\Models\Student;
use App\Modules\Teacher\Models\Teacher;
use App\Modules\Parent\Models\Family;
use App\Modules\School\Models\School;
use App\Modules\Star\Models\UserStar;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SerializesDates;

    protected string $guard_name = 'api';

    protected $fillable = [
        'name', 'surname', 'phone', 'avatar', 'type', 'email', 'password', 'is_approved',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_approved' => 'boolean',
        ];
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function family()
    {
        return $this->hasOne(Family::class);
    }

    public function school()
    {
        return $this->hasOne(School::class);
    }

    public function notificationSetting()
    {
        return $this->hasOne(NotificationSetting::class);
    }

    public function userStars()
    {
        return $this->hasMany(UserStar::class);
    }
}
