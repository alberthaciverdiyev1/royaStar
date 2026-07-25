<?php

namespace App\Modules\City\Models;

use App\Traits\SerializesDates;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use SoftDeletes, HasTranslations, SerializesDates;

    protected $fillable = ['name'];

    protected function casts(): array
    {
        return ['name' => 'array'];
    }
}
