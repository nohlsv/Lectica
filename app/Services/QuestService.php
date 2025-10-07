<?php

namespace App\Services;

use App\Models\User;
use App\Models\Quest;
use Carbon\Carbon;

class QuestService
{
    /**
     * Supported quest categories:
     * - practice_quiz: Complete quizzes
     * - practice_flashcard: Review flashcards
     * - battle_start: Start a battle
     * - battle_win: Win a battle
     * - battle_questions: Answer questions in battle
     * - multiplayer_create: Create a multiplayer game
     * - multiplayer_join: Join a multiplayer game
     * - multiplayer_win: Win a multiplayer game
     * - multiplayer_questions: Answer questions in multiplayer
     * - file_create: Create a file
     * - collection_create: Create a collection
     * - quiz_generate: Generate quizzes for a file
     * - daily_login: Log in daily
     * - activity_streak: Complete activities for X consecutive days
     */

    /**
     * Assign daily quests to a user.
     */
    public function assignDailyQuests(User $user): void
    {
        $today = today();

        // Check if user already has today's quests
        if ($user->todaysQuests()->exists()) {
            return;
        }

        // Get all active daily quests
        $dailyQuests = Quest::active()->daily()->get();

        foreach ($dailyQuests as $quest) {
            $user->quests()->attach($quest->id, [
                'progress' => 0,
                'target' => $quest->getRequiredCount(),
                'assigned_date' => $today,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Update quest progress for a user.
     */
    public function updateQuestProgress(User $user, string $category, int $amount = 1): void
    {
        $today = today();

        // Get active quests for this category assigned today
        $activeQuests = $user->activeQuests()
            ->where('category', $category)
            ->wherePivot('assigned_date', $today)
            ->get();

        foreach ($activeQuests as $quest) {
            $currentProgress = $quest->pivot->progress + $amount;
            $target = $quest->pivot->target;

            $isCompleted = $currentProgress >= $target;

            $user->quests()->updateExistingPivot($quest->id, [
                'progress' => min($currentProgress, $target),
                'is_completed' => $isCompleted,
                'completed_at' => $isCompleted ? now() : null,
                'updated_at' => now(),
            ]);

            // Award XP if quest is completed
            if ($isCompleted && $quest->pivot->is_completed == false) {
                $user->addExperience($quest->experience_reward);
                
                // Send quest completion notification
                $user->notify(new \App\Notifications\QuestCompletedNotification($quest, $quest->experience_reward));
                
                // Check for achievement unlocks
                $this->checkAchievementUnlocks($user);
            }
        }
    }

    /**
     * Get user's quest progress summary.
     */
    public function getUserQuestSummary(User $user): array
    {
        $today = today();

        $todaysQuests = $user->todaysQuests()->get();
        $completedToday = $todaysQuests->where('pivot.is_completed', true)->count();
        $totalToday = $todaysQuests->count();

        return [
            'todays_quests' => $todaysQuests,
            'completed_today' => $completedToday,
            'total_today' => $totalToday,
            'completion_rate' => $totalToday > 0 ? round(($completedToday / $totalToday) * 100) : 0,
        ];
    }

    /**
     * Check and complete quests automatically.
     */
    public function checkQuestCompletion(User $user, string $category): void
    {
        $this->updateQuestProgress($user, $category, 1);
    }

    /**
     * Check if user has unlocked any achievements and send notifications.
     */
    private function checkAchievementUnlocks(User $user): void
    {
        $totalCompleted = $user->quests()->wherePivot('is_completed', true)->count();
        $totalXpEarned = $user->experience + (($user->level - 1) * 100); // Approximate total XP earned
        
        // Check for "First Quest" achievement (1 quest completed)
        if ($totalCompleted == 1) {
            $user->notify(new \App\Notifications\AchievementUnlockedNotification(
                'First Quest',
                'Complete your first quest',
                '🎯'
            ));
        }
        
        // Check for "Quest Veteran" achievement (10 quests completed)
        if ($totalCompleted == 10) {
            $user->notify(new \App\Notifications\AchievementUnlockedNotification(
                'Quest Veteran',
                'Complete 10 quests',
                '🏆'
            ));
        }
        
        // Check for "Quest Master" achievement (50 quests completed)
        if ($totalCompleted == 50) {
            $user->notify(new \App\Notifications\AchievementUnlockedNotification(
                'Quest Master',
                'Complete 50 quests',
                '👑'
            ));
        }
        
        // Check for "XP Collector" achievement (1000 XP earned)
        if ($totalXpEarned >= 1000 && ($totalXpEarned - $user->quests()->where('quests.id', $user->quests()->latest()->first()?->id)->first()?->experience_reward ?? 0) < 1000) {
            $user->notify(new \App\Notifications\AchievementUnlockedNotification(
                'XP Collector',
                'Earn 1000 experience points',
                '⭐'
            ));
        }
    }
}
