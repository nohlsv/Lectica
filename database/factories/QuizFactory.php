<?php

namespace Database\Factories;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    public function definition(): array
    {
        return [
            'question' => $this->faker->sentence(),
            'options' => $this->faker->randomElements([
                'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'
            ], 4),
            'answers' => [$this->faker->randomElement(['A', 'B', 'C', 'D'])],
            'type' => 'multiple_choice',
        ];
    }
}
