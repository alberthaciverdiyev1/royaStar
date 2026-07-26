<?php

namespace Database\Factories\Modules\Subject\Models;

use App\Modules\Subject\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        return [
            'name' => fake()->word(),
        ];
    }
}
