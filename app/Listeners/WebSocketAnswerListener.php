<?php

namespace App\Listeners;

use App\Services\MultiplayerGameService;
use Illuminate\Support\Facades\Log;

class WebSocketAnswerListener
{
    protected $gameService;

    public function __construct(MultiplayerGameService $gameService)
    {
        $this->gameService = $gameService;
    }

    public function handle($event)
    {
        // Check if this is an answer-submitted whisper event
        if (isset($event->whisper) && $event->whisper === 'answer-submitted') {
            Log::info('WebSocket answer whisper received', $event->data);

            // Process the answer through the game service
            $this->gameService->processAnswer($event->data);
        }
    }
}
