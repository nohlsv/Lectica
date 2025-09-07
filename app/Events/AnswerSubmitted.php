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

class AnswerSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $game;
    public $quizId;
    public $answer;
    public $isCorrect;
    public $playerId;

    public function __construct(MultiplayerGame $game, $quizId, $answer, $isCorrect, $playerId)
    {
        $this->game = $game;
        $this->quizId = $quizId;
        $this->answer = $answer;
        $this->isCorrect = $isCorrect;
        $this->playerId = $playerId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('multiplayer-game.' . $this->game->id);
    }

    public function broadcastWith()
    {
        return [
            'quiz_id' => $this->quizId,
            'answer' => $this->answer,
            'is_correct' => $this->isCorrect,
            'player_id' => $this->playerId,
        ];
    }
}
