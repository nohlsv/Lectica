<?php

namespace App\Services;

use App\Enums\QuizType;
use App\Models\Quiz;
use Illuminate\Support\Collection;

class QuizDifficultyService
{
    /**
     * Difficulty mapping based on quiz types
     */
    private const DIFFICULTY_MAPPING = [
        QuizType::TRUE_FALSE->value => 'easy',
        QuizType::MULTIPLE_CHOICE->value => 'medium', 
        QuizType::ENUMERATION->value => 'hard',
    ];

    /**
     * Experience rewards per correct answer based on difficulty
     */
    private const EXP_REWARDS = [
        'easy' => 10,
        'medium' => 20,
        'hard' => 30,
    ];

    /**
     * Get difficulty level for a quiz type
     */
    public function getDifficultyForQuizType(QuizType|string $quizType): string
    {
        $type = $quizType instanceof QuizType ? $quizType->value : $quizType;
        return self::DIFFICULTY_MAPPING[$type] ?? 'medium';
    }

    /**
     * Get experience reward for a difficulty level
     */
    public function getExpRewardForDifficulty(string $difficulty): int
    {
        return self::EXP_REWARDS[$difficulty] ?? 20;
    }

    /**
     * Get experience reward for a quiz type
     */
    public function getExpRewardForQuizType(QuizType|string $quizType): int
    {
        $difficulty = $this->getDifficultyForQuizType($quizType);
        return $this->getExpRewardForDifficulty($difficulty);
    }

    /**
     * Calculate total possible experience from a collection of quizzes
     */
    public function calculateTotalPossibleExp(Collection $quizzes): int
    {
        return $quizzes->sum(function (Quiz $quiz) {
            return $this->getExpRewardForQuizType($quiz->type);
        });
    }

    /**
     * Calculate experience earned from correct answers
     */
    public function calculateEarnedExp(Collection $quizzes, array $correctAnswers): int
    {
        $totalExp = 0;
        
        foreach ($quizzes as $index => $quiz) {
            if (isset($correctAnswers[$index]) && $correctAnswers[$index]) {
                $totalExp += $this->getExpRewardForQuizType($quiz->type);
            }
        }
        
        return $totalExp;
    }

    /**
     * Get all difficulty levels
     */
    public function getAllDifficulties(): array
    {
        return array_unique(array_values(self::DIFFICULTY_MAPPING));
    }

    /**
     * Get quiz types for a specific difficulty
     */
    public function getQuizTypesForDifficulty(string $difficulty): array
    {
        return array_keys(array_filter(self::DIFFICULTY_MAPPING, function ($diff) use ($difficulty) {
            return $diff === $difficulty;
        }));
    }

    /**
     * Categorize quizzes by difficulty
     */
    public function categorizeQuizzesByDifficulty(Collection $quizzes): array
    {
        $categorized = [
            'easy' => collect(),
            'medium' => collect(),
            'hard' => collect(),
        ];

        foreach ($quizzes as $quiz) {
            $difficulty = $this->getDifficultyForQuizType($quiz->type);
            $categorized[$difficulty]->push($quiz);
        }

        return $categorized;
    }

    /**
     * Get damage per question based on total questions
     * Ensures monster dies exactly when all questions are answered
     */
    public function calculateDamagePerQuestion(int $monsterHp, int $totalQuestions): float
    {
        if ($totalQuestions <= 0) {
            return 0;
        }
        
        return $monsterHp / $totalQuestions;
    }

    /**
     * Calculate accumulated damage after answering questions
     */
    public function calculateAccumulatedDamage(int $monsterHp, int $totalQuestions, int $questionsAnswered): int
    {
        if ($totalQuestions <= 0 || $questionsAnswered <= 0) {
            return 0;
        }
        
        $damagePerQuestion = $this->calculateDamagePerQuestion($monsterHp, $totalQuestions);
        $totalDamage = $damagePerQuestion * $questionsAnswered;
        
        return (int) round($totalDamage);
    }

    /**
     * Calculate remaining monster HP after answering questions
     */
    public function calculateRemainingMonsterHp(int $initialMonsterHp, int $totalQuestions, int $questionsAnswered): int
    {
        $damageDealt = $this->calculateAccumulatedDamage($initialMonsterHp, $totalQuestions, $questionsAnswered);
        return max(0, $initialMonsterHp - $damageDealt);
    }
}