<?php

namespace App\Modules\Star\Models;

use App\Modules\User\Models\User;
use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserStar extends Model
{
    use SoftDeletes, SerializesDates;

    protected $fillable = ['user_id', 'star_id', 'reference_type', 'reference_id', 'metadata'];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'reference_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function star(): BelongsTo
    {
        return $this->belongsTo(Star::class);
    }
}
