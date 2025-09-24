<?php

namespace App\Models;

use App\Enums\MultiplayerGameStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;

class MultiplayerGame extends Model
{
    /** @use HasFactory<\Database\Factories\GameFactory> */
    use HasFactory;

    protected $fillable = [
        'player_one_id',
        'player_two_id',
        'file_id',
        'collection_id',
        'player_one_hp',
        'player_two_hp',
        'player_one_score',
        'player_two_score',
        'current_turn',
        'current_question_index',
        'status',
        'game_mode', // Add game_mode to fillable
        'correct_answers_p1',
        'correct_answers_p2',
        'total_questions_p1',
        'total_questions_p2',
        // Add accuracy and streak tracking fields
        'player_one_accuracy',
        'player_two_accuracy',
        'player_one_streak',
        'player_two_streak',
        'player_one_max_streak',
        'player_two_max_streak',
        'winner_id',
        'pvp_mode' // Add pvp_mode to fillable
    ];

    protected $casts = [
        'player_one_hp' => 'integer',
        'player_two_hp' => 'integer',
        'player_one_score' => 'integer',
        'player_two_score' => 'integer',
        'current_turn' => 'integer',
        'current_question_index' => 'integer',
        'correct_answers_p1' => 'integer',
        'correct_answers_p2' => 'integer',
        'total_questions_p1' => 'integer',
        'total_questions_p2' => 'integer',
        'status' => MultiplayerGameStatus::class,
    ];

    public function playerOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_one_id');
    }

    public function playerTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_two_id');
    }

    /**
     * Get the file used in the game.
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the collection used in the game.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Get the monster for PVE games.
     */
    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class, 'monster_id');
    }

    public function getPhaseAttribute()
    {
        if (is_null($this->player_two_id)) {
            return 'waiting';
        }
        if ($this->status === MultiplayerGameStatus::FINISHED) {
            return 'finished';
        }
        return 'playing';
    }

    /**
     * Check if the game is waiting for a second player.
     */
    public function isWaiting(): bool
    {
        return $this->status === MultiplayerGameStatus::WAITING;
    }

    /**
     * Check if the game is active.
     */
    public function isActive(): bool
    {
        return $this->status === MultiplayerGameStatus::ACTIVE;
    }

    /**
     * Check if the game is finished.
     */
    public function isFinished(): bool
    {
        return $this->status->isFinished();
    }

    /**
     * Get the accuracy percentage for player one.
     */
    public function getPlayerOneAccuracy(): float
    {
        if ($this->total_questions_p1 === 0) {
            return 0;
        }

        return round(($this->correct_answers_p1 / $this->total_questions_p1) * 100, 2);
    }

    /**
     * Get the accuracy percentage for player two.
     */
    public function getPlayerTwoAccuracy(): float
    {
        if ($this->total_questions_p2 === 0) {
            return 0;
        }

        return round(($this->correct_answers_p2 / $this->total_questions_p2) * 100, 2);
    }

    /**
     * Get the current player whose turn it is.
     */
    public function getCurrentPlayer(): ?User
    {
        if ($this->current_turn === 1) {
            return $this->playerOne;
        } elseif ($this->current_turn === 2) {
            return $this->playerTwo;
        }

        return null;
    }

    /**
     * Switch to the next player's turn.
     */
    public function switchTurn(): void
    {
        // Don't use nested transaction - work within existing transaction
        // Additional validation before switching turns
        if (!$this->isActive()) {
            throw new \Exception('Cannot switch turns on inactive game');
        }

        if (!$this->player_two_id) {
            throw new \Exception('Cannot switch turns without second player');
        }

        $this->current_turn = $this->current_turn === 1 ? 2 : 1;
        $this->touch(); // Update the updated_at timestamp
        $this->save();
    }

    /**
     * Mark game as finished.
     */
    public function markAsFinished(): void
    {
        DB::transaction(function () {
            $this->lockForUpdate();

            // Only mark as finished if not already finished
            if (!$this->isFinished()) {
                $this->update(['status' => MultiplayerGameStatus::FINISHED]);

                // Broadcast the game update to notify all connected clients
                broadcast(new \App\Events\MultiplayerGameUpdated($this->fresh(), 'game_ended'));
            }
        });
    }

    /**
     * Mark game as abandoned.
     */
    public function markAsAbandoned(): void
    {
        DB::transaction(function () {
            $this->lockForUpdate();

            // Only mark as abandoned if not already finished
            if (!$this->isFinished()) {
                $this->update(['status' => MultiplayerGameStatus::ABANDONED]);

                // Broadcast the abandonment to notify remaining player
                broadcast(new \App\Events\MultiplayerGameUpdated($this->fresh(), 'game_abandoned'));
            }
        });
    }

    /**
     * Start the game when second player joins.
     */
    public function startGame(): void
    {
        DB::transaction(function () {
            $this->lockForUpdate();

            // Validate that we can start the game
            if (!$this->isWaiting()) {
                throw new \Exception('Game is not in waiting state');
            }

            if (!$this->player_two_id) {
                throw new \Exception('Cannot start game without second player');
            }

            $this->update([
                'status' => MultiplayerGameStatus::ACTIVE,
                'current_turn' => 1 // Player one starts
            ]);
        });
    }

    /**
     * Check if the current turn is valid for the given player
     */
    public function isPlayerTurn(int $playerId): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->player_one_id === $playerId && $this->current_turn === 1) {
            return true;
        }

        if ($this->player_two_id === $playerId && $this->current_turn === 2) {
            return true;
        }

        return false;
    }

    /**
     * Get the waiting player (opponent) for a given player
     */
    public function getOpponent(int $playerId): ?User
    {
        if ($this->player_one_id === $playerId) {
            return $this->playerTwo;
        }

        if ($this->player_two_id === $playerId) {
            return $this->playerOne;
        }

        return null;
    }

    /**
     * Check if the game has been inactive for too long
     */
    public function isStale(int $minutesThreshold = 30): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return $this->updated_at->diffInMinutes(now()) > $minutesThreshold;
    }

    /**
     * Get game state summary for debugging
     */
    public function getStateDebugInfo(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'current_turn' => $this->current_turn,
            'player_one_id' => $this->player_one_id,
            'player_two_id' => $this->player_two_id,
            'player_one_hp' => $this->player_one_hp,
            'player_two_hp' => $this->player_two_hp,
            'last_updated' => $this->updated_at->toISOString(),
            'minutes_since_update' => $this->updated_at->diffInMinutes(now()),
        ];
    }

    /**
     * Scope to get waiting games.
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', MultiplayerGameStatus::WAITING);
    }

    /**
     * Scope to get active games.
     */
    public function scopeActive($query)
    {
        return $query->where('status', MultiplayerGameStatus::ACTIVE);
    }

    /**
     * Scope to get finished games.
     */
    public function scopeFinished($query)
    {
        return $query->whereIn('status', MultiplayerGameStatus::finished());
    }

    /**
     * Get all quizzes available for this game (from file or collection).
     * Filtered by difficulty if set: easy = true_false, medium = multiple_choice, hard = enumeration
     */
    public function getAvailableQuizzes()
    {
        $quizzes = collect([]);
        
        if ($this->collection_id) {
            // Get quizzes from all files in the collection
            $quizzes = Quiz::whereIn('file_id', $this->collection->files()->pluck('id'))->get();
        } elseif ($this->file_id) {
            // Get quizzes from the single file
            $quizzes = Quiz::where('file_id', $this->file_id)->get();
        }

        // Filter quizzes based on difficulty if set
        if (isset($this->difficulty) && $quizzes->count() > 0) {
            $allowedType = match($this->difficulty) {
                'easy' => 'true_false',
                'medium' => 'multiple_choice',
                'hard' => 'enumeration',
                default => null
            };

            if ($allowedType) {
                $quizzes = $quizzes->where('type', $allowedType);
            }
        }

        return $quizzes;
    }

    /**
     * Get the source name (file name or collection name).
     */
    public function getSourceName(): string
    {
        if ($this->collection_id) {
            return $this->collection->name;
        } elseif ($this->file_id) {
            return $this->file->name;
        }

        return 'Unknown Source';
    }

    /**
     * Get the total number of available questions.
     */
    public function getTotalAvailableQuestions(): int
    {
        return $this->getAvailableQuizzes()->count();
    }

    /**
     * Check if this is a PVP game.
     */
    public function isPvp(): bool
    {
        return $this->game_mode === 'pvp';
    }

    /**
     * Check if this is a PVE game.
     */
    public function isPve(): bool
    {
        return $this->game_mode === 'pve';
    }

    /**
     * Get the current question for both players.
     */
    public function getCurrentQuestion()
    {
        $quizzes = $this->getAvailableQuizzes();

        if ($quizzes->isEmpty() || $this->current_question_index >= $quizzes->count()) {
            return null;
        }

        return $quizzes->get($this->current_question_index);
    }

    /**
     * Advance to the next question for both players.
     */
    public function advanceToNextQuestion(): void
    {
        $totalQuestions = $this->getTotalAvailableQuestions();

        if ($this->current_question_index < $totalQuestions - 1) {
            // Use direct assignment instead of increment() to avoid separate database operations
            $this->current_question_index = $this->current_question_index + 1;
            $this->save();
        } else {
            // Reset to beginning if we've reached the end
            $this->current_question_index = 0;
            $this->save();
        }
    }
}
