<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\MultiplayerGame;

class MultiplayerGameLobbyUpdate implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public MultiplayerGame $game;
    /**
     * Create a new event instance.
     */
    public function __construct(MultiplayerGame $game )
    {
        $this->game = $game;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('game.lobby'),
        ];
    }

}
