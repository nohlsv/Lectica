<?php

namespace App\Http\Controllers;

use App\Enums\BattleStatus;
use App\Models\Battle;
use App\Models\Monster;
use App\Models\File;
use App\Models\Quiz;
use App\Models\Collection;
use App\Services\BattleService;
use App\Services\QuestService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BattleController extends Controller
{
    public function __construct(
        private BattleService $battleService,
        private QuestService $questService
    ) {}

    /**
     * Display a listing of battles.
     */
    public function index()
    {
        $battles = Battle::with(['file', 'user'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Battles/Index', [
            'battles' => $battles->through(function ($battle) {
                $monster = Monster::find($battle->monster_id);
                $battle->monster = $monster;
                return $battle;
            })
        ]);
    }

    /**
     * Show the form for creating a new battle.
     */
    public function create()
    {
        $monsters = Monster::all();
        $files = File::where('user_id', Auth::id())->get();
        $collections = Collection::where('user_id', Auth::id())
            ->with(['files'])
            ->where('file_count', '>', 0)
            ->get();

        return Inertia::render('Battles/Create', [
            'monsters' => $monsters,
            'files' => $files,
            'collections' => $collections
        ]);
    }

    /**
     * Store a newly created battle.
     */
    public function store(Request $request)
    {
        $request->validate([
            'monster_id' => 'required|string',
            'source_type' => 'required|in:file,collection',
            'file_id' => 'required_if:source_type,file|exists:files,id',
            'collection_id' => 'required_if:source_type,collection|exists:collections,id',
        ]);

        $monster = Monster::find($request->monster_id);
        if (!$monster) {
            return back()->withErrors(['monster_id' => 'Invalid monster selected.']);
        }

        $file = null;
        $collection = null;
        $quizCount = 0;

        if ($request->source_type === 'file') {
            $file = File::findOrFail($request->file_id);

            // Check if user owns the file
            if ($file->user_id !== Auth::id()) {
                abort(403, 'You can only battle with your own files.');
            }

            $quizCount = Quiz::where('file_id', $file->id)->count();
            if ($quizCount === 0) {
                return back()->withErrors(['file_id' => 'This file has no quizzes. Please generate quizzes first.']);
            }
        } else {
            $collection = Collection::findOrFail($request->collection_id);

            // Check if user owns the collection
            if ($collection->user_id !== Auth::id()) {
                abort(403, 'You can only battle with your own collections.');
            }

            $quizCount = $collection->getTotalQuizzesCount();
            if ($quizCount === 0) {
                return back()->withErrors(['collection_id' => 'This collection has no quizzes. Please add files with quizzes first.']);
            }
        }

        $battle = Battle::create([
            'user_id' => Auth::id(),
            'monster_id' => $request->monster_id,
            'file_id' => $file?->id,
            'collection_id' => $collection?->id,
            'status' => 'active',
            'player_hp' => 100,
            'monster_hp' => $monster->hp,
            'correct_answers' => 0,
            'total_questions' => 0
        ]);

        return redirect()->route('battles.show', $battle)
            ->with('success', 'Battle started! Good luck!');
    }

    /**
     * Display the specified battle.
     */
    public function show(Battle $battle)
    {
        // Check if user owns this battle
        if ($battle->user_id !== Auth::id()) {
            abort(403, 'You can only view your own battles.');
        }

        $quizzes = $battle->getAvailableQuizzes();
        $monster = Monster::find($battle->monster_id);

        $quizTypes = [
            'multiple_choice' => 'Multiple Choice',
            'true_false' => 'True/False',
            'enumeration' => 'Enumeration',
        ];

        // If battle is active and has quizzes, show the battle interface
        if ($battle->status === BattleStatus::ACTIVE && $quizzes->count() > 0) {
            return Inertia::render('Battles/BattleQuiz', [
                'battle' => array_merge($battle->toArray(), [
                    'monster' => $monster,
                    'user' => Auth::user(),
                    'source_name' => $battle->getSourceName()
                ]),
                'quizzes' => $quizzes,
                'quizTypes' => $quizTypes,
            ]);
        }

        // Otherwise show the battle results/summary
        return Inertia::render('Battles/Show', [
            'battle' => $battle,
            'monster' => $monster,
            'source_name' => $battle->getSourceName(),
            'quizTypes' => $quizTypes,
        ]);
    }

    /**
     * Handle answering a question in battle
     */
    public function answerQuestion(Request $request, Battle $battle)
    {
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'answer' => 'required|string',
            'is_correct' => 'required|boolean',
        ]);

        // Check if user owns this battle
        if ($battle->user_id !== Auth::id()) {
            abort(403, 'You can only participate in your own battles.');
        }

        // Check if battle is still active
        if ($battle->status !== 'active') {
            return response()->json(['error' => 'Battle is not active'], 400);
        }

        $monster = Monster::find($battle->monster_id);
        $isCorrect = $request->is_correct;

        DB::transaction(function () use ($battle, $request, $isCorrect, $monster) {
            // Update question stats
            $battle->total_questions++;

            if ($isCorrect) {
                $battle->correct_answers++;

                // Player deals damage to monster
                $baseDamage = rand(15, 25);
                $actualDamage = $monster->takeDamage($baseDamage);
                $battle->monster_hp = max(0, $battle->monster_hp - $actualDamage);

                $message = "Correct! You dealt {$actualDamage} damage to the {$monster->name}!";
            } else {
                // Monster deals damage to player
                $monsterDamage = rand($monster->attack - 5, $monster->attack + 5);
                $battle->player_hp = max(0, $battle->player_hp - $monsterDamage);

                $message = "Wrong! The {$monster->name} dealt {$monsterDamage} damage to you!";
            }

            // Check win/loss conditions
            if ($battle->monster_hp <= 0) {
                $battle->status = 'won';
                $message .= " You have defeated the {$monster->name}!";
            } elseif ($battle->player_hp <= 0) {
                $battle->status = 'lost';
                $message .= " You have been defeated by the {$monster->name}!";
            }

            $battle->save();
        });

        // Get next quiz if battle is still active
        $nextQuiz = null;
        if ($battle->status === 'active') {
            $file = File::find($battle->file_id);
            $quizzes = Quiz::where('file_id', $file->id)->get();
            $nextQuiz = $quizzes->random();
        }

        return response()->json([
            'battle' => $battle->fresh(),
            'monster' => $monster,
            'message' => $message ?? 'Battle continues...',
            'nextQuiz' => $nextQuiz,
            'battleEnded' => in_array($battle->status, ['won', 'lost'])
        ]);
    }

    /**
     * Complete battle - called by BattleQuiz.vue
     */
    public function complete(Request $request)
    {
        $request->validate([
            'battle_id' => 'required|exists:battles,id',
            'player_hp' => 'required|integer|min:0',
            'monster_hp' => 'required|integer|min:0',
            'correct_answers' => 'required|integer|min:0',
            'total_questions' => 'required|integer|min:0',
            'status' => 'required|in:victory,defeat',
        ]);

        $battle = Battle::findOrFail($request->battle_id);

        // Check if user owns this battle
        if ($battle->user_id !== Auth::id()) {
            abort(403, 'You can only complete your own battles.');
        }

        $user = Auth::user();

        // Update battle with final results
        $battle->update([
            'player_hp' => $request->player_hp,
            'monster_hp' => $request->monster_hp,
            'correct_answers' => $request->correct_answers,
            'total_questions' => $request->total_questions,
            'status' => $request->status === 'victory' ? 'won' : 'lost',
        ]);

        // Award XP based on battle performance
        $baseXP = $request->status === 'victory' ? 50 : 25; // Victory gives more XP
        $accuracyBonus = round(($request->correct_answers / max(1, $request->total_questions)) * 20); // Up to 20 bonus XP for accuracy
        $totalXP = $baseXP + $accuracyBonus;

        $user->addExperience($totalXP);

        // Update quest progress for completing a battle
        $this->questService->checkQuestCompletion($user, 'battle');

        return response()->json([
            'success' => true,
            'xp_gained' => $totalXP,
            'new_level' => $user->level
        ]);
    }

    /**
     * End a battle
     */
    public function end(Battle $battle)
    {
        // Check if user owns this battle
        if ($battle->user_id !== Auth::id()) {
            abort(403, 'You can only end your own battles.');
        }

        if ($battle->status === 'active') {
            $battle->status = 'abandoned';
            $battle->save();
        }

        return redirect()->route('battles.index')
            ->with('success', 'Battle ended.');
    }

    /**
     * Get battle statistics
     */
    public function stats()
    {
        $userId = Auth::id();

        $stats = [
            'total_battles' => Battle::where('user_id', $userId)->count(),
            'won_battles' => Battle::where('user_id', $userId)->where('status', 'won')->count(),
            'lost_battles' => Battle::where('user_id', $userId)->where('status', 'lost')->count(),
            'active_battles' => Battle::where('user_id', $userId)->where('status', 'active')->count(),
            'abandoned_battles' => Battle::where('user_id', $userId)->where('status', 'abandoned')->count(),
            'total_questions' => Battle::where('user_id', $userId)->sum('total_questions'),
            'correct_answers' => Battle::where('user_id', $userId)->sum('correct_answers'),
        ];

        $stats['win_rate'] = $stats['total_battles'] > 0
            ? round(($stats['won_battles'] / $stats['total_battles']) * 100, 2)
            : 0;

        $stats['accuracy'] = $stats['total_questions'] > 0
            ? round(($stats['correct_answers'] / $stats['total_questions']) * 100, 2)
            : 0;

        // Get recent battles
        $recentBattles = Battle::with(['file'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($battle) {
                $monster = Monster::find($battle->monster_id);
                $battle->monster = $monster;
                return $battle;
            });

        return Inertia::render('Battles/Stats', [
            'stats' => $stats,
            'recentBattles' => $recentBattles
        ]);
    }

    /**
     * Get available monsters by difficulty
     */
    public function getMonstersByDifficulty(Request $request)
    {
        $difficulty = $request->get('difficulty', 'easy');
        $monsters = Monster::getByDifficulty($difficulty);

        return response()->json([
            'monsters' => $monsters
        ]);
    }
}
