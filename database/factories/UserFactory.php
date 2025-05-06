<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = "password";

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'program_id' => null,
            'year_of_study' => fake()->randomElement(['1st Year', '2nd Year', '3rd Year', '4th Year', 'Graduate']),
            'college' => fake()->randomElement(['College of Arts and Sciences', 'College of Engineering', 'College of Business', 'College of Education', 'College of Medicine']),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user has a program.
     */
    public function withProgram(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'program_id' => \App\Models\Program::factory(),
            ];
        });
    }
}
