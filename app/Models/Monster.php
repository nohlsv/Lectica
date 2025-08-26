<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Monster extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hp',
        'attack',
        'defense',
        'image_path',
        'difficulty',
        'description',
        'is_active'
    ];

    protected $casts = [
        'hp' => 'integer',
        'attack' => 'integer',
        'defense' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get battles for this monster.
     */
    public function battles(): HasMany
    {
        return $this->hasMany(Battle::class);
    }

    /**
     * Calculate damage dealt to this monster.
     */
    public function takeDamage(int $damage): int
    {
        $actualDamage = max(1, $damage - ($this->defense / 2));
        return (int) round($actualDamage);
    }

    /**
     * Check if monster is defeated.
     */
    public function isDefeated(int $currentHp): bool
    {
        return $currentHp <= 0;
    }

    /**
     * Get monsters by difficulty level.
     */
    public static function getByDifficulty(string $difficulty)
    {
        return self::where('difficulty', $difficulty)
                   ->where('is_active', true)
                   ->orderBy('name')
                   ->get();
    }

    /**
     * Get a random monster by difficulty.
     */
    public static function getRandomByDifficulty(?string $difficulty = null)
    {
        $query = self::where('is_active', true);

        if ($difficulty) {
            $query->where('difficulty', $difficulty);
        }

        return $query->inRandomOrder()->first();
    }

    /**
     * Scope to only include active monsters.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by difficulty and name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('difficulty')
                    ->orderBy('name');
    }

    /**
     * Get the difficulty badge color for UI.
     */
    public function getDifficultyColor(): string
    {
        return match($this->difficulty) {
            'easy' => 'green',
            'medium' => 'yellow',
            'hard' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get the difficulty text for display.
     */
    public function getDifficultyText(): string
    {
        return ucfirst($this->difficulty);
    }
}
