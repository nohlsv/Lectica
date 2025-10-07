<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public const programs = [
        // College of Computer Studies
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

        // College of Engineering and Architecture
        [
            'name' => 'Civil Engineering',
            'code' => 'CE',
            'college' => 'College of Engineering and Architecture',
        ],
        [
            'name' => 'Electrical Engineering',
            'code' => 'EE',
            'college' => 'College of Engineering and Architecture',
        ],
        [
            'name' => 'Mechanical Engineering',
            'code' => 'ME',
            'college' => 'College of Engineering and Architecture',
        ],
        [
            'name' => 'Industrial Engineering',
            'code' => 'IE',
            'college' => 'College of Engineering and Architecture',
        ],
        [
            'name' => 'Architecture',
            'code' => 'ARC',
            'college' => 'College of Engineering and Architecture',
        ],

        // College of Business and Accountancy
        [
            'name' => 'Business Administration',
            'code' => 'BA',
            'college' => 'College of Business and Accountancy',
        ],
        [
            'name' => 'Accountancy',
            'code' => 'ACC',
            'college' => 'College of Business and Accountancy',
        ],

        // College of Technology
        [
            'name' => 'Industrial Technology',
            'code' => 'ITECH',
            'college' => 'College of Technology',
        ],

        // College of Allied Health and Sciences
        [
            'name' => 'Nursing',
            'code' => 'NUR',
            'college' => 'College of Allied Health and Sciences',
        ],
        [
            'name' => 'Midwifery',
            'code' => 'MID',
            'college' => 'College of Allied Health and Sciences',
        ],

        // College of Arts and Science (new college)
    ];
    public function run(): void
    {

        foreach (self::programs as $program) {
            Program::firstOrCreate(['code' => $program['code']], $program);
        }
    }
}
