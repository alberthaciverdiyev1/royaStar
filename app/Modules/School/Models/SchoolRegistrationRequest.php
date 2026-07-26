<?php

namespace App\Modules\School\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolRegistrationRequest extends Model
{
    protected $fillable = ['email', 'name', 'surname', 'phone', 'password', 'city_id', 'school_name', 'school_no', 'status'];

    protected function casts(): array
    {
        return [
            'city_id' => 'integer',
        ];
    }
}
