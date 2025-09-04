<?php

namespace App\Models;

use App\Models\Monster;
use App\Enums\MultiplayerGameStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MultiplayerGame extends Model
{
    /** @use HasFactory<\Database\Factories\GameFactory> */
    use HasFactory;

    protected $fillable = [
        'player_one_id',
        'player_two_id',
        'file_id',
        'collection_id',
        'monster_id',
        'player_one_hp',
        'player_two_hp',
        'monster_hp',
        'player_one_score',
        'player_two_score',
        'current_turn',
        'status',
        'game_mode', // Add game_mode to fillable
        'correct_answers_p1',
        'correct_answers_p2',
        'total_questions_p1',
        'total_questions_p2',
    ];

    protected $casts = [
        'player_one_hp' => 'integer',
        'player_two_hp' => 'integer',
        'monster_hp' => 'integer',
        'player_one_score' => 'integer',
        'player_two_score' => 'integer',
        'current_turn' => 'integer',
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
     * Get the monster data from config (not a database relationship).
     */
    public function monster(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->monster_id ? Monster::find($this->monster_id) : null
        );
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
     * Check if player one won the game.
     */
    public function isPlayerOneWinner(): bool
    {
        return $this->isFinished() && $this->player_one_hp > 0 && ($this->player_two_hp <= 0 || $this->monster_hp <= 0);
    }

    /**
     * Check if player two won the game.
     */
    public function isPlayerTwoWinner(): bool
    {
        return $this->isFinished() && $this->player_two_hp > 0 && ($this->player_one_hp <= 0 || $this->monster_hp <= 0);
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
        $this->current_turn = $this->current_turn === 1 ? 2 : 1;
        $this->save();
    }

    /**
     * Mark game as finished.
     */
    public function markAsFinished(): void
    {
        $this->update(['status' => MultiplayerGameStatus::FINISHED]);
    }

    /**
     * Mark game as abandoned.
     */
    public function markAsAbandoned(): void
    {
        $this->update(['status' => MultiplayerGameStatus::ABANDONED]);
    }

    /**
     * Start the game when second player joins.
     */
    public function startGame(): void
    {
        $this->update([
            'status' => MultiplayerGameStatus::ACTIVE,
            'current_turn' => 1 // Player one starts
        ]);
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
     */
    public function getAvailableQuizzes()
    {
        if ($this->collection_id) {
            // Get quizzes from all files in the collection
            return Quiz::whereIn('file_id', $this->collection->files()->pluck('id'))->get();
        } elseif ($this->file_id) {
            // Get quizzes from the single file
            return Quiz::where('file_id', $this->file_id)->get();
        }

        return collect([]);
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
}
