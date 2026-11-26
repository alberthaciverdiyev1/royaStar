<?php

namespace App\Modules\Notification\Models;

use App\Modules\User\Models\User;
use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notification extends Model
{
    use SerializesDates;

    protected $fillable = ['type', 'title', 'body', 'url', 'data'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notification_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }
}
