<?php

namespace Database\Seeders;

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

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@bpsu.edu.ph',
        ]);
        Tag::factory(20)->create();
        File::factory(20)->create([
            'user_id' => 1,
        ]);
    }
}
