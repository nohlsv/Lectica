<?php

namespace App\Services;

use App\Models\UserStudyActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StudyStreakService
{
    /**
     * Calculate the current study streak for a user.
     */
    public function getCurrentStreak(User $user): int
    {
        $activities = UserStudyActivity::where('user_id', $user->id)
            ->where('study_date', '>=', Carbon::now()->subDays(365))
            ->orderBy('study_date', 'desc')
            ->pluck('study_date');

        if ($activities->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $currentDate = Carbon::today();
        
        // Check if user studied today or yesterday (to account for timezone)
        $lastActivity = $activities->first();
        if ($lastActivity->diffInDays($currentDate) > 1) {
            return 0; // Streak is broken
        }

        // Start counting from the most recent activity date
        $expectedDate = $lastActivity->copy();
        
        foreach ($activities as $activityDate) {
            if ($activityDate->eq($expectedDate)) {
                $streak++;
                $expectedDate->subDay();
            } else {
                // There's a gap in the streak
                break;
            }
        }

        return $streak;
    }

    /**
     * Get the longest streak for a user.
     */
    public function getLongestStreak(User $user): int
    {
        $activities = UserStudyActivity::where('user_id', $user->id)
            ->orderBy('study_date', 'asc')
            ->pluck('study_date');

        if ($activities->isEmpty()) {
            return 0;
        }

        if ($activities->count() === 1) {
            return 1;
        }

        $longestStreak = 1;
        $currentStreak = 1;

        for ($i = 1; $i < $activities->count(); $i++) {
            $currentDate = $activities[$i];
            $previousDate = $activities[$i - 1];

            // Check if current date is exactly 1 day after previous date
            if ($previousDate->diffInDays($currentDate) <= 1.01) {
                $currentStreak++;
                $longestStreak = max($longestStreak, $currentStreak);
            } else {
                $currentStreak = 1;
            }
        }

        return $longestStreak;
    }

    /**
     * Get heatmap data for the last year.
     */
    public function getHeatmapData(User $user): array
    {
        $startDate = Carbon::now()->subYear()->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $activities = UserStudyActivity::where('user_id', $user->id)
            ->whereBetween('study_date', [$startDate, $endDate])
            ->get()
            ->keyBy(function ($activity) {
                return $activity->study_date->format('Y-m-d');
            });

        $heatmapData = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dateString = $currentDate->format('Y-m-d');
            $activity = $activities->get($dateString);
            
            $heatmapData[] = [
                'date' => $dateString,
                'value' => $activity ? $this->calculateIntensity($activity) : 0,
                'count' => $activity ? $activity->questions_answered : 0,
                'points' => $activity ? $activity->points_earned : 0,
            ];

            $currentDate->addDay();
        }

        return $heatmapData;
    }

    /**
     * Calculate study intensity level (0-4) for heatmap coloring.
     */
    private function calculateIntensity(UserStudyActivity $activity): int
    {
        // Base score calculation
        $score = 0;
        
        // Points from quizzes and battles
        $score += $activity->points_earned * 0.01;
        
        // Questions answered
        $score += $activity->questions_answered * 0.5;
        
        // Time studied (minutes)
        $score += $activity->time_studied * 0.1;
        
        // Bonus for completing quizzes and battles
        $score += $activity->quizzes_completed * 5;
        $score += $activity->battles_participated * 10;
        $score += $activity->flashcards_reviewed * 0.2;

        // Map to intensity levels
        if ($score >= 100) return 4;      // Very high activity
        if ($score >= 50) return 3;       // High activity
        if ($score >= 20) return 2;       // Medium activity
        if ($score >= 5) return 1;        // Low activity
        
        return 0; // No activity
    }

    /**
     * Get streak statistics for a user.
     */
    public function getStreakStats(User $user): array
    {
        return [
            'current_streak' => $this->getCurrentStreak($user),
            'longest_streak' => $this->getLongestStreak($user),
            'total_study_days' => UserStudyActivity::where('user_id', $user->id)->count(),
            'heatmap_data' => $this->getHeatmapData($user),
        ];
    }
}