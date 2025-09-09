<?php

namespace App\Services;

use App\Models\MultiplayerGame;
use App\Models\Monster;
use App\Events\MultiplayerGameUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MultiplayerGameService
{
    public function processAnswer($answerData)
    {
        try {
            return DB::transaction(function () use ($answerData) {
                // Extract data
                $gameId = $answerData['game_id'];
                $quizId = $answerData['quiz_id'];
                $answer = $answerData['answer'];
                $isCorrect = $answerData['is_correct'];
                $playerId = $answerData['player_id'];

                // Lock the game record to prevent race conditions
                $multiplayerGame = MultiplayerGame::where('id', $gameId)->lockForUpdate()->first();

                if (!$multiplayerGame) {
                    Log::error('Game not found: ' . $gameId);
                    return false;
                }

                // Validate that the player is part of this game
                if ($multiplayerGame->player_one_id !== $playerId && $multiplayerGame->player_two_id !== $playerId) {
                    Log::error('Player ' . $playerId . ' not part of game ' . $gameId);
                    return false;
                }

                // Check if game is active
                if (!$multiplayerGame->isActive()) {
                    Log::error('Game ' . $gameId . ' is not active');
                    return false;
                }

                // Validate that both players are still present
                if (!$multiplayerGame->player_two_id) {
                    Log::error('Game ' . $gameId . ' missing second player');
                    return false;
                }

                // Check if it's the player's turn
                $isPlayerOne = $multiplayerGame->player_one_id === $playerId;
                $isPlayerTwo = $multiplayerGame->player_two_id === $playerId;

                if (($isPlayerOne && $multiplayerGame->current_turn !== 1) ||
                    ($isPlayerTwo && $multiplayerGame->current_turn !== 2)) {
                    Log::error('Not player ' . $playerId . ' turn in game ' . $gameId);
                    return false;
                }

                // Double-check the game hasn't ended
                if ($multiplayerGame->isFinished()) {
                    Log::error('Game ' . $gameId . ' already finished');
                    return false;
                }

                // Update question statistics
                if ($isPlayerOne) {
                    $multiplayerGame->increment('total_questions_p1');
                    if ($isCorrect) {
                        $multiplayerGame->increment('correct_answers_p1');
                    }
                } else {
                    $multiplayerGame->increment('total_questions_p2');
                    if ($isCorrect) {
                        $multiplayerGame->increment('correct_answers_p2');
                    }
                }

                $damageDealt = 0;
                $damageReceived = 0;

                // Process damage based on game mode
                $this->processDamage($multiplayerGame, $isCorrect, $isPlayerOne, $damageDealt, $damageReceived);

                // Check win/lose conditions and handle game end
                $gameEnded = $this->checkGameEndConditions($multiplayerGame);

                // Only switch turns if game hasn't ended
                if (!$gameEnded) {
                    $multiplayerGame->switchTurn();

                    // Advance to the next question for both players
                    $multiplayerGame->advanceToNextQuestion();
                }

                // Broadcast the game update to all players
                $freshGame = $multiplayerGame->fresh();
                // Ensure pvp_mode is present in the event
                broadcast(new MultiplayerGameUpdated($freshGame, 'updated', [
                    'pvp_mode' => $freshGame->pvp_mode ?? 'accuracy',
                ]));

                Log::info('Answer processed for game ' . $gameId . ' by player ' . $playerId);
                return true;
            });
        } catch (\Exception $e) {
            Log::error('Error processing answer: ' . $e->getMessage(), [
                'answerData' => $answerData,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

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
}
