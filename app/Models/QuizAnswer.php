<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class QuizAnswer extends Model
{
    protected $fillable = [
        'user_id',
        'quiz_id',
        'user_answer',
        'is_correct',
        'context_type',
        'context_id',
        'answered_at',
    ];

    protected $casts = [
        'user_answer' => 'json',
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
    ];

    /**
     * Get the user who answered the quiz.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the quiz that was answered.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the context (battle or multiplayer game) for this answer.
     */
    public function context(): MorphTo
    {
        return $this->morphTo('context', 'context_type', 'context_id');
    }

    /**
     * Get battle context if applicable.
     */
    public function battle(): BelongsTo
    {
        return $this->belongsTo(Battle::class, 'context_id')->where('context_type', 'battle');
    }

    /**
     * Get multiplayer game context if applicable.
     */
    public function multiplayerGame(): BelongsTo
    {
        return $this->belongsTo(MultiplayerGame::class, 'context_id')->where('context_type', 'multiplayer');
    }

    /**
     * Scope to get answers for a specific context.
     */
    public function scopeForContext($query, string $type, int $id)
    {
        return $query->where('context_type', $type)->where('context_id', $id);
    }

    /**
     * Scope to get answers by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
