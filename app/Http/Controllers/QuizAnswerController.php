<?php

namespace App\Http\Controllers;

use App\Models\QuizAnswer;
use App\Models\Battle;
use App\Models\MultiplayerGame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class QuizAnswerController extends Controller
{
    /**
     * Display the quiz answer history page
     */
    public function index(Request $request)
    {
        $query = QuizAnswer::with(['quiz.file', 'user'])
            ->where('user_id', Auth::id())
            ->orderBy('answered_at', 'desc');

        // Filter by context type if provided
        if ($request->has('context_type') && in_array($request->context_type, ['battle', 'multiplayer'])) {
            $query->where('context_type', $request->context_type);
        }

        // Filter by correctness if provided
        if ($request->has('is_correct') && $request->is_correct !== '') {
            $query->where('is_correct', (bool) $request->is_correct);
        }

        // Search by quiz content or answer
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('quiz', function ($quizQuery) use ($search) {
                    $quizQuery->where('question', 'like', "%{$search}%")
                        ->orWhere('correct_answer', 'like', "%{$search}%");
                })->orWhere('user_answer', 'like', "%{$search}%");
            });
        }

        $answers = $query->paginate(20);

        // Add context information for each answer
        $answers->getCollection()->transform(function ($answer) {
            // Get context details based on context_type
            if ($answer->context_type === 'battle' && $answer->context_id) {
                $battle = Battle::with('monster')->find($answer->context_id);
                $answer->context_details = $battle ? [
                    'type' => 'Battle',
                    'name' => "Battle vs {$battle->monster->name}",
                    'status' => $battle->status,
                    'route' => route('battles.show', $battle->id)
                ] : null;
            } elseif ($answer->context_type === 'multiplayer' && $answer->context_id) {
                $game = MultiplayerGame::find($answer->context_id);
                $answer->context_details = $game ? [
                    'type' => 'Multiplayer',
                    'name' => "Multiplayer Game",
                    'status' => $game->status,
                    'route' => route('multiplayer-games.show', $game->id)
                ] : null;
            } else {
                $answer->context_details = null;
            }
            
            return $answer;
        });

        // Get statistics
        $stats = [
            'total_answers' => QuizAnswer::where('user_id', Auth::id())->count(),
            'correct_answers' => QuizAnswer::where('user_id', Auth::id())->where('is_correct', true)->count(),
            'battle_answers' => QuizAnswer::where('user_id', Auth::id())->where('context_type', 'battle')->count(),
            'multiplayer_answers' => QuizAnswer::where('user_id', Auth::id())->where('context_type', 'multiplayer')->count(),
        ];
        
        $stats['accuracy'] = $stats['total_answers'] > 0 
            ? round(($stats['correct_answers'] / $stats['total_answers']) * 100, 2) 
            : 0;

        return Inertia::render('QuizAnswers/Index', [
            'answers' => $answers,
            'stats' => $stats,
            'filters' => $request->only(['context_type', 'is_correct', 'search'])
        ]);
    }

    /**
     * Show quiz answers for a specific battle
     */
    public function battleAnswers(Battle $battle)
    {
        // Check if user owns this battle
        if ($battle->user_id !== Auth::id()) {
            abort(403, 'You can only view your own battle answers.');
        }

        $answers = QuizAnswer::with(['quiz'])
            ->where('context_type', 'battle')
            ->where('context_id', $battle->id)
            ->where('user_id', Auth::id())
            ->orderBy('answered_at', 'asc')
            ->get();

        $stats = [
            'total_questions' => $answers->count(),
            'correct_answers' => $answers->where('is_correct', true)->count(),
            'accuracy' => $answers->count() > 0 ? round(($answers->where('is_correct', true)->count() / $answers->count()) * 100, 2) : 0
        ];

        return Inertia::render('QuizAnswers/BattleAnswers', [
            'battle' => $battle->load(['monster', 'file', 'collection']),
            'answers' => $answers,
            'stats' => $stats
        ]);
    }

    /**
     * Show quiz answers for a specific multiplayer game
     */
    public function multiplayerAnswers(MultiplayerGame $game)
    {
        // Check if user participated in this game
        if ($game->player_one_id !== Auth::id() && $game->player_two_id !== Auth::id()) {
            abort(403, 'You can only view answers from games you participated in.');
        }

        $answers = QuizAnswer::with(['quiz'])
            ->where('context_type', 'multiplayer')
            ->where('context_id', $game->id)
            ->where('user_id', Auth::id())
            ->orderBy('answered_at', 'asc')
            ->get();

        // Get all participants' answers for comparison
        $allAnswers = QuizAnswer::with(['quiz', 'user'])
            ->where('context_type', 'multiplayer')
            ->where('context_id', $game->id)
            ->orderBy('answered_at', 'asc')
            ->get()
            ->groupBy('user_id');

        $stats = [
            'total_questions' => $answers->count(),
            'correct_answers' => $answers->where('is_correct', true)->count(),
            'accuracy' => $answers->count() > 0 ? round(($answers->where('is_correct', true)->count() / $answers->count()) * 100, 2) : 0
        ];

        $game->load(['playerOne', 'playerTwo', 'file', 'collection']);
        
        // Manually create participants array to ensure it's properly serialized
        $participants = [];
        if ($game->playerOne) {
            $participants[] = [
                'user_id' => $game->player_one_id,
                'user' => $game->playerOne
            ];
        }
        if ($game->playerTwo) {
            $participants[] = [
                'user_id' => $game->player_two_id,
                'user' => $game->playerTwo
            ];
        }
        $game->participants = $participants;
        
        return Inertia::render('QuizAnswers/MultiplayerAnswers', [
            'game' => $game,
            'answers' => $answers,
            'allAnswers' => $allAnswers,
            'stats' => $stats
        ]);
    }

    /**
     * Show detailed view of a specific answer
     */
    public function show(QuizAnswer $answer)
    {
        // Check if user owns this answer
        if ($answer->user_id !== Auth::id()) {
            abort(403, 'You can only view your own answers.');
        }

        $answer->load(['quiz.file', 'user']);

        // Get context information
        $context = null;
        if ($answer->context_type === 'battle' && $answer->context_id) {
            $context = Battle::with(['monster', 'file', 'collection'])->find($answer->context_id);
        } elseif ($answer->context_type === 'multiplayer' && $answer->context_id) {
            $context = MultiplayerGame::with(['playerOne', 'playerTwo', 'file', 'collection'])->find($answer->context_id);
            if ($context) {
                // Manually create participants array to ensure it's properly serialized
                $participants = [];
                if ($context->playerOne) {
                    $participants[] = [
                        'user_id' => $context->player_one_id,
                        'user' => $context->playerOne
                    ];
                }
                if ($context->playerTwo) {
                    $participants[] = [
                        'user_id' => $context->player_two_id,
                        'user' => $context->playerTwo
                    ];
                }
                $context->participants = $participants;
            }
        }

        return Inertia::render('QuizAnswers/Show', [
            'answer' => $answer,
            'context' => $context
        ]);
    }

    /**
     * Get answer statistics for dashboard
     */
    public function getStats()
    {
        $userId = Auth::id();
        
        $totalAnswers = QuizAnswer::where('user_id', $userId)->count();
        $correctAnswers = QuizAnswer::where('user_id', $userId)->where('is_correct', true)->count();
        
        // Get answers by context type
        $battleAnswers = QuizAnswer::where('user_id', $userId)->where('context_type', 'battle')->count();
        $multiplayerAnswers = QuizAnswer::where('user_id', $userId)->where('context_type', 'multiplayer')->count();
        
        // Get recent activity (last 7 days)
        $recentAnswers = QuizAnswer::where('user_id', $userId)
            ->where('answered_at', '>=', now()->subDays(7))
            ->count();
        
        // Get accuracy by context type
        $battleCorrect = QuizAnswer::where('user_id', $userId)
            ->where('context_type', 'battle')
            ->where('is_correct', true)
            ->count();
        
        $multiplayerCorrect = QuizAnswer::where('user_id', $userId)
            ->where('context_type', 'multiplayer')
            ->where('is_correct', true)
            ->count();

        return response()->json([
            'total_answers' => $totalAnswers,
            'correct_answers' => $correctAnswers,
            'overall_accuracy' => $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 2) : 0,
            'battle_answers' => $battleAnswers,
            'battle_accuracy' => $battleAnswers > 0 ? round(($battleCorrect / $battleAnswers) * 100, 2) : 0,
            'multiplayer_answers' => $multiplayerAnswers,
            'multiplayer_accuracy' => $multiplayerAnswers > 0 ? round(($multiplayerCorrect / $multiplayerAnswers) * 100, 2) : 0,
            'recent_activity' => $recentAnswers
        ]);
    }
}
