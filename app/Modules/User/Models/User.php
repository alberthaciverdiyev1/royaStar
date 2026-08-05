<?php

namespace App\Modules\User\Models;

use App\Traits\SerializesDates;

use App\Modules\Student\Models\Student;
use App\Modules\Star\Models\UserStar;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, SerializesDates;

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

    public function userStars()
    {
        return $this->hasMany(UserStar::class);
    }
}
