<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\Tag;
use App\Models\User;
use App\Models\File;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProgramSeeder::class,
        ]);

        // User::factory(10)->create();
        if (User::count() === 0) {
            User::factory()->create([
                'first_name' => 'Admin',
                'last_name' => '',
                'email' => 'admin@bpsu.edu.ph',
                'password' => Hash::make('password'),
                'user_role' => 'admin',
            ]);

            User::factory()->create([
                'first_name' => 'Albert',
                'last_name' => 'Einstein',
                'email' => 'einstein@bpsu.edu.ph',
                'program_id' => 1, // Computer Science
                'password' => Hash::make('password'),
                'user_role' => 'faculty',
            ]);

            $studentInformation = [
                ['first_name' => 'Linus', 'last_name' => 'Torvalds', 'email' => 'torvalds@bpsu.edu.ph', 'program_id' => 1], // Computer Science
                ['first_name' => 'Alan', 'last_name' => 'Turing', 'email' => 'turing@bpsu.edu.ph', 'program_id' => 1], // Computer Science
                ['first_name' => 'Marie', 'last_name' => 'Curie', 'email' => 'curie@bpsu.edu.ph', 'program_id' => 12], // Nursing
                ['first_name' => 'Florence', 'last_name' => 'Nightingale', 'email' => 'nightingale@bpsu.edu.ph', 'program_id' => 12], // Nursing
                ['first_name' => 'Ada', 'last_name' => 'Lovelace', 'email' => 'lovelace@bpsu.edu.ph', 'program_id' => 4], // Software Engineering
                ['first_name' => 'Nikola', 'last_name' => 'Tesla', 'email' => 'tesla@bpsu.edu.ph', 'program_id' => 6], // Electrical Engineering
                ['first_name' => 'Grace', 'last_name' => 'Hopper', 'email' => 'hopper@bpsu.edu.ph', 'program_id' => 3], // Entertainment and Multimedia Computing
                ['first_name' => 'Rosalind', 'last_name' => 'Franklin', 'email' => 'franklin@bpsu.edu.ph', 'program_id' => 13], // Midwifery
                ['first_name' => 'Thomas', 'last_name' => 'Edison', 'email' => 'edison@bpsu.edu.ph', 'program_id' => 7], // Mechanical Engineering
                ['first_name' => 'Leonardo', 'last_name' => 'da Vinci', 'email' => 'davinci@bpsu.edu.ph', 'program_id' => 9], // Architecture
            ];

            foreach ($studentInformation as $student) {
                User::factory()->create($student + [
                    'password' => Hash::make('password'),
                    'user_role' => 'student',
                    'year_of_study' => '1st Year',
                ]);
            }
        }

        // Define some predefined unique tag names
        $predefinedTags = [
            'programming',
            'math',
            'science',
            'history',
            'english',
            'physics',
            'chemistry',
            'biology',
            'literature',
            'psychology',
            'sociology',
            'economics',
            'statistics',
            'calculus',
            'algebra',
            'geometry',
            'research',
            'engineering',
            'computer_science',
            'education'
        ];

        foreach ($predefinedTags as $tagName) {
            Tag::firstOrCreate(['name' => $tagName]);
        }
        foreach (ProgramSeeder::programs as $program) {
            Tag::firstOrCreate(['name' => str_replace(' ', '_', strtolower($program['name']))]);
        }

        File::factory(20)->create([
            'user_id' => 1,
        ]);
    }
}
