<?php

namespace App\Http\Controllers;

use App\Models\Quest;
use App\Services\QuestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class QuestController extends Controller
{
    public function __construct(
        private QuestService $questService
    ) {}

    /**
     * Display a listing of quests for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();

        // Assign daily quests if not already assigned
        $this->questService->assignDailyQuests($user);

        // Get user's quest summary
        $questSummary = $this->questService->getUserQuestSummary($user);

        // Get all active quests with user progress
        $allQuests = Quest::active()
            ->with(['users' => function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->wherePivot('assigned_date', today());
            }])
            ->get()
            ->map(function ($quest) use ($user) {
                $userQuest = $quest->users->first();
                $quest->user_progress = $userQuest ? $userQuest->pivot : null;
                unset($quest->users); // Remove the relation to clean up the response
                return $quest;
            });

        // Separate quests by type
        $dailyQuests = $allQuests->where('type', 'daily');
        $weeklyQuests = $allQuests->where('type', 'weekly');
        $oneTimeQuests = $allQuests->where('type', 'one_time');

        return Inertia::render('Quests/Index', [
            'questSummary' => $questSummary,
            'dailyQuests' => $dailyQuests->values(),
            'weeklyQuests' => $weeklyQuests->values(),
            'oneTimeQuests' => $oneTimeQuests->values(),
        ]);
    }

    /**
     * Display quest statistics and history.
     */
    public function stats()
    {
        $user = Auth::user();

        // Get comprehensive quest statistics
        $stats = [
            'total_completed' => $user->completedQuests()->count(),
            'total_assigned' => $user->quests()->count(),
            'completion_rate' => 0,
            'total_xp_earned' => 0,
            'daily_streak' => 0,
        ];

        if ($stats['total_assigned'] > 0) {
            $stats['completion_rate'] = round(($stats['total_completed'] / $stats['total_assigned']) * 100);
        }

        // Calculate total XP earned from completed quests
        $completedQuests = $user->completedQuests()->get();
        $stats['total_xp_earned'] = $completedQuests->sum('experience_reward');

        // Calculate daily quest completion streak
        $dailyStreak = 0;
        $currentDate = today();

        while (true) {
            $hasCompletedDaily = $user->quests()
                ->where('type', 'daily')
                ->wherePivot('assigned_date', $currentDate)
                ->wherePivot('is_completed', true)
                ->exists();

            if ($hasCompletedDaily) {
                $dailyStreak++;
                $currentDate = $currentDate->subDay();
            } else {
                break;
            }
        }

        $stats['daily_streak'] = $dailyStreak;

        // Get recent quest completions
        $recentCompletions = $user->completedQuests()
            ->orderByPivot('completed_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($quest) {
                return [
                    'title' => $quest->title,
                    'type' => $quest->type,
                    'category' => $quest->category,
                    'experience_reward' => $quest->experience_reward,
                    'completed_at' => $quest->pivot->completed_at,
                ];
            });

        // Get quest completion by category
        $completionsByCategory = $user->completedQuests()
            ->groupBy('category')
            ->map(function ($quests, $category) {
                return [
                    'category' => $category,
                    'count' => $quests->count(),
                    'total_xp' => $quests->sum('experience_reward'),
                ];
            })
            ->values();

        return Inertia::render('Quests/Stats', [
            'stats' => $stats,
            'recentCompletions' => $recentCompletions,
            'completionsByCategory' => $completionsByCategory,
        ]);
    }

    /**
     * Get quest progress for API calls.
     */
    public function progress(Request $request)
    {
        $user = Auth::user();
        $questSummary = $this->questService->getUserQuestSummary($user);

        // Return JSON for AJAX requests, redirect to appropriate page for browser requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($questSummary);
        }

        // Redirect to quest stats page for browser requests
        return redirect()->route('quests.stats')->with('error', 'This endpoint is only available for AJAX requests.');
    }
}
