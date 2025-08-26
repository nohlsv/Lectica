<?php

namespace App\Services;

use App\Models\User;
use App\Models\Quest;
use Carbon\Carbon;

class QuestService
{
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
}
