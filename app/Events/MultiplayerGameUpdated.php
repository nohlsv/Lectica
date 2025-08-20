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

    public MultiplayerGame $game;

    public function __construct(MultiplayerGame $game)
    {
        $this->game = $game;
    }

    public function broadcastOn()
    {
        return new Channel('game.' . $this->game->id);
    }

}

