<?php

namespace App\Services;

use App\Monster;

class BattleDamageCalculator
{
    private const PLAYER_BASE_DAMAGE_MIN = 15;
    private const PLAYER_BASE_DAMAGE_MAX = 25;
    private const MONSTER_DAMAGE_VARIANCE = 5;

    /**
     * Calculate damage dealt by player to monster.
     */
    public function calculatePlayerDamage(Monster $monster): int
    {
        $baseDamage = rand(self::PLAYER_BASE_DAMAGE_MIN, self::PLAYER_BASE_DAMAGE_MAX);
        return $monster->takeDamage($baseDamage);
    }

    /**
     * Calculate damage dealt by monster to player.
     */
    public function calculateMonsterDamage(Monster $monster): int
    {
        $baseAttack = $monster->attack;
        return rand(
            max(1, $baseAttack - self::MONSTER_DAMAGE_VARIANCE),
            $baseAttack + self::MONSTER_DAMAGE_VARIANCE
        );
    }

    /**
     * Calculate critical hit damage (future enhancement).
     */
    public function calculateCriticalHit(int $baseDamage, float $criticalMultiplier = 1.5): int
    {
        return (int) round($baseDamage * $criticalMultiplier);
    }

    /**
     * Check if attack is a critical hit (future enhancement).
     */
    public function isCriticalHit(float $criticalChance = 0.1): bool
    {
        return mt_rand() / mt_getrandmax() <= $criticalChance;
    }
}
