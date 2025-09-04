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
        $request->validate([
            'game_mode' => 'required|in:pve,pvp',
            'monster_id' => 'required_if:game_mode,pve|string',
            'source_type' => 'required|in:file,collection',
            'file_id' => 'required_if:source_type,file|exists:files,id',
            'collection_id' => 'required_if:source_type,collection|exists:collections,id',
        ]);

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
            $file = File::findOrFail($request->file_id);

            // Check if user owns the file
            if ($file->user_id !== Auth::id()) {
                abort(403, 'You can only create games with your own files.');
            }

            $quizCount = Quiz::where('file_id', $file->id)->count();
            if ($quizCount === 0) {
                return back()->withErrors(['file_id' => 'This file has no quizzes. Please generate quizzes first.']);
            }
        } else {
            $collection = Collection::findOrFail($request->collection_id);

            // Check if user owns the collection
            if ($collection->user_id !== Auth::id()) {
                abort(403, 'You can only create games with your own collections.');
            }

            $quizCount = $collection->getTotalQuizzesCount();
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

        $game = MultiplayerGame::create($gameData);

        // Broadcast to lobby for real-time updates
        broadcast(new \App\Events\MultiplayerGameLobbyUpdate())->toOthers();

        return redirect()->route('multiplayer-games.show', $game);
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

        $quizzes = $multiplayerGame->getAvailableQuizzes();
        $monster = Monster::find($multiplayerGame->monster_id);

        $quizTypes = [
            'multiple_choice' => 'Multiple Choice',
            'true_false' => 'True/False',
            'enumeration' => 'Enumeration',
        ];

        // If game is active and has quizzes, show the game interface
        if ($multiplayerGame->status === MultiplayerGameStatus::ACTIVE && $quizzes->count() > 0) {
            return Inertia::render('MultiplayerGames/GameQuiz', [
                'game' => array_merge($multiplayerGame->toArray(), [
                    'monster' => $monster,
                    'playerOne' => $multiplayerGame->playerOne,
                    'playerTwo' => $multiplayerGame->playerTwo,
                    'currentUser' => Auth::user(),
                    'source_name' => $multiplayerGame->getSourceName()
                ]),
                'quizzes' => $quizzes,
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

        // Check if user is part of this game
        if ($multiplayerGame->player_one_id !== Auth::id() && $multiplayerGame->player_two_id !== Auth::id()) {
            abort(403, 'You are not part of this game.');
        }

        // Check if it's the user's turn
        $isPlayerOne = $multiplayerGame->player_one_id === Auth::id();
        $isPlayerTwo = $multiplayerGame->player_two_id === Auth::id();

        if (($isPlayerOne && $multiplayerGame->current_turn !== 1) ||
            ($isPlayerTwo && $multiplayerGame->current_turn !== 2)) {
            return response()->json(['error' => 'It is not your turn.'], 400);
        }

        // Check if game is active
        if (!$multiplayerGame->isActive()) {
            return response()->json(['error' => 'Game is not active.'], 400);
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

        if ($multiplayerGame->isPvp()) {
            // PVP Mode: Player vs Player
            if ($request->is_correct) {
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

            if ($request->is_correct) {
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

        // Check win/lose conditions
        $multiplayerGame->refresh();

        if ($multiplayerGame->isPvp()) {
            // PVP win conditions
            if ($multiplayerGame->player_one_hp <= 0 || $multiplayerGame->player_two_hp <= 0) {
                $multiplayerGame->markAsFinished();
            } else {
                $multiplayerGame->switchTurn();
            }
        } else {
            // PVE win conditions
            if ($multiplayerGame->monster_hp <= 0) {
                // Both players win against the monster
                $multiplayerGame->markAsFinished();
            } elseif ($multiplayerGame->player_one_hp <= 0 && $multiplayerGame->player_two_hp <= 0) {
                // Both players lost
                $multiplayerGame->markAsFinished();
            } elseif ($multiplayerGame->player_one_hp <= 0 || $multiplayerGame->player_two_hp <= 0) {
                // One player lost
                $multiplayerGame->markAsFinished();
            } else {
                // Game continues, switch turns
                $multiplayerGame->switchTurn();
            }
        }

        // Broadcast game update via websockets
        broadcast(new \App\Events\MultiplayerGameUpdated($multiplayerGame->fresh()))->toOthers();

        return response()->json([
            'success' => true,
            'game' => array_merge($multiplayerGame->fresh()->toArray(), [
                'monster' => $multiplayerGame->isPve() ? Monster::find($multiplayerGame->monster_id) : null,
                'playerOne' => $multiplayerGame->playerOne,
                'playerTwo' => $multiplayerGame->playerTwo,
            ]),
            'damage_dealt' => $damageDealt,
            'damage_received' => $damageReceived,
        ]);
    }

    /**
     * Abandon a multiplayer game.
     */
    public function abandon(MultiplayerGame $multiplayerGame)
    {
        // Check if user is part of this game
        if ($multiplayerGame->player_one_id !== Auth::id() && $multiplayerGame->player_two_id !== Auth::id()) {
            abort(403, 'You are not part of this game.');
        }

        $multiplayerGame->markAsAbandoned();

        return redirect()->route('multiplayer-games.index')
            ->with('success', 'Game abandoned.');
    }
}

