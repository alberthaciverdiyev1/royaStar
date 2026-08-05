<?php

namespace App\Modules\Star\Models;

use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Star extends Model
{
    use SoftDeletes, SerializesDates;

    protected $fillable = [
        'point', 'type', 'name', 'description', 'icon', 'category', 'group',
        'is_active', 'max_per_day', 'sort_order',
        'point_min', 'point_max', 'point_default',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function userStars(): HasMany
    {
        return $this->hasMany(UserStar::class);
    }
}
