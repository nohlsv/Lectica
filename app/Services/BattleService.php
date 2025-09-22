<?php

namespace App\Services;

use App\Models\Battle;
use App\Models\File;
use App\Models\Quiz;
use App\Models\Monster;
use App\Models\Collection;
use App\Models\User;
use App\Enums\BattleStatus;
use Illuminate\Support\Facades\Auth;

class BattleService
{
    public function __construct(
        private QuizDifficultyService $difficultyService
    ) {}
    /**
     * Create a new battle for the authenticated user.
     */
    public function createBattle(int $monsterId, ?int $fileId = null, ?int $collectionId = null): Battle
    {
        $monster = Monster::find($monsterId);
        if (!$monster) {
            throw new \InvalidArgumentException('Invalid monster selected.');
        }

        $file = null;
        $collection = null;
        $quizCount = 0;

        if ($fileId) {
            $file = File::findOrFail($fileId);

            // Check if user owns the file
            if ($file->user_id !== Auth::id()) {
                throw new \UnauthorizedAccessException('You can only battle with your own files.');
            }

            // Check if file has quizzes
            $quizCount = Quiz::where('file_id', $file->id)->count();
            if ($quizCount === 0) {
                throw new \InvalidArgumentException('This file has no quizzes. Please generate quizzes first.');
            }
        } elseif ($collectionId) {
            $collection = Collection::findOrFail($collectionId);

            // Check if user owns the collection
            if ($collection->user_id !== Auth::id()) {
                throw new \UnauthorizedAccessException('You can only battle with your own collections.');
            }

            $quizCount = $collection->getTotalQuizzesCount();
            if ($quizCount === 0) {
                throw new \InvalidArgumentException('This collection has no quizzes. Please add files with quizzes.');
            }
        } else {
            throw new \InvalidArgumentException('Either file_id or collection_id must be provided.');
        }

        return Battle::create([
            'user_id' => Auth::id(),
            'monster_id' => $monsterId,
            'file_id' => $fileId,
            'collection_id' => $collectionId,
            'status' => BattleStatus::ACTIVE,
            'player_hp' => $this->getDefaultPlayerHp(),
            'monster_hp' => $monster->hp,
            'correct_answers' => 0,
            'total_questions' => 0
        ]);
    }

    /**
     * Process a quiz answer during battle.
     */
    public function processAnswer(Battle $battle, Quiz $quiz, bool $isCorrect): array
    {
        if (!$battle->isActive()) {
            throw new \RuntimeException('Battle is not active.');
        }

        $monster = $battle->monster;
        $allQuizzes = $battle->getAvailableQuizzes();
        $totalQuestions = $allQuizzes->count();

        // Update question stats
        $battle->total_questions++;

        $message = '';
        $expEarned = 0;

        if ($isCorrect) {
            $battle->correct_answers++;

            // Calculate EXP reward based on question difficulty
            $expEarned = $this->difficultyService->getExpRewardForQuizType($quiz->type);
            
            // Award experience to the user
            $user = User::find($battle->user_id);
            if ($user) {
                $user->addExperience($expEarned);
            }

            // Calculate damage based on total questions to ensure monster dies when all questions are answered
            $damagePerQuestion = $this->difficultyService->calculateDamagePerQuestion($monster->hp, $totalQuestions);
            $actualDamage = (int) round($damagePerQuestion);
            $battle->monster_hp = max(0, $battle->monster_hp - $actualDamage);

            $difficultyText = $this->difficultyService->getDifficultyForQuizType($quiz->type);
            $message = "Correct! You dealt {$actualDamage} damage to the {$monster->name}! (+{$expEarned} EXP for {$difficultyText} question)";
        } else {
            // Monster deals damage to player - reduced since player should focus on learning
            $monsterDamage = $this->calculateMonsterDamage($monster);
            $battle->player_hp = max(0, $battle->player_hp - $monsterDamage);

            $message = "Wrong! The {$monster->name} dealt {$monsterDamage} damage to you!";
        }

        // Check win/loss conditions
        if ($battle->monster_hp <= 0) {
            $battle->markAsWon();
            $message .= " You have defeated the {$monster->name}!";
            
            // Bonus experience for completing the battle
            $user = User::find($battle->user_id);
            if ($user) {
                $bonusExp = 50; // Flat completion bonus
                $user->addExperience($bonusExp);
                $expEarned += $bonusExp;
                $message .= " (+{$bonusExp} completion bonus EXP)";
            }
        } elseif ($battle->player_hp <= 0) {
            $battle->markAsLost();
            $message .= " You have been defeated by the {$monster->name}!";
        } else {
            $battle->save();
        }

        return [
            'battle' => $battle->fresh(),
            'monster' => $monster,
            'message' => $message,
            'battleEnded' => $battle->isFinished(),
            'expEarned' => $expEarned
        ];
    }

    /**
     * Get battle statistics for a user.
     */
    public function getUserStats(int $userId): array
    {
        $stats = [
            'total_battles' => Battle::where('user_id', $userId)->count(),
            'won_battles' => Battle::where('user_id', $userId)->byStatus(BattleStatus::WON)->count(),
            'lost_battles' => Battle::where('user_id', $userId)->byStatus(BattleStatus::LOST)->count(),
            'active_battles' => Battle::where('user_id', $userId)->active()->count(),
            'abandoned_battles' => Battle::where('user_id', $userId)->byStatus(BattleStatus::ABANDONED)->count(),
            'total_questions' => Battle::where('user_id', $userId)->sum('total_questions'),
            'correct_answers' => Battle::where('user_id', $userId)->sum('correct_answers'),
        ];

        $stats['win_rate'] = $stats['total_battles'] > 0
            ? round(($stats['won_battles'] / $stats['total_battles']) * 100, 2)
            : 0;

        $stats['accuracy'] = $stats['total_questions'] > 0
            ? round(($stats['correct_answers'] / $stats['total_questions']) * 100, 2)
            : 0;

        return $stats;
    }

    /**
     * Get the next quiz question for a battle.
     */
    public function getNextQuiz(Battle $battle): ?Quiz
    {
        if (!$battle->isActive()) {
            return null;
        }

        return $battle->file->quizzes->random();
    }

    /**
     * Get files suitable for battles (files with quizzes).
     */
    public function getBattleReadyFiles(int $userId)
    {
        return File::where('user_id', $userId)
            ->whereHas('quizzes')
            ->with(['quizzes'])
            ->get();
    }

    /**
     * Get recent battles for a user.
     */
    public function getRecentBattles(int $userId, int $limit = 5): array
    {
        return Battle::with(['file'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($battle) {
                return [
                    ...$battle->toArray(),
                    'monster' => $battle->monster,
                    'accuracy' => $battle->getAccuracy()
                ];
            })
            ->toArray();
    }

    /**
     * Calculate damage dealt by player.
     */
    private function calculatePlayerDamage(): int
    {
        return rand(15, 25);
    }

    /**
     * Calculate damage dealt by monster.
     */
    private function calculateMonsterDamage(Monster $monster): int
    {
        $baseAttack = $monster->attack;
        $variance = 5;
        return rand($baseAttack - $variance, $baseAttack + $variance);
    }

    /**
     * Get default player HP.
     */
    private function getDefaultPlayerHp(): int
    {
        return 100;
    }

    /**
     * Complete a battle with final results.
     */
    public function completeBattle(
        Battle $battle,
        int $playerHp,
        int $monsterHp,
        int $correctAnswers,
        int $totalQuestions,
        string $status
    ): bool {
        $battle->update([
            'player_hp' => $playerHp,
            'monster_hp' => $monsterHp,
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
        ]);

        // Calculate and award final experience based on performance
        $allQuizzes = $battle->getAvailableQuizzes();
        $user = User::find($battle->user_id);
        
        if ($user && $correctAnswers > 0) {
            // Create a mock array of correct answers for calculation
            // This is a simplified approach - in practice you'd track individual question results
            $correctAnswersArray = array_fill(0, $correctAnswers, true);
            $expEarned = $this->difficultyService->calculateEarnedExp($allQuizzes, $correctAnswersArray);
            $user->addExperience($expEarned);
        }

        if ($status === 'victory') {
            $battle->markAsWon();
        } else {
            $battle->markAsLost();
        }

        return true;
    }
}
