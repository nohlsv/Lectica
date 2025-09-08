<?php

namespace App\Console\Commands;

use App\Models\MultiplayerGame;
use App\Enums\MultiplayerGameStatus;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupStaleGames extends Command
{
    protected $signature = 'games:cleanup-stale {--dry-run : Show what would be cleaned without making changes} {--minutes=30 : Minutes threshold for stale games}';
    protected $description = 'Clean up stale multiplayer games that have been inactive for too long';

    public function handle()
    {
        $minutesThreshold = (int) $this->option('minutes');
        $dryRun = $this->option('dry-run');

        $this->info("Looking for games inactive for more than {$minutesThreshold} minutes...");

        // Find stale active games
        $staleGames = MultiplayerGame::where('status', MultiplayerGameStatus::ACTIVE)
            ->where('updated_at', '<', Carbon::now()->subMinutes($minutesThreshold))
            ->with(['playerOne', 'playerTwo'])
            ->get();

        // Also find waiting games that are too old (over 1 hour)
        $oldWaitingGames = MultiplayerGame::where('status', MultiplayerGameStatus::WAITING)
            ->where('created_at', '<', Carbon::now()->subHours(1))
            ->with(['playerOne'])
            ->get();

        $totalGames = $staleGames->count() + $oldWaitingGames->count();

        if ($totalGames === 0) {
            $this->info('✅ No stale games found!');
            return 0;
        }

        $this->warn("Found {$totalGames} stale games:");

        // Handle stale active games
        foreach ($staleGames as $game) {
            $this->line('');
            $this->info("🎮 Stale Active Game ID: {$game->id}");
            $this->line("Players: {$game->playerOne->first_name} vs {$game->playerTwo->first_name}");
            $this->line("Current Turn: Player {$game->current_turn}");
            $this->line("Inactive for: {$game->updated_at->diffForHumans()}");
            $this->line("Mode: {$game->game_mode}");

            if ($dryRun) {
                $this->comment('🔄 Would mark as abandoned due to inactivity');
            } else {
                try {
                    $game->markAsAbandoned();
                    $this->comment('✅ Marked as abandoned');
                } catch (\Exception $e) {
                    $this->error("❌ Failed to abandon game: {$e->getMessage()}");
                }
            }
        }

        // Handle old waiting games
        foreach ($oldWaitingGames as $game) {
            $this->line('');
            $this->info("⏳ Old Waiting Game ID: {$game->id}");
            $this->line("Creator: {$game->playerOne->first_name}");
            $this->line("Waiting for: {$game->created_at->diffForHumans()}");
            $this->line("Mode: {$game->game_mode}");

            if ($dryRun) {
                $this->comment('🔄 Would mark as abandoned (too old)');
            } else {
                try {
                    $game->markAsAbandoned();
                    $this->comment('✅ Marked as abandoned');
                } catch (\Exception $e) {
                    $this->error("❌ Failed to abandon game: {$e->getMessage()}");
                }
            }
        }

        if ($dryRun) {
            $this->line('');
            $this->info('Run without --dry-run to actually clean up the games');
        } else {
            $this->line('');
            $this->info('All stale games have been cleaned up!');
        }

        return 0;
    }
}
