<?php

namespace App\Http\Controllers;

use App\Models\MultiplayerGame;
use App\Models\User;


use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

class GameController extends Controller
{
    //
    public function store(Request $request) {
        // Make a game
        $validated = $request->validate([
            'player_one_id' => 'required|exists:users,id',
            'player_two_id' => 'nullable|exists:users,id',
        ]);

        // If the player already has a game (as player one or two) that is not finished, redirect to that game
        $existingGame = MultiplayerGame::where(function($q) use ($validated) {
            $q->where('player_one_id', $validated['player_one_id']);
            if (!empty($validated['player_two_id'])) {
                $q->orWhere('player_two_id', $validated['player_two_id']);
            }
        })->where('status', '!=', 'finished')->first();
        if ($existingGame) {
            return redirect("/games/{$existingGame->id}");
        }

        $game = MultiplayerGame::create([
            'player_one_id' => $validated['player_one_id'],
            'player_two_id' => $validated['player_two_id'] ?? null,
        ]);
        \broadcast(new \App\Events\MultiplayerGameLobbyUpdate($game));
        // Otherwise, redirect to lobby
        return to_route('games.lobby');
    }

    // List games without player two
    public function openLobbies() {
        $games = MultiplayerGame::whereNull('player_two_id')
            ->where('status', '!=', 'finished')
            ->get();
        return response()->json($games);
    }

    // Join a game as player two
    public function join(Request $request, $id) {
        $game = MultiplayerGame::findOrFail($id);
        if ($game->player_two_id) {
            return response()->json(['error' => 'MultiplayerGame already has two players'], 400);
        }
        $validated = $request->validate([
            'player_two_id' => 'required|exists:users,id',
        ]);
        $game->player_two_id = $validated['player_two_id'];
        $game->save();
        $this->startQuizGame($id);
        broadcast(new \App\Events\MultiplayerGameUpdated($game));
        return redirect()->route('games.show', ['id' => $game->id]);
    }

    private function _startQuizGame($id) {
        $game = MultiplayerGame::findOrFail($id);
        if (!$game->player_one_id || !$game->player_two_id) {
            return response()->json(['error' => 'Both players required'], 400);
        }
        // Prevent starting if already started
        if (!empty($game->questions) && count($game->questions) > 0) {
            return response()->json(['error' => 'MultiplayerGame already started'], 400);
        }
        // Fetch quizzes from each player's starred files
        $playerOne = User::find($game->player_one_id);
        $playerTwo = User::find($game->player_two_id);
        $playerOneFileIds = $playerOne->starredFiles->pluck('id');
        $playerTwoFileIds = $playerTwo->starredFiles->pluck('id');
        $playerOneQuizzes = Quiz::whereIn('file_id', $playerOneFileIds)->inRandomOrder()->limit(5)->get();
        $playerTwoQuizzes = Quiz::whereIn('file_id', $playerTwoFileIds)->inRandomOrder()->limit(5)->get();
        $questions = [];
        foreach ($playerOneQuizzes as $quiz) {
            $questions[] = [
                'quiz_id' => $quiz->id,
                'question' => $quiz->question,
                'options' => $quiz->options,
                'answers' => $quiz->answers,
                'chosen_by' => $playerOne->id,
            ];
        }
        foreach ($playerTwoQuizzes as $quiz) {
            $questions[] = [
                'quiz_id' => $quiz->id,
                'question' => $quiz->question,
                'options' => $quiz->options,
                'answers' => $quiz->answers,
                'chosen_by' => $playerTwo->id,
            ];
        }
        if (count($questions) === 0) {
            $game->status = 'finished';
            $game->game_end_reason = 'no_quizzes_found';
            $game->save();
            broadcast(new \App\Events\MultiplayerGameUpdated($game));
            return response()->json([
                'error' => 'No quizzes available for either player.',
                'suggestion' => 'Add starred files with quizzes to your account and try again.',
                'game' => $game->toArray() + ['phase' => $game->phase]
            ], 400);
        }
        shuffle($questions);
        $game->questions = $questions;
        $game->player_one_score = 0;
        $game->player_two_score = 0;
        $game->current_turn = $game->player_one_id;
        $game->status = 'active';
        $game->save();
        broadcast(new \App\Events\MultiplayerGameUpdated($game));
        return $game;
    }
    // Start a quiz game when both players are present
    public function startQuizGame($id) {
        $game = $this->_startQuizGame($id);
        if ($game instanceof \Illuminate\Http\JsonResponse) {
            return $game;
        }
        return response()->json(['game' => $game->toArray() + ['phase' => $game->phase]]);
    }

    // Answer a question
    public function answer(Request $request, $id) {
        $game = MultiplayerGame::findOrFail($id);
        if ($game->status !== 'active') {
            return response()->json(['error' => 'MultiplayerGame not active'], 400);
        }
        $validated = $request->validate([
            'player_id' => 'required|exists:users,id',
            'answer' => 'required',
        ]);
        $currentQuestion = $game->questions[0];
        $isCorrect = in_array($validated['answer'], $currentQuestion['answers']);
        if ($isCorrect) {
            if ($validated['player_id'] == $game->player_one_id) {
                $game->player_one_score++;
            } else {
                $game->player_two_score++;
            }
        } else {
            // Point to the player who chose the question
            if ($currentQuestion['chosen_by'] == $game->player_one_id) {
                $game->player_one_score++;
            } else {
                $game->player_two_score++;
            }
        }
        // Remove the question
        $remainingQuestions = $game->questions;
        array_shift($remainingQuestions);
        $game->questions = $remainingQuestions;
        // Switch turn
        $game->current_turn = ($game->current_turn == $game->player_one_id) ? $game->player_two_id : $game->player_one_id;
        // Check for win or no more questions
        if ($game->player_one_score >= 5 || $game->player_two_score >= 5 || count($remainingQuestions) === 0) {
            $game->status = 'finished';
            // Set a game_end_reason for frontend display
            if ($game->player_one_score >= 5) {
                $game->game_end_reason = 'score_limit';
            } elseif ($game->player_two_score >= 5) {
                $game->game_end_reason = 'score_limit';
            } elseif (count($remainingQuestions) === 0) {
                $game->game_end_reason = 'no_more_questions';
            }
        }
        $game->save();
        // Broadcast game state using Laravel Reverb
        broadcast(new \App\Events\MultiplayerGameUpdated($game));
        return response()->json($game->toArray() + ['phase' => $game->phase]);
    }

    // Render the game lobby, but forcefully redirect to the user's active game if they are already in one
    public function lobby(Request $request) {
        $userId = $request->user()->id;
        $activeGame = MultiplayerGame::where(function($q) use ($userId) {
            $q->where('player_one_id', $userId)
              ->orWhere('player_two_id', $userId);
        })->where('status', '!=', 'finished')->first();
        if ($activeGame) {
            return redirect()->route('games.show', ['id' => $activeGame->id]);
        }
        $games = MultiplayerGame::whereNull('player_two_id')->get();
        return inertia('Games/GameLobby', [
            'games' => $games,
        ]);
    }

    // Show a game, passing player info and handling access
    public function show(Request $request, $id) {
        $game = MultiplayerGame::with(['playerOne', 'playerTwo'])->findOrFail($id);
        $userId = $request->user()->id;
        // Only allow access if the user is a participant
        if ($game->player_one_id !== $userId && $game->player_two_id !== $userId) {
            return to_route('games.lobby');
        }
        return inertia('Games/GameShow', [
            'game' => $game,
            'playerOne' => $game->playerOne,
            'playerTwo' => $game->playerTwo,
        ]);
    }

    public function finish(Request $request, $id) {
        $game = MultiplayerGame::findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|in:finished',
            'game_end_reason' => 'nullable|string',
        ]);
        $game->status = $validated['status'];
        $game->game_end_reason = $validated['game_end_reason'] ?? null;
        $game->save();
        broadcast(new \App\Events\MultiplayerGameUpdated($game));
        return response()->json($game);
    }
}
