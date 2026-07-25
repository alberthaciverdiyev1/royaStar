<?php

namespace Database\Factories\Modules\Lesson\Models;

use App\Modules\Lesson\Models\Lesson;
use App\Modules\Topic\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;

class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition(): array
    {
        return [
            'topic_id' => Topic::factory(),
            'name' => ['en' => fake()->sentence(), 'az' => fake()->sentence()],
            'description' => ['en' => fake()->paragraph(), 'az' => fake()->paragraph()],
        ];
    }
}
