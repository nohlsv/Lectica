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
            [
                'title' => 'Battle Winner',
                'description' => 'Win 1 battle today',
                'type' => 'daily',
                'category' => 'battle_win',
                'requirements' => ['count' => 1],
                'experience_reward' => 30,
                'is_active' => true,
            ],
            [
                'title' => 'Battle Starter',
                'description' => 'Start 1 battle today',
                'type' => 'daily',
                'category' => 'battle_start',
                'requirements' => ['count' => 1],
                'experience_reward' => 15,
                'is_active' => true,
            ],
            [
                'title' => 'Battle Questions',
                'description' => 'Answer 3 battle questions today',
                'type' => 'daily',
                'category' => 'battle_questions',
                'requirements' => ['count' => 3],
                'experience_reward' => 20,
                'is_active' => true,
            ],
            [
                'title' => 'Multiplayer Creator',
                'description' => 'Create 1 multiplayer game today',
                'type' => 'daily',
                'category' => 'multiplayer_create',
                'requirements' => ['count' => 1],
                'experience_reward' => 20,
                'is_active' => true,
            ],
            [
                'title' => 'Multiplayer Joiner',
                'description' => 'Join 1 multiplayer game today',
                'type' => 'daily',
                'category' => 'multiplayer_join',
                'requirements' => ['count' => 1],
                'experience_reward' => 20,
                'is_active' => true,
            ],
            [
                'title' => 'Multiplayer Winner',
                'description' => 'Win 1 multiplayer game today',
                'type' => 'daily',
                'category' => 'multiplayer_win',
                'requirements' => ['count' => 1],
                'experience_reward' => 35,
                'is_active' => true,
            ],
            [
                'title' => 'Multiplayer Questions',
                'description' => 'Answer 3 multiplayer questions today',
                'type' => 'daily',
                'category' => 'multiplayer_questions',
                'requirements' => ['count' => 3],
                'experience_reward' => 20,
                'is_active' => true,
            ],
            [
                'title' => 'File Creator',
                'description' => 'Create 1 file today',
                'type' => 'daily',
                'category' => 'file_create',
                'requirements' => ['count' => 1],
                'experience_reward' => 20,
                'is_active' => true,
            ],
            [
                'title' => 'Collection Creator',
                'description' => 'Create 1 collection today',
                'type' => 'daily',
                'category' => 'collection_create',
                'requirements' => ['count' => 1],
                'experience_reward' => 20,
                'is_active' => true,
            ],
            [
                'title' => 'Quiz Generator',
                'description' => 'Generate 1 quiz today',
                'type' => 'daily',
                'category' => 'quiz_generate',
                'requirements' => ['count' => 1],
                'experience_reward' => 20,
                'is_active' => true,
            ],
            [
                'title' => 'Daily Login',
                'description' => 'Log in today',
                'type' => 'daily',
                'category' => 'daily_login',
                'requirements' => ['count' => 1],
                'experience_reward' => 10,
                'is_active' => true,
            ],
            [
                'title' => 'Activity Streak',
                'description' => 'Maintain a 3-day activity streak',
                'type' => 'daily',
                'category' => 'activity_streak',
                'requirements' => ['count' => 3],
                'experience_reward' => 40,
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
            Quest::updateOrCreate(
                [
                    'category' => $questData['category'],
                    'type' => $questData['type'],
                    'title' => $questData['title'],
                ],
                $questData
            );
        }
    }
}
