<?php

namespace App\Services;

use App\Models\Battle;
use App\Models\File;
use App\Models\Quiz;
use App\Models\Monster;
use App\Enums\BattleStatus;
use Illuminate\Support\Facades\Auth;

class BattleService
{
    /**
     * Create a new battle for the authenticated user.
     */
    public function createBattle(string $monsterId, int $fileId): Battle
    {
        $monster = Monster::find($monsterId);
        if (!$monster) {
            throw new \InvalidArgumentException('Invalid monster selected.');
        }

        $file = File::findOrFail($fileId);

        // Check if user owns the file
        if ($file->user_id !== Auth::id()) {
            throw new \UnauthorizedAccessException('You can only battle with your own files.');
        }

        // Check if file has quizzes
        if (!$file->hasQuizzes()) {
            throw new \InvalidArgumentException('This file has no quizzes. Please generate quizzes first.');
        }

        return Battle::create([
            'user_id' => Auth::id(),
            'monster_id' => $monsterId,
            'file_id' => $file->id,
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
    public function processAnswer(Battle $battle, bool $isCorrect): array
    {
        if (!$battle->isActive()) {
            throw new \RuntimeException('Battle is not active.');
        }

        $monster = $battle->monster;

        // Update question stats
        $battle->total_questions++;

        $message = '';

        if ($isCorrect) {
            $battle->correct_answers++;

            // Player deals damage to monster
            $damage = $this->calculatePlayerDamage();
            $actualDamage = $monster->takeDamage($damage);
            $battle->monster_hp = max(0, $battle->monster_hp - $actualDamage);

            $message = "Correct! You dealt {$actualDamage} damage to the {$monster->name}!";
        } else {
            // Monster deals damage to player
            $monsterDamage = $this->calculateMonsterDamage($monster);
            $battle->player_hp = max(0, $battle->player_hp - $monsterDamage);

            $message = "Wrong! The {$monster->name} dealt {$monsterDamage} damage to you!";
        }

        // Check win/loss conditions
        if ($battle->monster_hp <= 0) {
            $battle->markAsWon();
            $message .= " You have defeated the {$monster->name}!";
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
            'battleEnded' => $battle->isFinished()
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

        if ($status === 'victory') {
            $battle->markAsWon();
        } else {
            $battle->markAsLost();
        }

        return true;
    }
}
