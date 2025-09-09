<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MultiplayerGame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    // General leaderboard: users ranked by level
    public function general()
    {
        $users = User::select('id', 'first_name', 'last_name', 'level', 'experience')
            ->orderByDesc('level')
            ->orderByDesc('experience')
            ->limit(50)
            ->get();
        return response()->json($users->values());
    }

    // Multiplayer leaderboard: users ranked by total multiplayer wins
    public function multiplayer()
    {
        $leaderboard = MultiplayerGame::select(
                'winner_id',
                DB::raw('COUNT(*) as wins'),
                'users.first_name',
                'users.last_name',
                'users.level',
                'users.id as id'
            )
            ->whereNotNull('winner_id')
            ->join('users', 'users.id', '=', 'multiplayer_games.winner_id')
            ->groupBy('winner_id', 'users.first_name', 'users.last_name', 'users.level', 'users.id')
            ->orderByDesc('wins')
            ->limit(50)
            ->get();

        return response()->json($leaderboard->values());
    }
}

