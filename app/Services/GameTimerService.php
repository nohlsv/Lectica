<?php

namespace App\Services;

use App\Models\MultiplayerGame;
use App\Enums\MultiplayerGameStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GameTimerService
{
    const TIMER_DURATION = 30; // seconds per question
    const INACTIVITY_FORFEIT_DURATION = 120; // 2 minutes of total inactivity = forfeit
    
    /**
     * Start timer for a game turn
     */
    public function startTimer(int $gameId): void
    {
        $timerKey = "game_timer_{$gameId}";
        $activityKey = "game_activity_{$gameId}";
        
        $timerData = [
            'game_id' => $gameId,
            'started_at' => now()->timestamp,
            'duration' => self::TIMER_DURATION,
            'warnings_sent' => []
        ];
        
        // Store timer data in cache with expiration slightly longer than timer duration
        Cache::put($timerKey, $timerData, now()->addSeconds(self::TIMER_DURATION + 10));
        
        // Update activity timestamp
        Cache::put($activityKey, now()->timestamp, now()->addMinutes(10));
        
        // Broadcast timer start to players
        $game = MultiplayerGame::find($gameId);
        if ($game) {
            broadcast(new \App\Events\MultiplayerGameUpdated($game, 'timer_started', [
                'timer_duration' => self::TIMER_DURATION,
                'started_at' => now()->timestamp,
                'current_turn' => $game->current_turn,
            ]));
        }
    }
    
    /**
     * Stop timer for a game
     */
    public function stopTimer(int $gameId): void
    {
        $timerKey = "game_timer_{$gameId}";
        Cache::forget($timerKey);
        
        // Broadcast timer stop
        $game = MultiplayerGame::find($gameId);
        if ($game) {
            broadcast(new \App\Events\MultiplayerGameUpdated($game, 'timer_stopped'));
        }
    }
    
    /**
     * Get remaining time for a game timer
     */
    public function getRemainingTime(int $gameId): int
    {
        $timerKey = "game_timer_{$gameId}";
        $timerData = Cache::get($timerKey);
        
        if (!$timerData) {
            return 0;
        }
        
        $elapsedTime = now()->timestamp - $timerData['started_at'];
        return max(0, $timerData['duration'] - $elapsedTime);
    }
    
    /**
     * Check if timer is running for a game
     */
    public function isTimerRunning(int $gameId): bool
    {
        $timerKey = "game_timer_{$gameId}";
        return Cache::has($timerKey) && $this->getRemainingTime($gameId) > 0;
    }
    
    /**
     * Update player activity timestamp
     */
    public function updateActivity(int $gameId): void
    {
        $activityKey = "game_activity_{$gameId}";
        Cache::put($activityKey, now()->timestamp, now()->addMinutes(10));
    }
    
    /**
     * Get last activity timestamp
     */
    public function getLastActivity(int $gameId): ?int
    {
        $activityKey = "game_activity_{$gameId}";
        return Cache::get($activityKey);
    }
    
    /**
     * Check all active game timers and handle timeouts
     */
    public function processAllTimers(): void
    {
        // Get all active games
        $activeGames = MultiplayerGame::active()->get();
        
        foreach ($activeGames as $game) {
            $this->processGameTimer($game);
            $this->checkInactivityForfeit($game);
        }
    }
    
    /**
     * Process timer for a specific game
     */
    protected function processGameTimer(MultiplayerGame $game): void
    {
        $timerKey = "game_timer_{$game->id}";
        $timerData = Cache::get($timerKey);
        
        if (!$timerData) {
            return; // No timer running
        }
        
        $remainingTime = $this->getRemainingTime($game->id);
        
        // Send warnings at specific intervals
        $this->sendTimerWarnings($game, $remainingTime, $timerData);
        
        // Handle timeout
        if ($remainingTime <= 0) {
            $this->handleTimeout($game);
        }
    }
    
    /**
     * Send timer warnings at specific intervals
     */
    protected function sendTimerWarnings(MultiplayerGame $game, int $remainingTime, array &$timerData): void
    {
        $warningPoints = [10, 5, 3, 2, 1]; // seconds
        
        foreach ($warningPoints as $warningPoint) {
            if ($remainingTime <= $warningPoint && !in_array($warningPoint, $timerData['warnings_sent'])) {
                // Mark warning as sent
                $timerData['warnings_sent'][] = $warningPoint;
                Cache::put("game_timer_{$game->id}", $timerData, now()->addSeconds(self::TIMER_DURATION + 10));
                
                // Broadcast warning
                broadcast(new \App\Events\MultiplayerGameUpdated($game, 'timer_warning', [
                    'remaining_time' => $remainingTime,
                    'warning_point' => $warningPoint,
                    'current_turn' => $game->current_turn,
                ]));
                
                break; // Only send one warning per check
            }
        }
    }
    
    /**
     * Handle timer timeout
     */
    protected function handleTimeout(MultiplayerGame $game): void
    {
        try {
            DB::transaction(function () use ($game) {
                $game = $game->lockForUpdate();
                
                // Stop the timer
                $this->stopTimer($game->id);
                
                if (!$game->isActive()) {
                    return;
                }
                
                $currentPlayer = $game->getCurrentPlayer();
                if (!$currentPlayer) {
                    return;
                }
                
                // Record timeout as incorrect answer
                if ($game->current_turn === 1) {
                    $game->increment('total_questions_p1');
                    $game->player_one_streak = 0; // Break streak
                } else {
                    $game->increment('total_questions_p2');
                    $game->player_two_streak = 0; // Break streak
                }
                
                // Update accuracies
                $game->player_one_accuracy = $game->getPlayerOneAccuracy();
                $game->player_two_accuracy = $game->getPlayerTwoAccuracy();
                $game->save();
                
                // Switch turn
                $game->switchTurn();
                
                // Broadcast timeout event
                broadcast(new \App\Events\MultiplayerGameUpdated($game->fresh(), 'timer_timeout', [
                    'timed_out_player' => $currentPlayer->id,
                    'timed_out_player_name' => $currentPlayer->first_name,
                    'current_turn' => $game->current_turn,
                    'message' => $currentPlayer->first_name . ' timed out!',
                ]));
                
                // Start timer for next turn
                $this->startTimer($game->id);
            });
        } catch (\Exception $e) {
            \Log::error('Timer timeout handling failed', [
                'game_id' => $game->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Check for inactivity and forfeit if necessary
     */
    protected function checkInactivityForfeit(MultiplayerGame $game): void
    {
        $lastActivity = $this->getLastActivity($game->id);
        
        if (!$lastActivity) {
            return;
        }
        
        $inactiveSeconds = now()->timestamp - $lastActivity;
        
        if ($inactiveSeconds >= self::INACTIVITY_FORFEIT_DURATION) {
            $this->forfeitDueToInactivity($game);
        }
    }
    
    /**
     * Forfeit game due to inactivity
     */
    protected function forfeitDueToInactivity(MultiplayerGame $game): void
    {
        try {
            DB::transaction(function () use ($game) {
                $game = $game->lockForUpdate();
                
                if (!$game->isActive()) {
                    return;
                }
                
                // Stop timer
                $this->stopTimer($game->id);
                
                // Clear activity cache
                Cache::forget("game_activity_{$game->id}");
                
                // Forfeit the current turn player (they were inactive)
                $currentPlayer = $game->getCurrentPlayer();
                if ($currentPlayer) {
                    $game->forfeitGame($currentPlayer->id);
                    
                    \Log::info('Game forfeited due to inactivity', [
                        'game_id' => $game->id,
                        'forfeited_player' => $currentPlayer->id
                    ]);
                }
            });
        } catch (\Exception $e) {
            \Log::error('Inactivity forfeit failed', [
                'game_id' => $game->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get timer status for a game (for API responses)
     */
    public function getTimerStatus(int $gameId): array
    {
        return [
            'is_running' => $this->isTimerRunning($gameId),
            'remaining_time' => $this->getRemainingTime($gameId),
            'duration' => self::TIMER_DURATION,
            'last_activity' => $this->getLastActivity($gameId),
        ];
    }
    
    /**
     * Clean up expired timers and activities
     */
    public function cleanupExpiredTimers(): void
    {
        // Cache will automatically expire, but we can force cleanup if needed
        $activeGames = MultiplayerGame::active()->pluck('id');
        
        foreach ($activeGames as $gameId) {
            if (!$this->isTimerRunning($gameId)) {
                Cache::forget("game_timer_{$gameId}");
                Cache::forget("game_activity_{$gameId}");
            }
        }
    }
}