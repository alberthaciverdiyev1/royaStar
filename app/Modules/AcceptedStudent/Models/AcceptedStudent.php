<?php

namespace App\Modules\AcceptedStudent\Models;

use App\Modules\User\Models\User;
use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcceptedStudent extends Model
{
    use SoftDeletes, SerializesDates;

    protected $fillable = [
        'user_id', 'name', 'surname', 'image', 'exam_points', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'exam_points' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope the currently-published showcase records (admin can hide entries).
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Highest custom exam points first — used by the public website.
     */
    public function scopeTop(Builder $query, int $limit): Builder
    {
        return $query->published()->orderByDesc('exam_points')->orderByDesc('id')->limit($limit);
    }
}
