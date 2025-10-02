<?php

namespace App\Services;

use App\Models\MultiplayerGame;
use App\Models\Quiz;
use App\Enums\MultiplayerGameStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GameTimerService
{
    const BASE_TIMER_DURATION = 30; // base seconds per question
    const ENUMERATION_MULTIPLIER = 30; // seconds per enumeration item
    const TIMER_GRACE_PERIOD = 5; // seconds of grace period after timer expires
    const INACTIVITY_FORFEIT_DURATION = 120; // 2 minutes of total inactivity = forfeit
    
    /**
     * Calculate timer duration based on question type
     */
    protected function calculateTimerDuration(MultiplayerGame $game): int
    {
        $currentQuestion = $game->getCurrentQuestion();
        
        if (!$currentQuestion) {
            return self::BASE_TIMER_DURATION;
        }
        
        switch ($currentQuestion->type) {
            case 'enumeration':
                // Count the number of answers expected
                $answerCount = is_array($currentQuestion->answers) 
                    ? count($currentQuestion->answers) 
                    : (is_string($currentQuestion->answers) ? count(explode(',', $currentQuestion->answers)) : 1);
                    
                // Give 30 seconds per enumeration item, minimum 30 seconds
                return max(self::BASE_TIMER_DURATION, $answerCount * self::ENUMERATION_MULTIPLIER);
                
            case 'multiple_choice':
                return self::BASE_TIMER_DURATION;
                
            case 'true_false':
                return self::BASE_TIMER_DURATION;
                
            default:
                return self::BASE_TIMER_DURATION;
        }
    }
    
    /**
     * Start timer for a game turn
     */
    public function startTimer(int $gameId, bool $withGracePeriod = true): void
    {
        $timerKey = "game_timer_{$gameId}";
        $activityKey = "game_activity_{$gameId}";
        
        $game = MultiplayerGame::find($gameId);
        if (!$game) return;
        
        // Calculate timer duration based on question type
        $timerDuration = $this->calculateTimerDuration($game);
        
        $timerData = [
            'game_id' => $gameId,
            'started_at' => now()->timestamp,
            'duration' => $timerDuration,
            'warnings_sent' => [],
            'grace_period_used' => false,
            'question_type' => $game->getCurrentQuestion()?->type ?? 'unknown'
        ];
        
        // Store timer data in cache with expiration longer than timer duration + grace period
        Cache::put($timerKey, $timerData, now()->addSeconds($timerDuration + self::TIMER_GRACE_PERIOD + 10));
        
        // Update activity timestamp
        Cache::put($activityKey, now()->timestamp, now()->addMinutes(10));
        
        // Broadcast timer start to players
        broadcast(new \App\Events\MultiplayerGameUpdated($game, 'timer_started', [
            'timer_duration' => $timerDuration,
            'started_at' => now()->timestamp,
            'current_turn' => $game->current_turn,
            'grace_period' => self::TIMER_GRACE_PERIOD,
            'question_type' => $game->getCurrentQuestion()?->type ?? 'unknown',
        ]));
    }

    
    /**
     * Check if answer submission is allowed (including grace period)
     */
    public function isSubmissionAllowed(int $gameId): bool
    {
        $timerKey = "game_timer_{$gameId}";
        $timerData = Cache::get($timerKey);
        
        if (!$timerData) {
            return true; // No timer running, allow submission
        }
        
        $elapsedTime = now()->timestamp - $timerData['started_at'];
        $gracePeriodEnd = $timerData['duration'] + self::TIMER_GRACE_PERIOD;
        
        return $elapsedTime <= $gracePeriodEnd;
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
        $delayKey = "game_timer_delay_{$game->id}";
        
        // Check if timer is delayed waiting for players
        if (Cache::has($delayKey)) {
            $delayUntil = Cache::get($delayKey);
            if (now()->timestamp >= $delayUntil) {
                Cache::forget($delayKey);
                $this->startTimer($game->id, false); // Start timer now
            }
            return; // Still waiting for delay to expire
        }
        
        $timerData = Cache::get($timerKey);
        if (!$timerData) {
            return; // No timer running
        }
        
        $remainingTime = $this->getRemainingTime($game->id);
        $elapsedTime = now()->timestamp - $timerData['started_at'];
        $gracePeriodEnd = $timerData['duration'] + self::TIMER_GRACE_PERIOD;
        
        // Send warnings at specific intervals (but not during grace period)
        if ($remainingTime > 0) {
            $this->sendTimerWarnings($game, $remainingTime, $timerData);
        }
        
        // Handle timeout (only after grace period expires)
        if ($elapsedTime > $gracePeriodEnd) {
            \Log::info('Timer grace period expired, handling timeout', [
                'game_id' => $game->id,
                'elapsed_time' => $elapsedTime,
                'grace_period_end' => $gracePeriodEnd
            ]);
            $this->handleTimeout($game);
        } else if ($remainingTime <= 0 && !$timerData['grace_period_used']) {
            // Timer expired but still in grace period - activate grace period silently
            \Log::info('Timer expired, entering grace period', [
                'game_id' => $game->id,
                'remaining_time' => $remainingTime,
                'grace_period_remaining' => $gracePeriodEnd - $elapsedTime
            ]);
            
            $timerData['grace_period_used'] = true;
            Cache::put($timerKey, $timerData, now()->addSeconds(self::TIMER_GRACE_PERIOD + 10));
            
            // Don't broadcast grace period UI - handle timeout silently after grace expires
        }
    }
    
    /**
     * Send timer warnings at specific intervals
     */
    protected function sendTimerWarnings(MultiplayerGame $game, int $remainingTime, array &$timerData): void
    {
        $duration = $timerData['duration'];
        
        // Dynamic warning points based on duration
        $warningPoints = [];
        
        // Always warn at final countdown
        $warningPoints = [3, 2, 1];
        
        // Add proportional warnings based on duration
        if ($duration >= 30) {
            $warningPoints[] = 10;
            $warningPoints[] = 5;
        }
        if ($duration >= 60) {
            $warningPoints[] = 30;
            $warningPoints[] = 15;
        }
        if ($duration >= 120) {
            $warningPoints[] = 60;
        }
        
        // Sort in descending order
        rsort($warningPoints);
        
        foreach ($warningPoints as $warningPoint) {
            if ($remainingTime <= $warningPoint && !in_array($warningPoint, $timerData['warnings_sent'])) {
                // Mark warning as sent
                $timerData['warnings_sent'][] = $warningPoint;
                Cache::put("game_timer_{$game->id}", $timerData, now()->addSeconds($duration + self::TIMER_GRACE_PERIOD + 10));
                
                // Broadcast warning
                broadcast(new \App\Events\MultiplayerGameUpdated($game, 'timer_warning', [
                    'remaining_time' => $remainingTime,
                    'warning_point' => $warningPoint,
                    'current_turn' => $game->current_turn,
                    'question_type' => $timerData['question_type'] ?? 'unknown',
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
        \Log::info('Processing timeout for game', [
            'game_id' => $game->id,
            'current_turn' => $game->current_turn,
            'status' => $game->status->value
        ]);
        
        try {
            // Get current player info directly from the game data
            $currentPlayerId = $game->current_turn === 1 ? $game->player_one_id : $game->player_two_id;
            $currentPlayerName = $game->current_turn === 1 ? 'Player 1' : 'Player 2';
            
            \Log::info('Processing timeout', [
                'game_id' => $game->id,
                'current_turn' => $game->current_turn,
                'current_player_id' => $currentPlayerId
            ]);
            
            // Stop the timer first
            $this->stopTimer($game->id);
            
            // Update game state directly without transaction for now to avoid relationship issues
            $gameId = $game->id;
            $currentTurn = $game->current_turn;
            
            // Record timeout as incorrect answer
            if ($currentTurn === 1) {
                MultiplayerGame::where('id', $gameId)->increment('total_questions_p1');
                MultiplayerGame::where('id', $gameId)->update(['player_one_streak' => 0]);
            } else {
                MultiplayerGame::where('id', $gameId)->increment('total_questions_p2');
                MultiplayerGame::where('id', $gameId)->update(['player_two_streak' => 0]);
            }
            
            // Switch turn
            $newTurn = $currentTurn === 1 ? 2 : 1;
            MultiplayerGame::where('id', $gameId)->update(['current_turn' => $newTurn]);
            
            // Refresh game model
            $game->refresh();
            
            // Update accuracies
            $game->player_one_accuracy = $game->getPlayerOneAccuracy();
            $game->player_two_accuracy = $game->getPlayerTwoAccuracy();
            $game->save();
            
            \Log::info('Timeout processed successfully', [
                'game_id' => $gameId,
                'old_turn' => $currentTurn,
                'new_turn' => $game->current_turn
            ]);
            
            // Broadcast timeout event
            $this->broadcastSimpleTimeoutEvent($game, $currentPlayerId, $currentPlayerName);
            
            // Only start timer for next turn if game is still active and has available questions
            if ($game->isActive() && $game->getAvailableQuizzes()->count() > 0) {
                \Log::info('Starting new timer after timeout for next turn', [
                    'game_id' => $game->id,
                    'new_turn' => $game->current_turn,
                    'available_quizzes' => $game->getAvailableQuizzes()->count()
                ]);
                $this->startTimer($game->id);
            } else {
                \Log::info('Game ended after timeout, not starting new timer', [
                    'game_id' => $game->id,
                    'status' => $game->status->value,
                    'available_quizzes' => $game->getAvailableQuizzes()->count()
                ]);
            }
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
     * Broadcast timeout event with simplified player data
     */
    protected function broadcastSimpleTimeoutEvent(MultiplayerGame $game, int $playerId, string $playerName): void
    {
        try {
            $timeoutData = [
                'timed_out_player' => $playerId,
                'timed_out_player_name' => $playerName,
                'current_turn' => $game->current_turn,
                'message' => $playerName . ' timed out!',
                'timestamp' => now()->timestamp,
            ];

            // Primary broadcast
            broadcast(new \App\Events\MultiplayerGameUpdated($game, 'timer_timeout', $timeoutData));
            
            \Log::info('Timeout event broadcasted', [
                'game_id' => $game->id,
                'timed_out_player' => $playerId,
                'current_turn' => $game->current_turn
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Timeout broadcast failed', [
                'game_id' => $game->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Broadcast timeout event with retry mechanism (legacy method)
     */
    protected function broadcastTimeoutEvent(MultiplayerGame $game, $currentPlayer): void
    {
        try {
            $timeoutData = [
                'timed_out_player' => $currentPlayer->id,
                'timed_out_player_name' => $currentPlayer->first_name,
                'current_turn' => $game->current_turn,
                'message' => $currentPlayer->first_name . ' timed out!',
                'timestamp' => now()->timestamp,
            ];

            // Primary broadcast
            broadcast(new \App\Events\MultiplayerGameUpdated($game, 'timer_timeout', $timeoutData));
            
            \Log::info('Timeout event broadcasted', [
                'game_id' => $game->id,
                'timed_out_player' => $currentPlayer->id,
                'current_turn' => $game->current_turn
            ]);
            
            // Ensure broadcast with a slight delay as backup
            dispatch(function () use ($game, $timeoutData) {
                try {
                    broadcast(new \App\Events\MultiplayerGameUpdated($game, 'timer_timeout', array_merge($timeoutData, [
                        'backup_broadcast' => true
                    ])));
                } catch (\Exception $e) {
                    \Log::error('Backup timeout broadcast failed', [
                        'game_id' => $game->id,
                        'error' => $e->getMessage()
                    ]);
                }
            })->delay(now()->addSeconds(1));
            
        } catch (\Exception $e) {
            \Log::error('Primary timeout broadcast failed', [
                'game_id' => $game->id,
                'timed_out_player' => $currentPlayer->id,
                'error' => $e->getMessage()
            ]);
            
            // Final fallback - try simple broadcast
            try {
                broadcast(new \App\Events\MultiplayerGameUpdated($game, 'timer_timeout', [
                    'timed_out_player' => $currentPlayer->id,
                    'timed_out_player_name' => $currentPlayer->first_name,
                    'fallback_broadcast' => true,
                ]));
            } catch (\Exception $finalError) {
                \Log::critical('All timeout broadcasts failed', [
                    'game_id' => $game->id,
                    'error' => $finalError->getMessage()
                ]);
            }
        }
    }

    /**
     * Force timeout for a game (public method for controller fallback)
     */
    public function forceTimeout(int $gameId): bool
    {
        try {
            $game = MultiplayerGame::find($gameId);
            if (!$game || !$game->isActive()) {
                return false;
            }
            
            \Log::info('Forcing timeout via public method', [
                'game_id' => $gameId,
                'current_turn' => $game->current_turn
            ]);
            
            $this->handleTimeout($game);
            return true;
        } catch (\Exception $e) {
            \Log::error('Force timeout failed', [
                'game_id' => $gameId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get timer status for a game (for API responses)
     */
    public function getTimerStatus(int $gameId): array
    {
        $timerKey = "game_timer_{$gameId}";
        $timerData = Cache::get($timerKey);
        $isRunning = $this->isTimerRunning($gameId);
        $remainingTime = $this->getRemainingTime($gameId);
        
        // Get the actual duration from timer data or calculate it
        $duration = self::BASE_TIMER_DURATION;
        if ($timerData && isset($timerData['duration'])) {
            $duration = $timerData['duration'];
        } else {
            // Calculate duration for the current game
            $game = MultiplayerGame::find($gameId);
            if ($game) {
                $duration = $this->calculateTimerDuration($game);
            }
        }
        
        // Check if we're in grace period
        $inGracePeriod = false;
        if ($timerData && $remainingTime <= 0) {
            $elapsedTime = now()->timestamp - $timerData['started_at'];
            $gracePeriodEnd = $timerData['duration'] + self::TIMER_GRACE_PERIOD;
            $inGracePeriod = $elapsedTime <= $gracePeriodEnd;
        }
        
        return [
            'is_running' => $isRunning,
            'remaining_time' => $remainingTime,
            'duration' => $duration,
            'grace_period' => self::TIMER_GRACE_PERIOD,
            'in_grace_period' => $inGracePeriod,
            'submission_allowed' => $this->isSubmissionAllowed($gameId),
            'last_activity' => $this->getLastActivity($gameId),
            'question_type' => $timerData['question_type'] ?? 'unknown',
        ];
    }
    
    /**
     * Clean up expired timers and activities, including stuck timers
     */
    public function cleanupExpiredTimers(): void
    {
        // Cache will automatically expire, but we can force cleanup if needed
        $activeGames = MultiplayerGame::active()->get();
        
        foreach ($activeGames as $game) {
            $gameId = $game->id;
            $timerKey = "game_timer_{$gameId}";
            $timerData = Cache::get($timerKey);
            
            if (!$timerData) {
                // No timer data but game is active with a turn - check if this is really stuck
                if ($game->current_turn > 0 && $game->getAvailableQuizzes()->count() > 0) {
                    // Check if this might be a recent timeout being processed
                    $activityKey = "game_activity_{$gameId}";
                    $lastActivity = Cache::get($activityKey);
                    $timeSinceLastActivity = $lastActivity ? (now()->timestamp - $lastActivity) : 0;
                    
                    // Only recover if it's been stuck for more than 30 seconds
                    // This prevents interfering with normal timeout processing
                    if ($timeSinceLastActivity > 30) {
                        \Log::warning('Found genuinely stuck game, recovering', [
                            'game_id' => $gameId,
                            'current_turn' => $game->current_turn,
                            'status' => $game->status->value,
                            'time_since_activity' => $timeSinceLastActivity
                        ]);
                        
                        // Start timer to recover from stuck state
                        $this->startTimer($gameId, false);
                    }
                }
                
                // Clean up activity cache
                Cache::forget("game_activity_{$gameId}");
            } else {
                // Check if timer is stuck (running way too long)
                $elapsedTime = now()->timestamp - $timerData['started_at'];
                $totalTimeWithGrace = $timerData['duration'] + self::TIMER_GRACE_PERIOD + 30; // Extra 30s buffer
                
                if ($elapsedTime > $totalTimeWithGrace) {
                    \Log::warning('Found stuck timer during cleanup, forcing timeout', [
                        'game_id' => $gameId,
                        'elapsed_time' => $elapsedTime,
                        'expected_max' => $totalTimeWithGrace,
                        'current_turn' => $game->current_turn
                    ]);
                    
                    // Force timeout for stuck timer
                    $this->handleTimeout($game);
                } else if (!$this->isTimerRunning($gameId)) {
                    // Timer not running, clean up
                    Cache::forget($timerKey);
                    Cache::forget("game_activity_{$gameId}");
                }
            }
        }
    }
}