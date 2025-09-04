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
        return new Channel('multiplayer-game.' . $this->game->id);
    }

    public function broadcastWith()
    {
        return [
            'game' => $this->game,
            'event_type' => $this->eventType,
            'timestamp' => now()->toISOString(),
        ];
    }
}
