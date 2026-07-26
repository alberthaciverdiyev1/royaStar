<?php

namespace App\Modules\Parent\Models;

use App\Traits\SerializesDates;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Family extends Model
{
    use SoftDeletes, SerializesDates;

    protected $table = 'families';

    protected $fillable = [
        'user_id',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
