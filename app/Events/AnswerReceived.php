<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnswerReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $answerData;
    public $gameId;

    public function __construct($answerData, $gameId)
    {
        $this->answerData = $answerData;
        $this->gameId = $gameId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('multiplayer-game.' . $this->gameId);
    }

    public function broadcastWith()
    {
        return $this->answerData;
    }
}
