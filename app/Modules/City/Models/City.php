<?php

namespace App\Modules\City\Models;

use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use SoftDeletes, SerializesDates;

    protected $fillable = ['name'];
}
