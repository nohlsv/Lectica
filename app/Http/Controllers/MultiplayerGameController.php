<?php

namespace App\Http\Controllers;

use App\Enums\MultiplayerGameStatus;
use App\Models\MultiplayerGame;
use App\Models\Monster;
use App\Models\File;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Collection;
use App\Services\QuestService;

class MultiplayerGameController extends Controller
{
    public function __construct(private QuestService $questService) {}

    /**
     * Display a listing of multiplayer games.
     */
    public function index()
    {
        // Redirect to the consolidated lobby page
        return redirect()->route('multiplayer-games.lobby');
    }

    /**
     * Show the form for creating a new multiplayer game.
     */
    public function create()
    {
        $monsters = Monster::all();
        $files = File::where('user_id', Auth::id())->get();
        $collections = Collection::where('user_id', Auth::id())
            ->with(['files'])
            ->where('file_count', '>', 0)
            ->get();

        return Inertia::render('MultiplayerGames/Create', [
            'monsters' => $monsters,
            'files' => $files,
            'collections' => $collections
        ]);
    }

    /**
     * Store a newly created multiplayer game.
     */
    public function store(Request $request)
    {
        // Add debugging
        \Log::info('Multiplayer game creation attempt', [
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        try {
            $request->validate([
                'game_mode' => 'required|in:pve,pvp',
                'pvp_mode' => 'required_if:game_mode,pvp|in:accuracy,hp', // Add validation for PvP mode
                'monster_id' => 'required_if:game_mode,pve|nullable|integer|exists:monsters,id',
                'source_type' => 'required|in:file,collection',
                'file_id' => [
                    'required_if:source_type,file',
                    'nullable',
                    'exists:files,id'
                ],
                'collection_id' => [
                    'required_if:source_type,collection',
                    'nullable',
                    'exists:collections,id'
                ],
            ]);

            \Log::info('Validation passed for multiplayer game creation');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed for multiplayer game creation', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e; // Re-throw to show errors in form
        }

        // Validate monster only for PVE mode
        $monster = null;
        if ($request->game_mode === 'pve') {
            $monster = Monster::find($request->monster_id);
            if (!$monster) {
                return back()->withErrors(['monster_id' => 'Invalid monster selected.']);
            }
        }

        $file = null;
        $collection = null;
        $quizCount = 0;

        if ($request->source_type === 'file') {
            \Log::info('Attempting to find file', ['file_id' => $request->file_id]);

            try {
                $file = File::findOrFail($request->file_id);
                \Log::info('File found', ['file' => $file->toArray()]);
            } catch (\Exception $e) {
                \Log::error('File not found', ['file_id' => $request->file_id, 'error' => $e->getMessage()]);
                return back()->withErrors(['file_id' => 'The selected file could not be found.']);
            }

            $quizCount = Quiz::where('file_id', $file->id)->count();
            \Log::info('Quiz count for file', ['file_id' => $file->id, 'quiz_count' => $quizCount]);

            if ($quizCount === 0) {
                return back()->withErrors(['file_id' => 'This file has no quizzes. Please generate quizzes first.']);
            }
        } else {
            \Log::info('Attempting to find collection', ['collection_id' => $request->collection_id]);

            try {
                $collection = Collection::findOrFail($request->collection_id);
                \Log::info('Collection found', ['collection' => $collection->toArray()]);
            } catch (\Exception $e) {
                \Log::error('Collection not found', ['collection_id' => $request->collection_id, 'error' => $e->getMessage()]);
                return back()->withErrors(['collection_id' => 'The selected collection could not be found.']);
            }

            $quizCount = $collection->getTotalQuizzesCount();
            \Log::info('Quiz count for collection', ['collection_id' => $collection->id, 'quiz_count' => $quizCount]);

            if ($quizCount === 0) {
                return back()->withErrors(['collection_id' => 'This collection has no quizzes. Please add files with quizzes first.']);
            }
        }

        // Create the game with appropriate values based on game mode
        $gameData = [
            'player_one_id' => Auth::id(),
            'game_mode' => $request->game_mode,
            'pvp_mode' => $request->pvp_mode ?? null, // Store PvP mode
            'status' => MultiplayerGameStatus::WAITING,
            'player_one_hp' => 100,
            'player_two_hp' => 100,
            'player_one_score' => 0,
            'player_two_score' => 0,
        ];

        // Add file or collection
        if ($request->source_type === 'file') {
            $gameData['file_id'] = $file->id;
        } else {
            $gameData['collection_id'] = $collection->id;
        }

        // Add monster data only for PVE mode
        if ($request->game_mode === 'pve' && $monster) {
            $gameData['monster_id'] = $monster->id;
            $gameData['monster_hp'] = $monster->hp;
        }

        \Log::info('Attempting to create game with data', ['game_data' => $gameData]);

        try {
            $game = MultiplayerGame::create($gameData);
            \Log::info('Game created successfully', ['game_id' => $game->id]);

            // Update quest progress for creating a multiplayer game
            $this->questService->updateQuestProgress(Auth::user(), 'multiplayer_create');

            // Broadcast to lobby for real-time updates
            broadcast(new \App\Events\MultiplayerGameLobbyUpdate())->toOthers();

            \Log::info('Broadcasting completed, redirecting to game');
            return redirect()->route('multiplayer-games.show', $game);
        } catch (\Exception $e) {
            \Log::error('Failed to create multiplayer game', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'game_data' => $gameData
            ]);
            return back()->withErrors(['general' => 'Failed to create the game. Please try again.']);
        }
    }

    /**
     * Display the specified multiplayer game.
     */
    public function show(MultiplayerGame $multiplayerGame)
    {
        // Check if user is part of this game
        if ($multiplayerGame->player_one_id !== Auth::id() && $multiplayerGame->player_two_id !== Auth::id()) {
            abort(403, 'You are not part of this game.');
        }

        // Handle JSON requests for sync checks (but not Inertia requests)
        if (request()->wantsJson() && !request()->header('X-Inertia')) {
            $monster = Monster::find($multiplayerGame->monster_id);

            return response()->json([
                'game' => array_merge($multiplayerGame->toArray(), [
                    'monster' => $monster,
                    'playerOne' => $multiplayerGame->playerOne,
                    'playerTwo' => $multiplayerGame->playerTwo,
                    'currentUser' => Auth::user(),
                    'source_name' => $multiplayerGame->getSourceName(),
                    'currentQuestion' => $multiplayerGame->getCurrentQuestion()
                ])
            ]);
        }

        $quizzes = $multiplayerGame->getAvailableQuizzes();
        $monster = Monster::find($multiplayerGame->monster_id);

        $quizTypes = [
            'multiple_choice' => 'Multiple Choice',
            'true_false' => 'True/False',
            'enumeration' => 'Enumeration',
        ];

        // If game is active and has quizzes, show the game interface
        if ($multiplayerGame->status === MultiplayerGameStatus::ACTIVE && $quizzes->count() > 0) {
            // Get the current synchronized question
            $currentQuestion = $multiplayerGame->getCurrentQuestion();

            return Inertia::render('MultiplayerGames/GameQuiz', [
                'game' => array_merge($multiplayerGame->toArray(), [
                    'monster' => $monster,
                    'playerOne' => $multiplayerGame->playerOne,
                    'playerTwo' => $multiplayerGame->playerTwo,
                    'currentUser' => Auth::user(),
                    'source_name' => $multiplayerGame->getSourceName()
                ]),
                'quizzes' => $quizzes,
                'currentQuestion' => $currentQuestion,
                'quizTypes' => $quizTypes,
            ]);
        }

        // If game is waiting for a player, show waiting room
        if ($multiplayerGame->status === MultiplayerGameStatus::WAITING) {
            return Inertia::render('MultiplayerGames/WaitingRoom', [
                'game' => array_merge($multiplayerGame->toArray(), [
                    'monster' => $monster,
                    'playerOne' => $multiplayerGame->playerOne,
                    'currentUser' => Auth::user(),
                    'source_name' => $multiplayerGame->getSourceName()
                ]),
            ]);
        }

        // Otherwise show the game results/summary
        return Inertia::render('MultiplayerGames/Show', [
            'game' => $multiplayerGame,
            'monster' => $monster,
            'playerOne' => $multiplayerGame->playerOne,
            'playerTwo' => $multiplayerGame->playerTwo,
            'source_name' => $multiplayerGame->getSourceName(),
            'quizTypes' => $quizTypes,
            'pvp_mode' => $multiplayerGame->pvp_mode ?? 'accuracy', // Ensure pvp_mode is present
        ]);
    }

    /**
     * Join an existing multiplayer game.
     */
    public function join(MultiplayerGame $multiplayerGame)
    {
        // Check if game is waiting for a player
        if ($multiplayerGame->status !== MultiplayerGameStatus::WAITING) {
            return back()->withErrors(['game' => 'This game is not available to join.']);
        }

        // Check if user is not already player one
        if ($multiplayerGame->player_one_id === Auth::id()) {
            return back()->withErrors(['game' => 'You cannot join your own game.']);
        }

        // Check if there's already a second player
        if ($multiplayerGame->player_two_id !== null) {
            return back()->withErrors(['game' => 'This game is already full.']);
        }

        // Join the game as player two
        $multiplayerGame->update([
            'player_two_id' => Auth::id(),
        ]);

        // Update quest progress for joining a multiplayer game
        $this->questService->updateQuestProgress(Auth::user(), 'multiplayer_join');

        // Start the game
        $multiplayerGame->startGame();

        // Broadcast the game start to both players
        broadcast(new \App\Events\MultiplayerGameUpdated($multiplayerGame->fresh(), 'game_started', [
            'player_two_joined' => true,
            'player_two_name' => Auth::user()->first_name,
        ]));

        return redirect()->route('multiplayer-games.show', $multiplayerGame)
            ->with('success', 'Successfully joined the game! The battle begins!');
    }

    /**
     * Show available games to join.
     */
    public function lobby()
    {
        $monsters = Monster::all();
        $files = File::where('user_id', Auth::id())->get();
        $collections = Collection::where('user_id', Auth::id())
            ->with(['files'])
            ->where('file_count', '>', 0)
            ->get();

        $waitingGames = MultiplayerGame::with(['file', 'playerOne'])
            ->waiting()
            ->where('player_one_id', '!=', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $myGames = MultiplayerGame::with(['file', 'collection', 'playerOne', 'playerTwo'])
            ->where(function($query) {
                $query->where('player_one_id', Auth::id())
                      ->orWhere('player_two_id', Auth::id());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('MultiplayerGames/Lobby', [
            'monsters' => $monsters,
            'files' => $files,
            'collections' => $collections,
            'waitingGames' => $waitingGames->through(function ($game) {
                $monster = Monster::find($game->monster_id);
                $game->monster = $monster;
                return $game;
            }),
            'myGames' => $myGames->through(function ($game) {
                $monster = Monster::find($game->monster_id);
                $game->monster = $monster;
                return $game;
            })
        ]);
    }

    /**
     * Handle answering a question in multiplayer game
     */
    public function answerQuestion(Request $request, MultiplayerGame $multiplayerGame)
    {
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'answer' => 'required|string',
            'is_correct' => 'required|boolean',
        ]);

        // Variables to hold data for post-transaction broadcast
        $broadcastData = null;
        $gameEnded = false;

        // Use database transaction to prevent race conditions
        DB::transaction(function () use ($request, $multiplayerGame, &$broadcastData, &$gameEnded) {
            // Lock the game record to prevent concurrent access
            $multiplayerGame = MultiplayerGame::where('id', $multiplayerGame->id)->lockForUpdate()->first();

            // Check if user is part of this game
            if ($multiplayerGame->player_one_id !== Auth::id() && $multiplayerGame->player_two_id !== Auth::id()) {
                abort(403, 'You are not part of this game.');
            }

            // Check if game is active
            if (!$multiplayerGame->isActive()) {
                return back()->withErrors(['game' => 'Game is not active.']);
            }

            // Validate that both players are still present
            if (!$multiplayerGame->player_two_id) {
                return back()->withErrors(['game' => 'Waiting for second player.']);
            }

            // Check if it's the user's turn
            $isPlayerOne = $multiplayerGame->player_one_id === Auth::id();
            $isPlayerTwo = $multiplayerGame->player_two_id === Auth::id();

            if (($isPlayerOne && $multiplayerGame->current_turn !== 1) ||
                ($isPlayerTwo && $multiplayerGame->current_turn !== 2)) {
                return back()->withErrors(['turn' => 'It is not your turn.']);
            }

            // Double-check the game hasn't ended while we were processing
            if ($multiplayerGame->isFinished()) {
                return back()->withErrors(['game' => 'Game has already ended.']);
            }

            // Update question statistics
            if ($isPlayerOne) {
                $multiplayerGame->increment('total_questions_p1');
                if ($request->is_correct) {
                    $multiplayerGame->increment('correct_answers_p1');
                }
            } else {
                $multiplayerGame->increment('total_questions_p2');
                if ($request->is_correct) {
                    $multiplayerGame->increment('correct_answers_p2');
                }
            }

            $damageDealt = 0;
            $damageReceived = 0;

            // Process damage based on game mode (but skip accuracy update for PVP)
            $this->processDamage($multiplayerGame, $request->is_correct, $isPlayerOne, $damageDealt, $damageReceived);

            // Update accuracy stats AFTER counters are incremented (for PVP mode)
            if ($multiplayerGame->isPvp()) {
                $this->updateAccuracyStats($multiplayerGame, $request->is_correct, $isPlayerOne);
            }

            // Check win/lose conditions and handle game end
            $gameEnded = $this->checkGameEndConditions($multiplayerGame);

            // Only switch turns if game hasn't ended
            if (!$gameEnded) {
                $multiplayerGame->switchTurn();
                // Advance to the next question for both players
                $multiplayerGame->advanceToNextQuestion();

                // Refresh to get the latest accuracy and streak data for broadcast
                $multiplayerGame->refresh();

                // Prepare broadcast data for after transaction with fresh data
                $broadcastData = [
                    'event_type' => 'answer_submitted',
                    'additional_data' => [
                        'player_id' => Auth::id(),
                        'is_correct' => $request->is_correct,
                        'damage_dealt' => $damageDealt,
                        'damage_received' => $damageReceived,
                        'answer_text' => $request->answer,
                        'player_name' => Auth::user()->first_name,
                        // Include current accuracy and streak data for both players
                        'player_one_accuracy' => $multiplayerGame->player_one_accuracy ?? $multiplayerGame->getPlayerOneAccuracy(),
                        'player_two_accuracy' => $multiplayerGame->player_two_accuracy ?? $multiplayerGame->getPlayerTwoAccuracy(),
                        'player_one_streak' => $multiplayerGame->player_one_streak ?? 0,
                        'player_two_streak' => $multiplayerGame->player_two_streak ?? 0,
                        'player_one_max_streak' => $multiplayerGame->player_one_max_streak ?? 0,
                        'player_two_max_streak' => $multiplayerGame->player_two_max_streak ?? 0,
                    ]
                ];
            } else {
                // Refresh to get the most current data before game end
                $multiplayerGame->refresh();

                // Calculate winner_id for broadcast
                $winnerId = $this->calculateAndSetWinner($multiplayerGame);

                // Prepare game end broadcast data
                $broadcastData = [
                    'event_type' => 'game_ended',
                    'additional_data' => [
                        'final_results' => $this->getFinalGameResults($multiplayerGame),
                        'winner_id' => $winnerId, // Use the calculated winner_id
                        // Include final accuracy and streak data
                        'player_one_accuracy' => $multiplayerGame->player_one_accuracy ?? $multiplayerGame->getPlayerOneAccuracy(),
                        'player_two_accuracy' => $multiplayerGame->player_two_accuracy ?? $multiplayerGame->getPlayerTwoAccuracy(),
                        'player_one_streak' => $multiplayerGame->player_one_streak ?? 0,
                        'player_two_streak' => $multiplayerGame->player_two_streak ?? 0,
                    ]
                ];
            }
            // At the end of the transaction, return a value to avoid warning
            return null;
        });

        // Update quest progress for answering a multiplayer question
        $this->questService->updateQuestProgress(Auth::user(), 'multiplayer_questions');

        // Broadcast AFTER transaction is committed
        if ($broadcastData) {
            // Refresh the model to get the committed data
            $freshGame = $multiplayerGame->fresh();
            broadcast(new \App\Events\MultiplayerGameUpdated(
                $freshGame,
                $broadcastData['event_type'],
                $broadcastData['additional_data']
            ))->toOthers();
        }

        // Return back to the same page instead of JSON response for Inertia compatibility
        return back()->with([
            'flash' => [
                'gameUpdate' => [
                    'success' => true,
                    'game' => array_merge($multiplayerGame->fresh()->toArray(), [
                        'monster' => $multiplayerGame->isPve() ? Monster::find($multiplayerGame->monster_id) : null,
                        'playerOne' => $multiplayerGame->playerOne,
                        'playerTwo' => $multiplayerGame->playerTwo,
                        'currentQuestion' => $multiplayerGame->getCurrentQuestion(),
                    ]),
                    'damage_dealt' => $broadcastData['additional_data']['damage_dealt'] ?? 0,
                    'damage_received' => $broadcastData['additional_data']['damage_received'] ?? 0,
                    'game_ended' => $gameEnded,
                ]
            ]
        ]);
    }


    /**
     * Process scoring based on game mode and answer correctness
     */
    private function processDamage(MultiplayerGame $multiplayerGame, bool $isCorrect, bool $isPlayerOne, &$damageDealt, &$damageReceived)
    {
        if ($multiplayerGame->isPvp()) {
            // Check PvP mode
            if ($multiplayerGame->pvp_mode === 'hp') {
                // HP-based PvP: deal damage to opponent, self-damage for wrong answer
                if ($isCorrect) {
                    $damage = 15; // Base damage for correct answer in PvP HP mode
                    $damageDealt = $damage;
                    if ($isPlayerOne) {
                        $newOpponentHp = max(0, $multiplayerGame->player_two_hp - $damage);
                        $multiplayerGame->update(['player_two_hp' => $newOpponentHp]);
                        $multiplayerGame->increment('player_one_score', 10);
                    } else {
                        $newOpponentHp = max(0, $multiplayerGame->player_one_hp - $damage);
                        $multiplayerGame->update(['player_one_hp' => $newOpponentHp]);
                        $multiplayerGame->increment('player_two_score', 10);
                    }
                } else {
                    $damage = 5; // Self-damage for wrong answer in PvP HP mode
                    $damageReceived = $damage;
                    if ($isPlayerOne) {
                        $newPlayerHp = max(0, $multiplayerGame->player_one_hp - $damage);
                        $multiplayerGame->update(['player_one_hp' => $newPlayerHp]);
                    } else {
                        $newPlayerHp = max(0, $multiplayerGame->player_two_hp - $damage);
                        $multiplayerGame->update(['player_two_hp' => $newPlayerHp]);
                    }
                }
            } else {
                // Accuracy-based PvP: only affect score and accuracy, not HP
                if ($isCorrect) {
                    $damageDealt = 10; // Visual indicator for correct answer
                    $multiplayerGame->increment($isPlayerOne ? 'player_one_score' : 'player_two_score', 10);
                } else {
                    $damageReceived = 5; // Visual indicator for wrong answer
                }
            }
        } else {
            // PVE Mode: Traditional HP-based system with monster
            $monster = Monster::find($multiplayerGame->monster_id);

            if ($isCorrect) {
                // Player deals damage to monster
                $damage = 10; // Base damage for correct answer
                $damageDealt = $damage;
                $newMonsterHp = max(0, $multiplayerGame->monster_hp - $damage);
                $multiplayerGame->update(['monster_hp' => $newMonsterHp]);

                // Increase player score
                if ($isPlayerOne) {
                    $multiplayerGame->increment('player_one_score', 10);
                } else {
                    $multiplayerGame->increment('player_two_score', 10);
                }
            } else {
                // Monster deals damage to current player
                $damage = $monster->attack ?? 15;
                $damageReceived = $damage;

                if ($isPlayerOne) {
                    $newPlayerHp = max(0, $multiplayerGame->player_one_hp - $damage);
                    $multiplayerGame->update(['player_one_hp' => $newPlayerHp]);
                } else {
                    $newPlayerHp = max(0, $multiplayerGame->player_two_hp - $damage);
                    $multiplayerGame->update(['player_two_hp' => $newPlayerHp]);
                }
            }
        }
    }

    /**
     * Update accuracy statistics for PVP mode
     */
    private function updateAccuracyStats(MultiplayerGame $multiplayerGame, bool $isCorrect, bool $isPlayerOne)
    {
        \Log::info('=== Starting updateAccuracyStats ===', [
            'game_id' => $multiplayerGame->id,
            'is_correct' => $isCorrect,
            'is_player_one' => $isPlayerOne
        ]);

        // Refresh the model to get the latest question statistics
        $multiplayerGame->refresh();

        // Log current state after refresh
        \Log::info('After refresh - Current model state', [
            'player_one_accuracy' => $multiplayerGame->player_one_accuracy,
            'player_one_streak' => $multiplayerGame->player_one_streak,
            'player_two_accuracy' => $multiplayerGame->player_two_accuracy,
            'player_two_streak' => $multiplayerGame->player_two_streak,
            'correct_answers_p1' => $multiplayerGame->correct_answers_p1,
            'correct_answers_p2' => $multiplayerGame->correct_answers_p2,
            'total_questions_p1' => $multiplayerGame->total_questions_p1,
            'total_questions_p2' => $multiplayerGame->total_questions_p2,
        ]);

        // Use separate update logic for each player to prevent data overwrites
        if ($isPlayerOne) {
            $this->updatePlayerOneStats($multiplayerGame, $isCorrect);
        } else {
            $this->updatePlayerTwoStats($multiplayerGame, $isCorrect);
        }

        \Log::info('=== Completed updateAccuracyStats ===');
    }

    /**
     * Update Player One statistics (isolated update)
     */
    private function updatePlayerOneStats(MultiplayerGame $multiplayerGame, bool $isCorrect)
    {
        // Get fresh data for Player One
        $correctAnswers = $multiplayerGame->correct_answers_p1;
        $totalQuestions = $multiplayerGame->total_questions_p1;
        $accuracy = ($totalQuestions > 0) ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;

        $currentStreak = $isCorrect ? ($multiplayerGame->player_one_streak ?? 0) + 1 : 0;
        $maxStreak = max(($multiplayerGame->player_one_max_streak ?? 0), $currentStreak);

        \Log::info('Player One - Calculated values', [
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
            'accuracy' => $accuracy,
            'current_streak' => $currentStreak,
            'max_streak' => $maxStreak,
            'is_correct' => $isCorrect,
            'previous_streak' => $multiplayerGame->player_one_streak ?? 0
        ]);

        // Use direct DB update to avoid model conflicts
        $updateResult = DB::table('multiplayer_games')
            ->where('id', $multiplayerGame->id)
            ->update([
                'player_one_accuracy' => $accuracy,
                'player_one_streak' => $currentStreak,
                'player_one_max_streak' => $maxStreak,
                'updated_at' => now()
            ]);

        \Log::info('Player One - Update result', [
            'update_success' => $updateResult,
            'attempted_values' => [
                'player_one_accuracy' => $accuracy,
                'player_one_streak' => $currentStreak,
                'player_one_max_streak' => $maxStreak
            ]
        ]);

        // Verify the update by checking database directly
        $freshData = DB::table('multiplayer_games')->where('id', $multiplayerGame->id)->first();
        \Log::info('Player One - Direct DB check after update', [
            'db_player_one_accuracy' => $freshData->player_one_accuracy,
            'db_player_one_streak' => $freshData->player_one_streak,
            'db_player_one_max_streak' => $freshData->player_one_max_streak
        ]);
    }

    /**
     * Update Player Two statistics (isolated update)
     */
    private function updatePlayerTwoStats(MultiplayerGame $multiplayerGame, bool $isCorrect)
    {
        // Get fresh data for Player Two
        $correctAnswers = $multiplayerGame->correct_answers_p2;
        $totalQuestions = $multiplayerGame->total_questions_p2;
        $accuracy = ($totalQuestions > 0) ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;

        $currentStreak = $isCorrect ? ($multiplayerGame->player_two_streak ?? 0) + 1 : 0;
        $maxStreak = max(($multiplayerGame->player_two_max_streak ?? 0), $currentStreak);

        \Log::info('Player Two - Calculated values', [
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
            'accuracy' => $accuracy,
            'current_streak' => $currentStreak,
            'max_streak' => $maxStreak,
            'is_correct' => $isCorrect,
            'previous_streak' => $multiplayerGame->player_two_streak ?? 0
        ]);

        // Use direct DB update to avoid model conflicts
        $updateResult = DB::table('multiplayer_games')
            ->where('id', $multiplayerGame->id)
            ->update([
                'player_two_accuracy' => $accuracy,
                'player_two_streak' => $currentStreak,
                'player_two_max_streak' => $maxStreak,
                'updated_at' => now()
            ]);

        \Log::info('Player Two - Update result', [
            'update_success' => $updateResult,
            'attempted_values' => [
                'player_two_accuracy' => $accuracy,
                'player_two_streak' => $currentStreak,
                'player_two_max_streak' => $maxStreak
            ]
        ]);

        // Verify the update by checking database directly
        $freshData = DB::table('multiplayer_games')->where('id', $multiplayerGame->id)->first();
        \Log::info('Player Two - Direct DB check after update', [
            'db_player_two_accuracy' => $freshData->player_two_accuracy,
            'db_player_two_streak' => $freshData->player_two_streak,
            'db_player_two_max_streak' => $freshData->player_two_max_streak
        ]);
    }

    /**
     * Check win/lose conditions and end game if necessary
     */
    private function checkGameEndConditions(MultiplayerGame $multiplayerGame): bool
    {
        // Refresh to get latest values
        $multiplayerGame->refresh();

        if ($multiplayerGame->isPvp()) {
            // PVP win conditions
            $quizzes = $multiplayerGame->getAvailableQuizzes();
            $totalQuestions = $quizzes->count();

            // End game when both players have answered all questions
            if ($multiplayerGame->total_questions_p1 >= $totalQuestions &&
                $multiplayerGame->total_questions_p2 >= $totalQuestions) {
                // Calculate and set winner_id before marking as finished
                $winnerId = $this->calculateAndSetWinner($multiplayerGame);
                $multiplayerGame->markAsFinished();
                // Update quest progress for winning a multiplayer game
                if ($winnerId) {
                    $winnerUser = User::find($winnerId);
                    if ($winnerUser) {
                        $this->questService->updateQuestProgress($winnerUser, 'multiplayer_win');
                        $winnerUser->addExperience(50); // Winner XP
                        $loserId = ($winnerId == $multiplayerGame->player_one_id) ? $multiplayerGame->player_two_id : $multiplayerGame->player_one_id;
                        $loserUser = User::find($loserId);
                        if ($loserUser) {
                            $loserUser->addExperience(25); // Loser XP
                        }
                    }
                } else {
                    // Tie: both get draw XP
                    $playerOne = User::find($multiplayerGame->player_one_id);
                    $playerTwo = User::find($multiplayerGame->player_two_id);
                    if ($playerOne) $playerOne->addExperience(35);
                    if ($playerTwo) $playerTwo->addExperience(35);
                }
                return true;
            }

            // Optional: End game early if one player has significantly higher accuracy
            if ($multiplayerGame->pvp_mode === 'accuracy') {
                if ($multiplayerGame->total_questions_p1 >= 10 && $multiplayerGame->total_questions_p2 >= 10) {
                    $accuracyDiff = abs($multiplayerGame->player_one_accuracy - $multiplayerGame->player_two_accuracy);
                    if ($accuracyDiff >= 30) {
                        $winnerId = $this->calculateAndSetWinner($multiplayerGame);
                        $multiplayerGame->markAsFinished();
                        if ($winnerId) {
                            $winnerUser = User::find($winnerId);
                            if ($winnerUser) {
                                $this->questService->updateQuestProgress($winnerUser, 'multiplayer_win');
                                $winnerUser->addExperience(50);
                                $loserId = ($winnerId == $multiplayerGame->player_one_id) ? $multiplayerGame->player_two_id : $multiplayerGame->player_one_id;
                                $loserUser = User::find($loserId);
                                if ($loserUser) {
                                    $loserUser->addExperience(25);
                                }
                            }
                        } else {
                            $playerOne = User::find($multiplayerGame->player_one_id);
                            $playerTwo = User::find($multiplayerGame->player_two_id);
                            if ($playerOne) $playerOne->addExperience(35);
                            if ($playerTwo) $playerTwo->addExperience(35);
                        }
                        return true;
                    }
                }
            } else if ($multiplayerGame->pvp_mode === 'hp') {
                // HP-based win condition: if either player's HP reaches 0, end game
                if ($multiplayerGame->player_one_hp <= 0 || $multiplayerGame->player_two_hp <= 0) {
                    $winnerId = $this->calculateAndSetWinner($multiplayerGame);
                    $multiplayerGame->markAsFinished();
                    if ($winnerId) {
                        $winnerUser = User::find($winnerId);
                        if ($winnerUser) {
                            $this->questService->updateQuestProgress($winnerUser, 'multiplayer_win');
                            $winnerUser->addExperience(50);
                            $loserId = ($winnerId == $multiplayerGame->player_one_id) ? $multiplayerGame->player_two_id : $multiplayerGame->player_one_id;
                            $loserUser = User::find($loserId);
                            if ($loserUser) {
                                $loserUser->addExperience(25);
                            }
                        }
                    } else {
                        $playerOne = User::find($multiplayerGame->player_one_id);
                        $playerTwo = User::find($multiplayerGame->player_two_id);
                        if ($playerOne) $playerOne->addExperience(35);
                        if ($playerTwo) $playerTwo->addExperience(35);
                    }
                    return true;
                }
            }
        } else {
            // PVE win conditions (unchanged)
            if ($multiplayerGame->monster_hp <= 0) {
                // Both players win against the monster - no single winner in PVE
                $multiplayerGame->markAsFinished();
                $playerOne = User::find($multiplayerGame->player_one_id);
                $playerTwo = User::find($multiplayerGame->player_two_id);
                if ($playerOne) $playerOne->addExperience(40); // PvE win XP
                if ($playerTwo) $playerTwo->addExperience(40);
                return true;
            } elseif ($multiplayerGame->player_one_hp <= 0 && $multiplayerGame->player_two_hp <= 0) {
                // Both players lost - no winner
                $multiplayerGame->markAsFinished();
                $playerOne = User::find($multiplayerGame->player_one_id);
                $playerTwo = User::find($multiplayerGame->player_two_id);
                if ($playerOne) $playerOne->addExperience(20); // PvE both lose XP
                if ($playerTwo) $playerTwo->addExperience(20);
                return true;
            } elseif ($multiplayerGame->player_one_hp <= 0 || $multiplayerGame->player_two_hp <= 0) {
                // One player lost - other player wins
                if ($multiplayerGame->player_one_hp > 0) {
                    $multiplayerGame->update(['winner_id' => $multiplayerGame->player_one_id]);
                    $winner = User::find($multiplayerGame->player_one_id);
                    $loser = User::find($multiplayerGame->player_two_id);
                } else {
                    $multiplayerGame->update(['winner_id' => $multiplayerGame->player_two_id]);
                    $winner = User::find($multiplayerGame->player_two_id);
                    $loser = User::find($multiplayerGame->player_one_id);
                }
                if ($winner) $winner->addExperience(40); // PvE win XP
                if ($loser) $loser->addExperience(20); // PvE lose XP
                $multiplayerGame->markAsFinished();
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate winner and set winner_id for PVP games
     */
    private function calculateAndSetWinner(MultiplayerGame $multiplayerGame): ?int
    {
        if (!$multiplayerGame->isPvp()) {
            return null;
        }
        $multiplayerGame->refresh();
        $winnerId = null;
        if ($multiplayerGame->pvp_mode === 'accuracy') {
            $playerOneAccuracy = $multiplayerGame->getPlayerOneAccuracy();
            $playerTwoAccuracy = $multiplayerGame->getPlayerTwoAccuracy();
            if ($playerOneAccuracy > $playerTwoAccuracy) {
                $winnerId = $multiplayerGame->player_one_id;
            } elseif ($playerTwoAccuracy > $playerOneAccuracy) {
                $winnerId = $multiplayerGame->player_two_id;
            }
        } else if ($multiplayerGame->pvp_mode === 'hp') {
            // HP-based: winner is the player with the most HP
            if ($multiplayerGame->player_one_hp > $multiplayerGame->player_two_hp) {
                $winnerId = $multiplayerGame->player_one_id;
            } elseif ($multiplayerGame->player_two_hp > $multiplayerGame->player_one_hp) {
                $winnerId = $multiplayerGame->player_two_id;
            }
        }
        if ($winnerId !== null) {
            $multiplayerGame->update(['winner_id' => $winnerId]);
        }

        return $winnerId;
    }

    /**
     * Abandon a multiplayer game.
     */
    public function abandon(MultiplayerGame $multiplayerGame)
    {
        // Use transaction to ensure atomic abandonment
        return DB::transaction(function () use ($multiplayerGame) {
            // Lock the game to prevent race conditions
            $multiplayerGame = $multiplayerGame->lockForUpdate();

            // Check if user is part of this game
            if ($multiplayerGame->player_one_id !== Auth::id() && $multiplayerGame->player_two_id !== Auth::id()) {
                abort(403, 'You are not part of this game.');
            }

            // Check if game is already finished
            if ($multiplayerGame->isFinished()) {
                return redirect()->route('multiplayer-games.lobby')
                    ->with('info', 'Game was already finished.');
            }

            // Mark as abandoned and broadcast to other player
            $multiplayerGame->markAsAbandoned();

            return redirect()->route('multiplayer-games.lobby')
                ->with('success', 'Game abandoned.');
        });
    }

    /**
     * Handle player disconnection and timeout scenarios
     */
    public function handlePlayerTimeout(MultiplayerGame $multiplayerGame, int $playerId)
    {
        return DB::transaction(function () use ($multiplayerGame, $playerId) {
            $multiplayerGame = $multiplayerGame->lockForUpdate();

            // Only handle timeouts for active games
            if (!$multiplayerGame->isActive()) {
                return;
            }

            // Check if the player is part of this game
            if ($multiplayerGame->player_one_id !== $playerId && $multiplayerGame->player_two_id !== $playerId) {
                return;
            }

            // Mark the game as abandoned due to timeout
            $multiplayerGame->update([
                'status' => MultiplayerGameStatus::ABANDONED
                // Removed abandoned_reason since it doesn't exist in database schema
            ]);

            // Broadcast the abandonment to remaining player
            broadcast(new \App\Events\MultiplayerGameUpdated($multiplayerGame->fresh(), 'player_timeout'));
        });
    }

    /**
     * Get final game results for broadcasting
     */
    private function getFinalGameResults(MultiplayerGame $multiplayerGame): array
    {
        // Force a fresh query to ensure we have the absolute latest data
        $freshGame = MultiplayerGame::find($multiplayerGame->id);

        if ($freshGame->isPvp()) {
            // Calculate accuracies directly from the fresh data to ensure accuracy
            $playerOneAccuracy = $freshGame->getPlayerOneAccuracy();
            $playerTwoAccuracy = $freshGame->getPlayerTwoAccuracy();

            \Log::info('Final game results calculation', [
                'game_id' => $freshGame->id,
                'player_one_accuracy_calculated' => $playerOneAccuracy,
                'player_two_accuracy_calculated' => $playerTwoAccuracy,
                'player_one_accuracy_field' => $freshGame->player_one_accuracy,
                'player_two_accuracy_field' => $freshGame->player_two_accuracy,
                'correct_answers_p1' => $freshGame->correct_answers_p1,
                'total_questions_p1' => $freshGame->total_questions_p1,
                'correct_answers_p2' => $freshGame->correct_answers_p2,
                'total_questions_p2' => $freshGame->total_questions_p2,
            ]);

            return [
                'player_one' => [
                    'name' => $freshGame->playerOne->first_name,
                    'accuracy' => $freshGame->player_one_accuracy ?? $playerOneAccuracy,
                    'score' => $freshGame->player_one_score,
                    'max_streak' => $freshGame->player_one_max_streak ?? 0,
                    'correct_answers' => $freshGame->correct_answers_p1 ?? 0,
                    'total_questions' => $freshGame->total_questions_p1 ?? 0,
                ],
                'player_two' => [
                    'name' => $freshGame->playerTwo->first_name,
                    'accuracy' => $freshGame->player_two_accuracy ?? $playerTwoAccuracy,
                    'score' => $freshGame->player_two_score,
                    'max_streak' => $freshGame->player_two_max_streak ?? 0,
                    'correct_answers' => $freshGame->correct_answers_p2 ?? 0,
                    'total_questions' => $freshGame->total_questions_p2 ?? 0,
                ],
            ];
        } else {
            return [
                'monster_defeated' => $freshGame->monster_hp <= 0,
                'monster_hp' => $freshGame->monster_hp,
                'player_one_hp' => $freshGame->player_one_hp,
                'player_two_hp' => $freshGame->player_two_hp,
            ];
        }
    }

    /**
     * Get winner ID for PVP games
     */
    private function getWinnerId(MultiplayerGame $multiplayerGame): ?int
    {
        if ($multiplayerGame->isPvp()) {
            if ($multiplayerGame->player_one_accuracy > $multiplayerGame->player_two_accuracy) {
                return $multiplayerGame->player_one_id;
            } elseif ($multiplayerGame->player_two_accuracy > $multiplayerGame->player_one_accuracy) {
                return $multiplayerGame->player_two_id;
            }
        }
        return null; // Tie or PVE mode
    }
}
