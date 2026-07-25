<?php

namespace Database\Factories\Modules\Topic\Models;

use App\Modules\Subject\Models\Subject;
use App\Modules\Topic\Enums\DifficultyLevel;
use App\Modules\Topic\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;

class TopicFactory extends Factory
{
    protected $model = Topic::class;

    public function definition(): array
    {
        return [
            'subject_id' => Subject::factory(),
            'name' => ['en' => fake()->word(), 'az' => fake()->word()],
            'difficulty_level' => fake()->randomElement(DifficultyLevel::cases())->value,
        ];
    }
}
