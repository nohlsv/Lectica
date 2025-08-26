<?php

namespace Database\Seeders;

use App\Models\Quest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing quests
        Quest::truncate();

        // Daily quests
        $dailyQuests = [
            [
                'title' => 'Daily Warrior',
                'description' => 'Complete 1 battle today',
                'type' => 'daily',
                'category' => 'battle',
                'requirements' => ['count' => 1],
                'experience_reward' => 25,
                'is_active' => true,
            ],
            [
                'title' => 'Knowledge Seeker',
                'description' => 'Upload 1 new file today',
                'type' => 'daily',
                'category' => 'file_upload',
                'requirements' => ['count' => 1],
                'experience_reward' => 30,
                'is_active' => true,
            ],
            [
                'title' => 'Quiz Master',
                'description' => 'Take 3 quizzes today',
                'type' => 'daily',
                'category' => 'quiz',
                'requirements' => ['count' => 3],
                'experience_reward' => 20,
                'is_active' => true,
            ],
            [
                'title' => 'Study Session',
                'description' => 'Study with flashcards for 10 minutes',
                'type' => 'daily',
                'category' => 'study',
                'requirements' => ['count' => 1],
                'experience_reward' => 15,
                'is_active' => true,
            ],
        ];

        // Weekly quests
        $weeklyQuests = [
            [
                'title' => 'Battle Champion',
                'description' => 'Win 5 battles this week',
                'type' => 'weekly',
                'category' => 'battle',
                'requirements' => ['count' => 5, 'condition' => 'win'],
                'experience_reward' => 100,
                'is_active' => true,
            ],
            [
                'title' => 'Content Creator',
                'description' => 'Upload 3 files this week',
                'type' => 'weekly',
                'category' => 'file_upload',
                'requirements' => ['count' => 3],
                'experience_reward' => 75,
                'is_active' => true,
            ],
        ];

        // One-time quests
        $oneTimeQuests = [
            [
                'title' => 'First Steps',
                'description' => 'Complete your first battle',
                'type' => 'one_time',
                'category' => 'battle',
                'requirements' => ['count' => 1],
                'experience_reward' => 50,
                'is_active' => true,
            ],
            [
                'title' => 'Knowledge Contributor',
                'description' => 'Upload your first file',
                'type' => 'one_time',
                'category' => 'file_upload',
                'requirements' => ['count' => 1],
                'experience_reward' => 40,
                'is_active' => true,
            ],
        ];

        // Insert all quests
        foreach ([$dailyQuests, $weeklyQuests, $oneTimeQuests] as $questGroup) {
            foreach ($questGroup as $quest) {
                Quest::create($quest);
            }
        }

        $this->command->info('Quests seeded successfully!');
    }
}
