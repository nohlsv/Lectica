<?php

namespace App\Http\Controllers;

use App\Models\MultiplayerGame;
use App\Models\User;
use App\Models\Quiz;
use App\Traits\TracksStudyActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

class GameController extends Controller
{
    use TracksStudyActivity;
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
        return redirect()->route('multiplayer-games.lobby');
    }

    // List games without player two
    public function openLobbies(Request $request) {
        $games = MultiplayerGame::whereNull('player_two_id')
            ->where('status', '!=', 'finished')
            ->get();

        // Return JSON for AJAX requests, redirect to appropriate page for browser requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($games);
        }

        // Redirect to game lobby for browser requests
        return redirect()->route('games.lobby');
    }

    // Join a game as player two
    public function join(Request $request, $id) {
        $game = MultiplayerGame::findOrFail($id);
        if ($game->player_two_id) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'MultiplayerGame already has two players'], 400);
            }
            return redirect()->route('games.lobby')->with('error', 'Game already has two players.');
        }
        $validated = $request->validate([
            'player_two_id' => 'required|exists:users,id',
        ]);
        $game->player_two_id = $validated['player_two_id'];
        $game->save();
        $this->startQuizGame($id);
        broadcast(new \App\Events\MultiplayerGameUpdated($game));
        return redirect()->route('multiplayer-games.show', $game->id);
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
        
        // Get all quizzes first, then filter if game has difficulty setting
        $playerOneQuizzes = Quiz::whereIn('file_id', $playerOneFileIds)->inRandomOrder();
        $playerTwoQuizzes = Quiz::whereIn('file_id', $playerTwoFileIds)->inRandomOrder();
        
        // Apply difficulty filtering if game has a difficulty setting
        if (isset($game->difficulty)) {
            $allowedType = match($game->difficulty) {
                'easy' => 'true_false',
                'medium' => 'multiple_choice',
                'hard' => 'enumeration',
                default => null
            };
            
            if ($allowedType) {
                $playerOneQuizzes = $playerOneQuizzes->where('type', $allowedType);
                $playerTwoQuizzes = $playerTwoQuizzes->where('type', $allowedType);
            }
        }
        
        $playerOneQuizzes = $playerOneQuizzes->limit(5)->get();
        $playerTwoQuizzes = $playerTwoQuizzes->limit(5)->get();
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
    public function startQuizGame(Request $request, $id) {
        $game = $this->_startQuizGame($id);
        if ($game instanceof \Illuminate\Http\JsonResponse) {
            return $game;
        }

        $response = ['game' => $game->toArray() + ['phase' => $game->phase]];

        // Return JSON for AJAX requests, redirect to appropriate page for browser requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($response);
        }

        // Redirect to game show for browser requests
        return redirect()->route('multiplayer-games.show', $game->id);
    }

    // Answer a question
    public function answer(Request $request, $id) {
        $game = MultiplayerGame::findOrFail($id);
        if ($game->status !== 'active') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'MultiplayerGame not active'], 400);
            }
            return redirect()->route('multiplayer-games.show', $id)->with('error', 'Game is not active.');
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
            
            // Award XP and track study activity for both players
            $totalQuestions = 10 - count($remainingQuestions); // Estimate original questions minus remaining
            
            // Award XP and track activity for player one
            $playerOneXP = $game->player_one_score * 15; // 15 XP per correct answer
            if ($playerOne = User::find($game->player_one_id)) {
                $playerOne->addExperience($playerOneXP);
            }
            $this->recordBattleActivity($game->player_one_id, [
                'questions_answered' => $totalQuestions,
                'correct_answers' => $game->player_one_score,
                'points_earned' => $playerOneXP,
                'time_spent_minutes' => 10, // Estimate 10 minutes for a multiplayer game
            ]);
            
            // Award XP and track activity for player two
            if ($game->player_two_id) {
                $playerTwoXP = $game->player_two_score * 15; // 15 XP per correct answer
                if ($playerTwo = User::find($game->player_two_id)) {
                    $playerTwo->addExperience($playerTwoXP);
                }
                $this->recordBattleActivity($game->player_two_id, [
                    'questions_answered' => $totalQuestions,
                    'correct_answers' => $game->player_two_score,
                    'points_earned' => $playerTwoXP,
                    'time_spent_minutes' => 10, // Estimate 10 minutes for a multiplayer game
                ]);
            }
        }
        $game->save();
        // Broadcast game state using Laravel Reverb
        broadcast(new \App\Events\MultiplayerGameUpdated($game));

        $response = $game->toArray() + ['phase' => $game->phase];

        // Return JSON for AJAX requests, redirect to appropriate page for browser requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($response);
        }

        // Redirect to game show for browser requests
        return redirect()->route('multiplayer-games.show', $id);
    }

    // Render the game lobby, but forcefully redirect to the user's active game if they are already in one
    public function lobby(Request $request) {
        $userId = $request->user()->id;
        $activeGame = MultiplayerGame::where(function($q) use ($userId) {
            $q->where('player_one_id', $userId)
              ->orWhere('player_two_id', $userId);
        })->where('status', '!=', 'finished')->first();
        if ($activeGame) {
            return redirect()->route('multiplayer-games.show', $activeGame->id);
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
            return redirect()->route('multiplayer-games.lobby');
        }
        
        // Redirect to the new multiplayer games system which has GameQuiz
        return redirect()->route('multiplayer-games.show', $game->id);
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

        // Return JSON for AJAX requests, redirect to appropriate page for browser requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($game);
        }

        // Redirect to game lobby for browser requests
        return redirect()->route('multiplayer-games.lobby')->with('success', 'Game finished successfully.');
    }

    public function forfeit(Request $request, $id) {
        $game = MultiplayerGame::findOrFail($id);
        
        // Check if user is part of this game
        if ($game->player_one_id !== auth()->id() && $game->player_two_id !== auth()->id()) {
            abort(403, 'You are not part of this game.');
        }

        // Check if game is not already finished
        if ($game->status === 'finished') {
            return redirect()->route('games.lobby')->with('info', 'Game is already finished.');
        }

        // Determine winner (opponent of the forfeiting player)
        $forfeitingPlayerId = auth()->id();
        $winnerId = $game->player_one_id === $forfeitingPlayerId ? $game->player_two_id : $game->player_one_id;
        
        // Update game status
        $game->status = 'finished';
        $game->game_end_reason = 'forfeit';
        
        // Set winner's score to winning score if not already
        if ($game->player_one_id === $winnerId && $game->player_one_score < 5) {
            $game->player_one_score = 5;
        } elseif ($game->player_two_id === $winnerId && $game->player_two_score < 5) {
            $game->player_two_score = 5;
        }
        
        $game->save();
        
        // Broadcast the game update
        broadcast(new \App\Events\MultiplayerGameUpdated($game));

        // Return JSON for AJAX requests, redirect to appropriate page for browser requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Game forfeited successfully.',
                'game' => $game
            ]);
        }

        return redirect()->route('multiplayer-games.lobby')->with('success', 'Game forfeited successfully.');
    }
}
