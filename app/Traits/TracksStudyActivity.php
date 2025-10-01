<?php

namespace App\Traits;

use App\Models\UserStudyActivity;
use Carbon\Carbon;

trait TracksStudyActivity
{
    /**
     * Record quiz completion activity.
     */
    protected function recordQuizActivity(int $userId, array $quizData): void
    {
        UserStudyActivity::incrementActivity($userId, [
            'quizzes_completed' => 1,
            'questions_answered' => $quizData['questions_answered'] ?? 0,
            'correct_answers' => $quizData['correct_answers'] ?? 0,
            'points_earned' => $quizData['points_earned'] ?? 0,
            'time_studied' => $quizData['time_spent_minutes'] ?? 0,
        ]);
    }

    /**
     * Record battle participation activity.
     */
    protected function recordBattleActivity(int $userId, array $battleData): void
    {
        UserStudyActivity::incrementActivity($userId, [
            'battles_participated' => 1,
            'questions_answered' => $battleData['questions_answered'] ?? 0,
            'correct_answers' => $battleData['correct_answers'] ?? 0,
            'points_earned' => $battleData['points_earned'] ?? 0,
            'time_studied' => $battleData['time_spent_minutes'] ?? 0,
        ]);
    }

    /**
     * Record flashcard study activity.
     */
    protected function recordFlashcardActivity(int $userId, int $cardsReviewed, int $timeSpentMinutes = 0): void
    {
        UserStudyActivity::incrementActivity($userId, [
            'flashcards_reviewed' => $cardsReviewed,
            'time_studied' => $timeSpentMinutes,
        ]);
    }

    /**
     * Record general study time.
     */
    protected function recordStudyTime(int $userId, int $timeSpentMinutes): void
    {
        UserStudyActivity::incrementActivity($userId, [
            'time_studied' => $timeSpentMinutes,
        ]);
    }
}