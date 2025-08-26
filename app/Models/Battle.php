<?php

namespace App\Models;

use App\Models\Monster;
use App\Enums\BattleStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Battle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'monster_id',
        'file_id',
        'status',
        'player_hp',
        'monster_hp',
        'correct_answers',
        'total_questions'
    ];

    protected $casts = [
        'player_hp' => 'integer',
        'monster_hp' => 'integer',
        'correct_answers' => 'integer',
        'total_questions' => 'integer',
        'status' => BattleStatus::class,
    ];

    /**
     * Get the user that owns the battle.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the file used in the battle.
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the monster data from config (not a database relationship).
     */
    public function monster(): Attribute
    {
        return Attribute::make(
            get: fn () => Monster::find($this->monster_id)
        );
    }

    /**
     * Check if the battle is active.
     */
    public function isActive(): bool
    {
        return $this->status === BattleStatus::ACTIVE;
    }

    /**
     * Check if the battle is finished.
     */
    public function isFinished(): bool
    {
        return $this->status->isFinished();
    }

    /**
     * Get the accuracy percentage.
     */
    public function getAccuracy(): float
    {
        if ($this->total_questions === 0) {
            return 0;
        }

        return round(($this->correct_answers / $this->total_questions) * 100, 2);
    }

    /**
     * Check if player won the battle.
     */
    public function isVictory(): bool
    {
        return $this->status === BattleStatus::WON;
    }

    /**
     * Check if player lost the battle.
     */
    public function isDefeat(): bool
    {
        return $this->status === BattleStatus::LOST;
    }

    /**
     * Mark battle as won.
     */
    public function markAsWon(): void
    {
        $this->update(['status' => BattleStatus::WON]);
    }

    /**
     * Mark battle as lost.
     */
    public function markAsLost(): void
    {
        $this->update(['status' => BattleStatus::LOST]);
    }

    /**
     * Mark battle as abandoned.
     */
    public function markAsAbandoned(): void
    {
        $this->update(['status' => BattleStatus::ABANDONED]);
    }

    /**
     * Scope to get active battles.
     */
    public function scopeActive($query)
    {
        return $query->where('status', BattleStatus::ACTIVE);
    }

    /**
     * Scope to get finished battles.
     */
    public function scopeFinished($query)
    {
        return $query->whereIn('status', BattleStatus::finished());
    }

    /**
     * Scope to get battles by status.
     */
    public function scopeByStatus($query, BattleStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get the status badge color for UI.
     */
    public function getStatusColor(): string
    {
        return $this->status->color();
    }

    /**
     * Get the status label for UI.
     */
    public function getStatusLabel(): string
    {
        return $this->status->label();
    }
}
