<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ExtraTestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Extended list of famous scientists, inventors, and notable figures
        // Ensuring every program has at least 2 users for comprehensive testing
        $extraTestUsers = [
            // College of Computer Studies (Programs 1-4)
            ['first_name' => 'Ada', 'last_name' => 'Lovelace', 'email' => 'lovelace@bpsu.edu.ph', 'program_id' => 1], // Computer Science
            ['first_name' => 'Katherine', 'last_name' => 'Johnson', 'email' => 'johnson@bpsu.edu.ph', 'program_id' => 1], // Computer Science

            // College of Engineering and Architecture (Programs 5-12)
            ['first_name' => 'Isambard', 'last_name' => 'Brunel', 'email' => 'brunel@bpsu.edu.ph', 'program_id' => 5], // Civil Engineering
            ['first_name' => 'Emily Warren', 'last_name' => 'Roebling', 'email' => 'roebling@bpsu.edu.ph', 'program_id' => 5], // Civil Engineering
            ['first_name' => 'Nikola', 'last_name' => 'Tesla', 'email' => 'tesla@bpsu.edu.ph', 'program_id' => 6], // Electrical Engineering

            // College of Business and Accountancy (Programs 13-14)
            ['first_name' => 'Conrad', 'last_name' => 'Hilton', 'email' => 'hilton@bpsu.edu.ph', 'program_id' => 13], // Hospitality Management
            ['first_name' => 'Julia', 'last_name' => 'Child', 'email' => 'child@bpsu.edu.ph', 'program_id' => 13], // Hospitality Management

            // College of Technology (Programs 15-16)
            ['first_name' => 'John', 'last_name' => 'Dewey', 'email' => 'dewey@bpsu.edu.ph', 'program_id' => 16], // Teacher Education
            ['first_name' => 'Maria', 'last_name' => 'Montessori', 'email' => 'montessori@bpsu.edu.ph', 'program_id' => 16], // Teacher Education

            // College of Allied Health and Sciences (Programs 17-19)
            ['first_name' => 'Florence', 'last_name' => 'Nightingale', 'email' => 'nightingale@bpsu.edu.ph', 'program_id' => 17], // Nursing
            ['first_name' => 'Clara', 'last_name' => 'Barton', 'email' => 'barton@bpsu.edu.ph', 'program_id' => 17], // Nursing

            // College of Arts and Science (Program 20)
            ['first_name' => 'Edward', 'last_name' => 'Murrow', 'email' => 'murrow@bpsu.edu.ph', 'program_id' => 20], // Communication
            ['first_name' => 'Oprah', 'last_name' => 'Winfrey', 'email' => 'winfrey@bpsu.edu.ph', 'program_id' => 20], // Communication

            ['first_name' => 'Marie', 'last_name' => 'Curie', 'email' => 'curie@bpsu.edu.ph', 'program_id' => 1], // Computer Science
            ['first_name' => 'Albert', 'last_name' => 'Einstein', 'email' => 'einstein@bpsu.edu.ph', 'program_id' => 6], // Electrical Engineering
        ];

        foreach ($extraTestUsers as $userData) {
            // Only create if user doesn't already exist
            if (!User::where('email', $userData['email'])->exists()) {
                User::create($userData + [
                    'password' => Hash::make('password'),
                    'user_role' => 'student',
                    'year_of_study' => fake()->randomElement(['1st Year', '2nd Year', '3rd Year', '4th Year']),
                    'verification_status' => fake()->randomElement(['approved', 'pending']),
                    'verified_at' => fake()->boolean(70) ? now() : null, // 70% chance of being verified
                    'created_at' => fake()->dateTimeBetween('-2 years', 'now'),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
