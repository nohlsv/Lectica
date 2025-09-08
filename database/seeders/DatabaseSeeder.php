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
            QuestSeeder::class,
            MonsterSeeder::class,
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
                // ['first_name' => 'Marie', 'last_name' => 'Curie', 'email' => 'curie@bpsu.edu.ph', 'program_id' => 12], // Nursing
                // ['first_name' => 'Florence', 'last_name' => 'Nightingale', 'email' => 'nightingale@bpsu.edu.ph', 'program_id' => 12], // Nursing
                // ['first_name' => 'Nikola', 'last_name' => 'Tesla', 'email' => 'tesla@bpsu.edu.ph', 'program_id' => 6], // Electrical Engineering
                // ['first_name' => 'Grace', 'last_name' => 'Hopper', 'email' => 'hopper@bpsu.edu.ph', 'program_id' => 3], // Entertainment and Multimedia Computing
                // ['first_name' => 'Rosalind', 'last_name' => 'Franklin', 'email' => 'franklin@bpsu.edu.ph', 'program_id' => 13], // Midwifery
                // ['first_name' => 'Thomas', 'last_name' => 'Edison', 'email' => 'edison@bpsu.edu.ph', 'program_id' => 7], // Mechanical Engineering
                // ['first_name' => 'Leonardo', 'last_name' => 'da Vinci', 'email' => 'davinci@bpsu.edu.ph', 'program_id' => 9], // Architecture
            ];

            foreach ($studentInformation as $student) {
                User::factory()->create($student + [
                    'password' => Hash::make('password'),
                    'user_role' => 'student',
                    'year_of_study' => '1st Year',
                ]);
            }
        }

        // Define some predefined unique tag names with aliases
        $predefinedTags = [
            ['name' => 'programming', 'aliases' => ['coding', 'development', 'software']],
            ['name' => 'math', 'aliases' => ['mathematics', 'maths', 'numerical']],
            ['name' => 'science', 'aliases' => ['scientific', 'research', 'experimental']],
            ['name' => 'history', 'aliases' => ['historical', 'past', 'timeline']],
            ['name' => 'english', 'aliases' => ['language', 'grammar', 'literature']],
            ['name' => 'physics', 'aliases' => ['physical_science', 'mechanics', 'quantum']],
            ['name' => 'chemistry', 'aliases' => ['chemical', 'organic', 'inorganic']],
            ['name' => 'biology', 'aliases' => ['life_science', 'anatomy', 'genetics']],
            ['name' => 'literature', 'aliases' => ['books', 'novels', 'poetry']],
            ['name' => 'psychology', 'aliases' => ['mental_health', 'behavior', 'cognitive']],
            ['name' => 'sociology', 'aliases' => ['social_science', 'society', 'culture']],
            ['name' => 'economics', 'aliases' => ['finance', 'money', 'market']],
            ['name' => 'statistics', 'aliases' => ['stats', 'data_analysis', 'probability']],
            ['name' => 'calculus', 'aliases' => ['derivatives', 'integrals', 'limits']],
            ['name' => 'algebra', 'aliases' => ['equations', 'variables', 'polynomials']],
            ['name' => 'geometry', 'aliases' => ['shapes', 'angles', 'triangles']],
            ['name' => 'research', 'aliases' => ['study', 'investigation', 'analysis']],
            ['name' => 'engineering', 'aliases' => ['technical', 'design', 'construction']],
            ['name' => 'computer_science', 'aliases' => ['cs', 'computing', 'algorithms']],
            ['name' => 'education', 'aliases' => ['learning', 'teaching', 'pedagogy']]
        ];

        foreach ($predefinedTags as $tagData) {
            Tag::firstOrCreate(
                ['name' => $tagData['name']],
                ['aliases' => $tagData['aliases']]
            );
        }
        foreach (ProgramSeeder::programs as $program) {
            Tag::firstOrCreate(['name' => str_replace(' ', '_', strtolower($program['name']))]);
        }

        File::factory(20)->create([
            'user_id' => 1,
        ]);
    }
}
