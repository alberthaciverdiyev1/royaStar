<?php

namespace App\Modules\Banner\Models;

use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use SoftDeletes, SerializesDates;

    protected $fillable = ['image', 'title', 'subtitle'];
}
