<?php

namespace App\Modules\User\Models;

use App\Traits\SerializesDates;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    use SerializesDates;
    protected $fillable = ['user_id', 'is_email', 'is_subscription', 'is_task'];

    protected function casts(): array
    {
        return [
            'is_email' => 'boolean',
            'is_subscription' => 'boolean',
            'is_task' => 'boolean',
        ];
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
