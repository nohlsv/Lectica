<?php

namespace Database\Seeders;

use App\Models\Program;
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
        // Get program IDs dynamically to avoid foreign key constraint errors
        $programs = Program::all()->keyBy('code');
        
        // Extended list of famous scientists, inventors, and notable figures
        // Using program codes to dynamically find IDs
        $extraTestUsers = [
            // College of Computer Studies
            ['first_name' => 'Ada', 'last_name' => 'Lovelace', 'email' => 'lovelace@bpsu.edu.ph', 'program_code' => 'CS'], // Computer Science
            ['first_name' => 'Katherine', 'last_name' => 'Johnson', 'email' => 'johnson@bpsu.edu.ph', 'program_code' => 'CS'], // Computer Science

            // College of Engineering and Architecture
            ['first_name' => 'Isambard', 'last_name' => 'Brunel', 'email' => 'brunel@bpsu.edu.ph', 'program_code' => 'CE'], // Civil Engineering
            ['first_name' => 'Emily Warren', 'last_name' => 'Roebling', 'email' => 'roebling@bpsu.edu.ph', 'program_code' => 'CE'], // Civil Engineering
            ['first_name' => 'Nikola', 'last_name' => 'Tesla', 'email' => 'tesla@bpsu.edu.ph', 'program_code' => 'EE'], // Electrical Engineering

            // College of Business and Accountancy
            ['first_name' => 'Conrad', 'last_name' => 'Hilton', 'email' => 'hilton@bpsu.edu.ph', 'program_code' => 'HM'], // Hospitality Management
            ['first_name' => 'Julia', 'last_name' => 'Child', 'email' => 'child@bpsu.edu.ph', 'program_code' => 'HM'], // Hospitality Management

            // College of Technology
            ['first_name' => 'John', 'last_name' => 'Dewey', 'email' => 'dewey@bpsu.edu.ph', 'program_code' => 'TE'], // Teacher Education
            ['first_name' => 'Maria', 'last_name' => 'Montessori', 'email' => 'montessori@bpsu.edu.ph', 'program_code' => 'TE'], // Teacher Education

            // College of Allied Health and Sciences
            ['first_name' => 'Florence', 'last_name' => 'Nightingale', 'email' => 'nightingale@bpsu.edu.ph', 'program_code' => 'NUR'], // Nursing
            ['first_name' => 'Clara', 'last_name' => 'Barton', 'email' => 'barton@bpsu.edu.ph', 'program_code' => 'NUR'], // Nursing

            // College of Arts and Science
            ['first_name' => 'Edward', 'last_name' => 'Murrow', 'email' => 'murrow@bpsu.edu.ph', 'program_code' => 'COMM'], // Communication
            ['first_name' => 'Oprah', 'last_name' => 'Winfrey', 'email' => 'winfrey@bpsu.edu.ph', 'program_code' => 'COMM'], // Communication

            // Additional users for better distribution
            ['first_name' => 'Marie', 'last_name' => 'Curie', 'email' => 'curie@bpsu.edu.ph', 'program_code' => 'CS'], // Computer Science
            ['first_name' => 'Albert', 'last_name' => 'Einstein', 'email' => 'aeinstein@bpsu.edu.ph', 'program_code' => 'EE'], // Electrical Engineering
        ];

        foreach ($extraTestUsers as $userData) {
            // Only create if user doesn't already exist
            if (!User::where('email', $userData['email'])->exists()) {
                // Get program ID dynamically using the program code
                $program = $programs->get($userData['program_code']);
                
                if ($program) {
                    // Remove program_code and add program_id
                    unset($userData['program_code']);
                    $userData['program_id'] = $program->id;
                    
                    User::create($userData + [
                        'password' => Hash::make('password'),
                        'user_role' => 'student',
                        'year_of_study' => fake()->randomElement(['1st Year', '2nd Year', '3rd Year', '4th Year']),
                        'verification_status' => fake()->randomElement(['approved', 'pending']),
                        'verified_at' => fake()->boolean(70) ? now() : null, // 70% chance of being verified
                        'created_at' => fake()->dateTimeBetween('-2 years', 'now'),
                        'updated_at' => now(),
                    ]);
                } else {
                    echo "Warning: Program with code '{$userData['program_code']}' not found. Skipping user {$userData['first_name']} {$userData['last_name']}.\n";
                }
            }
        }
    }
}
