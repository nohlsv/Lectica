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

class MultiplayerGameController extends Controller
{
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

        // Start the game
        $multiplayerGame->startGame();

        // Broadcast the game update to notify player one that player two has joined
        broadcast(new \App\Events\MultiplayerGameUpdated($multiplayerGame->fresh()))->toOthers();

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

        // Use database transaction to prevent race conditions
        return DB::transaction(function () use ($request, $multiplayerGame) {
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

            // Process damage based on game mode
            $this->processDamage($multiplayerGame, $request->is_correct, $isPlayerOne, $damageDealt, $damageReceived);

            // Check win/lose conditions and handle game end
            $gameEnded = $this->checkGameEndConditions($multiplayerGame);

            // Only switch turns if game hasn't ended
            if (!$gameEnded) {
                $multiplayerGame->switchTurn();

                // Advance to the next question for both players
                $multiplayerGame->advanceToNextQuestion();

                // Broadcast game update via websockets
                broadcast(new \App\Events\MultiplayerGameUpdated($multiplayerGame->fresh()))->toOthers();
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
                        'damage_dealt' => $damageDealt,
                        'damage_received' => $damageReceived,
                        'game_ended' => $gameEnded,
                    ]
                ]
            ]);
        });
    }

    /**
     * Process damage based on game mode and answer correctness
     */
    private function processDamage(MultiplayerGame $multiplayerGame, bool $isCorrect, bool $isPlayerOne, &$damageDealt, &$damageReceived)
    {
        if ($multiplayerGame->isPvp()) {
            // PVP Mode: Player vs Player
            if ($isCorrect) {
                // Player deals damage to opponent
                $damage = 15; // Base damage for correct answer in PVP
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
                // Player takes damage for wrong answer
                $damage = 5; // Self-damage for wrong answer in PVP
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
            // PVE Mode: Player vs Monster
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
     * Check win/lose conditions and end game if necessary
     */
    private function checkGameEndConditions(MultiplayerGame $multiplayerGame): bool
    {
        // Refresh to get latest HP values
        $multiplayerGame->refresh();

        if ($multiplayerGame->isPvp()) {
            // PVP win conditions
            if ($multiplayerGame->player_one_hp <= 0 || $multiplayerGame->player_two_hp <= 0) {
                $multiplayerGame->markAsFinished();
                return true;
            }
        } else {
            // PVE win conditions
            if ($multiplayerGame->monster_hp <= 0) {
                // Both players win against the monster
                $multiplayerGame->markAsFinished();
                return true;
            } elseif ($multiplayerGame->player_one_hp <= 0 && $multiplayerGame->player_two_hp <= 0) {
                // Both players lost
                $multiplayerGame->markAsFinished();
                return true;
            } elseif ($multiplayerGame->player_one_hp <= 0 || $multiplayerGame->player_two_hp <= 0) {
                // One player lost
                $multiplayerGame->markAsFinished();
                return true;
            }
        }

        return false;
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
}
