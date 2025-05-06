<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\Tag;
use App\Models\User;
use App\Models\File;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProgramSeeder::class,
            // Other seeders...
        ]);

        // User::factory(10)->create();
        if (User::count() === 0) {
            User::factory()->create([
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@bpsu.edu.ph',
            ]);
        }

        // Define some predefined unique tag names
        $predefinedTags = [
            'programming', 'math', 'science', 'history', 'english',
            'physics', 'chemistry', 'biology', 'literature', 'psychology',
            'sociology', 'economics', 'statistics', 'calculus', 'algebra',
            'geometry', 'research', 'engineering', 'computer_science', 'education'
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
