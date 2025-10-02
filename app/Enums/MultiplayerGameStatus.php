<?php

namespace App\Enums;

enum MultiplayerGameStatus: string
{
    case WAITING = 'waiting';
    case ACTIVE = 'active';
    case FINISHED = 'finished';
    case ABANDONED = 'abandoned';
    case FORFEITED = 'forfeited';

    public function label(): string
    {
        return match($this) {
            self::WAITING => 'Waiting for Player',
            self::ACTIVE => 'Active',
            self::FINISHED => 'Finished',
            self::ABANDONED => 'Abandoned',
            self::FORFEITED => 'Forfeited',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::WAITING => 'yellow',
            self::ACTIVE => 'blue',
            self::FINISHED => 'green',
            self::ABANDONED => 'gray',
            self::FORFEITED => 'red',
        };
    }

    public function isFinished(): bool
    {
        return in_array($this, [self::FINISHED, self::ABANDONED, self::FORFEITED]);
    }

    public static function finished(): array
    {
        return [self::FINISHED, self::ABANDONED, self::FORFEITED];
    }
}
