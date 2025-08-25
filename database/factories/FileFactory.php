<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word . '.' . $this->faker->fileExtension(),
            'path' => $this->faker->filePath(),
            'content' => $this->faker->text(),
            'user_id' => \App\Models\User::factory(),
            'verified' => $this->faker->boolean(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($file) {
            $file->quizzes()->saveMany(
                \App\Models\Quiz::factory()->count(3)->make()
            );
            $file->flashcards()->saveMany(
                \App\Models\Flashcard::factory()->count(3)->make()
            );
        });
    }
}
