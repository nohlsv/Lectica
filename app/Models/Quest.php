<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Quest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'category',
        'requirements',
        'experience_reward',
        'is_active'
    ];

    protected $casts = [
        'requirements' => 'array',
        'experience_reward' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Users who have this quest assigned.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_quests')
                    ->withPivot(['progress', 'target', 'is_completed', 'completed_at', 'assigned_date'])
                    ->withTimestamps();
    }

    /**
     * Get active quests.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get daily quests.
     */
    public function scopeDaily($query)
    {
        return $query->where('type', 'daily');
    }

    /**
     * Get weekly quests.
     */
    public function scopeWeekly($query)
    {
        return $query->where('type', 'weekly');
    }

    /**
     * Get one-time quests.
     */
    public function scopeOneTime($query)
    {
        return $query->where('type', 'one_time');
    }

    /**
     * Get quests by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Check if this is a daily quest.
     */
    public function isDaily(): bool
    {
        return $this->type === 'daily';
    }

    /**
     * Check if this is a weekly quest.
     */
    public function isWeekly(): bool
    {
        return $this->type === 'weekly';
    }

    /**
     * Check if this is a one-time quest.
     */
    public function isOneTime(): bool
    {
        return $this->type === 'one_time';
    }

    /**
     * Get the required count for this quest.
     */
    public function getRequiredCount(): int
    {
        return $this->requirements['count'] ?? 1;
    }

    /**
     * Get the quest icon based on category.
     */
    public function getIcon(): string
    {
        return match($this->category) {
            'battle' => '⚔️',
            'file_upload' => '📁',
            'quiz' => '❓',
            'study' => '📚',
            default => '🎯'
        };
    }
}
