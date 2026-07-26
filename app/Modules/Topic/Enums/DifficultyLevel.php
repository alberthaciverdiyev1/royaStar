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
            self::Beginner => 'Başlanğıc',
            self::Elementary => 'Elementar',
            self::Intermediate => 'Orta',
            self::Advanced => 'Qabaqcıl',
            self::Expert => 'Ekspert',
        };
    }
}
