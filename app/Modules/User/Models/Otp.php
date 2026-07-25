<?php

namespace App\Modules\User\Models;

use App\Traits\SerializesDates;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use SerializesDates;
    protected $table = 'otp';
    protected $fillable = ['phone', 'otp', 'expires_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }
}
