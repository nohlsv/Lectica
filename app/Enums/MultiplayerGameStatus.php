<?php

namespace App\Enums;

enum MultiplayerGameStatus: string
{
    case WAITING = 'waiting';
    case ACTIVE = 'active';
    case FINISHED = 'finished';
    case ABANDONED = 'abandoned';

    public function label(): string
    {
        return match($this) {
            self::WAITING => 'Waiting for Player',
            self::ACTIVE => 'Active',
            self::FINISHED => 'Finished',
            self::ABANDONED => 'Abandoned',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::WAITING => 'yellow',
            self::ACTIVE => 'blue',
            self::FINISHED => 'green',
            self::ABANDONED => 'gray',
        };
    }

    public function isFinished(): bool
    {
        return in_array($this, [self::FINISHED, self::ABANDONED]);
    }

    public static function finished(): array
    {
        return [self::FINISHED, self::ABANDONED];
    }
}
