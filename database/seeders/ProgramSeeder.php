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
                'college' => 'College of Computer Studies',
            ],
            [
                'name' => 'Information Technology',
                'code' => 'IT',
                'college' => 'College of Computer Studies',
            ],
            [
                'name' => 'Entertainment and Multimedia Computing',
                'code' => 'EMC',
                'college' => 'College of Computer Studies',
            ],
            [
                'name' => 'Software Engineering',
                'code' => 'SE',
                'college' => 'College of Engineering',
            ],
            [
                'name' => 'Civil Engineering',
                'code' => 'CE',
                'college' => 'College of Engineering',
            ],
            [
                'name' => 'Electrical Engineering',
                'code' => 'EE',
                'college' => 'College of Engineering',
            ],
            [
                'name' => 'Mechanical Engineering',
                'code' => 'ME',
                'college' => 'College of Engineering',
            ],
            [
                'name' => 'Industrial Engineering',
                'code' => 'IE',
                'college' => 'College of Engineering',
            ],
            [
                'name' => 'Architecture',
                'code' => 'ARC',
                'college' => 'College of Engineering',
            ],
            [
                'name' => 'Business Administration',
                'code' => 'BA',
                'college' => 'College of Business',
            ],
            [
                'name' => 'Industrial Technology',
                'code' => 'ITECH',
                'college' => 'College of Technology',
            ],
            [
                'name' => 'Nursing',
                'code' => 'NUR',
                'college' => 'College of Applied Health and Sciences',
            ],
            [
                'name' => 'Midwifery',
                'code' => 'MID',
                'college' => 'College of Applied Health and Sciences',
            ],
        ];

        foreach ($programs as $program) {
            Program::firstOrCreate(['code' => $program['code']], $program);
        }
    }
}
