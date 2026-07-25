<?php

namespace App\Modules\Question\Models;

use App\Modules\Topic\Enums\DifficultyLevel;
use App\Modules\Topic\Models\Topic;
use App\Traits\HasTranslations;
use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes, HasTranslations, SerializesDates;

    protected $fillable = [
        'question', 'variant_a', 'variant_b', 'variant_c', 'variant_d', 'variant_e',
        'right_answer', 'open_answer', 'type', 'explanation',
        'difficulty_level', 'topic_id', 'answer_type',
    ];

    protected function casts(): array
    {
        return [
            'question' => 'array',
            'variant_a' => 'array',
            'variant_b' => 'array',
            'variant_c' => 'array',
            'variant_d' => 'array',
            'variant_e' => 'array',
            'open_answer' => 'array',
            'explanation' => 'array',
            'difficulty_level' => DifficultyLevel::class,
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }
}
