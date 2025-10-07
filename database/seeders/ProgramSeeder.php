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
        [
            'name' => 'Data Science',
            'code' => 'DS',
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
        [
            'name' => 'Electronics Engineering',
            'code' => 'ECE',
            'college' => 'College of Engineering and Architecture',
        ],
        [
            'name' => 'Computer Engineering',
            'code' => 'COE',
            'college' => 'College of Engineering and Architecture',
        ],
        [
            'name' => 'Railway Engineering',
            'code' => 'RE',
            'college' => 'College of Engineering and Architecture',
        ],

        // College of Business and Accountancy
        [
            'name' => 'Hospitality Management',
            'code' => 'HM',
            'college' => 'College of Business and Accountancy',
        ],
        [
            'name' => 'Tourism Management',
            'code' => 'TM',
            'college' => 'College of Business and Accountancy',
        ],

        // College of Technology
        [
            'name' => 'Industrial Technology',
            'code' => 'ITECH',
            'college' => 'College of Technology',
        ],
        [
            'name' => 'Teacher Education',
            'code' => 'TE',
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
        [
            'name' => 'Public Health',
            'code' => 'PH',
            'college' => 'College of Allied Health and Sciences',
        ],

        // College of Arts and Science
        [
            'name' => 'Communication',
            'code' => 'COMM',
            'college' => 'College of Arts and Science',
        ],
    ];
    public function run(): void
    {

        foreach (self::programs as $program) {
            Program::firstOrCreate(['code' => $program['code']], $program);
        }
    }
}
