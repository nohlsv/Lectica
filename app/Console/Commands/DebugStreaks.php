<?php

namespace App\Console\Commands;

use App\Models\UserStudyActivity;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DebugStreaks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:streaks {--user-id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug streak calculation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = (int) $this->option('user-id');
        
        $activities = UserStudyActivity::where('user_id', $userId)
            ->orderBy('study_date', 'asc')
            ->pluck('study_date');

        $this->info("Total activities: " . $activities->count());
        
        if ($activities->isEmpty()) {
            $this->error("No activities found");
            return;
        }

        // Calculate streaks
        $longestStreak = 1;
        $currentStreak = 1;
        $streaks = [];
        $streakStart = '';

        for ($i = 1; $i < $activities->count(); $i++) {
            $current = $activities[$i];
            $previous = $activities[$i - 1];
            $daysDiff = $previous->diffInDays($current);
            
            if ($daysDiff === 1) {
                if ($currentStreak === 1) {
                    $streakStart = $previous->format('Y-m-d');
                }
                $currentStreak++;
                $longestStreak = max($longestStreak, $currentStreak);
            } else {
                if ($currentStreak > 1) {
                    $streakEnd = $activities[$i - 1]->format('Y-m-d');
                    $streaks[] = "Streak: {$streakStart} to {$streakEnd} (length: {$currentStreak})";
                }
                $currentStreak = 1;
            }
        }

        // Check if the last sequence is a streak
        if ($currentStreak > 1) {
            $streakEnd = $activities->last()->format('Y-m-d');
            $streaks[] = "Streak: {$streakStart} to {$streakEnd} (length: {$currentStreak})";
        }

        $this->info("Longest streak found: {$longestStreak}");
        $this->info("All streaks (length > 1):");
        foreach ($streaks as $streak) {
            $this->line($streak);
        }

        return Command::SUCCESS;
    }
}