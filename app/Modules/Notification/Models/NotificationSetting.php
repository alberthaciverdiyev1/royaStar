<?php

namespace App\Modules\Notification\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = [
        'notify_lesson_video',
        'notify_quiz',
        'notify_exam',
    ];

    protected function casts(): array
    {
        return [
            'notify_lesson_video' => 'boolean',
            'notify_quiz' => 'boolean',
            'notify_exam' => 'boolean',
        ];
    }

    /**
     * The settings live in a single row; it is created on first use.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'notify_lesson_video' => true,
            'notify_quiz' => true,
            'notify_exam' => true,
        ]);
    }
}
