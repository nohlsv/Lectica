<?php

namespace App\Events;

use App\Models\MultiplayerGame;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MultiplayerGameUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $game;
    public $eventType;

    public function __construct(MultiplayerGame $game, string $eventType = 'updated')
    {
        $this->game = $game->load(['playerOne', 'playerTwo', 'file', 'collection']);
        $this->eventType = $eventType;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('multiplayer-game.' . $this->game->id);
    }

    public function broadcastWith()
    {
        $gameData = $this->game->toArray();

        // Add monster data if it's a PvE game without modifying the model instance
        if ($this->game->isPve() && $this->game->monster_id) {
            $monster = \App\Models\Monster::find($this->game->monster_id);
            $gameData['monster'] = $monster ? $monster->toArray() : null;
        }

        // Add the current question to the broadcast data
        $currentQuestion = $this->game->getCurrentQuestion();
        $gameData['currentQuestion'] = $currentQuestion ? $currentQuestion->toArray() : null;

        return [
            'game' => $gameData,
            'event_type' => $this->eventType,
            'timestamp' => now()->toISOString(),
        ];
    }
}
