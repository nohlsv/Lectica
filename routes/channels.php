<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\MultiplayerGame;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private channel for multiplayer games
Broadcast::channel('multiplayer-game.{gameId}', function ($user, $gameId) {
    $game = MultiplayerGame::find($gameId);

    // Only allow players who are part of this game
    return $game && (
        $game->player_one_id === $user->id ||
        $game->player_two_id === $user->id
    );
});
