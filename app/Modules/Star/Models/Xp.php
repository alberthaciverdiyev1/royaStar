<?php

namespace App\Modules\Star\Models;

use App\Traits\SerializesDates;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Xp extends Model
{
    use SoftDeletes, SerializesDates;

    protected $fillable = ['point', 'type'];

    public function userPoints():HasMany
    {
        return $this->hasMany(UserPoint::class);
    }
}
