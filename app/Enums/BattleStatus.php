<?php

namespace App\Enums;

enum BattleStatus: string
{
    case ACTIVE = 'active';
    case WON = 'won';
    case LOST = 'lost';
    case ABANDONED = 'abandoned';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::WON => 'Victory',
            self::LOST => 'Defeat',
            self::ABANDONED => 'Abandoned',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::ACTIVE => 'blue',
            self::WON => 'green',
            self::LOST => 'red',
            self::ABANDONED => 'gray',
        };
    }

    public function isFinished(): bool
    {
        return in_array($this, [self::WON, self::LOST, self::ABANDONED]);
    }

    public static function finished(): array
    {
        return [self::WON, self::LOST, self::ABANDONED];
    }
}
