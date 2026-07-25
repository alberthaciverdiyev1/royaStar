<?php

namespace App\Modules\Star\Models;

use App\Traits\SerializesDates;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPoint extends Model
{
    use SoftDeletes, SerializesDates;

    protected $fillable = ['user_id', 'xp_id'];

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function xp():BelongsTo
    {
        return $this->belongsTo(Xp::class);
    }
}
