<?php

namespace App\Modules\Teacher\Models;

use App\Traits\SerializesDates;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use SoftDeletes, SerializesDates;

    protected $fillable = [
        'user_id', 'city_id',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
