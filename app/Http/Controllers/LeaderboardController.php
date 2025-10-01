<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\MultiplayerGame;
use App\Services\StudyStreakService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    // General leaderboard: users ranked by level
    public function general(Request $request)
    {
        $query = User::select('users.id', 'users.first_name', 'users.last_name', 'users.level', 'users.experience', 'programs.college')
            ->join('programs', 'users.program_id', '=', 'programs.id');

        // Filter by college if specified
        if ($request->has('college') && $request->college !== 'all') {
            $query->where('programs.college', $request->college);
        }

        $users = $query->orderByDesc('users.level')
            ->orderByDesc('users.experience')
            ->limit(50)
            ->get();
            
        return response()->json($users->values());
    }

    // Multiplayer leaderboard: users ranked by total multiplayer wins
    public function multiplayer(Request $request)
    {
        $query = MultiplayerGame::select(
                'winner_id',
                DB::raw('COUNT(*) as wins'),
                'users.first_name',
                'users.last_name',
                'users.level',
                'users.id as id',
                'programs.college'
            )
            ->whereNotNull('winner_id')
            ->join('users', 'users.id', '=', 'multiplayer_games.winner_id')
            ->join('programs', 'users.program_id', '=', 'programs.id');

        // Filter by college if specified
        if ($request->has('college') && $request->college !== 'all') {
            $query->where('programs.college', $request->college);
        }

        $leaderboard = $query->groupBy('winner_id', 'users.first_name', 'users.last_name', 'users.level', 'users.id', 'programs.college')
            ->orderByDesc('wins')
            ->limit(50)
            ->get();

        return response()->json($leaderboard->values());
    }

    // Study streaks leaderboard: users ranked by current and longest streaks
    public function streaks(Request $request)
    {
        $streakService = new StudyStreakService();
        
        $query = User::select('users.id', 'users.first_name', 'users.last_name', 'users.level', 'programs.college')
            ->join('programs', 'users.program_id', '=', 'programs.id');

        // Filter by college if specified
        if ($request->has('college') && $request->college !== 'all') {
            $query->where('programs.college', $request->college);
        }

        $users = $query->get();
            
        $streakData = [];
        
        foreach ($users as $user) {
            $stats = $streakService->getStreakStats($user);
            
            // Only include users with some streak activity
            if ($stats['current_streak'] > 0 || $stats['longest_streak'] > 0 || $stats['total_study_days'] > 0) {
                $streakData[] = [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'level' => $user->level,
                    'college' => $user->college,
                    'current_streak' => $stats['current_streak'],
                    'longest_streak' => $stats['longest_streak'],
                    'total_study_days' => $stats['total_study_days'],
                ];
            }
        }
        
        // Sort by longest streak first, then current streak, then total study days
        usort($streakData, function ($a, $b) {
            if ($a['longest_streak'] === $b['longest_streak']) {
                if ($a['current_streak'] === $b['current_streak']) {
                    return $b['total_study_days'] - $a['total_study_days'];
                }
                return $b['current_streak'] - $a['current_streak'];
            }
            return $b['longest_streak'] - $a['longest_streak'];
        });
        
        // Limit to top 50
        $streakData = array_slice($streakData, 0, 50);
        
        return response()->json(array_values($streakData));
    }

    // Get all available colleges for filtering
    public function colleges()
    {
        $colleges = DB::table('programs')
            ->select('college')
            ->distinct()
            ->whereNotNull('college')
            ->orderBy('college')
            ->pluck('college');

        return response()->json($colleges->values());
    }
}

