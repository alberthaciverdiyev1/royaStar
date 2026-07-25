<?php

namespace App\Modules\Topic\Enums;

enum DifficultyLevel: int
{
    case Beginner = 1;
    case Elementary = 2;
    case Intermediate = 3;
    case Advanced = 4;
    case Expert = 5;

    public function label(): string
    {
        return match ($this) {
            self::Beginner => __('difficulty.beginner'),
            self::Elementary => __('difficulty.elementary'),
            self::Intermediate => __('difficulty.intermediate'),
            self::Advanced => __('difficulty.advanced'),
            self::Expert => __('difficulty.expert'),
        };
    }
}
