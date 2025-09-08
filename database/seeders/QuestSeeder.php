<?php

namespace Database\Seeders;

use App\Models\Quest;
use Illuminate\Database\Seeder;

class QuestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quests = [
            // Daily Quests
            [
                'title' => 'Daily Battle',
                'description' => 'Complete 1 battle today',
                'type' => 'daily',
                'category' => 'battle',
                'requirements' => ['count' => 1],
                'experience_reward' => 25,
                'is_active' => true,
            ],
            [
                'title' => 'File Upload',
                'description' => 'Upload 1 new file today',
                'type' => 'daily',
                'category' => 'file_upload',
                'requirements' => ['count' => 1],
                'experience_reward' => 20,
                'is_active' => true,
            ],
            [
                'title' => 'Quiz Master',
                'description' => 'Answer 5 quiz questions correctly today',
                'type' => 'daily',
                'category' => 'quiz',
                'requirements' => ['count' => 5],
                'experience_reward' => 30,
                'is_active' => true,
            ],
            [
                'title' => 'Study Session',
                'description' => 'Study for 30 minutes today',
                'type' => 'daily',
                'category' => 'study',
                'requirements' => ['count' => 30, 'unit' => 'minutes'],
                'experience_reward' => 35,
                'is_active' => true,
            ],

            // Weekly Quests
            [
                'title' => 'Battle Champion',
                'description' => 'Win 5 battles this week',
                'type' => 'weekly',
                'category' => 'battle',
                'requirements' => ['count' => 5],
                'experience_reward' => 100,
                'is_active' => true,
            ],
            [
                'title' => 'Knowledge Gatherer',
                'description' => 'Upload 3 files this week',
                'type' => 'weekly',
                'category' => 'file_upload',
                'requirements' => ['count' => 3],
                'experience_reward' => 75,
                'is_active' => true,
            ],
            [
                'title' => 'Quiz Expert',
                'description' => 'Answer 25 quiz questions correctly this week',
                'type' => 'weekly',
                'category' => 'quiz',
                'requirements' => ['count' => 25],
                'experience_reward' => 120,
                'is_active' => true,
            ],

            // One-time Quests
            [
                'title' => 'First Victory',
                'description' => 'Win your first battle',
                'type' => 'one_time',
                'category' => 'battle',
                'requirements' => ['count' => 1],
                'experience_reward' => 50,
                'is_active' => true,
            ],
            [
                'title' => 'Getting Started',
                'description' => 'Upload your first file',
                'type' => 'one_time',
                'category' => 'file_upload',
                'requirements' => ['count' => 1],
                'experience_reward' => 30,
                'is_active' => true,
            ],
            [
                'title' => 'Scholar',
                'description' => 'Answer 100 quiz questions correctly',
                'type' => 'one_time',
                'category' => 'quiz',
                'requirements' => ['count' => 100],
                'experience_reward' => 200,
                'is_active' => true,
            ],
        ];

        foreach ($quests as $questData) {
            Quest::create($questData);
        }
    }
}
