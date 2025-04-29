<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            [
                'name' => 'Computer Science',
                'code' => 'CS',
                'college' => 'College of Science',
            ],
            [
                'name' => 'Software Engineering',
                'code' => 'SE',
                'college' => 'College of Engineering',
            ],
            [
                'name' => 'Business Administration',
                'code' => 'BA',
                'college' => 'College of Business',
            ],
            [
                'name' => 'Mathematics',
                'code' => 'MATH',
                'college' => 'College of Science',
            ],
            [
                'name' => 'Biology',
                'code' => 'BIO',
                'college' => 'College of Science',
            ],
        ];

        foreach ($programs as $program) {
            Program::firstOrCreate(['code' => $program['code']], $program);
        }
    }
}
