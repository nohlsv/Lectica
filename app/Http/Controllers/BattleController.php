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
use App\Services\QuizDifficultyService;
use App\Traits\TracksStudyActivity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BattleController extends Controller
{
    use TracksStudyActivity;
    
    public function __construct(
        private BattleService $battleService,
        private QuestService $questService,
        private QuizDifficultyService $difficultyService
    ) {}

    /**
     * Display a listing of battles.
     */
    public function index()
    {
        $battles = Battle::with(['file', 'user', 'monster'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Battles/Index', [
            'battles' => $battles
        ]);
    }

    /**
     * Show the form for creating a new battle.
     */
    public function create(Request $request)
    {
        $files = File::where('user_id', Auth::id())->get();
        $collections = Collection::where('user_id', Auth::id())
            ->with(['files'])
            ->where('file_count', '>', 0)
            ->get();

        // Get file_id and collection_id from query parameters if provided
        $preselectedFileId = $request->query('file_id');
        $preselectedCollectionId = $request->query('collection_id');
        
        // Validate that the preselected file belongs to the user (if provided)
        if ($preselectedFileId) {
            $validFile = $files->firstWhere('id', $preselectedFileId);
            if (!$validFile) {
                // If the file doesn't belong to the user, don't preselect it
                $preselectedFileId = null;
            }
        }
        
        // Validate that the preselected collection belongs to the user (if provided)
        if ($preselectedCollectionId) {
            $validCollection = $collections->firstWhere('id', $preselectedCollectionId);
            if (!$validCollection) {
                // If the collection doesn't belong to the user, don't preselect it
                $preselectedCollectionId = null;
            }
        }

        return Inertia::render('Battles/Create', [
            'files' => $files,
            'collections' => $collections,
            'preselectedFileId' => $preselectedFileId,
            'preselectedCollectionId' => $preselectedCollectionId
        ]);
    }

    /**
     * Store a newly created battle.
     */
    public function store(Request $request)
    {
        $request->validate([
            'source_type' => 'required|in:file,collection',
            'file_id' => 'required_if:source_type,file|exists:files,id',
            'collection_id' => 'required_if:source_type,collection|exists:collections,id',
            'difficulty' => 'required|in:easy,medium,hard',
        ]);

        // Get a random monster for the initial battle setup
        $monster = Monster::where('is_active', true)->inRandomOrder()->first();
        if (!$monster) {
            return back()->withErrors(['monster_id' => 'No monsters available.']);
        }

        $file = null;
        $collection = null;
        $quizCount = 0;

        if ($request->source_type === 'file') {
            $battle = $this->battleService->createBattle($monster->id, $request->file_id, null, $request->difficulty);
        } else {
            $battle = $this->battleService->createBattle($monster->id, null, $request->collection_id, $request->difficulty);
        }

        // Update quest progress for starting a battle
        $this->questService->updateQuestProgress(Auth::user(), 'battle_start');

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

        // Load the battle with its relationships
        $battle->load(['monster', 'file', 'collection']);

        $quizzes = $battle->getAvailableQuizzes();

        $quizTypes = [
            'multiple_choice' => 'Multiple Choice',
            'true_false' => 'True/False',
            'enumeration' => 'Enumeration',
        ];

        // If battle is active and has quizzes, show the battle interface
        if ($battle->status === BattleStatus::ACTIVE && $quizzes->count() > 0) {
            return Inertia::render('Battles/BattleQuiz', [
                'battle' => array_merge($battle->toArray(), [
                    'monster' => $battle->monster,
                    'user' => Auth::user(),
                    'source_name' => $battle->getSourceName()
                ]),
                'file' => $battle->file ?: $battle->collection,
                'quizzes' => $quizzes->values()->toArray(),
                'quizTypes' => $quizTypes,
            ]);
        }
        
        // If battle is active but has no quizzes (shouldn't happen with new validation), mark as abandoned
        if ($battle->status === BattleStatus::ACTIVE && $quizzes->count() === 0) {
            $battle->markAsAbandoned();
            $difficultyQuizType = match($battle->monster->difficulty) {
                'easy' => 'True/False',
                'medium' => 'Multiple Choice', 
                'hard' => 'Enumeration',
                default => 'appropriate difficulty'
            };
            return back()->withErrors([
                'battle' => "This battle was abandoned because no {$difficultyQuizType} questions are available for {$battle->monster->difficulty} difficulty. Please generate {$difficultyQuizType} quizzes first."
            ]);
        }

        // Otherwise show the battle results/summary
        return Inertia::render('Battles/Show', [
            'battle' => $battle,
            'monster' => $battle->monster,
            'file' => $battle->file ?: $battle->collection,
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

        $monster = Monster::find($battle->monster_id);
        $quiz = Quiz::find($request->quiz_id);
        $isCorrect = $request->is_correct;

        // Record the answer
        \App\Models\QuizAnswer::create([
            'user_id' => Auth::id(),
            'quiz_id' => $request->quiz_id,
            'user_answer' => $request->answer,
            'is_correct' => $isCorrect,
            'context_type' => 'battle',
            'context_id' => $battle->id,
            'answered_at' => now(),
        ]);

        // Only process battle mechanics if battle is still active
        if ($battle->status === 'active') {
            $result = $this->battleService->processAnswer($battle, $quiz, $isCorrect);
        } else {
            // For finished battles, return minimal data
            $result = [
                'battle' => $battle,
                'monster' => $monster,
                'message' => 'Answer recorded for completed battle.',
                'battleEnded' => true,
                'expEarned' => 0
            ];
        }

        // Update quest progress for answering a battle question
        $this->questService->updateQuestProgress(Auth::user(), 'battle_questions');
        // Update quest progress for activity streak
        $this->questService->updateQuestProgress(Auth::user(), 'activity_streak');

        // Get next quiz if battle is still active
        $nextQuiz = null;
        if ($battle->status === 'active') {
            $allQuizzes = $battle->getAvailableQuizzes();
            $nextQuiz = $allQuizzes->random();
        }

        return response()->json([
            'battle' => $result['battle'],
            'monster' => $result['monster'],
            'message' => $result['message'],
            'nextQuiz' => $nextQuiz,
            'battleEnded' => $result['battleEnded'],
            'expEarned' => $result['expEarned'] ?? 0
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

        // Award XP based on difficulty-based system (this is handled by the service during battle)
        // Additional completion bonus
        $completionBonus = $request->status === 'victory' ? 50 : 25; // Victory gives more XP
        $user->addExperience($completionBonus);

        // Update quest progress for completing a battle (win only)
        if ($request->status === 'victory') {
            $this->questService->updateQuestProgress($user, 'battle_win');
            
            // Send battle victory notification
            $user->notify(new \App\Notifications\BattleVictoryNotification($battle, $completionBonus));

            // Check for first battle victory achievement
            $userBattleWins = \App\Models\Battle::where('user_id', $user->id)
                ->where('status', 'won')
                ->count();
            
            if ($userBattleWins == 1) {
                $user->notify(new \App\Notifications\AchievementUnlockedNotification(
                    'First Victory',
                    'Win your first battle',
                    '⚔️'
                ));
            }
        }

        // Track study activity
        $this->recordBattleActivity($user->id, [
            'questions_answered' => $request->total_questions,
            'correct_answers' => $request->correct_answers,
            'points_earned' => $completionBonus,
            'time_spent_minutes' => 5, // Estimate 5 minutes per battle
        ]);

        return response()->json([
            'success' => true,
            'xp_gained' => $completionBonus,
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
        $recentBattles = Battle::with(['file', 'monster'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

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

    /**
     * Get question counts by difficulty for a source (file or collection)
     */
    public function getQuestionCounts(Request $request)
    {
        $request->validate([
            'source_type' => 'required|in:file,collection',
            'source_id' => 'required|integer',
        ]);

        $quizzes = collect();

        if ($request->source_type === 'file') {
            $file = File::find($request->source_id);
            if ($file && $file->user_id === Auth::id()) {
                $quizzes = Quiz::where('file_id', $file->id)->get();
            }
        } else {
            $collection = Collection::find($request->source_id);
            if ($collection && $collection->user_id === Auth::id()) {
                // Ensure we only get quizzes from files that still exist and belong to the user
                $fileIds = $collection->files()
                    ->where('files.user_id', Auth::id()) // Additional safety check
                    ->pluck('files.id');
                
                if ($fileIds->isNotEmpty()) {
                    $quizzes = Quiz::whereIn('file_id', $fileIds)
                        ->whereHas('file', function ($query) {
                            $query->where('user_id', Auth::id());
                        })
                        ->get();
                }
            }
        }

        // Categorize questions by difficulty using the difficulty service
        $categorized = $this->difficultyService->categorizeQuizzesByDifficulty($quizzes);
        
        $counts = [
            'easy' => $categorized['easy']->count(),
            'medium' => $categorized['medium']->count(),
            'hard' => $categorized['hard']->count(),
            'total' => $quizzes->count(),
        ];

        // Add warnings for insufficient questions
        $warnings = [];
        foreach (['easy', 'medium', 'hard'] as $difficulty) {
            if ($counts[$difficulty] < 5 && $counts[$difficulty] > 0) {
                $warnings[$difficulty] = "Only {$counts[$difficulty]} {$difficulty} questions available. Battle will be shorter and give less EXP.";
            } elseif ($counts[$difficulty] === 0) {
                $warnings[$difficulty] = "No {$difficulty} questions available for this source.";
            }
        }

        return response()->json([
            'counts' => $counts,
            'warnings' => $warnings,
            'has_questions' => $quizzes->count() > 0
        ]);
    }
}
